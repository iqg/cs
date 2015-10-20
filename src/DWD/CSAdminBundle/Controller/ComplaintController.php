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

    private function _packageComplaintInfo( $op )
    {
       $complaintInfo = array();

       switch ( $op ) {

         case 'branchOffline':
            $complaintInfo['reason']           = $this->getRequest()->get('reason');
            break;
         case 'lockUser':
            $complaintInfo['reason']           = $this->getRequest()->get('reason');
            $complaintInfo['reasonId']         = $this->getRequest()->get('reasonId');
            if( intval( $this->getRequest()->get('locked') ) == 1 ){
                $complaintInfo['locked']       = 1;
            }
            break;
         case 'redeem':
            $complaintInfo['orderId']          = intval( $this->getRequest()->get('orderId') );
            break;
         case 'orderCorrect':
            $complaintInfo['orderId']          = intval( $this->getRequest()->get('orderId') );
            $complaintInfo['content']          = $this->getRequest()->get('content');
            $complaintInfo['needOffline']      = intval( $this->getRequest()->get('needOffline') );
            $complaintInfo['campaignBranchId'] = $this->getRequest()->get('campaignBranchId');
            if( intval( $this->getRequest()->get('offlined') ) == 1 ){
                $complaintInfo['offlined']     = 1; 
            }
             
            break;
         case 'updateBranch':
            $complaintInfo['address']          = $this->getRequest()->get('address');
            $complaintInfo['redeemTels']       = intval( $this->getRequest()->get('redeemTels') );
            $complaintInfo['redeemTime']       = $this->getRequest()->get('redeemTime');
            $complaintInfo['branchTel']        = intval( $this->getRequest()->get('branchTel') );  
            break;
        case 'viewInfo':
            $complaintInfo['view']             = $this->getRequest()->get('view');
            break;
         default:
           break;
       }

       return $complaintInfo;
    }

    private function _packageOpLog()
    {
        $opLog                = array(     
                                    array(
                                        'adminId'   => $this->getUser()->getId(),
                                        'op'        => $this->getRequest()->get('op'),
                                        'timestamp' => time(),
                                     ),
                                );

        return $opLog;
    }

    
    /**
     *
     * @Route("/complaint/submit",name="dwd_csadmin_complaint_submit")
     */
    public function submitAction(Request $request)
    {
        $branchId             = $this->getRequest()->get('branchId', 0);
        $userId               = $this->getRequest()->get('userId', 0);
        $orderId              = $this->getRequest()->get('orderId', 0);
        $op                   = $this->getRequest()->get('op');
        $source               = $this->getRequest()->get('source');
        $mobile               = $this->getRequest()->get('mobile');
        $status               = $this->getRequest()->get('status');
        $method               = $this->getRequest()->get('method'); 
        $note                 = $this->getRequest()->get('note');
        $tags                 = $this->getRequest()->get('tags');
        $platform             = $this->getRequest()->get('platform');

        if( !empty( $tags ) ){
            $tags             = explode( ',', $tags );
            foreach ($tags as $key => $value) {
                $tags[$key]   = intval($value);
            }
        }  else {
            $tags             = array();
        }

        $now                  = time();
        $complaintInfo        = self::_packageComplaintInfo( $op );
        $opLog                = self::_packageOpLog();
 
        $complaint            = new Complaint();
        if( $branchId != 0 ){
            $complaint->setbranchId( $branchId );
            $dataHttp        = $this->get('dwd.data.http');
            $data            =  array( 
                                    array(
                                        'url'    => '/branch/branchinfo',
                                        'data'   => array(
                                            'branchId'  => $branchId, 
                                        ),
                                        'method' => 'get',
                                        'key'    => 'branchinfo',
                                    )
                                );
            $data            =  $dataHttp->MutliCall($data); 
            $branchInfo      =  $data['branchinfo']['data'];
       
            $complaint->setSalerId( $branchInfo['saler_id'] );
            $complaint->setZoneId( $branchInfo['zone_id'] );
        } else if( $orderId != 0 ){
            $dataHttp        = $this->get('dwd.data.http');
            $data            =  array( 
                                    array(
                                        'url'    => '/branch/branchinfo',
                                        'data'   => array(
                                            'orderId'  => $orderId, 
                                        ),
                                        'method' => 'get',
                                        'key'    => 'branchinfo',
                                    )
                                );
            $data            =  $dataHttp->MutliCall($data); 
            $branchInfo      =  $data['branchinfo']['data'];
            $complaint->setSalerId( $branchInfo['saler_id'] );
            $complaint->setZoneId( $branchInfo['zone_id'] );
        }
        
        if( $userId != 0 ){
            $complaint->setuserId( $userId );
        }

        if( $platform ){
            $complaint->setPlatform( $platform );
        }

        if( !empty( $complaintInfo ) ){
            $complaint->setComplaintInfo( $complaintInfo );
        }

        $complaint->setSource( intval( $source ) );   
        $complaint->setTags( $tags );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( intval( $method ) );
        $complaint->setStatus( intval( $status ) );
        $complaint->setNote( $note );   
        $complaint->setCreatedAt( $now );
        $complaint->setOp( $op );
        $complaint->setOpLog( $opLog );

        if( intval( $status ) != self::UNSOLVED ){
            $complaint->setResolvedAt( $now );
        }

        $dm                = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($complaint);
        $res               = $dm->flush();

        $logRecord         = array(
                              'route'    => $this->getRequest()->get('_route'),
                              'res'      => true,
                              'adminId'  => $this->getUser()->getId(),
                              'ext'      => array(
                                              'complaintId'  => $complaint->getId(),
                                              'tags'         => $tags,
                                              'mobile'       => $mobile,
                                              'status'       => $status,
                                              'note'         => $note,
                                              'platform'     => $platform,
                                              'complaintWay' => $method,                                             ),
                             );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );

        $response             = new Response();
        $response->setContent( json_encode( $complaint->getId() ) );
        return $response;
    }


    /**
     *
     * @Route("/complaint/save",name="dwd_csadmin_complaint_save")
     */
    public function saveAction(Request $request)
    { 
        $complaintId          = $this->getRequest()->get('id');
        $mobile               = $this->getRequest()->get('mobile');
        $status               = $this->getRequest()->get('status');
        $method               = $this->getRequest()->get('method'); 
        $note                 = $this->getRequest()->get('note');
        $tags                 = $this->getRequest()->get('tags'); 

        if( !empty( $tags ) ){
            $tags             = explode( ',', $tags );
            foreach ($tags as $key => $value) {
                $tags[$key]   = intval($value);
            }
        }  else {
            $tags             = array();
        }

        $now                  = time();   
        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $complaint            = $dm->getRepository('DWDDataBundle:Complaint')->findOneBy( array( '_id' => $complaintId) );
 
        $complaint->setTags( $tags );
        $complaint->setMobile( $mobile );
        $complaint->setMethod( intval( $method ) );
        $complaint->setStatus( intval( $status ) );
        $complaint->setNote( $note );     

        if( intval( $status ) != self::UNSOLVED ){
            $complaint->setResolvedAt( $now );
        }
   //     var_dump( $complaint );
    //    var_dump( $complaint->getOp() );
        $complaintDetail       = $dm->getRepository('DWDDataBundle:Complaint')->getComplaint( $complaintId );
    //    var_dump( $complaintDetail );

        switch ( $complaintDetail['op'] ) {
            case 'lockUser':
                $complaintInfo = $complaintDetail['complaintInfo']; 
                if( false == isset( $complaintInfo['locked'] ) && 1 == intval( $this->getRequest()->get('locked') ) )
                {
                    $complaintInfo['locked']  = 1;
                }
                $complaint->setComplaintInfo( $complaintInfo );  
                break; 
            case 'orderCorrect':
                $complaintInfo = $complaintDetail['complaintInfo']; 

                if( false == isset( $complaintInfo['offlined'] )  )
                {
                    $complaintInfo['needOffline']  = intval( $this->getRequest()->get('needOffline') );
                }

                if( false == isset( $complaintInfo['offlined'] ) && 1 == intval( $complaintInfo['needOffline'] )  && 1 == intval( $this->getRequest()->get('offlined') ) )
                {
                    $complaintInfo['offlined']  = 1;
                }
                $complaint->setComplaintInfo( $complaintInfo );  
                break; 
            default: 
                break;
        }

        $dm->flush($complaint);

        $logRecord            = array(
                                  'route'    => $this->getRequest()->get('_route'),
                                  'res'      => true,
                                  'adminId'  => $this->getUser()->getId(),
                                  'ext'      => array(
                                                  'tags'         => $tags,
                                                  'mobile'       => $mobile,
                                                  'status'       => $status,
                                                  'note'         => $note, 
                                                  'complaintWay' => $method,
                                                  'complaintId'  => $complaintId,
                                                ),
                                );
        $this->get('dwd.oplogger')->addCommonLog( $logRecord );

        $response             = new Response();
        $response->setContent( json_encode( $complaint->getId() ) );
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
        $iDisplayStart        = $this->getRequest()->get('iDisplayStart', 0);
        $iDisplayLength       = $this->getRequest()->get('iDisplayLength', 20);   
        $sSearch              = $this->getRequest()->get('sSearch', null);

        $dm                   = $this->get('doctrine_mongodb')->getManager();
        $conditions           = array();
        if( $city != 0 ){
            $conditions['zoneId']    = intval($city);    
        }

        if( $saler != 0 ){
            $conditions['salerId']   = intval($saler);
        }

        if( $branchId != 0 ){
            $conditions['branchId']  = intval($branchId);
        }

        $options              = array(
                                  'skip'  => $iDisplayStart,
                                  'limit' => $iDisplayLength,
                                );
 
        $complaintList        = $dm->getRepository('DWDDataBundle:Complaint')->getAll( $conditions, $options );
        $complaintCnt         = $dm->getRepository('DWDDataBundle:Complaint')->getCount( $conditions );
  

        $aaData                    = array();
    
        foreach( $complaintList as $complaint ){
            $tags                  = array();

            if( isset( $complaint['tags'] ) ){
                foreach ($complaint['tags'] as $tagId) { 
                    $tags[]        = $this->get('dwd.util')->getComplaintTag( $tagId ); 
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
        $complaintId       = $this->getRequest()->get('id');
        $dm                = $this->get('doctrine_mongodb')->getManager(); 
        $complaint         = $dm->getRepository('DWDDataBundle:Complaint')->getComplaint( $complaintId );
       
        $str               = '<table class="table table-striped table-bordered"><tr><th>管理员</th><th>内容</th><th>时间</th></tr>';
        $dataHttp          = $this->get('dwd.data.http');
        foreach( $complaint['oplog'] as $log ){ 
            $data          =  array( 
                                    array(
                                        'url'    => '/user/userinfo',
                                        'data'   => array(
                                            'userId'  => $log['adminId'], 
                                        ),
                                        'method' => 'get',
                                        'key'    => 'userinfo',
                                    )
                                );
            $data          =  $dataHttp->MutliCall($data); 
            $userInfo      =  $data['userinfo']['data'];
            $op            =  '';

            switch ( $log['op'] ) {
                case 'branchOffline':
                    $op    = '商户下线';
                    break;
                case 'unbindUser':
                    $op    = '解绑用户';
                    break;
                case 'lockUser':
                    $op    = '用户封号';
                    break;
                case 'redeem':
                    $op    = '订单验证';
                    break;
                case 'orderCorrect':
                    $op    = '信息纠错';
                    break;
                case 'updateBranch':
                    $op    = '编辑商户';
                    break;
                case 'viewInfo':
                    $op    = '查看信息';
                    break;
                case 'resetPwd':
                    $op    = '重置密码';
                    break;
                case 'ask':
                    $op    = '咨询';
                    break;
                case 'tech-error':
                    $op    = '技术故障';
                    break;
                case 'other':
                    $op    = '其他';
                    break;
                default: 
                  break;
            }

            $str          .= "</tr><td>" . $userInfo['username'] . "</td><td>$op</td><td>" . date("Y-m-d H:i:s", $log['timestamp']) . "</td></tr>";
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