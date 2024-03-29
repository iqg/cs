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
   public function getComplaintTagTypeId( $typeId ){
      $typeLabel          =  array(
                                 1    => 1,
                                 2    => 2,
                                 3    => 4,
                               );
        return  isset( $typeLabel[$typeId] ) ? $typeLabel[$typeId] : 0;
   }

   public function getPaymentTypeLabel( $typeId )
   {
        $typeLabel          =  array(
                                 1    => '余额支付',
                                 2    => '支付宝钱包支付',
                                 3    => '微信支付',
                                 4    => '支付宝网页支付',
                                 5    => '百川支付',
                                 999  => '老系统支付',
                               );
        return  isset( $typeLabel[$typeId] ) ? $typeLabel[$typeId] : '';
   }

   public function getOrderTypeLabel( $typeId )
   {
        $typeLabel          =  array(
                                 1    => '往下拍',
                                 2    => '预售',
                                 3    => '倒计时',
                                 4    => '回头客',
                                 5    => '品牌购买限制',
                                 6    => '睡前摇',
                                 7    => '市场活动奖励',
                                 8    => '扫一扫活动',
                               );
        return  isset( $typeLabel[$typeId] ) ? $typeLabel[$typeId] : '';
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
                                 12   => '订单失败',
                                 13   => '优惠券错误',
                                 14   => '已作废',
                               );
        return isset( $statusLabel[$statusId] ) ? $statusLabel[$statusId] : $statusId;
   }

   public function getOrderLogStatusLabel( $statusId )
   {
        $statusLabel        =  array(
                                 1    => '订单创建',
                                 2    => '请求支付',
                                 3    => '超时取消订单',
                                 4    => '客户端请求取消订单',
                                 5    => '客户端通知支付完成',
                                 6    => '服务端通知已接收',
                                 7    => '等待到账',
                                 8    => '服务端主动发起订单状态查询',
                                 9    => '余额不足支付失败',
                                 10   => '完成支付',
                                 11   => '完成兑换',
                                 12   => '订单超时完成',
                                 13   => '请求退款',
                                 14   => '服务端通知的订单没找到',
                                 15   => '服务端通知的订单状态错误',
                                 16   => '服务端通知的订单支付记录错误',
                                 17   => '服务端接收的微信通知签名错误',
                                 18    => '开始处理微信订单',
                                 19   => '调用v2接口更新订单发生错误',
                                 20   => 'V2支付成功',
                                 21   => '开始处理支付宝订单',
                                 22   => '服务端接收的支付宝通知签名错误',
                                 23   => '开始处理支付宝网页支付订单',
                                 24  => '订单延期',
                                 25   => '支付宝网页支付同步回调签名错误',
                                 26   => '获取了支付宝网页同步回调',
                                 27   => '客户端在没有获得同步通知的情况下查询',
                                 28   => '订单状态变更为未知',
                                 29   => '退款成功',
                                 30   => '退款失败',
                                 31   => '等待退款到账',
                                 32   => '服务端接收的支付宝通知签名错误',
                                 33   => '服务端退款重复通知',
                                 34   => '服务端查询订单状态失败',
                                 35   => '开始处理支付宝订单退款',
                                 36   => '活动订单创建',
                                 37   => '查询到微信退款成功',
                                 38   => '退款申请成功',
                                 39   => '客户端请求订单退款成功',
                                 40   => '退款补偿成功',
                                 41   => '退款补偿失败',
                                 42   => '客服请求取消订单',
                                 43   => '倒计时订单自动设置为兑换完成状态',
                                 44   => '订单用户申请退款被拒',
                                 45   => '开始处理百川支付订单',
                                 46   => '查询到百川退款成功',
                                 47   => '开始查询订单状态',
                                 48   => '查询订单状态成功',
                                 49   => '取消订单成功',
                               );
        return isset( $statusLabel[$statusId] ) ? $statusLabel[$statusId] : $statusId;
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
                                 10   => '兑吧金币兑换',
                                 11   => '兑吧金币兑换失败退回金币',
                                 12   => '退款补偿',
                                 13   => '金币兑换代金券',
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

   public function getOrderTableInfo( $typeId ,$orderType = null)
   {
      $tableInfo            =  array(
                                 2    => array(
                                           'label'     => '未领用',
                                           'code'      => 'waitredeem',
                                           'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '兑换码',
                                                             '状态',
                                                             '订单类型',
                                                             '过期时间',
                                                          ),
                                           'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'redeemNumber',
                                                             'status',
                                                             'type',
                                                             'expiredTime',
                                                          ),
                                           'operation' => array(
                                                            '验证',
                                                            $orderType ==6 ?'取消':'退款',
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 6    => array(
                                            'label'     => '已退款',
                                            'code'      => 'refund',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '退款时间',
                                                             '订单类型',
                                                             '原因',
                                                             '补充说明',
                                                           ),
                                            'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'refundTime',
                                                             'type',
                                                             'feedback',
                                                             'note',
                                                           ),
                                            'operation' => array(
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 3    => array(
                                            'label'     => '已过期',
                                            'code'      => 'expired',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '过期时间',
                                                             '订单类型',
                                                           ),
                                            'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'expiredTime',
                                                             'type'
                                                           ),
                                            'operation' => array(
                                                            $orderType ==6 ?'':'退款', //如果是睡前摇，则没有退款按钮
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 4    => array(
                                            'label'     => '已领用',
                                            'code'      => 'finish',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '领用时间',
                                                             '订单类型',
                                                           ),
                                            'field'     => array(
                                                             'itemName',
                                                             'branchName',
                                                             'redeemTime',
                                                             'type'
                                                           ),
                                            'operation' => array(
                                                            '纠错',
                                                            '日志',
                                                            '详情',
                                                          ),
                                         ),
                                 11    => array(
                                            'label'     => '其他',
                                            'code'      => 'processing',
                                            'head'      => array(
                                                             '商品',
                                                             '门店',
                                                             '状态',
                                                             '订单类型',
                                                           ),
                                           'field'      => array(
                                                             'itemName',
                                                             'branchName',
                                                             'status',
                                                             'type'
                                                           ),
                                            'operation' => array(
                                                            '详情',
                                                          ),
                                         ),
                               );
        return $typeId == 0 ? $tableInfo : $tableInfo[$typeId];
   }

   public function getComplaintStatusLabel( $statusId )
   {
        $statusLabel        =  array(
                                 0    => '待跟进',
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
                                 3    => '咨询',
                                 4    => '技术故障',
                                 5    => '其他',
                               );
        return isset( $sourceLabel[$sourceId] ) ?  $sourceLabel[$sourceId] : '' ;
   }

   public function getComplaintTypesLabel( $typeId )
   {
        $typeLabel          =  array(
                                 'usage'         => '领用问题',
                                 'refund'        => '订单退款',
                                 'correction'    => '信息纠错',
                                 'redeem'        => '订单验证',
                                 'offline'       => '要求下线',
                                 'modifyBranch'  => '修改信息',
                                 'ask'           => '咨询',
                                 'tech-error'    => '技术故障',
                                 'user'          => '用户咨询',
                                 'branch'        => '商户咨询',
                                 'other'         => '其他',
                                 'branchOffline' => '商户下线',
                               );

        return isset( $typeLabel[$typeId] ) ?  $typeLabel[$typeId] : '' ;
   }

   public function getComplaintTag( $tagId )
   {
        $tagLabel           =  array(
                                  1        => '用户咨询',
                                  2        => '技术故障',
                                  4        => '信息错误',
                                  5        => '无法验证',
                                  6        => '关门/装修',
                                  7        => '商户活动不详',
                                  8        => '拒绝领用',
                                  9        => '取消合作',
                                  10       => '缺货',
                                  14       => '用户原因要求退货',
                                  15       => '用户要求封号',
                                  16       => '违规封号',
                                  17       => '没有合作',
                                  18       => '验证有误',
                                  19       => '信息纠错',
                                  20       => '重置密码',
                                  21       => '解绑设备',
                                  22       => '咨询',
                                  23       => '其他',
                                  24       => '查看记录',
                                  25       => '要求退款',
                                  26       => '修改信息',
                                  27       => '商户原因取消订单',
                               );
        return isset( $tagLabel[$tagId] ) ?  $tagLabel[$tagId] : '' ;
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


   public function getCampaignBranchCategoryLabel( $categoryId )
   {
        $categoryLabel      =  array(
                                  1        => '饮料甜品',
                                  2        => '生活服务',
                                  3        => '日用商品',
                                  5        => '快餐小食',
                                  6        => '服装饰品',
                               );
        return isset( $categoryLabel[$categoryId] ) ?  $categoryLabel[$categoryId] : '' ;
   }

   public function getEnabledLabel( $enabled )
   {
      return intval( $enabled ) == 0 ? '已下线' : '在线';
   }

   public function getRecommendRecordNoteLabel( $enabled )
   {
       return intval( $enabled ) == 0 ? '未加金币' : '已加金币';
   }

   public function getUnbindReasonLabel( $reasonId )
   {
      $reasonLabel          =  array(
                                  1        => '别人帐号在我手机上登录了',
                                  2        => '原来的手机号不用了',
                                  3        => '一个帐号在好几个设备上登录了',
                                  4        => '其他原因',
                               );
      return isset( $reasonLabel[$reasonId] ) ?  $reasonLabel[$reasonId] : '' ;
   }

   public function getBranchOfflineReasonLabel( $reasonId )
   {
      $reasonLabel          =  array(
                                  1        => '推广效果不好',
                                  2        => '用户质量差',
                                  3        => '操作太麻烦',
                                  4        => '不结算成本太高',
                                  5        => '合作到期了',
                                  6        => '做了别的推广，不需要爱抢购了',
                                  7        => '门店要转让/倒闭/装修了',
                               );
      return isset( $reasonLabel[$reasonId] ) ?  $reasonLabel[$reasonId] : '' ;
   }

    public function getCouponStatusLabel( $reasonId )
    {
        $reasonLabel          =  array(
            1        => '未领用',
            2        => '未到使用日期',
            3        => '有效',
            4        => '过期',
            5        => '已使用',
        );
        return isset( $reasonLabel[$reasonId] ) ?  $reasonLabel[$reasonId] : '' ;
    }
    //获取商家活动状态
    public function getVendorCouponStatusLabel( $reasonId )
    {
        $reasonLabel          =  array(
            1        => '未使用',
            2        => '已使用',
            3        => '已过期',
        );
        return isset( $reasonLabel[$reasonId] ) ?  $reasonLabel[$reasonId] : '' ;
    }
    //campaign_branch是否需要预约．
    public function getBookTypeLabel( $enabled )
    {
        return intval( $enabled ) == 0 ? '不需要预约' : '需要预约';
    }
}
