<?php

namespace AppBundle\Controller;

use AppBundle\Form\NodeSearchFormData;
use AppBundle\Form\NodeSearchFormType;
use AppBundle\Service\DbAdapterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
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

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', $tplVars);
    }
}
