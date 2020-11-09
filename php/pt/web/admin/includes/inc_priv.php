<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

//商品管理权限
    $purview['goods_list']        = 'goods_manage';
    $purview['goods_trash']       = 'goods_manage';
    $purview['category_list']     = 'category_manage';  
    $purview['comment_list']    = 'comment_manage';
    $purview['label_list']    = 'label_manage';


//促销管理权限
    $purview['bonus_type']    = 'coupon_manage';
    $purview['seckill_list']    = 'seckill_manage';
    $purview['lottery_list']    = 'lottery_manage';
    $purview['assist_list']    = 'assist_manage';


//文章管理权限
    $purview['articlecat_list']   = 'articlecat_manage';
    $purview['article_list']      = 'article_manage';
    

//会员管理权限
    $purview['users_manage']        = 'user_manage';
    $purview['users_sign']        = 'users_sign';
    $purview['rank_list']        = 'rank_manage';

//权限管理
    $purview['admin_logs']           = 'logs_manage';
    $purview['admin_list']           = 'admin_manage';
    $purview['admin_role']           = 'role_manage';

//商店设置权限
    $purview['shop_config']       = 'config_manage';
    $purview['setting_manage']       = 'config_manage';
    $purview['payment_manage']      = 'payment_manage';
    $purview['shipping_manage']     = 'ship_manage';
    $purview['shipping_area_manage']     = 'shiparea_manage';
    $purview['store_list']     = 'store_manage';

//广告管理

    $purview['ad_list']              = 'ads_manage';

//订单管理权限
    $purview['order_list']        = 'order_manage';
    $purview['tuan_list']        = 'tuan_manage';
    $purview['order_query']       = 'order_view';
    $purview['edit_order_print']  = 'order_os_edit';
    $purview['delivery_list']    = 'delivery_manage';
    $purview['back_list']        = 'back_manage';
    $purview['invoice_list']        = 'invoice_manage';


//微信设置权限
    $purview['wx_config']               = 'wxconfig';
    $purview['wx_menu']               = 'wxmenu';
    $purview['wx_corn']               = 'wx_corn';
    $purview['wx_act']               = 'wx_act';
    $purview['wx_win']               = 'wx_win';


?>