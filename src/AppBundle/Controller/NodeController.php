<?php

namespace AppBundle\Controller;

use AppBundle\DataSource;
use AppBundle\Form\NodeFormType;
use AppBundle\Form\NodeSearchFormData;
use AppBundle\Form\NodeSearchFormType;
use AppBundle\Service\DbAdapterService;
use AppBundle\ViewModel\NodeViewModel;
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
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $searchFormData = new NodeSearchFormData();
        $searchForm = $this->createForm(NodeSearchFormType::class, $searchFormData);

        $searchForm->handleRequest($request);

        $searchLabel = '';

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            if ($searchFormData->label !== null) {
                $searchLabel = $searchFormData->label;
            }
        }

        $page = max(1, $request->query->getInt('p', 1));
        $pageSize = 20;
        $skip = $pageSize * ($page - 1);

        $tplVars = [];
        $tplVars['searchForm'] = $searchForm->createView();
        $tplVars['nodes'] = [];

        $tplVars['page'] = $page;
        $tplVars['previousPage'] = max(1, ($page - 1));

        // Fetch one too much to find out whether more is available,
        // i.e. whether to enable "next page"

        $tplVars['nextPage'] = $page;

        $query = $dbAdapter->buildNodeQuery(
            $searchLabel,
            $skip,
            ($pageSize + 1)
        );

        foreach ($dbAdapter->listNodes($query) as $node) {
            if (count($tplVars['nodes']) === $pageSize) {
                $tplVars['nextPage'] = $page + 1;
                break;
            }

            $tplVars['nodes'][] = new NodeViewModel($node);
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
        $tplVars['node'] = new NodeViewModel($dbAdapter->loadNode($nodeUuid));
        $tplVars['relationships'] = $dbAdapter->listNodeRelationships($nodeUuid);

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
}
