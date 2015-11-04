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
        $dataHttp          = $this->get('dwd.data.http');
        $id                = $this->getRequest()->get('campaignBranchId');
        $data              = array(
                                 array(
                                     'url'    => '/campaignbranch/detail',
                                     'data'   => array(
                                         'campaignBranchId'    => $id,
                                     ),
                                     'method' => 'get',
                                     'key'    => 'detail',
                                 ),
                                 array(
                                     'url'    => '/campaignbranch/categorylist',
                                     'data'   => array(
                                         'campaignBranchId'    => $id,
                                     ),
                                     'method' => 'get',
                                     'key'    => 'categorylist',
                                 ), 
                                 array(
                                     'url'    => '/campaignbranch/iteminfo',
                                     'data'   => array(
                                         'campaignBranchId'    => $id,
                                     ),
                                     'method' => 'get',
                                     'key'    => 'iteminfo',
                                 ),  
                             );
        
        $data              = $dataHttp->MutliCall( $data ); 
        $campaignBranch    = $data['detail']['data'];
        $iteminfo          = $data['iteminfo']['data'];
        $categoriesList    = array();
        if( !empty( $data['categorylist']['data'] ) )
        {
            $categories           = $data['categorylist']['data']['list'];
            foreach ($categories as  $categoryId) {
                $categoriesList[] = $this->get('dwd.util')->getCampaignBranchCategoryLabel( $categoryId );
            }
        }
 
        $str               = '<table class="table table-striped table-bordered"><tr></tr>';
        $str              .= "<tr><td>活动id</td><td>" . $campaignBranch['id'] . "</td></tr>";
        $str              .= "<tr><td>商品名称</td><td>" . $iteminfo['name'] . "</td></tr>";
        $str              .= "<tr><td>分类</td><td>" .  implode(',', $categoriesList) . "</td></tr>";
        $str              .= "<tr><td>市场价格</td><td>￥ " . $campaignBranch['start_price'] . "元</td></tr>";
        $str              .= "<tr><td>商品</td><td>" . $iteminfo['name'] . "</td></tr>";
        $str              .= "<tr><td>描述</td><td>" . $iteminfo['description'] . "</td></tr>";
        $str              .= "<tr><td>每日供应量</td><td>" . $campaignBranch['stock'] . "</td></tr>";
        $str              .= "<tr><td>当天库存</td><td>" . $campaignBranch['left'] . "</td></tr>";
        $str              .= "<tr><td>活动类型</td><td>" . $this->get('dwd.util')->getOrderTypeLabel( $campaignBranch['type'] ) . "</td></tr>";
        $str              .= "<tr><td>活动开始时间</td><td>" . $campaignBranch['start_time'] . "</td></tr>";
        $str              .= "<tr><td>活动结束时间</td><td>" . $campaignBranch['end_time'] . "</td></tr>";
        $str              .= "<tr><td>使用方式</td><td>" . $campaignBranch['allow_take_out'] . "</td></tr>";
        $str              .= "<tr><td>周几营业</td><td>" . $campaignBranch['week'] . "</td></tr>";
        $str              .= "<tr><td>特别提示</td><td>" . $campaignBranch['tips'] . "</td></tr>";
        $str              .= "<tr><td>状态</td><td>" . $this->get('dwd.util')->getEnabledLabel( $campaignBranch['enabled'] ) . "</td></tr>";
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