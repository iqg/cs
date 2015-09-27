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

   public function getSMSTypeLabel( $typeId )
   {
        $typeLabel          =  array(
                                 1    => '注册',
                                 2    => '重置密码',
                                 3    => '绑定手机',
                                 4    => '推荐好友',
                                 5    => '重要事项提醒',
                                 6    => '后台操作退款提醒',
                                 7    => '扫码活动注册成功短信',
                                 8    => '发送品牌管理帐号和初始密码',
                                 9    => '活动批准上线发送通知短信',
                                 10   => '后台操作订单取消提醒',
                                 11   => '订单相关反馈的短信提醒',
                                 12   => '审核不通过提醒', 
                                 13   => '审核通过提醒',
                                 14   => '审核通过，活动上线提醒',
                                 15   => '商户App绑定手机号码',
                                 16   => '第三方帐号绑定手机号',
                                 17   => '培训失败提醒',
                               );
        return isset( $typeLabel[$typeId] ) ? $typeLabel[$typeId] : '' ;
   }

   public function getCoinTypeLabel( $typeId )
   {
        $typeLabel          =  array(
                                 0    => '所有记录',
                                 1    => '每日签到',
                                 2    => '推荐好友',
                                 3    => '订单评论',
                                 4    => '活动赠送',
                                 5    => '余额兑换',
                                 6    => '金币充值',
                                 7    => '下单奖励',
                                 8    => '取消订单补偿',
                                 9    => '订单纠错通过奖励',
                               );
        return $typeLabel[$typeId];
   }

   public function getBalanceTypeLabel( $typeId )
   {
        $typeLabel          =  array( 
                                 1    => '余额支付',
                                 2    => '充值卡充值',
                                 3    => '退款',
                                 4    => '充值',
                                 5    => '活动赠送',
                                 6    => '金币兑换',
                                 7    => '支付失败退款',
                                 8    => '订单取消退款',
                                 9    => '新用户赠送',
                                 10   => '优质用户奖励',
                                 11   => '退款补偿',
                                 12   => '六一活动注册赠送', 
                                 13   => '退款撤回',
                               );
        return $typeLabel[$typeId];
   }

   public function getLockReasonTypeLabel( $typeId )
   {
        $typeLabel          =  array( 
                                 0    => '未知',
                                 1    => '使用多个账号重复购买同一商品',
                                 2    => '领用时使用截图或抄写验证码',
                                 3    => '一天内在同一门店领用1份以上商品',
                                 4    => '与商户发生纠纷，在门店内闹事',
                                 5    => '转卖爱抢购订单进行牟利',
                                 6    => '其他',
                                 7    => '退款过多',
                               );
        return $typeLabel[$typeId];
   }

   public function getOrderTableInfo( $typeId )
   {
      $tableInfo            =  array(
                                 2    => array(
                                           'label'     => '未领用',
                                           'code'      => 'wait-redeem',
                                           'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '兑换码',
                                                          ),
                                           'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'redeemNumber',
                                                          ),
                                           'operation' => array(
                                                            '退款',
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 3    => array(
                                            'label'     => '已退款',
                                            'code'      => 'refund',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '退款时间',
                                                           ),
                                            'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'refundTime',
                                                           ),
                                            'operation' => array( 
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 4    => array(
                                            'label'     => '已过期',
                                            'code'      => 'expired',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '过期时间',
                                                           ),
                                            'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'expiredTime',
                                                           ),
                                            'operation' => array(
                                                            '退款',
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 5    => array(
                                            'label'     => '已完成',
                                            'code'      => 'finish',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '领用时间',
                                                           ),
                                            'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'redeemTime',
                                                           ),
                                            'operation' => array( 
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 6    => array(
                                            'label'     => '待处理',
                                            'code'      => 'processing',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '状态',
                                                           ),
                                           'field'      => array(
                                                             'itemName',
                                                             'branchName',
                                                             'status',
                                                           ),
                                            'operation' => array(
                                                            '退款',
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                               );
        return $typeId == 0 ? $tableInfo : $tableInfo[$typeId];
   }

   public function getComplaintStatusLabel( $statusId )
   {
        $statusLabel        =  array( 
                                 0    => '未解决',
                                 1    => '已解决',
                                 10   => '已解决（退款处理）',
                                 11   => '已解决（延期处理）',
                                 99   => '已解决（其它）', 
                               );
        return  isset( $statusLabel[$statusId] ) ?  $statusLabel[$statusId] : '' ;
   }

   public function getComplaintSourceLabel( $sourceId )
   {
        $sourceLabel        =  array( 
                                 1    => '用户投诉',
                                 2    => '商户投诉',
                                 3    => '其他',
                               );
        return isset( $sourceLabel[$sourceId] ) ?  $sourceLabel[$sourceId] : '' ;
   }

   public function getComplaintTypesLabel( $typeId )
   {
        $typeLabel          =  array( 
                                 'usage'        => '领用问题',
                                 'refund'       => '订单退款',
                                 'correction'   => '信息纠错',
                                 'redeem'       => '订单验证',
                                 'offline'      => '要求下线',
                                 'modifyBranch' => '修改信息',
                                 'ask'          => '咨询',
                                 'tech-error'   => '技术故障', 
                               );
        return isset( $typeLabel[$typeId] ) ?  $typeLabel[$typeId] : '' ;
   }

   public function getCampaignBranchTypeLabel( $typeId )
   {
        $typeLabel          =  array( 
                                  1        => '往下拍',
                                  2        => '预售',
                                  3        => '倒计时',
                                  4        => '回头客',
                                  5        => '品牌门店限制商品',
                                  6        => '睡前摇活动',
                                  7        => '市场活动奖励活动', 
                               );
        return isset( $typeLabel[$typeId] ) ?  $typeLabel[$typeId] : '' ;
   }
}