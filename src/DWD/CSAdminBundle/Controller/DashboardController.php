<?php

namespace DWD\CsAdminBundle\Controller;

use DWD\CsAdminBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use DWD\CsAdminBundle\Entity\Product;

/**
 * Class DashboardController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/admin")
 */
class DashboardController extends Controller
{
    /**
     *
     * @Route("/",name="dwd_csadmin_dashboard")
     */
    public function indexAction(Request $request)
    { 
    	$errMsg          = $this->getRequest()->get('errMsg', "");
        return $this->render('DWDCsAdminBundle:Dashboard:index.html.twig', array(
        	'errMsg'     => $errMsg
        ));
    }
}