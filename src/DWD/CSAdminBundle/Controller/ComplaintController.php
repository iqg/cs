<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;  
use Symfony\Component\HttpFoundation\Response;
/**
 * Class ComplaintController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/")
 */
class ComplaintController extends Controller
{
    /** 
     *
     * @Route("/complaint/confirm",name="dwd_csadmin_complaint_submit")
     */
    public function confirmAction(Request $request)
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $opTags          = $this->getRequest()->get('opTags');
         
        $tagsList        = array();
        $data            = array( 
                                array(
                                    'url'    => '/complaint/taglist', 
                                    'method' => 'get',
                                    'key'    => 'complaintTags',
                                ),  
                            ); 

        $data            = $dataHttp->MutliCall($data);
        $complaintTags   = array(

                               'list'         => $data['complaintTags']['data']['list'],
                               'total'        => $data['complaintTags']['data']['totalCnt'], 
                             );

        $autoSelectTags     = array();
        if( false == empty( $opTags ) ){
            $autoSelectTags = explode(",", $opTags);
        }
 
 
        return $this->render('DWDCSAdminBundle:complaint:confirm.html.twig', array(
                'complaintTags'  => $complaintTags,
                'autoSelectTags' => $autoSelectTags,
                'complaintType'  => $this->get('dwd.util')->getComplaintTypesLabel( $type ),
                'param'          => array(
                                       'item_id'   => 6666,
                                       'item'      => '1',
                                       'branch'    => '1',
                                       'branch_id' => 10002, 
                                       'saler_id'  => 28,
                                       'saler'     => '1',  
                                       'orderId'   => 1382,   
                                    ),
        ));
    }

    /** 
     *
     * @Route("/complaint/save",name="dwd_csadmin_complaint_save")
     */
    public function save(Request $request)
    {   
        $type            = $this->getRequest()->get('type');
        $operators       = $this->getRequest()->get('operators');

        if( $operators != null ){
            $operators   = json_decode( $operators, true );
        }

        var_dump( $operators );
        foreach( $operators as $operator )
        //$dm             = $this->get('doctrine_mongodb')->getManager();
        //$resultByName   = $dm->getRepository('DWDDataBundle:Store')
        exit;
    }

    /** 
     *
     * @Route("/complaint/processSubmit",name="dwd_csadmin_complaint_processSubmit")
     */
    public function processSubmitAction(Request $request)
    {  
        $branch               = $this->getRequest()->get('branch');
        $branchId             = $this->getRequest()->get('branch_id');
        $complaintWay         = $this->getRequest()->get('complaintWay');
        $from                 = $this->getRequest()->get('from');
        $item                 = $this->getRequest()->get('item');
        $itemId               = $this->getRequest()->get('item_id');
        $mobile               = $this->getRequest()->get('mobile');
        $note                 = $this->getRequest()->get('note');
        $platform             = $this->getRequest()->get('platform');
        $saler                = $this->getRequest()->get('saler');
        $salerId              = $this->getRequest()->get('saler_id');
        $status               = $this->getRequest()->get('status');
        $tags                 = $this->getRequest()->get('tags');
        $orderId              = $this->getRequest()->get('order_id');
        
        $data                 = array( 
                                    array(
                                        'url'    => '/complaint/submit', 
                                        'data'   =>  array( 
                                                        'branchName'    => $branch,
                                                        'branchId'      => $branchId,
                                                        'typeId'        => $complaintWay,
                                                        'fromId'        => $from, 
                                                        'description'   => $note, 
                                                        'orderId'       => $orderId, 
                                                        'itemId'        => $itemId, 
                                                        'itemName'      => $item, 
                                                        'salerId'       => $salerId, 
                                                        'mobile'        => $mobile,
                                                        'status'        => $status,
                                                        'userId'        => '111',
                                                        'categoryId'    => 1,
                                                     ),
                                        'method' => 'post',
                                        'key'    => 'complaint',
                                    ),  
                                );
 
        $dataHttp             = $this->get('dwd.data.http');
        $data                 = $dataHttp->MutliCall($data);
        $res                  = $data['complaint']['data'];
        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }
 
}