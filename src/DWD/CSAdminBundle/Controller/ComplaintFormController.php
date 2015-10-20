<?php

namespace DWD\CSAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 

/**
 * Class ComplaintFormController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/complaintform")
 */
class ComplaintFormController extends Controller
{
    /**
     *
     * @Route("/resetpwd",name="dwd_csadmin_complaintform_resetpwd")
     */
    public function resetPwd()
    {
        $userId          = $this->getRequest()->get('userId'); 
        $branchId        = $this->getRequest()->get('branchId', 0); 
        $mobile          = $this->getRequest()->get('mobile', ''); 
        $dataHttp        = $this->get('dwd.data.http');

        $data            = array(
                                array(
                                    'url'    => '/user/userInfo',
                                    'data'   => array(
                                        'userId'         => $userId,
                                    ),
                                    'method' => 'get',
                                    'key'    => 'user',
                                ),
                           );
   
        $data            = $dataHttp->MutliCall($data); 

        if( empty( $data['user']['data'] ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户不存在'
          ));
        }

        return $this->render('DWDCSAdminBundle:ComplaintForm:resetpwd.html.twig', array(
                'userId'         => $userId,
                'branchId'       => $branchId,
                'mobile'         => $mobile,
        ));
    }

    /**
     *
     * @Route("/redeem",name="dwd_csadmin_complaintform_redeem")
     */
    public function redeem()
    {
        $orderId              = $this->getRequest()->get('orderId');
        $branchId             = $this->getRequest()->get('branchId');
        $mobile               = $this->getRequest()->get('mobile', ''); 

        $dataHttp             = $this->get('dwd.data.http');  
  
        $data                 = array(
                                    array(
                                        'url'    => '/order/orderinfo',
                                        'data'   =>  array( 
                                                        'orderId'  => $orderId,
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderinfo',
                                    ),  
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'branchId' => $branchId, 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

        $data                 = $dataHttp->MutliCall($data);
        $orderinfo            = $data['orderinfo']['data'];
        $salerinfo            = $data['salerinfo']['data'];

        return $this->render('DWDCSAdminBundle:ComplaintForm:redeem.html.twig', array(  
            'salerName'       => isset( $salerinfo['name'] ) ? $salerinfo['name'] : '',
            'itemName'        => $orderinfo['item_name'],   
            'redeemNumber'    => $orderinfo['redeem_number'],   
            'orderId'         => $orderId,
            'branchId'        => $branchId,
            'mobile'          => $mobile,
        ));
    }

    /**
     *
     * @Route("/updatebranch",name="dwd_csadmin_complaintform_updatebranch")
     */
    public function updatebranch()
    {
        $address         = $this->getRequest()->get('address'); 
        $redeemTels      = $this->getRequest()->get('redeemTels');
        $redeemTime      = $this->getRequest()->get('redeemTime');
        $tel             = $this->getRequest()->get('tel');
        $branchId        = $this->getRequest()->get('branchId');
        $mobile          = $this->getRequest()->get('mobile', ''); 
        
        return $this->render('DWDCSAdminBundle:ComplaintForm:updatebranch.html.twig', array( 
            'address'         => $address,
            'redeemTels'      => $redeemTels,
            'redeemTime'      => $redeemTime,
            'tel'             => $tel,
            'branchId'        => $branchId,
            'mobile'          => $mobile,
        ));
    }

    /**
     *
     * @Route("/lockuser",name="dwd_csadmin_complaintform_lockuser")
     */
    public function lockuser()
    {
        $userId          = $this->getRequest()->get('userId'); 
        $reason          = $this->getRequest()->get('reason'); 
        $note            = $this->getRequest()->get('note');  
        $dataHttp        = $this->get('dwd.data.http');
        $mobile          = $this->getRequest()->get('mobile', ''); 

        $data            = array(
                                array(
                                    'url'    => '/user/userInfo',
                                    'data'   => array(
                                        'userId'         => $userId,
                                    ),
                                    'method' => 'get',
                                    'key'    => 'user',
                                ),
                           );
   
        $data            = $dataHttp->MutliCall($data); 

        if( empty( $data['user']['data'] ) ){
            return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
             'errMsg'    => '用户不存在'
          ));
        }

        return $this->render('DWDCSAdminBundle:ComplaintForm:lockuser.html.twig', array( 
                'userId'         => $userId,
                'reason'         => $this->get('dwd.util')->getLockReasonTypeLabel( $reason ),
                'reasonId'       => $reason,
                'mobile'         => $mobile,
                'note'           => $note,
        ));
    }

    /**
     *
     * @Route("/unbinduser",name="dwd_csadmin_complaintform_unbinduser")
     */
    public function unbinduser()
    {
        $userId              = $this->getRequest()->get('userId'); 
        $reason              = $this->getRequest()->get('reason');
        $mobile              = $this->getRequest()->get('mobile', ''); 
        
        return $this->render('DWDCSAdminBundle:ComplaintForm:unbinduser.html.twig', array( 
            'userId'         => $userId,
            'reason'         => $this->get('dwd.util')->getUnbindReasonLabel( $reason ), 
            'mobile'         => $mobile,
        ));
    }

    /**
     *
     * @Route("/ordercorrect",name="dwd_csadmin_complaintform_ordercorrect")
     */
    public function ordercorrect()
    {
        $orderId              = $this->getRequest()->get('orderId'); 
        $correctContent       = $this->getRequest()->get('correctContent'); 
        $correctNote          = $this->getRequest()->get('correctNote'); 
        $needOffline          = $this->getRequest()->get('needOffline', 0);
        $mobile               = $this->getRequest()->get('mobile', ''); 

        if( $needOffline == 'on' ){
            $needOffline      = 1;
        }

        $dataHttp             = $this->get('dwd.data.http');  
  
        $data                 = array(
                                    array(
                                        'url'    => '/order/orderinfo',
                                        'data'   =>  array( 
                                                        'orderId'  => $orderId,
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderinfo',
                                    ),  
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'orderId'  => $orderId, 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

        $data                 = $dataHttp->MutliCall($data);
        $orderinfo            = $data['orderinfo']['data'];
        $salerinfo            = $data['salerinfo']['data'];
          
        return $this->render('DWDCSAdminBundle:ComplaintForm:ordercorrect.html.twig', array( 
            'correctContent'   => $correctContent,
            'correctNote'      => $correctNote, 
            'needOffline'      => $needOffline,
            'orderId'          => $orderId,
            'userId'           => $orderinfo['user_id'],
            'campaignBranchId' => $orderinfo['campaign_branch_id'],
            'salerName'        => isset( $salerinfo['name'] ) ? $salerinfo['name'] : '',
            'itemName'         => $orderinfo['item_name'],
            'branchName'       => $orderinfo['branch_name'],     
            'mobile'           => $mobile,
        ));
    }


    /**
     * @Route("/branchoffline",name="dwd_csadmin_complaintform_branchoffline")
     */
    public function branchoffline()
    { 
        $branchId             = $this->getRequest()->get('branchId'); 
        $reason               = $this->getRequest()->get('reason');
        $note                 = $this->getRequest()->get('note');
        $mobile               = $this->getRequest()->get('mobile', ''); 
        $dataHttp             = $this->get('dwd.data.http');
        $data                 = array(
                                    array(
                                        'url'    => '/branch/branchinfo',
                                        'data'   =>  array( 
                                                        'branchId'  => $branchId,
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'branchinfo',
                                    ),
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'branchId'  => $branchId, 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

        $data                 = $dataHttp->MutliCall($data);
        $branchinfo           = $data['branchinfo']['data'];
        $salerinfo            = $data['salerinfo']['data'];
     
        return $this->render('DWDCSAdminBundle:ComplaintForm:branchoffline.html.twig', array( 
            'branchId'        => $branchId,
            'note'            => $note,
            'reason'          => $this->get('dwd.util')->getBranchOfflineReasonLabel( $reason ), 
            'salerName'       => isset( $salerinfo['name'] ) ? $salerinfo['name'] : '',
            'branchName'      => $branchinfo['name'],  
            'mobile'          => $mobile,
        ));
    }

    /**
     *
     * @Route("/other",name="dwd_csadmin_complaintform_other")
     */
    public function other()
    {
        $type                 = $this->getRequest()->get('type');
        $tagId                = 22;
        $tag                  = '咨询';
        $source               = 3;
        switch( $type ){
            case 'tech-error':
                $tagId        = 2;
                $tag          = '技术故障';
                $source       = 4;
                break;
            case 'other':
                $tagId        = 23;
                $tag          = '其他';
                $source       = 5;
                break;
        }

        return $this->render('DWDCSAdminBundle:ComplaintForm:other.html.twig', array( 
            'type'            => $type,
            'tagId'           => $tagId,
            'tag'             => $tag,
            'source'          => $source,
        )); 
    }

    /**
     *
     * @Route("/viewinfo",name="dwd_csadmin_complaintform_viewinfo")
     */
    public function viewinfo()
    {
        $userId               = $this->getRequest()->get('userId');
        $view                 = $this->getRequest()->get('view');
        $mobile               = $this->getRequest()->get('mobile', ''); 
        $note                 = '';
        
        switch( $view ){
            case 'smsrecords':
                $note         = '查看短信记录';
                break;
            case 'recommendrecords':
                $note         = '查看推荐记录';
                break;
            case 'coinrecords':
                $note         = '查看金币记录'; 
                break;
            case 'balancerecords':
                $note         = '查看余额记录'; 
                break;
            case 'lockuserrecords':
                $note         = '查看封号记录'; 
                break;  
        }

        return $this->render('DWDCSAdminBundle:ComplaintForm:viewinfo.html.twig', array( 
            'userId'          => $userId, 
            'note'            => $note,
            'view'            => $view,
            'mobile'          => $mobile,
        )); 
    }
}