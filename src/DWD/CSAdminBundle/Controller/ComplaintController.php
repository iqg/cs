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
    const UNSOLVED  = 0; //未解决
    const ASK       = 1; //咨询
    const TEC_ERROR = 2; //技术故障

    /** 
     *
     * @Route("/complaint/other",name="dwd_csadmin_complaint_other")
     */
    public function otherAction(Request $request)
    {
        $type            = $this->getRequest()->get('type');
        $id              = $this->getRequest()->get('id');
        $idType          = $this->getRequest()->get('idType');
        $tagId           = self::ASK;
        $userId          = 0;
        $branchId        = 0;

        if( $type == 'tech-error' ){
            $tagId       = self::TEC_ERROR;
        }

        if( $idType == 'user' ){
           $userId       = $id;
        } else if( $idType == 'branch' ){
           $branchId     = $id;
        }

        return $this->render('DWDCSAdminBundle:complaint:other.html.twig', array(
                'complaintType'  => $this->get('dwd.util')->getComplaintTypesLabel( $type ),
                'type'           => $type,
                'tagId'          => $tagId,
                'userId'         => $userId,
                'branchId'       => $branchId,
        ));
    }

    /**
     *
     * @Route("/complaint/confirm",name="dwd_csadmin_complaint_submit")
     */
    public function confirmAction(Request $request)
    { 
        $dataHttp        = $this->get('dwd.data.http');
        $id              = $this->getRequest()->get('id');

        $dm              = $this->get('doctrine_mongodb')->getManager();
        $complaint       = $dm->getRepository('DWDDataBundle:Complaint')->getComplaint( $id ); //createQueryBuilder()( array( '_id' => $id ) )->toArray(); 
 
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
 
        $autoSelectTags     =  isset( $complaint['tags'] ) ? $complaint['tags'] : array();

        $branch             =  array(
                                  'itemId'    => '',
                                  'itemName'  => '',
                                  'name'      => '',
                                  'id'        => '',
                                  'salerId'   => '',
                                  'saler'     => '',
                                  'orderId'   => '',
                                  'item'      => '',
                                  'branch'    => '',
                                  'branchId'  => '',
                               ) ;
    
      /*  if( empty( $complaint->getBranchs() ) ){
           $branch          =  $complaint->getBranchs();                             
        } */ 
        $complaint['id']   =  strval($complaint['_id']);
        unset( $complaint['_id'] ); 

        return $this->render('DWDCSAdminBundle:complaint:confirm.html.twig', array(
                'complaintTags'   => $complaintTags,
                'autoSelectTags'  => $autoSelectTags,
                'status'          => isset( $complaint['status'] ) ? $complaint['status'] : 0,
                'method'          => isset( $complaint['method'] ) ? $complaint['method'] : 1,
                'mobile'          => isset( $complaint['mobile'] ) ? $complaint['mobile'] : '',
                'platform'        => isset( $complaint['platform'] ) ? $complaint['platform'] : 1,
                'note'            => isset( $complaint['note'] ) ? $complaint['note'] : '',
                'complaint'       => json_encode( $complaint ),
                'complaintType'   => $this->get('dwd.util')->getComplaintTypesLabel( $complaint['type'] ),
                'complaintSource' => $this->get('dwd.util')->getComplaintSourceLabel( $complaint['source'] ),
                'param'           => $branch,
        ));
    }

    private function _packageOperators( $operators )
    {
        $opRecords           = array(
                                   'users'      => array(),
                                   'branchs'    => array(),
                                   'campaigns'  => array(),
                                   'orders'     => array(),
                               );

        foreach( $operators as $operator ){

            switch ($operator['type']) {
                case '重置密码':
                    $opRecords['users'][]      = array(
                                                    'id'         => $operator['userId'],
                                                    'name'       => $operator['userName'],
                                                    'op'         => 'resetPwd',
                                                    'res'        => $operator['res'],
                                                 );
                    break;
                case '封号':
                    $opRecords['users'][]      = array(
                                                    'id'         => $operator['userId'],
                                                    'name'       => $operator['userName'],
                                                    'reason'     => $operator['reason'],
                                                    'op'         => 'lock',
                                                    'res'        => $operator['res'],
                                                 );
                    break;
                case '纠错':
                    $opRecords['orders'][]     = array(
                                                    'id'         => $operator['orderId'],  
                                                    'content'    => $operator['content'],
                                                    'note'       => $operator['note'],
                                                    'op'         => 'correctOrder',
                                                    'res'        => $operator['res'],
                                                 );
                    break;
                case '解绑设备':
                    $opRecords['users'][]      = array(
                                                    'id'         => $operator['userId'],
                                                    'name'       => $operator['userName'],
                                                    'reason'     => $operator['reason'],
                                                    'op'         => 'unbindDevice',
                                                    'res'        => $operator['res'],
                                                 );
                    break;
                case '退款':
                    $opRecords['orders'][]     = array(
                                                    'id'         => $operator['userId'],
                                                    'name'       => $operator['userName'],
                                                    'op'         => 'refundOrder',
                                                    'res'        => $operator['res'],
                                                 );
                    break;
                case '编辑信息':              
                    $opRecords['branchs'][]    = array(
                                                    'id'         => $operator['branchId'],
                                                    'name'       => $operator['branchName'],
                                                    'op'         => 'modifyInfo',
                                                    'ext'        => $operator['ext'],
                                                    'res'        => $operator['res'],
                                                 );  
                    break;
                case '验证':              
                    $opRecords['orders'][]     = array(
                                                    'id'         => $operator['orderId'],
                                                    'branchId'   => $operator['branchId'],
                                                    'branchName' => $operator['branchName'],
                                                    'itemName'   => $operator['itemName'],
                                                    'userName'   => $operator['userName'], 
                                                    'op'         => 'redeem',
                                                    'res'        => $operator['res'],
                                                 );
                    break;    
                case '下线':              
                    $opRecords['campaigns'][]  = array(
                                                    'id'         => $operator['id'],
                                                    'name'       => $operator['name'],
                                                    'branchId'   => $operator['branchId'],
                                                    'branchName' => $operator['branchName'],
                                                    'reason'     => $operator['reason'],
                                                    'note'       => $operator['note'], 
                                                    'op'         => 'offline',
                                                    'res'        => $operator['res'],
                                                 );   
                    break;                      
                default: 
                    break;
            }
        }

        return $opRecords;
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
        $userId          = $this->getRequest()->get('userId', 0);
        $branchId        = $this->getRequest()->get('branchId', 0);

        $users           = array();
        $branchs         = array();
        $orders          = array();
        $campaigns       = array();

        if( $operators  != null ){
            $operators   = json_decode( $operators, true );
        }
 
        $opRecords       = self::_packageOperators( $operators );

        $complaint       = new Complaint();
        $complaint->setUserId( $userId );
        $complaint->setBranchId( $branchId );
        $complaint->setSource( $source );
        $complaint->setType( $type );
        $complaint->setTags( array() );

        if( false == empty( $opRecords['users'] ) ){
          $complaint->setUsers( $users );
        }

        if( false == empty( $opRecords['branchs'] ) ){
          $complaint->setBranchs( $opRecords['branchs'] );
        }

        if( false == empty( $opRecords['campaigns'] ) ){
          $complaint->setCampaigns( $opRecords['campaigns'] );
        }

        if( false == empty( $opRecords['orders'] ) ){ 
          $complaint->setOrders( $opRecords['orders'] );
        }

        $complaint->setCreatedAt( time() );
        $complaint->setStatus( 0 );
        $dm                = $this->get('doctrine_mongodb')->getManager();
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
 
        $response             = new Response();
        $ret                  = array(
                                   'res' => true, 
                                   'data' => $complaint->getId() 
                                ); 
        $response->setContent(  json_encode( $ret ) );
        return $response;
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
        $type                 = $this->getRequest()->get('type');
        $branchId             = $this->getRequest()->get('branchId');
        $userId               = $this->getRequest()->get('userId');
        $now                  = time();

        $tags                 = explode(',', $tags );
        foreach ($tags as $key => $tagId) {
           $tags[$key]        = intval( $tagId );
        }

        $complaint            = new Complaint();
        $complaint->setUserId( $userId );
        $complaint->setbranchId( $branchId );
        $complaint->setSource(  self::OTHER );  //其他咨询
        $complaint->setTags( $tags );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( $method );
        $complaint->setStatus( $status );
        $complaint->setNote( $note );  
        $complaint->setType( $type );
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
        $complaintId          = $this->getRequest()->get('id');
        $method               = $this->getRequest()->get('method'); 
        $mobile               = $this->getRequest()->get('mobile');
        $note                 = $this->getRequest()->get('note');
        $platform             = $this->getRequest()->get('platform'); 
        $status               = $this->getRequest()->get('status');
        $source               = $this->getRequest()->get('source');
        $tags                 = $this->getRequest()->get('tags');  
        $type                 = $this->getRequest()->get('type');
        $userId               = $this->getRequest()->get('userId');
        $branchId             = $this->getRequest()->get('branchId');
        
        $now                  = time();
        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $complaint            = $dm->getRepository('DWDDataBundle:Complaint')->findOneBy( array( '_id' => $complaintId) );
        $complaint->setSource(  $source );  //其他咨询
        $complaint->setTags( explode( ',', $tags ) );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( $method );
        $complaint->setStatus( $status );
        $complaint->setNote( $note );  
        $complaint->setCreatedAt( $now );
        $complaint->setType( $type );
        $complaint->setUserId( $userId );
        $complaint->setBranchId( $branchId );
        $complaint->setPlatform( $platform );

        if( $status != self::UNSOLVED ){
            $complaint->setResolvedAt( $now );
        }
         
        $dm->flush($complaint);

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
                                              'complaintWay' => $method,
                                              'complaintId'  => $id,
                                            ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
        
        $response             = new Response();
        $response->setContent( json_encode( $id ) );
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

            if( isset( $complaint['tags'] ) ){
                foreach ($complaint['tags'] as $tagId) {
                    if( isset( $tagsList[$tagId] ) ){
                        $tags[]        = $tagsList[$tagId];
                    } 
                }
            }
          
            $aaData[]              = array(
                                        $this->get('dwd.util')->getComplaintSourceLabel( $complaint['source'] ),
                                        isset( $complaint['mobile'] ) ? $complaint['mobile'] : '' ,
                                        implode(",", $tags),
                                        date("Y-m-d H:i:s", $complaint['createdAt']),
                                        isset( $complaint['resolvedAt'] ) ? date("Y-m-d H:i:s", $complaint['resolvedAt']) : "",
                                        $this->get('dwd.util')->getComplaintStatusLabel( $complaint['status'] ),
                                        $complaint['status'] == self::UNSOLVED ? "<a href='#' data-rel=" . $complaint['_id'] . " class='complaint-edit-btn'>[编辑]</a>  <a href='#' data-rel=" . $complaint['_id'] . " class='complaint-detail-btn'>[详情]</a>" : "<a href='#' data-rel=" . $complaint['_id'] . "  class='complaint-detail-btn'>[详情]</a>",
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
        $complaint            = $dm->getRepository('DWDDataBundle:Complaint')->findOneBy( array( '_id' => $id ) ); 

        $response             = new Response();
        $response->setContent( json_encode( $complaint ) );
        return $response; 
    }
}