<?php

namespace AppBundle\Controller;

use AppBundle\DataSource;
use AppBundle\Form\RelationshipFormType;
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


class ApiRelationshipController extends Controller
{
    /**
     * @Route("/api/relationships/list", name="listRelationships")
     * @param Request $request
     * @return Response
     */
    public function listRelationshipsAction(Request $request)
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = [];
        $tplVars['relationships'] = $dbAdapter->listRelationships(20);

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('relationships_list.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/relationships/add", name="addRelationship")
     * @param Request $request
     * @return Response
     */
    public function addRelationshipAction(Request $request)
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();
        $dataSource = new DataSource($dbAdapter);

        $response = new Response();

        $tplVars = [];

        $formData = $dataSource->getAddRelationshipFormData();

        // Support ?type=TYPE for pre-filling
        if ($request->query->has('type')) {
            $formData->type = $request->query->get('type');
        }

        $form = $this->createForm(RelationshipFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $relationship = $dataSource->createRelationshipFromFormData($formData);

                return $this->redirectToRoute
                (
                    'editRelationship',
                    ['relationshipUuid' => $relationship->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('relationship_add.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/relationship/{relationshipUuid}/edit", name="editRelationship")
     * @param Request $request
     * @param string $relationshipUuid
     * @return Response
     */
    public function editRelationshipAction(Request $request, string $relationshipUuid)
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();
        $dataSource = new DataSource($dbAdapter);

        $response = new Response();

        $tplVars = ['relationshipUuid' => $relationshipUuid];

        $formData = $dataSource->getEditRelationshipFormData($relationshipUuid);

        $form = $this->createForm(RelationshipFormType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $relationship = $dataSource->updateRelationshipFromFormData($formData);

                return $this->redirectToRoute
                (
                    'viewRelationship',
                    ['relationshipUuid' => $relationship->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('relationship_edit.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/relationship/{relationshipUuid}", name="viewRelationship")
     * @param Request $request
     * @param string $relationshipUuid
     * @return Response
     */
    public function viewRelationshipAction(Request $request, string $relationshipUuid)
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = ['relationshipUuid' => $relationshipUuid];
        $tplVars['relationship'] = $dbAdapter->loadRelationship($relationshipUuid);

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('relationship_view.html.twig', $tplVars, $response);
    }
}