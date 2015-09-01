<?php
/**
 * Created by PhpStorm.
 * User: caowei
 * Date: 8/24/15
 * Time: 17:48
 */

namespace DWD\CSAdminBundle\Util;

 
class Util
{
   public function getOrderTypeLabel( $typeId )
   { 
        $typeLabel          =  array(
                                 1    => '往下拍',
                                 2    => '往下拍',
                                 3    => '倒计时',
                                 4    => '活动赠送', 
                               );
        return $typeLabel[$typeId];
   }

   public function getOrderStatusLabel( $statusId )
   {
        $statusLabel        =  array(
                                 1    => '未支付',
                                 2    => '已支付',
                                 3    => '已过期',
                                 4    => '已使用',
                                 5    => '已取消',
                                 6    => '已退款',
                                 7    => '余额不足，支付失败',
                                 8    => '等待到账',
                                 9    => '支付超时',
                                 10   => '未知',
                                 11   => '申请退款中',
                               );
        return $statusLabel[$statusId];
   }
}