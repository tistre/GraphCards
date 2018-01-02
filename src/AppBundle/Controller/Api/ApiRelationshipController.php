<?php

namespace AppBundle\Controller\Api;

use AppBundle\DataSource;
use AppBundle\Form\RelationshipFormType;
use AppBundle\Service\DbAdapterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ApiRelationshipController extends Controller
{
    /**
     * @Route("/api/relationships/list", name="apiListRelationships")
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

        return $this->render('Api/relationships_list.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/relationships/add", name="apiAddRelationship")
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
                    'apiViewRelationship',
                    ['relationshipUuid' => $relationship->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();
        $tplVars['allRelationshipTypes'] = $dbAdapter->listRelationshipTypes();
        $tplVars['allPropertyKeys'] = $dbAdapter->listPropertyKeys();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('Api/relationship_add.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/relationship/{relationshipUuid}/edit", name="apiEditRelationship")
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
                    'apiViewRelationship',
                    ['relationshipUuid' => $relationship->getUuid()]
                );
            } catch (\Exception $exception) {
                $form->addError(new FormError($exception->getMessage()));
            }
        }

        $tplVars['form'] = $form->createView();
        $tplVars['allRelationshipTypes'] = $dbAdapter->listRelationshipTypes();
        $tplVars['allPropertyKeys'] = $dbAdapter->listPropertyKeys();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('Api/relationship_edit.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/relationship/{relationshipUuid}", name="apiViewRelationship")
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

        return $this->render('Api/relationship_view.html.twig', $tplVars, $response);
    }
}