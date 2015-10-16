<?php

namespace DWD\CSAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BranchController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
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
        $redeemTypeIds  = array();

        if( $redeemType & self::WEB_VERIFY  ){
          $redeemTypes[]   = 'web验证';
          $redeemTypeIds[] = self::WEB_VERIFY;
        }

        if( $redeemType & self::TEL_VERIFY  ){
          $redeemTypes[]   = '电话验证';
          $redeemTypeIds[] = self::TEL_VERIFY;
        }

        if( $redeemType & self::PAPER_VERIFY  ){
          $redeemTypes[]   = '纸质验证';
          $redeemTypeIds[] = self::PAPER_VERIFY;
        }

        if( $redeemType & self::SECRET_VERIFY  ){
          $redeemTypes[]   = '密码验证';
          $redeemTypeIds[] = self::SECRET_VERIFY;
        }

        return  array(
                  $redeemTypes,
                  $redeemTypeIds,
                );
    }

    private function _getOperation( $operation, $orderId )
    {
        $opStr               = '';
        foreach ($operation as $operator) {
          switch ( $operator ) {
            /*  case '退款':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-refund-btn' data-rel='$orderId'>[退款]</a>";
                    break;  
             case '纠错':
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-correct-btn' data-rel='$orderId'>[纠错]</a>";
                    break;*/
             case '日志': 
                    $opStr  .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-log-btn' data-rel='$orderId'>[日志]</a>";
                    break;
             case '详情': 
                    $opStr   .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-detail-btn' data-rel='$orderId'>[详情]</a>";
                    break;
             case '验证': 
                    $opStr   .= "&nbsp;&nbsp;&nbsp;<a href='#' class='order-redeem-btn' data-rel='$orderId'>[验证]</a>";
                    break;
          }
        }

        return $opStr;
    }

    /** 
     *
     * @Route("/branch",name="dwd_csadmin_branch")
     */
    public function indexAction()
    {    

        $branchId        = $this->getRequest()->get("branchId");
        $dataHttp        = $this->get('dwd.data.http');
        $source          = $this->getRequest()->get('source');
        $type            = $this->getRequest()->get('type');

        $requests        = array(
            array(
                'url'    => '/branch/branchinfo',
                'data'   => array(
                    'branchId'      => $branchId,
                ),
                'method' => 'get',
                'key'    => 'branch',
            ), 
        ); 

        $data           = $dataHttp->MutliCall($requests);
        $branchInfo     = $data['branch']['data'];
        if( empty( $branchInfo ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '商户不存在'
          ));
        }
        
        $requests        = array(
                              array(
                                  'url'    => '/brand/brandinfo',
                                  'data'   => array(
                                                  'brandId'      => $branchInfo['brand_id'],
                                              ),
                                  'method' => 'get',
                                  'key'    => 'brand',
                              ), 
                              array(
                                  'url'    => '/zone/zoneinfo',
                                  'data'   => array(
                                                  'zoneId'       => $branchInfo['zone_id'],
                                              ),
                                  'method' => 'get',
                                  'key'    => 'zone',
                              ), 
                              array(
                                  'url'    => '/saler/salerinfo',
                                  'data'   => array(
                                                  'salerId'      => $branchInfo['saler_id'],
                                              ),
                                  'method' => 'get',
                                  'key'    => 'saler',
                              ),
                              array(
                                  'url'    => '/branch/campaignbranchlist',
                                  'data'   => array(
                                                  'branchId'     => $branchId,
                                              ),
                                  'method' => 'get',
                                  'key'    => 'campaignbranchlist',
                              ),
                              array(
                                  'url'    => '/branch/redeemtel',
                                  'data'   => array(
                                                'branchId'       => $branchId,
                                              ),
                                  'method' => 'get',
                                  'key'    => 'redeemtel',
                              ),
                              array(
                                  'url'    => '/branch/accountinfo',
                                  'data'   => array(
                                                'branchId'       => $branchId,
                                              ),
                                  'method' => 'get',
                                  'key'    => 'accountinfo',
                              )
                          ); 

        $data                        =  $dataHttp->MutliCall($requests);
        $brandInfo                   =  $data['brand']['data'];
        $zoneInfo                    =  $data['zone']['data'];
        $salerInfo                   =  $data['saler']['data'];
        $campaignbranchlist          =  $data['campaignbranchlist']['data'];
        $accountInfo                 =  $data['accountinfo']['data'];

        if( empty( $accountInfo ) ){
           $accountInfo['username']  = '该门店不存在帐号';
           $accountInfo['mobile']    = '该门店不存在手机号';
           $accountInfo['id']        = -1;
        } 
 
        $branchInfo['brandName']     =  $brandInfo['name'];
        $branchInfo['brandTel']      =  $brandInfo['tel'];
        $branchInfo['zoneName']      =  $zoneInfo['name'];
        if( isset( $salerInfo['name'] ) ){
          $branchInfo['salerName']   =  $salerInfo['name'];
        } else {
          $branchInfo['salerName']   =  '';
        }
         
        $redeemTypes                 =  self::_getRedeemTypes( $branchInfo['redeem_type'] );
        $branchInfo['redeemTypes']   =  implode( ", ", $redeemTypes[0]); //self::_getRedeemTypes( $branchInfo['redeem_type'] );
        $branchInfo['redeemTypeIds'] =  $redeemTypes[1];
        $orderListTypes              =  $this->get('dwd.util')->getOrderTableInfo( 0 );
        $redeemTels                  =  array();

        foreach( $data['redeemtel']['data']['list'] as $redeemTel ){
                      $redeemTels[]  =  $redeemTel['tel'];
        } 

        return $this->render('DWDCSAdminBundle:Branch:index.html.twig', array( 
            'jsonBranchInfo'         => json_encode( $branchInfo ),
            'jsonAccountInfo'        => json_encode( $accountInfo ),
            'branchinfo'             => $branchInfo,
            'orderlistTypes'         => $orderListTypes,
            'branchId'               => $branchId,
            'type'                   => $type,
            'source'                 => $source,
            'campaignbranchlist'     => $campaignbranchlist,
            'accountInfo'            => $accountInfo,
            'redeemTels'             => implode(',', $redeemTels),
        ));
    } 

    //获取订单列表
    private function _getOrderList( $branchId, $orderType, $limitStart, $pageLimit )
    {
        $dataHttp            = $this->get('dwd.data.http');
        $orderTypeId         = 2;
        $pageNum             = $limitStart / $pageLimit + 1;

        switch ( $orderType ) {
            case 'waitredeem':
                $orderTypeId = 2;
                break;
            case 'refund':
                $orderTypeId = 6;
                break;
            case 'expired': 
                $orderTypeId = 3;
                break;
            case 'finish': 
                $orderTypeId = 4;
                break;
            case 'processing': 
                $orderTypeId = 11;
                break;
            default: 
                break;
        }

        $data            = array(
            array(
                'url'    => '/branch/orderlist',
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
    
        $data              = $dataHttp->MutliCall($data);
        $orderList         = array(
                                'list'         => array(),
                                'total'        => $data['orderlist']['data']['totalCnt'], 
                             );
        
        $campaignBranchIds = array();
        $ordersInfo        = array();
         
        foreach( $data['orderlist']['data']['list'] as $orderInfo )
        {
            $ordersInfo[]        = $orderInfo;
            $campaignBranchIds[] = $orderInfo['campaign_branch_id'];
        }

  
        $tableInfo               =  $this->get('dwd.util')->getOrderTableInfo( $orderTypeId );

        foreach( $ordersInfo as $key => $orderInfo ){
           $tdValues             = array();
           foreach ($tableInfo['field'] as $field) 
           {  
              $tdValue           = '';
              switch ( $field ) {
                case 'itemName':
                  $tdValue       = $orderInfo['item_name'];
                  break;
                case 'branchName':
                  $tdValue       = $orderInfo['branch_name'];
                  break;
                case 'redeemNumber':
                  $tdValue       = $orderInfo['redeem_number'];
                  break;
                case 'refundTime':
                  $tdValue       = $orderInfo['refunded_at'];
                  break;
                case 'expiredTime':
                  $tdValue       = $orderInfo['expire_date'];
                  break;
                case 'redeemTime':
                  $tdValue       = $orderInfo['redeem_time'];
                  break;
                case 'status':
                  $tdValue       = $this->get('dwd.util')->getOrderStatusLabel($orderInfo['status']);
                  break;
                default:
                  break;
              }
              if( empty( $tdValue ) ){
                $tdValue         = '';
              }
              $tdValues[]        = $tdValue;
           }
           $tdValues[]           = $this->_getOperation( $tableInfo['operation'], $orderInfo['id'] );
           $orderList['list'][]  = $tdValues; 
        }

        return $orderList;
    }

    //获取订单信息
    private function _getOrderInfo( $redeemNumber, $branchId )
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $data            =  array(
                                array(
                                    'url'    => '/order/orderinfo',
                                    'data'   => array(
                                        'redeemNumber'      => $redeemNumber, 
                                    ),
                                    'method' => 'get',
                                    'key'    => 'orderinfo',
                                ),
                                array(
                                    'url'    => '/branch/branchinfo',
                                    'data'   => array(
                                        'redeemNumber'      => $redeemNumber, 
                                    ),
                                    'method' => 'get',
                                    'key'    => 'branchinfo',
                                )
                            );
        $data            =  $dataHttp->MutliCall($data);
        $orderInfo       =  $data['orderinfo']['data'];
        $branchInfo      =  $data['branchinfo']['data']; 
        $orderList       =  array(
                                'list'  => array(),
                                'total' => 0,
                            );
       
        $operation       = array(
                             '纠错','日志','详情'
                           ); 

        if( !empty( $orderInfo ) &&  $branchInfo['id'] ==  $branchId ){ 
            $orderList   =  array(
                                'list' => 
                                    array(
                                        array(
                                           $orderInfo['item_name'], 
                                           $orderInfo['branch_name'], 
                                           $orderInfo['redeem_number'], 
                                           $this->_getOperation( $operation, $orderInfo['id'] ),
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
            $orderList   = self::_getOrderInfo( $sSearch, $branchId );
        }
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $orderList['list'],
                                "iTotalRecords"        => $orderList['total'],
                                "iTotalDisplayRecords" => $orderList['total'],
                           );
        exit(json_encode( $res ));
        //return $this->render('DWDCSAdminBundle:Dashboard:show.html.twig', array(
         //   'product'      => $product,
      //  ));
    }
 
    /**
     * @Route("/branch/complaintrecords", name="dwd_csadmin_branch_complaintrecords_show")
     * @Method("GET")
     */
    public function ComplaintRecordsDataAction()
    {


        $sEcho                = $this->getRequest()->get('sEcho');
        $branchId             = $this->getRequest()->get('branchId');
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength');   
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $options              = array(
                                  'limit'  => $iDisplayLength,
                                  'skip'   => $iDisplayStart,
                                );
        $complaintList        = $dm->getRepository('DWDDataBundle:Complaint')->getBranchComplaints( $branchId, $options );
        $complaintCnt         = $dm->getRepository('DWDDataBundle:Complaint')->getBranchCount( $branchId );
 
        $dataHttp             = $this->get('dwd.data.http'); 
        $tagsList             = array();
        $data                 = array(
                                    array(
                                        'url'    => '/complaint/taglist', 
                                        'method' => 'get',
                                        'key'    => 'complaintTags',
                                    ),  
                                ); 

        $data                      = $dataHttp->MutliCall($data);
 
        foreach ($data['complaintTags']['data']['list'] as  $tag) {
            $tagsList[$tag['id']]  = $tag['name'];
        } 

        $aaData                    = array();
    
        foreach( $complaintList as $complaint ){
            $tags                  = array();

            if( isset( $complaint['tags'] ) ){
              foreach ($complaint['tags'] as $tagId) {
                  if( isset( $tagsList[$tagId] ) ){
                      $tags[]      = $tagsList[$tagId];   
                  } 
              }
            } 
        
            $aaData[]              = array(
                                        $this->get('dwd.util')->getComplaintSourceLabel( $complaint['source'] ),
                                        implode(",", $tags),
                                        $this->get('dwd.util')->getComplaintStatusLabel( $complaint['status'] ),
                                        "<a href='/complaint/edit?id=" . $complaint['_id'] . "' target='_blank' >[详情]</a>",
                                     );
        }

        $res                       = array(
                                        "sEcho"                => $sEcho, 
                                        "aaData"               => $aaData,
                                        "iTotalRecords"        => $complaintCnt,
                                        "iTotalDisplayRecords" => $complaintCnt,
                                     );

        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

    /**
     * @Route("/branch/update", name="dwd_csadmin_branch_update")
     * @Method("POST")
     */
    public function UpdateAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $branchId        = $this->getRequest()->get('branchId');
        $tel             = $this->getRequest()->get('tel');
        $address         = $this->getRequest()->get('address');
        $redeemTels      = $this->getRequest()->get('redeemTels');
        $redeemTime      = $this->getRequest()->get('redeemTime');
        $webRedeem       = $this->getRequest()->get('webRedeem');
        $mobileRedeem    = $this->getRequest()->get('mobileRedeem');
        $paperRedeem     = $this->getRequest()->get('paperRedeem');
        $secretRedeem    = $this->getRequest()->get('secretRedeem');
 
        $redeemType      = 0; 
        if( $webRedeem   ) {
          $redeemType   += self::WEB_VERIFY;
        }

        if( $mobileRedeem ) {
          $redeemType   += self::TEL_VERIFY;
        }

        if( $paperRedeem ) {
          $redeemType   += self::PAPER_VERIFY;
        }

        if( $secretRedeem ) {
          $redeemType   += self::SECRET_VERIFY;
        } 
 
        $data            = array(
                                array(
                                    'url'    => '/branch/update',
                                    'data'   => array(
                                        'branchId'       => $branchId,
                                        'address'        => $address, 
                                        'redeem_time'    => $redeemTime,
                                        'redeem_type'    => $redeemType,
                                        'tel'            => $tel,
                                    ), 
                                    'method' => 'post',
                                    'key'    => 'update',
                                ),
                                array(
                                    'url'    => '/branch/addredeemtels',
                                    'data'   => array(
                                        'branchId'       => $branchId, 
                                        'redeemTels'     => $redeemTels,
                                    ), 
                                    'method' => 'post',
                                    'key'    => 'addredeemtels',
                                ),
                            );

        $data            = $dataHttp->MutliCall( $data );
        $res             = false;

        if( $data['update']['errno'] == 0 && $data['update']['data'] == true ){
          $res           = true;
        }

        $logRecord       = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => $res,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array( 
                                              'branchId'      => $branchId,
                                              'address'       => $address,
                                              'redeemTime'    => $redeemTime,
                                              'redeemType'    => $redeemType,
                                              'redeemTels'    => $redeemTels,
                                              'tel'           => $tel,
                                            ),
                           );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );

        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /**
     * @Route("/branch/campaignbranchs", name="dwd_csadmin_branch_campaignbranchs")
     * @Method("GET")
     */
    public function CampaignBranchsAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $iDisplayStart   = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength  = $this->getRequest()->get('iDisplayLength'); 
        $sEcho           = $this->getRequest()->get('sEcho');
        $sSearch         = $this->getRequest()->get('sSearch', null);
        $branchId        = $this->getRequest()->get('branchId'); 
        $orderList       = array();
        $data            = array( 
                                array(
                                    'url'    => '/branch/campaignbranchlist',
                                    'data'   => array(
                                        'branchId'       => $branchId,
                                        'needPagination' => 1,
                                        'pageLimit'      => $iDisplayLength,
                                        'pageNum'        => $iDisplayStart / $iDisplayLength + 1,
                                    ), 
                                    'method' => 'get',
                                    'key'    => 'campaignbranchs',
                                ),
                            ); 

        $data              = $dataHttp->MutliCall($data);
 
        $campaignbranchs   = array(
                               'list'         => array(),
                               'total'        => $data['campaignbranchs']['data']['totalCnt'], 
                             );
        foreach( $data['campaignbranchs']['data']['list'] as $campaignbranch )
        {  
            $campaignbranchs['list'][] = array(
                                             $campaignbranch['campaign_id'],
                                             $campaignbranch['start_time'],
                                             $campaignbranch['end_time'], 
                                             $this->get('dwd.util')->getCampaignBranchTypeLabel( $campaignbranch['type'] ), 
                                             '<a href="#" data-rel=' . $campaignbranch['id'] . ' class="campaignbranch-detail">[详情]</a>'
                                        );
        }
 
        $res             = array
                           (
                                "sEcho"                => $sEcho, 
                                "aaData"               => $campaignbranchs['list'],
                                "iTotalRecords"        => $data['campaignbranchs']['data']['totalCnt'],
                                "iTotalDisplayRecords" => $data['campaignbranchs']['data']['totalCnt'],
                           );
        exit(json_encode( $res )); 

    }

    /**
     * @Route("/branch/offline", name="dwd_csadmin_branch_offline")
     * @Method("POST")
     */
    public function OfflineAction()
    {
        $dataHttp        = $this->get('dwd.data.http');
        $branchId        = $this->getRequest()->get('branchId');
        $offlineReason   = $this->getRequest()->get('offlineReason'); 
        $offlineNote     = $this->getRequest()->get('offlineNote');

        $data            = array( 
                                array(
                                    'url'    => '/branch/update',
                                    'data'   => array(
                                        'branchId'       => $branchId,
                                        'enabled'        => 0, 
                                    ), 
                                    'method' => 'post',
                                    'key'    => 'update',
                                ),
                                array(
                                    'url'    => '/campaignbranch/offline',
                                    'data'   => array(
                                        'branchId'       => $branchId, 
                                    ), 
                                    'method' => 'post',
                                    'key'    => 'offline',
                                ),
                            ); 

        $data            = $dataHttp->MutliCall($data);  

        $res             = false;
        if( $data['offline']['errno'] == 0 && $data['update']['errno'] == 0 && $data['update']['data'] == true ){
          $res           = true;
        }

        $logRecord       = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => $res,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array( 
                                              'branchId'      => $branchId,
                                              'offlineReason' => $offlineReason,
                                              'offlineNote'   => $offlineNote,
                                            ),
                           );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );

        $response        = new Response();
        $response->setContent( json_encode( $res ) );
        return $response;
    }

}