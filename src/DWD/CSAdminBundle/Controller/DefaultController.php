<?php

namespace DWD\CsAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DWDCsAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
