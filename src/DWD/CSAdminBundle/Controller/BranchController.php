<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BranchController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/admin")
 */
class BranchController extends Controller
{
 
    const WEB_VERIFY    = 1;  //Web验证
    const TEL_VERIFY    = 2;  //电话验证
    const PAPER_VERIFY  = 4;  //纸质验证
    const SECRET_VERIFY = 8;  //密码验证

    private function _getRedeemTypes( $redeemType )
    {
        $redeemTypes    = array();

        if( $redeemType & self::WEB_VERIFY  ){
          $redeemTypes[] = 'web验证';
        }

        if( $redeemType & self::TEL_VERIFY  ){
          $redeemTypes[] = '电话验证';
        }

        if( $redeemType & self::PAPER_VERIFY  ){
          $redeemTypes[] = '纸质验证';
        }

        if( $redeemType & self::SECRET_VERIFY  ){
          $redeemTypes[] = '密码验证';
        }

        return implode( ", ", $redeemTypes);
    }

    /** 
     *
     * @Route("/branch",name="dwd_csadmin_branch")
     */
    public function indexAction()
    { 
        $internalApiHost = 'http://127.0.0.1';//'http://10.0.0.10:12306';
        $branchId        = $this->getRequest()->get("branchId");
        $dataHttp        = $this->get('dwd.data.http');

        $requests        = array(
            array(
                'url'    => $internalApiHost.'/branch/branchinfo',
                'data'   => array(
                    'branchId'      => $branchId,
                ),
                'method' => 'get',
                'key'    => 'branch',
            ), 
        ); 

        $data           = $dataHttp->MutliCall($requests);
        $branchInfo     = $data['branch']['data'];
        
        $requests        = array(
            array(
                'url'    => $internalApiHost.'/brand/brandinfo',
                'data'   => array(
                    'brandId'      => $branchInfo['brand_id'],
                ),
                'method' => 'get',
                'key'    => 'brand',
            ), 
            array(
                'url'    => $internalApiHost.'/zone/zoneinfo',
                'data'   => array(
                    'zoneId'       => $branchInfo['zone_id'],
                ),
                'method' => 'get',
                'key'    => 'zone',
            ), 
            array(
                'url'    => $internalApiHost.'/saler/salerinfo',
                'data'   => array(
                    'salerId'      => $branchInfo['saler_id'],
                ),
                'method' => 'get',
                'key'    => 'saler',
            ),
        );

        $orderListTypes = array(
                            'wait-redeem' => '未领用',
                            'refund'      => '退款',
                            'expired'     => '过期',
                            'finish'      => '完成',
                            'processing'  => '需处理',
                          );
        

        $data                      = $dataHttp->MutliCall($requests);
        $brandInfo                 = $data['brand']['data'];
        if( empty( $brandInfo ) ){
            return $this->render('DWDCsAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '商户不存在'
          ));
        }

        $zoneInfo                  = $data['zone']['data'];
        $salerInfo                 = $data['saler']['data'];
 
        $branchInfo['brandName']   = $brandInfo['name'];
        $branchInfo['brandTel']    = $brandInfo['tel'];
        $branchInfo['zoneName']    = $zoneInfo['name'];
        $branchInfo['salerName']   = $salerInfo['name'];
        $branchInfo['redeemTypes'] = self::_getRedeemTypes( $branchInfo['redeem_type'] );
        return $this->render('DWDCsAdminBundle:Branch:index.html.twig', array( 
            'branchinfo'           => $branchInfo, 
            'orderlistTypes'       => $orderListTypes,
            'branchId'             => $branchId,
        ));
    } 

    //获取订单列表
    private function _getOrderList( $branchId, $orderType, $limitStart, $pageLimit )
    {
        $dataHttp            = $this->get('dwd.data.http');
        $orderTypeId         = 2;
        $pageNum             = $limitStart / $pageLimit + 1;

        switch ( $orderType ) {
            case 'wait-redeem':
                $orderTypeId = 2;
                break;
            case 'refund':
                $orderTypeId = 3;
                break;
            case 'expired': 
                $orderTypeId = 4;
                break;
            case 'finish': 
                $orderTypeId = 5;
                break;
            case 'processing': 
                $orderTypeId = 6;
                break;
            default: 
                break;
        }

        $internalApiHost = 'http://127.0.0.1';//'http://10.0.0.10:12306';
        $data            = array(
            array(
                'url'    => $internalApiHost.'/branch/orderlist',
                'data'   => array(
                    'branchId'       => $branchId,
                    'needPagination' => 1, 
                    'type'           => $orderTypeId,
                    'pageNum'        => $pageNum,
                    'pageLimit'      => $pageLimit,
                ),
                'method' => 'get',
                'key'    => 'orderlist',
            )
        );
    
        $data             = $dataHttp->MutliCall($data);
        $orderList        = array(
                                'list'         => array(),
                                'total'        => $data['orderlist']['data']['totalCnt'], 
                            );
        
        foreach( $data['orderlist']['data']['list'] as $orderInfo )
        {

            $orderList['list'][] = array(
                                       $orderInfo['id'],
                                       $orderInfo['campaign_branch_id'],
                                       $orderInfo['user_id'],
                                       $orderInfo['price'],
                                       $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status'] ),
                                       $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type'] ),
                                       $orderInfo['trade_number'],
                                       $orderInfo['created_at'],
                                       $orderInfo['updated_at'],
                                    );  
        }

        return $orderList;
    }

    //获取订单信息
    private function _getOrderInfo( $orderId )
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost =  'http://127.0.0.1';//'http://10.0.0.10:12306';
        $data            =  array(
            array(
                'url'    => $internalApiHost.'/order/orderinfo',
                'data'   => array(
                    'orderId'      => $orderId, 
                ),
                'method' => 'get',
                'key'    => 'orderinfo',
            )
        );
        $data            =  $dataHttp->MutliCall($data);
        $orderInfo       =  $data['orderinfo']['data'];
        $orderList       =  array(
                                'list'  => array(),
                                'total' => 0,
                            );
        if( !empty( $orderInfo )  ){ 
            $orderList       =  array(
                                'list' => 
                                    array(
                                        array(
                                         $orderInfo['id'],
                                         $orderInfo['campaign_branch_id'],
                                         $orderInfo['user_id'],
                                         $orderInfo['price'],
                                         $this->get('dwd.util')->getOrderStatusLabel( $orderInfo['status'] ),
                                         $this->get('dwd.util')->getOrderTypeLabel($orderInfo['type']),
                                         $orderInfo['trade_number'],
                                         $orderInfo['created_at'],
                                         $orderInfo['updated_at'],
                                       ),    
                                    ), 
                                "total" => 1,
                            );
        } 

        return $orderList;
    }

    /**
     * @Route("/branch/orderlist", name="dwd_csadmin_branch_orderlist_show")
     * @Method("GET")
     */
    public function OrderListDataAction()
    {
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $branchId        = $this->getRequest()->get('branchId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();

        if( empty( $sSearch ) ){
            $orderList   = self::_getOrderList( $branchId, $orderType, $iDisplayStart, $iDisplayLength);
        } else {
            $orderList   = self::_getOrderInfo( $sSearch );
        }
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $orderList['list'],
                                "iTotalRecords"        => $orderList['total'],
                                "iTotalDisplayRecords" => $orderList['total'],
                           );
        exit(json_encode( $res ));
        //return $this->render('DWDCsAdminBundle:Dashboard:show.html.twig', array(
         //   'product'      => $product,
      //  ));
    }
 
    /**
     * @Route("/branch/complaintrecords", name="dwd_csadmin_branch_complaintrecords_show")
     * @Method("GET")
     */
    public function ComplaintRecordsDataAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $internalApiHost = 'http://127.0.0.1';
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $branchId        = $this->getRequest()->get('branchId');
        $orderType       = $this->getRequest()->get('type', 'redeem');
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/branch/complaints',
                                    'data'   => array(
                                        'branchId'       => $branchId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'complaintrecords',
                                ),  
                            ); 

        $data              = $dataHttp->MutliCall($data);

        $complaintrecords  = array(
                               'list'         => array(),
                               'total'        => $data['complaintrecords']['data']['totalCnt'], 
                             );
        foreach( $data['complaintrecords']['data']['list'] as $complaintrecord )
        { 
            $complaintrecords['list'][] = array(
                                         $complaintrecord['item_id'],
                                         $complaintrecord['order_id'],
                                         $complaintrecord['user_id'], 
                                         $complaintrecord['saler_id'],
                                         $complaintrecord['type_id'], 
                                         $complaintrecord['status'],
                                         $complaintrecord['description'], 
                                         $complaintrecord['from_id'],
                                         $complaintrecord['category_id'],
                                         $complaintrecord['mobile'],
                                         $complaintrecord['created_at'],
                                         $complaintrecord['updated_at'],
                                         $complaintrecord['resolved_at'],
                                    );
        }
 
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $complaintrecords['list'],
                                "iTotalRecords"        => $data['complaintrecords']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['complaintrecords']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 
    }
}