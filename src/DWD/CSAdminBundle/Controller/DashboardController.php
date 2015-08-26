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
     * Lists all Product entities.
     *
     * @Route("/",name="dwd_csadmin_dashboard")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('DWDCsAdminBundle:Product')->findAll();

        return $this->render('DWDCsAdminBundle:Dashboard:index.html.twig', array(
            'products'      => $products
        ));
    }

    /**
     * Finds and displays a Product entity
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="dwd_csadmin_product_show")
     * @Method("GET")
     */
    public function showAction(Product $product)
    {
        return $this->render('DWDCsAdminBundle:Dashboard:show.html.twig', array(
            'product'      => $product,
        ));
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="dwd_csadmin_product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Product $product, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(new ProductType(), $product);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->flush();

            return $this->redirectToRoute('dwd_csadmin_product_edit', array('id' => $product->getId()));
        }

        return $this->render('DWDCsAdminBundle:Dashboard:edit.html.twig', array(
            'product'        => $product,
            'edit_form'      => $editForm->createView()
        ));
    }
}