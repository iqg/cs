<?php

namespace DWD\CSAdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchController
 * @package DWD\CSAdminBundle\Controller
 */
class SearchController extends Controller
{
    /**
     * autocomplete函数 门店名称的模糊查询
     *
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
        $resultByPinyin = array();
        if ( !preg_match("/([\x81-\xfe][\x40-\xfe])/", $q) ) {
            $resultByPinyin = $dm->getRepository('DWDDataBundle:Store')->findByPinyin(array('$regex' => $q));
        }
        $result = array_merge($resultByName, $resultByPinyin);

        $resultHash = array();
        $arrayResult = array();
        foreach ($result as $record) {
            if (isset($resultHash[$record->getBranchId()])) {
                continue;
            }
            $branchInfo = array();
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
     * autocomplete函数 根据门店ID获取门店名称
     *
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