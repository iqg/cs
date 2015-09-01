<?php

namespace DWD\DataBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DWD\DataBundle\Document\Store;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        $store = new Store();
        $store->setName('A Foo Bar');
        $store->setPinyin(array('afoobar', 'afb'));

        $stores = $this->get('doctrine_mongodb')
            ->getRepository('DWDDataBundle:Store')
            ->findAll();

        var_dump($stores);

        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($store);
        $dm->flush();

        return array('name' => $name);
    }
}
