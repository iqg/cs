<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OrderController
 * @package DWD\CsAdminBundle\Controller
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

        $data              = $dataHttp->MutliCall($data);
    
        $res               = array();
        $res['result']     = false;
        if( $data['feedback']['data'] == true ){
            $res['result'] = true;
        } 
 
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    } 

    /** 
     *
     * @Route("/order/orderlog",name="dwd_csadmin_order_orderlog")
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
     
        $str               = '<table class="table table-striped table-bordered"><tr><th>状态</th><th>备注</th><th>创建时间</th></tr>';
        foreach( $data['orderlog']['data']['list'] as $logrecord ){
            $str          .= "<tr><td>" . $this->get('dwd.util')->getOrderStatusLabel( $logrecord['status'] ). "</td><td>" . $logrecord['remark'] . "</td><td>" . $logrecord['created_at'] . "</td></tr>";
        } 
        $str              .= "</table》";
        $res               = array(
                                'result'  => true,
                                'content' => $str,
                             );
        $response          = new Response();
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
                                                        'needPagination' => 2,
                                                        'pageNum'        => 1,
                                                        'pageLimit'      => 100, 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderlog',
                                    ),  
                                );

        $data              = $dataHttp->MutliCall($data);
        $logrecord         = $data['orderlog']['data'];
        $str               = '<table class="table table-striped table-bordered"><tr></tr>';
        $str              .= "<tr><td>订单id</td><td>" . $logrecord['id'] . "</td></tr>";
        $str              .= "<tr><td>用户id</td><td>" . $logrecord['user_id'] . "</td></tr>";
        $str              .= "<tr><td>用户手机</td><td>" . $logrecord['user_id'] . "</td></tr>";
        $str              .= "<tr><td>商品</td><td>" . $logrecord['campaign_branch_id'] . "</td></tr>";
        $str              .= "<tr><td>门店</td><td>" . $logrecord['campaign_branch_id'] . "</td></tr>";
        $str              .= "<tr><td>兑换码</td><td>" . $logrecord['redeem_number'] . "</td></tr>";
        $str              .= "<tr><td>支付信息</td><td>" . $logrecord['trade_number'] . "</td></tr>";
        $str              .= "<tr><td>订单类型</td><td>" . $logrecord['type'] . "</td></tr>";
        $str              .= "<tr><td>跟进销售</td><td>" . $logrecord['campaign_branch_id'] . "</td></tr>";
        $str              .= "<tr><td>下单时间</td><td>" . $logrecord['created_at'] . "</td></tr>";
        $str              .= "<tr><td>过期时间</td><td>" . $logrecord['expire_date'] . "</td></tr>";
        $str              .= "<tr><td>兑换时间</td><td>" . $logrecord['redeem_time'] . "</td></tr>";
        $str              .= "<tr><td>退款帐号</td><td>" . $logrecord['user_id'] . "</td></tr>";
        $str              .= "<tr><td>状态</td><td>" . $this->get('dwd.util')->getOrderStatusLabel($logrecord['status']) . "</td></tr>";
   
        $str              .= "</table》";
        $res               = array(
                                'result'  => true,
                                'content' => $str,
                             );
        $response          = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }
 
}