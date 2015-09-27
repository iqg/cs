<?php

namespace DWD\CSAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 

/**
 * Class CampaignBranchController
 * @package DWD\CSAdminBundle\Controller
 * @Route("/campaignbranch")
 */
class CampaignBranchController extends Controller
{
    /**
     *
     * @Route("/detail",name="dwd_csadmin_campaignbranch_detail")
     */
    public function detailAction(Request $request)
    {  
        $dataHttp        = $this->get('dwd.data.http');
        $id              = $this->getRequest()->get('id');
        $data            = array(
                               array(
                                   'url'    => '/campaignbranch/detail',
                                   'data'   => array(
                                       'campaignBranchId'    => $id,
                                   ),
                                   'method' => 'get',
                                   'key'    => 'detail',
                               ),
                           );
      
        $response        = $dataHttp->MutliCall( $data );   

        return $this->render('DWDCSAdminBundle:Dashboard:index.html.twig', array(
          	'errMsg'     => $errMsg,
            'zoneList'   => $response['zonelist']['data']['list'],
            'salerlist'  => $response['salerlist']['data']['list'],
        ));
    }
 
}