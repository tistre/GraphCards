<?php

namespace AppBundle\Controller;

use AppBundle\DataSource;
use AppBundle\Form\NodeFormType;
use AppBundle\Form\NodeSearchFormData;
use AppBundle\Form\NodeSearchFormType;
use AppBundle\Service\DbAdapterService;
use AppBundle\Service\SearchService;
use AppBundle\ViewModel\NodeViewModel;
use AppBundle\ViewModel\RelationshipViewModel;
use GraphCards\Model\Node;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class NodeController extends Controller
{
    /**
     * @Route("/nodes/list", name="listNodes")
     * @param Request $request
     * @return Response
     */
    public function listNodesAction(Request $request): Response
    {
        $searchFormData = new NodeSearchFormData();
        $searchForm = $this->createForm(NodeSearchFormType::class, $searchFormData);

        $searchForm->handleRequest($request);

        $searchLabel = '';
        $searchQuery = '';

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            if ($searchFormData->label !== null) {
                $searchLabel = $searchFormData->label;
            }

            if ($searchFormData->q !== null) {
                $searchQuery = $searchFormData->q;
            }
        }

        $page = max(1, $request->query->getInt('p', 1));
        $pageSize = 20;

        $tplVars = [];
        $tplVars['searchForm'] = $searchForm->createView();
        $tplVars['nodes'] = [];

        $tplVars['page'] = $page;
        $tplVars['previousPage'] = max(1, ($page - 1));

        // Fetch one too much to find out whether more is available,
        // i.e. whether to enable "next page"

        $tplVars['nextPage'] = $page;

        $nodes = $this->searchNodes($searchLabel, $searchQuery, $page, $pageSize, $moreAvailable);

        if ($moreAvailable) {
            $tplVars['nextPage'] = $page + 1;
        }

        foreach ($nodes as $node) {
            $tplVars['nodes'][] = new NodeViewModel(
                $node,
                $this->get('twig'),
                $this->getParameter('display_templates')
            );
        }

        $pageUrlParams = [
            'node_search_form' => [
                'label' => $searchFormData->label,
                's' => ''
            ]
        ];

        $tplVars['previousPageUrl'] = $this->generateUrl(
            'listNodes',
            array_merge($pageUrlParams, ['p' => $tplVars['previousPage']])
        );

        $tplVars['nextPageUrl'] = $this->generateUrl(
            'listNodes',
            array_merge($pageUrlParams, ['p' => $tplVars['nextPage']])
        );

        return $this->render('default/nodes_list.html.twig', $tplVars);
    }


    /**
     * @Route("/node/{nodeUuid}", name="viewNode")
     * @param Request $request
     * @param string $nodeUuid
     * @return Response
     */
    public function viewNodeAction(Request $request, string $nodeUuid): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $tplVars = [];
        $tplVars['nodeUuid'] = $nodeUuid;

        $nodeViewModel = new NodeViewModel(
            $dbAdapter->loadNode($nodeUuid),
            $this->get('twig'),
            $this->getParameter('display_templates')
        );

        $tplVars['node'] = $nodeViewModel;

        $tplVars['nodeRelationships'] = [];

        foreach ($dbAdapter->listNodeRelationships($nodeUuid, true) as $relationship) {
            $relationshipViewModel = new RelationshipViewModel(
                $relationship,
                $this->get('twig'),
                $this->getParameter('display_templates')
            );

            if ($relationshipViewModel->getSourceNode()->getUuid() === $nodeViewModel->getUuid()) {
                $key = 'source';
            } else {
                $key = 'target';
            }

            $type = $relationshipViewModel->getType();

            if (!isset($tplVars['nodeRelationships'][$type])) {
                $tplVars['nodeRelationships'][$type] = [
                    'source' => [],
                    'target' => []
                ];
            }

            $tplVars['nodeRelationships'][$type][$key][] = $relationshipViewModel;
        }

        return $this->render('default/node_view.html.twig', $tplVars);
    }


    /**
     * @Route("/node/{nodeUuid}/edit", name="editNode")
     * @param Request $request
     * @param string $nodeUuid
     * @return Response
     */
    public function editNodeAction(Request $request, string $nodeUuid): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();
        $dataSource = new DataSource($dbAdapter);

        $tplVars = [];
        $tplVars['node'] = $dbAdapter->loadNode($nodeUuid);
        $tplVars['nodeUuid'] = $nodeUuid;

        $formData = $dataSource->getEditNodeFormData($nodeUuid);

        $form = $this->createForm(NodeFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $node = $dataSource->updateNodeFromFormData($formData);

                return $this->redirectToRoute
                (
                    'viewNode',
                    ['nodeUuid' => $node->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();
        $tplVars['allNodeLabels'] = $dbAdapter->listNodeLabels();
        $tplVars['allPropertyKeys'] = $dbAdapter->listPropertyKeys();

        return $this->render('default/node_edit.html.twig', $tplVars);
    }


    /**
     * @param string $searchLabel
     * @param string $searchQuery
     * @param int $page
     * @param int $pageSize
     * @param bool $moreAvailable
     * @return Node[]
     */
    protected function searchNodes(string $searchLabel, string $searchQuery, int $page, int $pageSize, &$moreAvailable): array
    {
        $moreAvailable = false;

        /** @var LoggerInterface $logger */
        $logger = $this->get('logger');

        /** @var SearchService $searchService */
        $searchService = $this->get('AppBundle\Service\SearchService');

        $params = [
            'index' => 'neo4j-index-node',
            'size' => $pageSize,
            'from' => ($pageSize * ($page - 1))
        ];

        if ($searchLabel !== '') {
            $params['type'] = $searchLabel;
        }

        if ($searchQuery !== '') {
            $params['body'] = [
                'query' => [
                    'simple_query_string' => [
                        'query' => $searchQuery,
                        'default_operator' => 'and'
                    ]
                ]
            ];
        }

        try {
            $client = $searchService->getClient();
            $response = $client->search($params);
        } catch (\Exception $exception) {
            $logger->error('Elasticsearch exception: ' . $exception->getMessage(), $exception->getTrace());
            return [];
        }

        // TODO: Fix this hack
        $moreAvailable = ($response['hits']['total'] > $pageSize);

        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $nodes = [];

        foreach ($response['hits']['hits'] as $hit) {
            $nodes[] = $dbAdapter->loadNode($hit['_id']);
        }

        return $nodes;
    }
}
