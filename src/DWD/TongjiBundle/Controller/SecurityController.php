<?php

namespace DWD\TongjiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="dwd_tongji_form")
     */
    public function loginAction()
    {
        $helper = $this->get('security.authentication_utils');

        return $this->render('DWDTongjiBundle:Security:login.html.twig', array(
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ));
    }

    /**
     * This is the route the login form submits to.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the login automatically. See form_login in app/config/security.yml
     *
     * @Route("/login_check", name="dwd_tongji_login_check")
     */
    public function loginCheckAction(Request $request)
    {
//        $username =  $request->query->get('_username');
//        $password = $request->query->get('_password');
//        $dwdAPI = $this->container->get('dwdapi');
//        $result = $dwdAPI->login_brandadmin( array( 'username' => $username, 'password' => $password ) );

        throw new \Exception('This should never be reached!');
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout", name="dwd_tongji_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }
}