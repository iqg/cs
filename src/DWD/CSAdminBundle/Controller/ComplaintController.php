<?php

namespace DWD\CsAdminBundle\Controller;
 
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request; 

/**
 * Class ComplaintController
 * @package DWD\CsAdminBundle\Controller
 * @Route("/admin")
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
        $internalApiHost = 'http://127.0.0.1'; 
        $tagsList        = array();
        $data            = array( 
                                array(
                                    'url'    => $internalApiHost.'/complaint/taglist', 
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

 
        return $this->render('DWDCsAdminBundle:complaint:confirm.html.twig', array(
                'complaintTags'  => $complaintTags,
                'autoSelectTags' => $autoSelectTags,
        ));
    }
 
}