<?php

namespace DWD\CSAdminBundle\Controller;

use DWD\CSAdminBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DWD\CSAdminBundle\Entity\Product;

/**
 * Class DashboardController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/admin")
 */
class DashboardController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @Route("/",name="dwd_csadmin_dashboard")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('DWDCSAdminBundle:Product')->findAll();

        $paginator = $this->get('knp_paginator');
        $products = $paginator->paginate($qb, $request->query->getInt('page', 1));

        return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
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
        return $this->render('DWDCSAdminBundle:Dashboard:show.html.twig', array(
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

        return $this->render('DWDCSAdminBundle:Dashboard:edit.html.twig', array(
            'product'        => $product,
            'edit_form'      => $editForm->createView()
        ));
    }

    /**
     * List add Products entities by JSON
     *
     * @Route("/products", name="dwd_csadmin_product_list_json")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $page = $request->query->get('page') | 1;

        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('DWDCSAdminBundle:Product')->findAll();

        $paginator = $this->get('knp_paginator');
        $products = $paginator->paginate($qb, $request->query->getInt('page', $page));

        $arrayProducts = array();
        foreach ($products as $product) {
            $itemProduct['id'] = $product->getId();
            $itemProduct['name'] = $product->getName();
            $itemProduct['price'] = $product->getPrice();
            $itemProduct['description'] = $product->getDescription();
            $arrayProducts []= $itemProduct;
        }

        $response = new Response();
        $response->setContent(json_encode($arrayProducts));
        return $response;
    }
}