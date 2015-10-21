<?php

namespace DWD\CSAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OrderController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
 */
class OrderController extends Controller
{
 
    const WEB_VERIFY    = 1;  //Web验证
    const TEL_VERIFY    = 2;  //电话验证
    const PAPER_VERIFY  = 4;  //纸质验证
    const SECRET_VERIFY = 8;  //密码验证

 

    /** 
     *
     * @Route("/order/feedback",name="dwd_csadmin_order_feedback")
     */
    public function feedbackAction()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $orderId              = $this->getRequest()->get('orderId');
        $feedback             = $this->getRequest()->get('feedback');
  
        $data                 = array( 
                                    array(
                                        'url'    => '/order/feedback',
                                        'data'   =>  array( 
                                                        'orderId'    => $orderId,
                                                        'type'       => 1,
                                                        'feedback'   => $feedback,
                                                        'status'     => 0, 
                                                     ),
                                        'method' =>  'post',
                                        'key'    =>  'feedback',
                                    ),  
                                );

        $data                 = $dataHttp->MutliCall($data);
    
        $res                  = array();
        $res['result']        = false;
        if( $data['feedback']['data'] == true ){
            $res['result']    = true;
        } 

        $logRecord            = array(
                                  'route'    => $this->getRequest()->get('_route'),
                                  'res'      => $res['result'],
                                  'adminId'  => $this->getUser()->getId(),
                                  'ext'      => array(
                                                  'orderId'        => $orderId,
                                                  'feedback'       => $feedback,
                                                  'type'           => 1,
                                                  'status'         => 0,
                                                ),
                                );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
 
        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    } 

    /** 
     *
     * @Route("/order/orderlogs",name="dwd_csadmin_order_orderlogs")
     */
    public function orderlogAction()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $orderId              = $this->getRequest()->get('orderId');
  
        $data                 = array( 
                                    array(
                                        'url'    => '/order/orderlog',
                                        'data'   =>  array(
                                                        'orderId'        => $orderId,
                                                        'needPagination' => 2,
                                                        'pageNum'        => 1,
                                                        'pageLimit'      => 100, 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderlog',
                                    ),
                                );

        $data              = $dataHttp->MutliCall($data);
     
        $str               = '<table class="table table-striped table-bordered"><tr><th>状态</th><th>创建时间</th></tr>';
       
        foreach( $data['orderlog']['data']['list'] as $logrecord ){
            $str          .= "<tr><td>" . $this->get('dwd.util')->getOrderLogStatusLabel( $logrecord['status'] ). "</td><td>" . $logrecord['created_at'] . "</td></tr>";
        } 
        $str              .= "</table>";
        $res               = array(
                                'result'  => true,
                                'content' => $str,
                             );
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    } 

    /** 
     * @Route("/order/redeem",name="dwd_csadmin_order_redeem")
     */
    public function redeemAction()
    {
        $dataHttp             = $this->get('dwd.data.http'); 
        $orderId              = $this->getRequest()->get('orderId');
        $adminPwd             = $this->getRequest()->get('adminPwd');
        $redeemNumber         = $this->getRequest()->get('redeemNumber');

        $factory              = $this->get('security.encoder_factory');
        $user                 = $this->getUser();
        $encoder              = $factory->getEncoder($user);
        $password             = $encoder->encodePassword($adminPwd, $user->getSalt());

        if( $password != $this->getUser()->getPassword() ){
            $response         = new Response();
            $response->setContent( json_encode( '密码错误' ) );
            return $response;     
        }
  
        $data                 = array(
                                    array(
                                        'url'    => '/order/redeem',
                                        'data'   =>  array( 
                                                        'orderId'        => $orderId,
                                                        'redeemNumber'   => $redeemNumber, 
                                                        'opUserId'       => $this->getUser()->getId(),
                                                     ),
                                        'method' =>  'post',
                                        'key'    =>  'redeem',
                                    ), 
                                );

        $data                 = $dataHttp->MutliCall($data);
 
 
        $logRecord            = array(
                                  'route'    => $this->getRequest()->get('_route'),
                                  'res'      => true,
                                  'adminId'  => $this->getUser()->getId(),
                                  'ext'      => array(
                                                  'orderId'        => $orderId,
                                                  'redeemNumber'   => $redeemNumber,
                                                ),
                                );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
        $redeemInfo           = $data['redeem'];
        $res                  = true;
        if( intval($redeemInfo['errno']) != 0 || $redeemInfo['data'] == 'failed' ){
            $res              = '验证失败';
        }
  
        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

    /** 
     *
     * @Route("/order/orderdetail",name="dwd_csadmin_order_orderdetail")
     */
    public function orderdetailAction()
    { 
        $dataHttp             = $this->get('dwd.data.http'); 
        $orderId              = $this->getRequest()->get('orderId');
  
        $data                 = array(
                                    array(
                                        'url'    => '/order/orderinfo',
                                        'data'   =>  array( 
                                                        'orderId'        => $orderId,
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderinfo',
                                    ),  
                                    array(
                                        'url'    => '/order/paymentinfo',
                                        'data'   =>  array( 
                                                        'orderId'        => $orderId,
                                                        'realPay'        => 1, 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'paymentinfo',
                                    ),  
                                );

        $data                 = $dataHttp->MutliCall($data);
        $orderinfo            = $data['orderinfo']['data'];
        $paymentinfo          = $data['paymentinfo']['data'];

        $data                 = array( 
                                    array(
                                        'url'    => '/user/userinfo',
                                        'data'   =>  array( 
                                                        'userId'            => $orderinfo['user_id'], 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'userinfo',
                                    ),  
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'campaignBranchId'  => $orderinfo['campaign_branch_id'], 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

        $data                 = $dataHttp->MutliCall($data);
        $userinfo             = $data['userinfo']['data'];
        $salerinfo            = $data['salerinfo']['data'];

        $payInfo              = '无';
        if( isset( $paymentinfo['amount'] ) ){

            $payInfo          = $paymentinfo['amount'] . '元(' . $this->get('dwd.util')->getPaymentTypeLabel( $paymentinfo['payment_method'] ) . ")";
        }


        $str               = '<table class="table table-striped table-bordered"><tr></tr>';
        $str              .= "<tr><td>订单id</td><td>" . $orderinfo['id'] . "</td></tr>";
        $str              .= "<tr><td>用户id</td><td>" . $orderinfo['user_id'] . "</td></tr>";
        $str              .= "<tr><td>用户手机</td><td>" .  $userinfo['mobile'] . "</td></tr>";
        $str              .= "<tr><td>商品</td><td>" . $orderinfo['item_name'] . "</td></tr>";
        $str              .= "<tr><td>门店</td><td>" . $orderinfo['branch_name'] . "</td></tr>";
        $str              .= "<tr><td>兑换码</td><td>" . $orderinfo['redeem_number'] . "</td></tr>";
        $str              .= "<tr><td>支付信息</td><td>" . $payInfo . "</td></tr>";
        $str              .= "<tr><td>订单类型</td><td>" . $this->get('dwd.util')->getOrderTypeLabel($orderinfo['type']) . "</td></tr>";
        $str              .= "<tr><td>跟进销售</td><td>" . $salerinfo['name'] . "</td></tr>";
        $str              .= "<tr><td>下单时间</td><td>" . $orderinfo['created_at'] . "</td></tr>";
        $str              .= "<tr><td>过期时间</td><td>" . $orderinfo['expire_date'] . "</td></tr>";
        $str              .= "<tr><td>兑换时间</td><td>" . $orderinfo['redeem_time'] . "</td></tr>";
        $str              .= "<tr><td>退款帐号</td><td>" . $orderinfo['third_party_account'] . "</td></tr>";
        $str              .= "<tr><td>状态</td><td>" . $this->get('dwd.util')->getOrderStatusLabel($orderinfo['status']) . "</td></tr>";
   
        $str              .= "</table>";
        $res               = array(
                                'result'  => true,
                                'content' => $str,
                             );
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }
 
}