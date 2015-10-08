<?php

namespace DWD\CSAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DWD\DataBundle\Document\Store;
use Overtrue\Pinyin\Pinyin;
use Doctrine\ODM\MongoDB;
/**
 * Class DashboardController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
 */
class SearchController extends Controller
{
    
    /** 
     * 如果是混合的关键字，全部转化成拼音
     * @Route("/autocomplete-branchname/search", name="dwd_csadmin_autocomplete_branchname_search")
     */
    public function autocompleteBranchNameSearchAction(Request $request)
    {
        $dataHttp       = $this->get('dwd.data.http');
        $q              = $request->get('term');
        $mb_size        = mb_strlen($q, 'UTF-8');
        if ( $mb_size < 3 ) {
            $response = new Response();
            $response->setContent(json_encode([]));
            return $response;
        }

        $dm             = $this->get('doctrine_mongodb')->getManager();
        $resultByName   = $dm->getRepository('DWDDataBundle:Store')->findByName(array('$regex' => $q));
        $resultByPinyin = $dm->getRepository('DWDDataBundle:Store')->findByPinyin(array('$regex' => $q));
        $result         = array_merge($resultByName, $resultByPinyin);

        $resultHash     = array();
        $arrayResult    = array();

        foreach ($result as $record) {
            if (isset($resultHash[$record->getBranchId()])) {
                continue;
            }
            $branchInfo           = array();
            $branchInfo['id']     = $record->getBranchId();
            $branchInfo['label']  = $record->getName(); 
            $arrayResult[]        = $branchInfo;
            $resultHash[$record->getBranchId()] = True;
        }

        $response                 = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }

    /** 
     * 如果是混合的关键字，全部转化成拼音
     * @Route("/autocomplete-branch/search", name="dwd_csadmin_autocomplete_branch_search")
     */
    public function autocompleteBranchSearchAction(Request $request)
    {
        $dataHttp       = $this->get('dwd.data.http');
        $q              = $request->get('term');
        $mb_size        = mb_strlen($q, 'UTF-8');
        if ( $mb_size < 3 ) {
            $response = new Response();
            $response->setContent(json_encode([]));
            return $response;
        }

        $dm             = $this->get('doctrine_mongodb')->getManager();
        $resultByName   = $dm->getRepository('DWDDataBundle:Store')->findByName(array('$regex' => $q));
        $resultByPinyin = $dm->getRepository('DWDDataBundle:Store')->findByPinyin(array('$regex' => $q));
        $result         = array_merge($resultByName, $resultByPinyin);

        $resultHash     = array();
        $arrayResult    = array();

        $data           = array(
                              array(
                                  'url'    => '/branch/branchInfo',
                                  'data'   => array(
                                      'redeemNumber'    => $q,
                                  ),
                                  'method' => 'get',
                                  'key'    => 'redeemNumber',
                              ), 
                              array(
                                  'url'    => '/branch/branchInfo',
                                  'data'   => array(
                                      'branchId'     => $q,
                                  ),
                                  'method' => 'get',
                                  'key'    => 'branchId',
                              ),
                          );
      
        $data              = $dataHttp->MutliCall( $data );  

        if( false == empty( $data['redeemNumber']['data'] ) && $data['redeemNumber']['errno'] == 0 ){
            $branchInfo    = array(
                                'id'       => $data['redeemNumber']['data']['id'],
                                'label'    => '兑换码搜索: ' . $data['redeemNumber']['data']['name'],
                             );
            $arrayResult[] = $branchInfo;
        }


        if( false == empty( $data['branchId']['data'] ) && $data['branchId']['errno'] == 0 ){
            $branchInfo    = array(
                                'id'       => $data['branchId']['data']['id'],
                                'label'    => '门店id搜索: ' . $data['branchId']['data']['name'],
                             );
            $arrayResult[] = $branchInfo;
        }

        foreach ($result as $record) {
            if (isset($resultHash[$record->getBranchId()])) {
                continue;
            }
            $branchInfo           = array();
            $branchInfo['id']     = $record->getBranchId();
            $branchInfo['label']  = '门店模糊搜索: ' . $record->getName(); 
            $arrayResult[]        = $branchInfo;
            $resultHash[$record->getBranchId()] = True;
        }

        $response                 = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }
 
    /**
     * 搜索用户
     * @Route("/autocomplete-user/search", name="dwd_csadmin_autocomplete_user_search")
     */
    public function autocompleteUserSearchAction(Request $request)
    {
        $dataHttp       = $this->get('dwd.data.http');
        $q              = $request->get('term');
      
        $data           = array(
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'userId'         => $q,
                ),
                'method' => 'get',
                'key'    => 'userId',
            ),
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'mobile'         => $q,
                ),
                'method' => 'get',
                'key'    => 'mobile',
            ),
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'redeemNum'      => $q, 
                ),
                'method' => 'get',
                'key'    => 'redeem',
            ),
        );

        $data              = $dataHttp->MutliCall( $data ); 
     
        $arrayResult       = array();

        if( false == empty( $data['userId']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['userId']['data']['id'],
                                'label'    => '用户id搜索: ' . $data['userId']['data']['username'],
                             );
            $arrayResult[] = $userInfo;
        }


        if( false == empty( $data['mobile']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['mobile']['data']['id'],
                                'label'    => '手机号码搜索: ' . $data['mobile']['data']['username'],
                             );
            $arrayResult[] = $userInfo;
        }

        if( false == empty( $data['redeem']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['redeem']['data']['id'],
                                'label'    => '兑换码搜索: ' . $data['redeem']['data']['username'],
                             );
            $arrayResult[] = $userInfo;
        }

        $response                 = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }

    /**
     * 其他搜索
     * @Route("/autocomplete-other/search", name="dwd_csadmin_autocomplete_other_search")
     */
    public function autocompleteOtherSearchAction(Request $request)
    {
        $dataHttp       = $this->get('dwd.data.http');
        $q              = $request->get('term');
      
        $data           = array(
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'userId'         => $q,
                ),
                'method' => 'get',
                'key'    => 'userId',
            ),
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'mobile'         => $q,
                ),
                'method' => 'get',
                'key'    => 'mobile',
            )
        );

        $data              = $dataHttp->MutliCall( $data ); 
     
        $resultHash        = array();
        $arrayResult       = array();

        if( false == empty( $data['userId']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['userId']['data']['id'],
                                'type'     => 'user',
                                'label'    => '用户id搜索: ' . $data['userId']['data']['username'],
                             );
            $arrayResult[] = $userInfo;
        }


        if( false == empty( $data['mobile']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['mobile']['data']['id'],
                                'type'     => 'user',
                                'label'    => '手机号码搜索: ' . $data['mobile']['data']['username'],
                             );
            $arrayResult[] = $userInfo;
        }

        $mb_size        = mb_strlen($q, 'UTF-8');
        if ( $mb_size < 3 ) {
            $response = new Response();
            $response->setContent( $arrayResult );
            return $response;
        }

        $dm             = $this->get('doctrine_mongodb')->getManager();
        $resultByName   = $dm->getRepository('DWDDataBundle:Store')->findByName(array('$regex' => $q));
        $resultByPinyin = $dm->getRepository('DWDDataBundle:Store')->findByPinyin(array('$regex' => $q));
        $result         = array_merge($resultByName, $resultByPinyin);
   
        foreach ($result as $record) {
            if (isset($resultHash[$record->getBranchId()])) {
                continue;
            }
            $branchInfo           = array();
            $branchInfo['id']     = $record->getBranchId();
            $branchInfo['type']   = 'branch';
            $branchInfo['label']  = '门店模糊搜索: ' . $record->getName(); 
            $arrayResult[]        = $branchInfo;
            $resultHash[$record->getBranchId()] = True;
        }

        $response                 = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }

}