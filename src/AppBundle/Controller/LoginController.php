<?php

namespace AppBundle\Controller;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tistre\SimpleOAuthLogin\Login;
use Tistre\SimpleOAuthLogin\OAuthInfo;


class LoginController extends Controller
{
    /** @var LoggerInterface */
    protected $logger;


    /**
     * LoginController constructor.
     * @param LoggerInterface $logger
     */
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
        $oAuthInfo = (new OAuthInfo([]))
            ->setAuthenticated(true)
            ->setProvider('dummy')
            ->setName('Dummy User')
            ->setMail('dummy@example.com');

        $request->getSession()->set('oauth_info', $oAuthInfo->getArray());

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


    /**
     * @Route("/login/{service}", name="serviceLogin")
     * @param Request $request
     * @return Response
     */
    public function serviceLoginAction(Request $request, $service): Response
    {
        $oauthLogin = new Login();
        $oauthLogin->addServiceConfigsFromArray($this->getParameter('oauth_configs'));

        if ($request->getMethod() === 'GET') {
            if ($request->query->has('code')) {
                return $this->onReturnFromService($request, $oauthLogin, $service);
            } elseif ($request->query->has('error')) {
                $this->logger->error(__METHOD__ . ': Service returned error: ' . $request->query->get('error'));
                return new Response("Error. See log file for details.\n", 400);
            } else {
                return $this->redirectToService($request, $oauthLogin, $service);
            }
        } else {
            $this->logger->error(__METHOD__ . ': Unsupported request method ' . $request->getMethod());
            return new Response("Error. See log file for details.\n", 400);
        }
    }


    /**
     * @param Request $request
     * @param Login $oauthLogin
     * @param $service
     * @return Response
     */
    protected function redirectToService(Request $request, Login $oauthLogin, $service): Response
    {
        $oauthService = $oauthLogin->getService($service);

        $authorizationUrl = $oauthService->getAuthorizationUrl();

        // The OAuth library automatically generates a state value that we can
        // validate later. We just save it for now.

        $oAuthInfo = new OAuthInfo($request->getSession()->get('oauth_info'));

        $oAuthInfo
            ->setState($oauthService->getProvider()->getState())
            ->setRedirectAfterlogin($request->query->get('redirect_after_login', ''));

        $request->getSession()->set('oauth_info', $oAuthInfo->getArray());

        return new RedirectResponse($authorizationUrl);
    }


    /**
     * @param Request $request
     * @param Login $oauthLogin
     * @param $service
     * @return Response
     */
    protected function onReturnFromService(Request $request, Login $oauthLogin, $service): Response
    {
        $oAuthInfo = new OAuthInfo($request->getSession()->get('oauth_info'));

        // Validate the OAuth state parameter

        $state = $request->query->get('state');

        if ((strlen($state) === 0) || ($state !== $oAuthInfo->getState())) {
            $oAuthInfo->setState('');
            $request->getSession()->set('oauth_info', $oAuthInfo->getArray());

            $this->logger->error(__METHOD__ . ': State value does not match the one initially sent');

            return new Response("Error. See log file for details.\n", 400);
        }

        // With the authorization code, we can retrieve access tokens and other data.
        try {
            $oauthService = $oauthLogin->getService($service);

            // Get an access token using the authorization code grant
            $accessToken = $oauthService->getAuthorizationCodeAccessToken($_GET['code']);

            // We got an access token, let's now get the user's details
            $userDetails = $oauthService->getUserDetails($accessToken);

            $oAuthInfo
                ->setAuthenticated(true)
                ->setProvider($oauthService->getService())
                ->setAccessToken($accessToken->getToken())
                ->setName($userDetails['name'])
                ->setMail($userDetails['mail'])
                ->setImage($userDetails['image'])
                ->setUrl($userDetails['url']);

            $request->getSession()->set('oauth_info', $oAuthInfo->getArray());

            $redirect = $oAuthInfo->getRedirectAfterlogin();

            // TODO: Fix hardcoded URL
            if (strlen($redirect) === 0) {
                $redirect = '/graphcards/nodes/list';
            }

            return new RedirectResponse($redirect);
        } catch (IdentityProviderException $e) {
            $this->logger->error(__METHOD__ . ': Something went wrong, couldn\'t get tokens: ' . $e->getMessage());

            return new Response("Error. See log file for details.\n", 400);
        }
    }
}
