<?php

namespace DWD\CSAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;  
use Symfony\Component\HttpFoundation\Response;
use DWD\DataBundle\Document\Complaint;
/**
 * Class ComplaintController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/")
 */
class ComplaintController extends Controller
{

    const OTHER     = 3; //其他咨询
    const UNSOLVED  = 0; //已解决
    const ASK       = 1; //咨询
    const TEC_ERROR = 2; //技术故障

    /** 
     *
     * @Route("/complaint/other",name="dwd_csadmin_complaint_other")
     */
    public function otherAction(Request $request)
    {
        $type            = $this->getRequest()->get('type');
        $tagId           = self::ASK;

        if( $type == 'tech-error' ){
            $tagId       = self::TEC_ERROR;
        }

        return $this->render('DWDCSAdminBundle:complaint:other.html.twig', array(
                'complaintType'  => $this->get('dwd.util')->getComplaintTypesLabel( $type ),
                'type'           => $type,
                'tagId'          => $tagId,
        ));
    }

    /**
     *
     * @Route("/complaint/confirm",name="dwd_csadmin_complaint_submit")
     */
    public function confirmAction(Request $request)
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $opTags          = $this->getRequest()->get('opTags');
        $type            = $this->getRequest()->get('type');

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
     * @Route("/complaint/prepare",name="dwd_csadmin_complaint_save")
     */
    public function prepare(Request $request)
    {   
        $type            = $this->getRequest()->get('type');
        $operators       = $this->getRequest()->get('operators');
        $source          = $this->getRequest()->get('source');
        $users           = array();
        $branchs         = array();
        $orders          = array();
        $campaigns       = array();

        if( $operators != null ){
            $operators   = json_decode( $operators, true );
        }
 
        foreach( $operators as $operator ){

            switch ($operator['type']) {
                case '重置密码':
                    $users[] = array(
                                  'id'   => $operator['userId'],
                                  'name' => $operator['userName'],
                                  'op'   => 'resetPwd',
                                  'res'  => $operator['res'],
                               );
                    break;
                
                default: 
                    break;
            }
        }

        $complaintRecord  = array(
                                'source'   => $source,
                                'type'     => $type,
                                'status'   => 0,
                                'createAt' => time(), 
                            );


        if( !empty( $users ) ){
            $complaintRecord['users']      = $users;
        }
        
        if( !empty( $branchs ) ){
            $complaintRecord['branchs']    = $branchs;
        }

        if( !empty( $orders ) ){
            $complaintRecord['orders']     = $orders;
        }

        if( !empty( $orders ) ){
            $complaintRecord['campaigns']  = $campaigns;
        }

        $complaint = new Complaint();
        $complaint->setSource( $source );

        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($complaint);
        $dm->flush();

        $logRecord         = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => true,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array(
                                              'source'        => $source,
                                              'type'          => $type,
                                              'status'        => 0,
                                              'type'          => $type,
                                            ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
 
        exit;
    }

    /**
     *
     *@Route("/complaint/othersubmit",name="dwd_csadmin_complaint_othersubmit")
     */
    public function otherSubmitAction(Request $request)
    {  
        $tags                 = $this->getRequest()->get('tags');
        $mobile               = $this->getRequest()->get('mobile');
        $method               = $this->getRequest()->get('method');
        $status               = $this->getRequest()->get('status');
        $note                 = $this->getRequest()->get('note');
        $now                  = time();

        $tags                 = explode(',', $tags );
        foreach ($tags as $key => $tagId) {
           $tags[$key]        = intval( $tagId );
        }

        $complaint            = new Complaint();
        $complaint->setSource(  self::OTHER );  //其他咨询
        $complaint->setTags( $tags );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( $method );
        $complaint->setStatus( $status );
        $complaint->setNote( $note );  
        $complaint->setCreatedAt( $now );

        if( $status != self::UNSOLVED ){
            $complaint->setResolvedAt( $now );
        }
        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($complaint); 
        $dm->flush();

        $logRecord         = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => true,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array(
                                              'tags'        => $tags,
                                              'mobile'      => $mobile,
                                              'status'      => $status,
                                              'note'        => $note,
                                            ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );

        $response             = new Response();
        $response->setContent(  'ok' );
        return $response; 
    }

    /** 
     *
     * @Route("/complaint/savecommon",name="dwd_csadmin_complaint_savecommon")
     */
    public function saveCommonAction(Request $request)
    {   
        $complaintId          = $this->getRequest()->get('complaintId');
        $complaintWay         = $this->getRequest()->get('complaintWay');
        $from                 = $this->getRequest()->get('from'); 
        $mobile               = $this->getRequest()->get('mobile');
        $note                 = $this->getRequest()->get('note');
        $platform             = $this->getRequest()->get('platform'); 
        $status               = $this->getRequest()->get('status');
        $tags                 = $this->getRequest()->get('tags');  
        
        $complaint            = new Complaint();
        $complaint->setSource(  self::OTHER );  //其他咨询
        $complaint->setTags( $tags );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( $complaintWay );
        $complaint->setStatus( $status );
        $complaint->setNote( $note );  
        $complaint->setCreatedAt( $now );

        if( $status != self::UNSOLVED ){
            $complaint->setResolvedAt( $now );
        }
        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($complaint); 
        $dm->flush();

        $logRecord         = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => true,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array(
                                              'tags'         => $tags,
                                              'mobile'       => $mobile,
                                              'status'       => $status,
                                              'note'         => $note,
                                              'platform'     => $platform,
                                              'complaintWay' => $complaintWay,
                                              'complaintId'  => $complaintId,
                                            ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
        
        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }

     /** 
     *
     * @Route("/complaint/addcommon",name="dwd_csadmin_complaint_addcommon")
     */
    public function addCommonAction(Request $request)
    {    
        $complaintWay         = $this->getRequest()->get('complaintWay');
        $from                 = $this->getRequest()->get('from'); 
        $mobile               = $this->getRequest()->get('mobile');
        $note                 = $this->getRequest()->get('note');
        $platform             = $this->getRequest()->get('platform'); 
        $status               = $this->getRequest()->get('status');
        $tags                 = $this->getRequest()->get('tags'); 
        
        $complaint            = new Complaint();
        $complaint->setSource(  self::OTHER );  //其他咨询
        $complaint->setTags( $tags );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( $complaintWay );
        $complaint->setStatus( $status );
        $complaint->setNote( $note );  
        $complaint->setCreatedAt( $now );

        if( $status != self::UNSOLVED ){
            $complaint->setResolvedAt( $now );
        }
        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($complaint); 
        $dm->flush();

        $logRecord         = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => true,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array(
                                              'tags'         => $tags,
                                              'mobile'       => $mobile,
                                              'status'       => $status,
                                              'note'         => $note,
                                              'platform'     => $platform,
                                              'complaintWay' => $complaintWay,                                             ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
        
        $response             = new Response();
        $response->setContent( json_encode( $res ) );
        return $response; 
    }
 
    /** 
     *
     * @Route("/complaint/list",name="dwd_csadmin_complaint_list")
     */
    public function listAction(Request $request)
    {
        $sEcho                = $this->getRequest()->get('sEcho');
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength');   
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $complaintList        = $dm->getRepository('DWDDataBundle:Complaint')->getAll();
        $complaintCnt         = $dm->getRepository('DWDDataBundle:Complaint')->getCount();
 
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

            foreach ($complaint['tags'] as $tagId) {
                if( isset( $tagsList[$tagId] ) ){
                    $tags[]        = $tagsList[$tagId];   
                } 
            }
          
            $aaData[]              = array(
                                        $this->get('dwd.util')->getComplaintSourceLabel( $complaint['source'] ),
                                        $complaint['mobile'],
                                        implode(",", $tags),
                                        date("Y-m-d H:i:s", $complaint['createdAt']),
                                        isset( $complaint['resolvedAt'] ) ? date("Y-m-d H:i:s", $complaint['resolvedAt']) : "",
                                        $this->get('dwd.util')->getComplaintStatusLabel( $complaint['status'] ),
                                        $complaint['status'] == self::UNSOLVED ? "[编辑]  <a href='#' class='complaint-detail-btn'>[详情]</a>" : "<a href='#'>[详情]</a>",
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
     *
     * @Route("/complaint/detail",name="dwd_csadmin_complaint_detail")
     */
    public function detailAction(Request $request)
    {    
        $id                   = $this->getRequest()->get('id'); 
        $dm                   = $this->get('doctrine_mongodb')->getManager(); 
        $complaint            = $dm->getRepository('DWDDataBundle:Complaint')->findBy( array( '_id' => $id ) ); 
  
        var_dump( $complaint );


        $response             = new Response();
        $response->setContent( json_encode( $complaint ) );
        return $response; 
    }
}