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
    
        if( false == empty( $complaint['branchs'] ) ){
           $branch['branch']          =  $complaint['branchs'][0]['name'];
           $data                      =  array(
                                            array(
                                                'url'    => '/saler/salerinfo', 
                                                'data'   => array(
                                                               'branchId'    => $complaint['branchs'][0]['id'],
                                                            ),
                                                'method' => 'get',
                                                'key'    => 'salerinfo',
                                            ),  
                                         ); 

           $data                      = $dataHttp->MutliCall($data);
           $branch['saler']           = $data['salerinfo']['data']['name'];
        } else if( false == empty( $complaint['orders'] ) ){
           $branch['branch']          =  isset($complaint['orders'][0]['branchName']) ? $complaint['orders'][0]['branchName'] : '';
           $branch['itemName']        =  isset($complaint['orders'][0]['itemName']) ? $complaint['orders'][0]['itemName'] : '';
           $data                      =  array(
                                            array(
                                                'url'    => '/saler/salerinfo', 
                                                'data'   => array(
                                                               'orderId'    => $complaint['orders'][0]['id'],
                                                            ),
                                                'method' => 'get',
                                                'key'    => 'salerinfo',
                                            ),  
                                         ); 

           $data                      = $dataHttp->MutliCall($data);

           $branch['saler']           = $data['salerinfo']['data']['name'];
        }
        
        $complaint['id']        =  strval($complaint['_id']);
        unset( $complaint['_id'] ); 

        $salerinfo              =  array(
                                     'name' => '',
                                   );
        if( isset( $complaint['salers'] ) || !empty( $complaint['salers'] ) )
        {
           $dataHttp            = $this->get('dwd.data.http'); 
           $data                = array(
                                      array(
                                         'url'    => '/saler/salerinfo',
                                         'data'   => array(
                                             'salerId'    => $complaint['salers'][0],
                                         ),
                                         'method' => 'get',
                                         'key'    => 'salerinfo',
                                      ),
                                  );
          
           $data                = $dataHttp->MutliCall( $data ); 
           $salerinfo           = $data['salerinfo']['data'];
        }
        if( empty( $branch['saler'] ) ){
            $branch['saler']    = $salerinfo['name'];
        }      

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
                                                    'reason'     => $operator['reason'],
                                                    'op'         => 'lock',
                                                 );
                    break;
                case '纠错':
                    $opRecords['orders'][]     = array(
                                                    'id'         => $operator['orderId'],  
                                                    'content'    => $operator['content'],
                                                    'itemName'   => $operator['itemName'],
                                                    'branchName' => $operator['branchName'],
                                                    'note'       => $operator['note'],
                                                    'op'         => 'correctOrder',
                                                 );
                    break;
                case '解绑设备':
                    $opRecords['users'][]      = array(
                                                    'id'         => $operator['userId'], 
                                                    'reason'     => $operator['reason'],
                                                    'op'         => 'unbindDevice',
                                                    'res'        => $operator['res'],
                                                 );
                    break;
            /*    case '退款':
                    $opRecords['orders'][]     = array(
                                                    'id'         => $operator['userId'],
                                                    'name'       => $operator['userName'],
                                                    'op'         => 'refundOrder',
                                                    'res'        => $operator['res'],
                                                 );
                    break; */
                case '更新商户信息':              
                    $opRecords['branchs'][]    = array(
                                                    'id'         => $operator['branchId'],
                                                    'name'       => $operator['branchName'],
                                                    'op'         => 'modifyInfo', 
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
                case '商户下线':              
                    $opRecords['branchs'][]    = array(
                                                    'id'         => $operator['branchId'],
                                                    'name'       => $operator['branchName'], 
                                                    'reason'     => $operator['reason'],
                                                    'note'       => $operator['note'], 
                                                    'op'         => 'offline',
                                                    'res'        => $operator['res'],
                                                 );   
                case '活动下线':              
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
          $complaint->setUsers( $opRecords['users'] );
        }
 
        if( false == empty( $opRecords['branchs'] ) ){
          $complaint->setBranchs( $opRecords['branchs'] );
          $branchIds     = array();
          foreach ( $opRecords['branchs'] as $branch ) {
            $branchIds[] = $branch['id'];    
          }

          $dataHttp        =  $this->get('dwd.data.http');
          $data            =  array(
                                  array(
                                      'url'    => '/branch/branchList',
                                      'data'   => array(
                                          'branchIds'      => implode(',', $branchIds), 
                                      ),
                                      'method' => 'get',
                                      'key'    => 'branchList',
                                  ), 
                              );
          $data            =  $dataHttp->MutliCall($data);
          $branchList      =  $data['branchList']['data'];
          $zoneList        =  array();
          $salerList       =  array();
          foreach ($branchList as  $branchInfo) {
            $zoneList[]    =  $branchInfo['zone_id'];
            $salerList[]   =  $branchInfo['saler_id'];
          }
          $complaint->setSalers( $salerList );
          $complaint->setZones( $zoneList );
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
        $users                = $this->getRequest()->get('users');
         
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
        $complaint->setUsers( $users );

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
                                              'complaintId'  => $complaintId,
                                            ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );
        
        $response             = new Response();
        $response->setContent( json_encode( $complaintId ) );
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
        $city                 = $this->getRequest()->get('city', 0);
        $saler                = $this->getRequest()->get('saler', 0);
        $branchId             = $this->getRequest()->get('branchId', 0);
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart');
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength');   
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $conditions           = array();
        if( $city != 0 ){
            $conditions['zones']    = intval($city);    
        }

        if( $saler != 0 ){
            $conditions['salers']   = intval($saler);
        }

        if( $branchId != 0 ){
            $conditions['branchId'] = intval($branchId);
        }
 
        $complaintList        = $dm->getRepository('DWDDataBundle:Complaint')->getAll( $conditions );
        $complaintCnt         = $dm->getRepository('DWDDataBundle:Complaint')->getCount( $conditions );
 
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
                                        $complaint['status'] == self::UNSOLVED ? "<a href='#' data-rel=" . $complaint['_id'] . " class='complaint-edit-btn'>[编辑]</a>  <a href='#' data-rel=" . $complaint['_id'] . " class='complaint-log-btn'>[日志]</a>" : "<a href='#' data-rel=" . $complaint['_id'] . "  class='complaint-log-btn'>[日志]</a>",
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

    /**
     *
     * @Route("/complaint/complaintlog",name="dwd_csadmin_complaint_log")
     */
    public function logAction(Request $request)
    {    
        $id                = $this->getRequest()->get('id');
        $dm                = $this->get('doctrine_mongodb')->getManager(); 
        $complaint         = $dm->getRepository('DWDDataBundle:Complaint')->findOneBy( array( '_id' => $id ) ); 
       
        $str               = '<table class="table table-striped table-bordered"><tr><th>操作</th><th>内容</th></tr>';

        $branchs           = $complaint->getBranchs();
        $users             = $complaint->getUsers();
        $orders            = $complaint->getOrders();
        $campaigns         = $complaint->getCampaigns();

        if( !empty( $branchs ) ){

            foreach ($branchs as $branch) {
                switch ($branch['op']) {
                    case 'modifyInfo':
                        $str   .= "<tr><td>更新商户信息</td><td>" . $branch['name'] . "</td></tr>";
                        break;
                    case 'offline':
                        $str   .= "<tr><td>商户下线</td><td>" . $branch['name'] . "</td></tr>";
                        break;
                    default:
                        break;
                }
            }
        }

        if( !empty( $users ) ){

            foreach ($users as $user) {

                $data                 = array(
                                            array(
                                                'url'    => '/user/userinfo', 
                                                'data'   => array(
                                                                'userId' => $user['id'],
                                                            ),
                                                'method' => 'get',
                                                'key'    => 'userinfo',
                                            ),  
                                        ); 
                $dataHttp             = $this->get('dwd.data.http');
                $data                 = $dataHttp->MutliCall($data);
                $username             = $data['userinfo']['data']['username'];
                switch ($user['op']) {
                    case 'resetPwd':
                        $str   .= "<tr><td>重置密码</td><td>" . $username . "</td></tr>";
                        break;
                    case 'lock':
                        $str   .= "<tr><td>封号</td><td>" . $username . "</td></tr>";
                        break;
                    case 'unbindDevice':
                        $str   .= "<tr><td>解绑设备</td><td>" . $username . "</td></tr>";
                        break; 
                    default:
                        break;
                }
            }
        }

        if( !empty( $orders ) ){

            foreach ($orders as $order) {
                switch ($order['op']) {
                    case 'correctOrder':
                        $str   .= "<tr><td>纠错</td><td>" . ( isset( $order['itemName'] ) ? $order['itemName'] : '' ) . "</td></tr>";
                        break;
                    case 'redeem':
                        $str   .= "<tr><td>验证</td><td>" . ( isset( $order['itemName'] ) ? $order['itemName'] : '' ) . "</td></tr>";
                        break; 
                    default:
                        break;
                }
            }
        }

        if( !empty( $campaigns ) ){

            foreach ($campaigns as $campaign) {
                switch ($campaign['op']) {
                    case 'offline':
                        $str   .= "<tr><td>活动下线</td><td>" . $campaign['name'] . "</td></tr>";
                        break; 
                    default:
                        break;
                }
            }
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
}