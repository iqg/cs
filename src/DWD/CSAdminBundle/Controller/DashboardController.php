<?php

namespace DWD\CSAdminBundle\Controller;

use DWD\CSAdminBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DWD\CSAdminBundle\Entity\Product;
use DWD\DataBundle\Document\Store;
use Overtrue\Pinyin\Pinyin;

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

    /**
     * @Route("/autocomplete-product/search", name="dwd_csadmin_autocomplete_product_search")
     */
    public function autocompleteProductSearchAction(Request $request)
    {
        $q = $request->get('term');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $products = $qb->select('p')->from('DWD\CSAdminBundle\Entity\Product', 'p')
            ->where(
                $qb->expr()->like('p.name', $qb->expr()->literal('%' . $q . '%'))
            )
            ->getQuery()
            ->getResult();

        $arrayProducts = array();
        foreach ($products as $product) {
            $itemProduct['id'] = $product->getId();
            $itemProduct['label'] = $product->getName();
            $itemProduct['name'] = $product->getName();
            $arrayProducts []= $itemProduct;
        }

        $response = new Response();
        $response->setContent(json_encode($arrayProducts));
        return $response;
    }

    /**
     * @Route("/autocomplete-product/{id}", requirements={"id" = "\d+"}, name="dwd_csadmin_autocomplete_product_get")
     * @Method("GET")
     */
    public function autocompleteProductGetAction(Product $product)
    {
        return new Response($product->getName());
    }

    /**
     * 显示10条的话，有些重要的门店可能永远显示不出来了
     * 如果是混合的关键字，全部转化成拼音
     * @Route("/autocomplete-branch/search", name="dwd_csadmin_autocomplete_branch_search")
     */
    public function autocompleteBranchSearchAction(Request $request)
    {
        $q = $request->get('term');
        $mb_size = mb_strlen($q, 'UTF-8');
        if ( $mb_size < 3 ) {
            $response = new Response();
            $response->setContent(json_encode([]));
            return $response;
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $resultByName = $dm->getRepository('DWDDataBundle:Store')->findByName(array('$regex' => $q));
        $resultByPinyin = $dm->getRepository('DWDDataBundle:Store')->findByPinyin(array('$regex' => $q));
        $result = array_merge($resultByName, $resultByPinyin);

        $resultHash = array();
        $arrayResult = array();
        foreach ($result as $record) {
            if (isset($resultHash[$record->getBranchId()])) {
                continue;
            }
            $branchInfo['id'] = $record->getBranchId();
            $branchInfo['label'] = $record->getName();
            $branchInfo['name'] = $record->getName();
            $branchInfo['pinyin'] = $record->getPinyin();
            $arrayResult []= $branchInfo;
            $resultHash[$record->getBranchId()] = True;
        }

        $response = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }

    /**
     * @Route("/autocomplete-branch/{branch_id}", name="dwd_csadmin_autocomplete_branch_get")
     * @Method("GET")
     */
    public function autocompleteBranchGetAction($branch_id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $store = $dm->getRepository('DWDDataBundle:Store')->findOneBy( array('branch_id' => intval($branch_id)) );
        return new Response($store->getName());
    }
}