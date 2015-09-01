<?php

namespace DWD\CSAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DWDCSAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
