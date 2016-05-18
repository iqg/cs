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
            $branchInfo['type']   = 'branch';
            $branchInfo['label']  = $record->getName();
            $branchInfo['inputValue']  = $q;
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
            foreach($data['redeemNumber']['data'] as $redeemRow){
            $branchInfo    = array(
                                'id'       => $redeemRow['id'],
                                'type'     => 'redeemNumber',
                                'label'    => '兑换码搜索: ' . $redeemRow['name'],
                                'inputValue'=> $q,
                             );
            $arrayResult[] = $branchInfo;
            }
        }


        if( false == empty( $data['branchId']['data'] ) && $data['branchId']['errno'] == 0 ){
            $branchInfo    = array(
                                'id'       => $data['branchId']['data']['id'],
                                'type'     => 'branchId',
                                'label'    => '门店id搜索: ' . $data['branchId']['data']['name'],
                                'inputValue'=> $q,
                             );
            $arrayResult[] = $branchInfo;
        }

        foreach ($result as $record) {
            if (isset($resultHash[$record->getBranchId()])) {
                continue;
            }
            $branchInfo           = array();
            $branchInfo['id']     = $record->getBranchId();
            $branchInfo['type']   = 'branch';
            $branchInfo['label']  = '门店模糊搜索: ' . $record->getName();
            $branchInfo['inputValue']  = $q;
            $arrayResult[]        = $branchInfo;
            $resultHash[$record->getBranchId()] = True;
        }

        $response                 = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }


    /**
     * 搜索用户投诉相关的信息
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
                    'redeemNumber'      => $q, 
                ),
                'method' => 'get',
                'key'    => 'redeem',
            ),
            array(
                'url'    => '/order/orderInfo',
                'data'   => array(
                    'orderId'      => $q,
                ),
                'method' => 'get',
                'key'    => 'orderid',
            ),
            array(
                'url'    => '/user/userInfo',
                'data'   => array(
                    'username'      => $q,
                ),
                'method' => 'get',
                'key'    => 'username',
            ),
        );

        $data              = $dataHttp->MutliCall( $data );
        $arrayResult       = array();

        if( false == empty( $data['userId']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['userId']['data']['id'],
                                'type'     => 'userId',
                                'label'    => '用户id搜索: ' . $data['userId']['data']['username'],
                                'inputValue'=> $q,
                             );
            $arrayResult[] = $userInfo;
        }


        if( false == empty( $data['mobile']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['mobile']['data']['id'],
                                'type'     => 'mobile',
                                'label'    => '手机号码搜索: ' . $data['mobile']['data']['username'],
                                'inputValue'=> $q,
                             );
            $arrayResult[] = $userInfo;
        }

        if( false == empty( $data['redeem']['data'] ) && $data['userId']['errno'] == 0 ){
            foreach( $data['redeem']['data'] as $redeemNum){

                $userInfo      = array(
                                'id'       => $redeemNum['id'],
                                'type'     => 'redeemNumber',
                                'label'    => '兑换码搜索: ' . $redeemNum['username'],
                                'inputValue'=> $q,
                             );
                $arrayResult[] = $userInfo;
            }
        }
        //用户订单搜索
        if( false == empty( $data['orderid']['data'] ) && $data['orderid']['errno'] == 0 ){
                $userInfo      = array(
                    'id'       => $data['orderid']['data']['user_id'],
                    'type'     => 'orderid',
                    'label'    => '用户订单搜索: ' . $data['orderid']['data']['user_id'],
                    'inputValue'=> $q,
                );
                $arrayResult[] = $userInfo;
        }

        if( false == empty( $data['username']['data'] ) && $data['username']['errno'] == 0 ){
            foreach( $data['username']['data']['list'] as $user){
                $userInfo      = array(
                    'id'       => $user['id'],
                    'type'     => 'username',
                    'label'    => '用户昵称搜索: ' . $user['username'],
                    'inputValue'=> $q,
                );
                $arrayResult[] = $userInfo;
            }
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
                                'inputValue'=> $q,
                             );
            $arrayResult[] = $userInfo;
        }


        if( false == empty( $data['mobile']['data'] ) && $data['userId']['errno'] == 0 ){
            $userInfo      = array(
                                'id'       => $data['mobile']['data']['id'],
                                'type'     => 'user',
                                'label'    => '手机号码搜索: ' . $data['mobile']['data']['username'],
                                'inputValue'=> $q,
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
            $branchInfo['inputValue']  = $q;
            $arrayResult[]        = $branchInfo;
            $resultHash[$record->getBranchId()] = True;
        }

        $response                 = new Response();
        $response->setContent(json_encode($arrayResult));
        return $response;
    }

}