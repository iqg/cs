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
         case 'lockUser':
            $complaintInfo['reason']            = $this->getRequest()->get('reason');
            break;
         case 'redeem':
            $complaintInfo['orderId']           = intval( $this->getRequest()->get('orderId') );
            break;
         case 'orderCorrect':
            $complaintInfo['content']          = $this->getRequest()->get('content');
            $complaintInfo['needOffline']      = intval( $this->getRequest()->get('needOffline') );
            $complaintInfo['campaignBranchId'] = $this->getRequest()->get('campaignBranchId');
            $complaintInfo['offlined']         = intval( $this->getRequest()->get('offlined') ); 
            break;
         case 'updateBranch':
            $complaintInfo['address']          = $this->getRequest()->get('address');
            $complaintInfo['redeemTels']       = intval( $this->getRequest()->get('redeemTels') );
            $complaintInfo['redeemTime']       = $this->getRequest()->get('redeemTime');
            $complaintInfo['branchTel']         = intval( $this->getRequest()->get('branchTel') );  
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
}