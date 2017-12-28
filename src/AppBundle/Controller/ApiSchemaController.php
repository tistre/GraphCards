<?php

namespace AppBundle\Controller;

use AppBundle\Service\DbAdapterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class ApiSchemaController extends Controller
{
    /**
     * @Route("/api/schema/nodeLabels/list", name="listNodeLabels")
     * @param Request $request
     * @return Response
     */
    public function listNodeLabelsAction(Request $request): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = [];
        $tplVars['nodeLabels'] = $dbAdapter->listNodeLabels();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('schema_nodelabels_list.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/schema/relationshipTypes/list", name="listRelationshipTypes")
     * @param Request $request
     * @return Response
     */
    public function listRelationshipTypesAction(Request $request): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = [];
        $tplVars['relationshipTypes'] = $dbAdapter->listRelationshipTypes();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('schema_relationshiptypes_list.html.twig', $tplVars, $response);
    }


    /**
     * @Route("/api/schema/propertyKeys/list", name="listPropertyKeys")
     * @param Request $request
     * @return Response
     */
    public function listPropertyKeysAction(Request $request): Response
    {
        /** @var DbAdapterService $dbAdapterService */
        $dbAdapterService = $this->get('AppBundle\Service\DbAdapterService');
        $dbAdapter = $dbAdapterService->getDbAdapter();

        $response = new Response();

        $tplVars = [];
        $tplVars['propertyKeys'] = $dbAdapter->listPropertyKeys();

        $response->headers->set('Content-Type', 'application/xhtml+xml; charset=UTF-8');

        return $this->render('schema_propertykeys_list.html.twig', $tplVars, $response);
    }
}