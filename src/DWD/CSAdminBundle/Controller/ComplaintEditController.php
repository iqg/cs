<?php

namespace DWD\CSAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;  
use Symfony\Component\HttpFoundation\Response;
use DWD\DataBundle\Document\Complaint;
/**
 * Class ComplaintEditController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
 */
class ComplaintEditController extends Controller
{

    private function _editBranchOffline( $complaint )
    {  
        $dataHttp             = $this->get('dwd.data.http');
        $data                 = array(
                                    array(
                                        'url'    => '/branch/branchinfo',
                                        'data'   =>  array( 
                                                        'branchId'  => $complaint['branchId'],
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'branchinfo',
                                    ),
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'branchId'  => $complaint['branchId'], 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

        $data                 = $dataHttp->MutliCall($data);
        $branchinfo           = $data['branchinfo']['data'];
        $salerinfo            = $data['salerinfo']['data'];

         return $this->render('DWDCSAdminBundle:ComplaintEdit:branchoffline.html.twig', array( 
            'tags'            => $complaint['tags'],
            'reason'          => $complaint['complaintInfo']['reason'],
            'salerName'       => isset( $salerinfo['name'] ) ? $salerinfo['name'] : '',
            'branchName'      => $branchinfo['name'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'],
            'branchEnabled'   => $branchinfo['enabled'],
            'branchId'        => $branchinfo['id'],
        ));
    }

    private function _editUnbindUser( $complaint )
    {   
         return $this->render('DWDCSAdminBundle:ComplaintEdit:unbinduser.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'],
        ));
    }

    private function _editLockUser( $complaint )
    {   
         $locked              = 0;
         if( isset( $complaint['complaintInfo']['locked'] ) ){
             $locked          = 1;
         }  
         return $this->render('DWDCSAdminBundle:ComplaintEdit:lockuser.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'],
            'locked'          => $locked,
            'userId'          => $complaint['userId'],
            'reason'          => $complaint['complaintInfo']['reason'],
            'reasonId'        => $complaint['complaintInfo']['reasonId'],
        ));
    }

    private function _editRedeem( $complaint )
    {    
         $dataHttp             = $this->get('dwd.data.http');  
  
         $data                 = array(
                                    array(
                                        'url'    => '/order/orderinfo',
                                        'data'   =>  array( 
                                                        'orderId'  => $complaint['complaintInfo']['orderId'],
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderinfo',
                                    ),  
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'branchId' => $complaint['branchId'], 
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

         $data                 = $dataHttp->MutliCall($data);
         $orderInfo            = $data['orderinfo']['data'];
         $salerInfo            = $data['salerinfo']['data'];

         return $this->render('DWDCSAdminBundle:ComplaintEdit:redeem.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'], 
            'salerName'       => isset( $salerInfo['name'] ) ? $salerInfo['name'] : '',
            'itemName'        => $orderInfo['item_name'],
            'redeemNumber'    => $orderInfo['redeem_number'],
        ));
    }

    private function _editOrderCorrect( $complaint )
    {    
         $offlined             = 0;
         if( isset( $complaint['complaintInfo']['offlined'] ) ){
             $offlined         = 1;
         }

         $dataHttp             = $this->get('dwd.data.http');   
  
         $data                 = array(
                                    array(
                                        'url'    => '/order/orderinfo',
                                        'data'   =>  array( 
                                                        'orderId'  => $complaint['complaintInfo']['orderId'],
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'orderinfo',
                                    ),  
                                    array(
                                        'url'    => '/saler/salerinfo',
                                        'data'   =>  array( 
                                                        'orderId'  => $complaint['complaintInfo']['orderId'],
                                                     ),
                                        'method' =>  'get',
                                        'key'    =>  'salerinfo',
                                    ),
                                );

         $data                 = $dataHttp->MutliCall($data);
         $orderinfo            = $data['orderinfo']['data'];
         $salerinfo            = $data['salerinfo']['data']; 

         return $this->render('DWDCSAdminBundle:ComplaintEdit:ordercorrect.html.twig', array( 
            'tags'             => $complaint['tags'],
            'method'           => $complaint['method'],
            'mobile'           => $complaint['mobile'],
            'status'           => $complaint['status'],
            'note'             => $complaint['note'],
            'complaintId'      => $complaint['_id'], 
            'needOffline'      => $complaint['complaintInfo']['needOffline'],
            'offlined'         => $offlined, 
            'content'          => $complaint['complaintInfo']['content'], 
            'userId'           => $orderinfo['user_id'],
            'campaignBranchId' => $complaint['complaintInfo']['campaignBranchId'],
            'salerName'        => isset( $salerinfo['name'] ) ? $salerinfo['name'] : '',
            'itemName'         => $orderinfo['item_name'],
            'branchName'       => $orderinfo['branch_name'],
        ));
    }

    private function _editOrderRefund( $complaint )
    {
        $offlined             = 0;
        if( isset( $complaint['complaintInfo']['offlined'] ) ){
            $offlined         = 1;
        }

        $dataHttp             = $this->get('dwd.data.http');

        $data                 = array(
            array(
                'url'    => '/order/orderinfo',
                'data'   =>  array(
                    'orderId'  => $complaint['complaintInfo']['orderId'],
                ),
                'method' =>  'get',
                'key'    =>  'orderinfo',
            ),
            array(
                'url'    => '/saler/salerinfo',
                'data'   =>  array(
                    'orderId'  => $complaint['complaintInfo']['orderId'],
                ),
                'method' =>  'get',
                'key'    =>  'salerinfo',
            ),
        );

        $data                 = $dataHttp->MutliCall($data);
        $orderinfo            = $data['orderinfo']['data'];
        $salerinfo            = $data['salerinfo']['data'];

        return $this->render('DWDCSAdminBundle:ComplaintEdit:orderrefund.html.twig', array(
            'tags'             => $complaint['tags'],
            'method'           => $complaint['method'],
            'mobile'           => $complaint['mobile'],
            'status'           => $complaint['status'],
            'note'             => $complaint['note'],
            'complaintId'      => $complaint['_id'],
            'needOffline'      => $complaint['complaintInfo']['needOffline'],
            'offlined'         => $offlined,
            'reason'          => $complaint['complaintInfo']['reason'],
            'userId'           => $orderinfo['user_id'],
            'campaignBranchId' => $complaint['complaintInfo']['campaignBranchId'],
            'salerName'        => isset( $salerinfo['name'] ) ? $salerinfo['name'] : '',
            'itemName'         => $orderinfo['item_name'],
            'branchName'       => $orderinfo['branch_name'],
        ));
    }

    private function _editUpdateBranch( $complaint )
    { 
        return $this->render('DWDCSAdminBundle:ComplaintEdit:updatebranch.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'],
            'address'         => $complaint['complaintInfo']['address'],
            'redeemTels'      => $complaint['complaintInfo']['redeemTels'],
            'redeemTime'      => $complaint['complaintInfo']['redeemTime'],
            'branchTel'       => $complaint['complaintInfo']['branchTel'],
        ));
    }

    private function _editViewInfo( $complaint )
    { 
        return $this->render('DWDCSAdminBundle:ComplaintEdit:viewinfo.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'],  
        ));
    }
     
    private function _editResetPwd( $complaint )
    { 
        return $this->render('DWDCSAdminBundle:ComplaintEdit:resetpwd.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'],  
        ));
    }

    private function _editOther( $complaint )
    { 
        $op                   = $complaint['op'];
        $tagId                = 22;
        $tag                  = '咨询';
        switch( $op ){
            case 'tech-error':
                $tagId        = 2;
                $tag          = '技术故障';
                break;
            case 'other':
                $tagId        = 23;
                $tag          = '其他';
                break;
        }
        return $this->render('DWDCSAdminBundle:ComplaintEdit:other.html.twig', array( 
            'tags'            => $complaint['tags'],
            'method'          => $complaint['method'],
            'mobile'          => $complaint['mobile'],
            'status'          => $complaint['status'],
            'note'            => $complaint['note'],
            'complaintId'     => $complaint['_id'], 
            'tag'             => $tag,
            'tagId'           => $tagId,
        ));
    }

    /**
     *
     * @Route("/complaint/edit",name="dwd_csadmin_complaint_edit")
     */
    public function redeem()
    {
        $complaintId          = $this->getRequest()->get('id');  
        $dataHttp             = $this->get('dwd.data.http');  
  
        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $complaint            = $dm->getRepository('DWDDataBundle:Complaint')->getComplaint( $complaintId );

        switch ($complaint['op']) {
           case 'branchOffline':
              return self::_editBranchOffline( $complaint );
              break;
           case 'unbindUser':
              return self::_editUnbindUser( $complaint );
              break;
           case 'lockUser':
              return self::_editLockUser( $complaint );
              break;
           case 'redeem':
              return self::_editRedeem( $complaint );
              break;
           case 'orderCorrect':
              return self::_editOrderCorrect( $complaint );
              break;
           case 'updateBranch':
              return self::_editUpdateBranch( $complaint );
              break;
           case 'viewInfo':
              return self::_editViewInfo( $complaint );
              break;
           case 'resetPwd':
              return self::_editResetPwd( $complaint );
              break;
           case 'orderRefund':
              return self::_editOrderRefund( $complaint );
              break;
           case 'ask':
           case 'tech-error':
           case 'other':
           default:
              return self::_editOther( $complaint );
              break;
        }

        return $this->render('DWDCSAdminBundle:ComplaintForm:other.html.twig', array( 
        ));
    }
}