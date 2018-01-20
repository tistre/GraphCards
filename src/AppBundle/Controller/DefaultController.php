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


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        return $this->render('default/index.html.twig');
    }
}
