<?php

namespace AppBundle\Controller;

use AppBundle\DataSource;
use AppBundle\Form\NodeFormType;
use AppBundle\Form\NodeSearchFormData;
use AppBundle\Form\NodeSearchFormType;
use AppBundle\Service\DbAdapterService;
use GraphCards\Db\Db;
use GraphCards\Db\DbAdapter;
use GraphCards\Db\DbConfig;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ApiNodeController extends Controller
{
    /**
     * @Route("/api/nodes/list", name="listNodes")
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
            $searchLabel = $searchFormData->label;
        }

        $tplVars = ['searchForm' => $searchForm->createView()];
        $tplVars['nodes'] = $dbAdapter->listNodes($searchLabel);

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('nodes_list.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/nodes/add", name="addNode")
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

        $form = $this->createForm(NodeFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $node = $dataSource->createNodeFromFormData($formData);

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

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('node_add.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/node/{nodeUuid}/edit", name="editNode")
     */
    public function editNodeAction(Request $request, $nodeUuid): Response
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
                    'viewNode',
                    ['nodeUuid' => $node->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('node_edit.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/node/{nodeUuid}", name="viewNode")
     */
    public function viewNodeAction(Request $request, $nodeUuid): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = ['nodeUuid' => $nodeUuid];
        $tplVars['node'] = $dbAdapter->loadNode($nodeUuid);
        $tplVars['relationships'] = $dbAdapter->listNodeRelationships($nodeUuid);

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('node_view.html.twig', $tplVars, $response);
    }
}