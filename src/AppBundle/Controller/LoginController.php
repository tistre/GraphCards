<?php

namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class LoginController extends Controller
{
    /** @var LoggerInterface */
    protected $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request): Response
    {
        $request->getSession()->set('oauth_info', [
            'authenticated' => true,
            'provider' => 'dummy',
            'access_token' => '',
            'name' => 'Dummy User',
            'mail' => 'dummy@example.com',
            'image' => '',
            'url' => ''
        ]);

        // TODO: Fix hardcoded URL
        return new RedirectResponse('/graphcards/nodes/list');
    }


    /**
     * TODO: Who on earth insists on redirecting to login/ ?
     *
     * @Route("/login/")
     * @param Request $request
     * @return Response
     */
    public function loginSlashAction(Request $request): Response
    {
        return $this->loginAction($request);
    }
}
