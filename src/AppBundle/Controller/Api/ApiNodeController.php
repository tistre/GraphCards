<?php

namespace AppBundle\Controller\Api;

use AppBundle\DataSource;
use AppBundle\Form\NodeFormType;
use AppBundle\Form\NodeSearchFormData;
use AppBundle\Form\NodeSearchFormType;
use AppBundle\Service\DbAdapterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ApiNodeController extends Controller
{
    /**
     * @Route("/api/nodes/list", name="apiListNodes")
     * @param Request $request
     * @return Response
     */
    public function listNodesAction(Request $request): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $searchFormData = new NodeSearchFormData();
        $searchForm = $this->createForm(NodeSearchFormType::class, $searchFormData);

        $searchForm->handleRequest($request);

        $searchLabel = '';

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            if ($searchFormData->label !== null) {
                $searchLabel = $searchFormData->label;
            }
        }

        $tplVars = ['searchForm' => $searchForm->createView()];

        // TODO: Add support for $skip, $limit
        $tplVars['nodes'] = $dbAdapter->listNodes($searchLabel);

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('Api/nodes_list.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/nodes/add", name="apiAddNode")
     * @param Request $request
     * @return Response
     */
    public function addNodeAction(Request $request): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();
        $dataSource = new DataSource($dbAdapter);

        $response = new Response();

        $tplVars = [];

        $formData = $dataSource->getAddNodeFormData();

        // Support ?label=LABEL for pre-filling
        if ($request->query->has('label')) {
            array_unshift($formData->labels, $request->query->get('label'));
        }

        $form = $this->createForm(NodeFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $node = $dataSource->createNodeFromFormData($formData);

                return $this->redirectToRoute
                (
                    'apiViewNode',
                    ['nodeUuid' => $node->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();
        $tplVars['allNodeLabels'] = $dbAdapter->listNodeLabels();
        $tplVars['allPropertyKeys'] = $dbAdapter->listPropertyKeys();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('Api/node_add.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/node/{nodeUuid}/edit", name="apiEditNode")
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

        $response = new Response();

        $tplVars = ['nodeUuid' => $nodeUuid];

        $formData = $dataSource->getEditNodeFormData($nodeUuid);

        $form = $this->createForm(NodeFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $node = $dataSource->updateNodeFromFormData($formData);

                return $this->redirectToRoute
                (
                    'apiViewNode',
                    ['nodeUuid' => $node->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();
        $tplVars['allNodeLabels'] = $dbAdapter->listNodeLabels();
        $tplVars['allPropertyKeys'] = $dbAdapter->listPropertyKeys();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('Api/node_edit.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/node/{nodeUuid}", name="apiViewNode")
     * @param Request $request
     * @param string $nodeUuid
     * @return Response
     */
    public function viewNodeAction(Request $request, string $nodeUuid): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = ['nodeUuid' => $nodeUuid];
        $tplVars['node'] = $dbAdapter->loadNode($nodeUuid);
        $tplVars['relationships'] = $dbAdapter->listNodeRelationships($nodeUuid);

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('Api/node_view.html.twig', $tplVars, $response);
    }
}