<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . 'source/library/order.php');
//处理团过失败订单
//-start

$old_pay_time = gmtime() - $GLOBALS['_CFG']['tuan_time']*3600;

$sql="select extension_id,pay_time from ".$GLOBALS['aos']->table('order_info')." where  tuan_first=1 and pay_status=2 and extension_code in ('tuan','lottery','miao') and tuan_status in(0,1) and tuan_first=1 and pay_status=2 and pay_time< ".$old_pay_time." order by order_id desc LIMIT 20";

$order_list=$GLOBALS['db']->getAll($sql);

if(!empty($order_list) ){
    
    
    foreach($order_list as $v){
       $sql="select count(o.order_id) from ".$GLOBALS['aos']->table('order_info')." as o  where o.extension_id=".$v['extension_id']." and o.pay_status = 2 and o.extension_code != '' and o.tuan_status = 1 and o.order_status = 1";
        $team_count= $GLOBALS['db']->getOne($sql);
       $sql="select g.goods_name,g.goods_id,g.goods_price,o.user_id,o.surplus,o.tuan_num,o.order_status,o.pay_status,o.shipping_status,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.`extension_id`,o.`extension_code`,o.act_id,o.bonus_id,o.integral from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id  where  o.extension_id=".$v['extension_id']." and  o.order_status = 1  and o.tuan_status in(0,1) ";
        $team_list= $GLOBALS['db']->getAll($sql);
        $extension_code=$team_list[0]['extension_code'];
        $act_id=$team_list[0]['act_id'];
        if($extension_code=='miao'){
            $now_time=gmtime();
            $sql="SELECT seck_tuan_num from ".$GLOBALS['aos']->table('seckill')." where seckill_id = ".$act_id;
            $tuan_num=$GLOBALS['db']->getOne($sql);
        }elseif($extension_code=='lottery'){
            $now_time=gmtime();
            $sql="SELECT lottery_tuan_num from ".$GLOBALS['aos']->table('lottery')." where lottery_id = ".$act_id;
            $tuan_num=$GLOBALS['db']->getOne($sql);
        }elseif($extension_code=='tuan'){
            $goods_tuan_number=get_tuan_number($team_list[0]['goods_id']);
            $tuan_num = min($goods_tuan_number);
            
        }
        if($team_count<$tuan_num){
        	//不够人数自动退款
            foreach($team_list as $f){
                return_user_surplus_integral_bonus($f);
                if($f['pay_status']!=2){
                    $arr['tuan_status']  = 4;
                    $arr['order_status']  = 3;
                    update_order($f['order_id'], $arr);
                    order_action($f['order_sn'], $arr['order_status'], $f['shipping_status'], $f['pay_status'], '退款未支付订单设无效', '');
                }else{

                	$arr['tuan_status']  = 3;
    				update_order($f['order_id'], $arr);
                	//成团失败模板消息

                    $wx_url=$aos->url();
                    $wx_url.="index.php?c=share&tuan_id=".$f['extension_id'];
    	            $goods_price="¥".$f[goods_price];
                    $money_paid="¥".$f[money_paid]+$f[surplus];
                    $openid=getOpenid($f['user_id']);
                    $message=getMessage(4);
                    $wx_title = "组团失败通知";
                    $wx_desc = $message[title]."\r\n拼团商品：".$f[goods_name]."\r\n商品金额：".$goods_price."\r\n退款金额：¥".$money_paid."\r\n".$message[note];
                    //$wx_pic = $aos_url;
                    $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);

    	            $r= refunds($f);
                    if($r){
                        /* 如果支付成功使用库存，且退款时库存，则增加库存 */
                        if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == 1 && $extension_code!='lottery'){
                            change_order_goods_storage($f['order_id'], false, 2);
                        }
                    } 
                }
            } 
    	}else{
    		//够团人数自动成团
            cheng_tuan($v['extension_id']);
    		//order_action($f['order_sn'], $f['order_status'], $f['shipping_status'], $f['pay_status'], '自动成团', '');
    	}
       
    }

}


//-end
//微信支付待退款订单处理

    $sql="select g.goods_name,g.goods_id,g.goods_price,o.user_id,o.surplus,o.tuan_num,o.order_status,o.pay_status,o.shipping_status,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.`extension_id`,o.`extension_code`,o.act_id from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id left join ".$GLOBALS['aos']->table('payment')." as p on o.pay_id = p.pay_id where  o.order_status = 1 and o.pay_status = 2 and o.tuan_status = 3 and p.pay_code = 'wxpay' and  o.extension_code != '' LIMIT 20";
    $lottery_list= $GLOBALS['db']->getAll($sql);
  

    //处理未退款
    foreach($lottery_list as $f){
        
        $r= refunds($f);
        if($r){
            /* 如果支付成功使用库存，且退款时库存，则增加库存 */
            if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == 1 && $f['extension_code'] != 'lottery'){
                change_order_goods_storage($f['order_id'], false, 2);
            }
        } 
        
    } 
        
//自动收货
$old_shipping_time = gmtime() - 7*3600*24;
$sql="select o.money_paid,o.user_id,o.order_id,o.order_status,o.pay_status,o.order_sn,g.goods_id,g.goods_name from ".$GLOBALS['aos']->table('order_info')." as o left join ".$aos->table('order_goods')." as g on o.order_id = g.order_id where o.order_status = 5 and o.pay_status = 2 and o.shipping_status = 1 and o.shipping_time < $old_shipping_time LIMIT 20";
$await_receipt=$GLOBALS['db']->getAll($sql);
if($await_receipt){
	foreach($await_receipt as $vo){
		$arr=array();
		$arr['lastmodify']=gmtime();
		$arr['shipping_status']=2;
		update_order($vo['order_id'], $arr);
		order_action($order['order_sn'], $vo['order_status'], 2, $vo['pay_status'], '自动收货', '');

	}
}

//未支付订单自动无效

$sql="select user_id,order_id,order_status,pay_status,order_sn from ".$GLOBALS['aos']->table('order_info')." where order_status = 1 and pay_status = 0 and shipping_status = 0 and add_time < $old_shipping_time ";
$await_receipt=$GLOBALS['db']->getAll($sql);
if($await_receipt){
    foreach($await_receipt as $vo){
        $arr=array();
        $arr['order_status']  = 3;
        update_order($vo['order_id'], $arr);
        order_action($vo['order_sn'], $arr['order_status'], $vo['shipping_status'], $vo['pay_status'], '成团未支付订单设无效', '');
        

    }
}
    
//助力订单
$start_time = gmtime()-24*3600;
$sql = "SELECT o.user_id,o.order_id,o.assist_num,o.tuan_num, o.extension_id,o.pay_time, o.tuan_status,g.goods_name " .
           " FROM " .$GLOBALS['aos']->table('order_info') . " AS o left join ".$GLOBALS['aos']->table('order_goods') .
           " as g on o.order_id = g.order_id WHERE o.extension_code = 'assist' and o.tuan_status = 1  and o.pay_time<$start_time ORDER BY add_time DESC limit 20";
$result = $GLOBALS['db']->getAll($sql);
foreach ($result AS $idx => $row)
{
    
    $openid=getOpenid($row['user_id']);
    $time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
    if($row['tuan_status']==1){
        if($row['assist_num']<$row['tuan_num']){
            $sql = "UPDATE ". $GLOBALS['aos']->table('order_info') ." SET tuan_status = '4' WHERE order_id='".$row['order_id']."'";
            $GLOBALS['db']->query($sql);
            $message=getMessage(13);
            $wx_title = "助力活动失败通知";
            $wx_desc = $message[title]."\r\n任务名称：助力享免单\r\n助力商品：".$row[goods_name]."\r\n失败时间：".$time."\r\n".$message['note'];
            //$wx_pic = $aos_url;
            $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
            order_action($row['order_sn'], 1, 0, 2, '助力活动失败', '');
        }  
    }
    

}

//佣金处理
$dist_time = gmtime()-24*60*60*($_CFG['refund_time']+5);
$sql = "SELECT o.user_id,o.order_id,o.order_sn,o.parent_id, o.extension_code,o.pay_time, g.goods_id " .
           " FROM " .$GLOBALS['aos']->table('order_info') . " AS o left join ".$GLOBALS['aos']->table('order_goods') .
           " as g on o.order_id = g.order_id WHERE o.order_status = 5 and shipping_status = 2 and pay_status = 2  and is_dist = 0 and parent_id > 0 and o.pay_time<$dist_time and extension_code != 'assist' ORDER BY pay_time DESC limit 10";
$result = $GLOBALS['db']->getAll($sql);
foreach ($result AS $idx => $row)
{
    $sql="select comm_shop_price,comm_tuan_price,goods_name from ".$aos->table('goods')." where goods_id = ".$row['goods_id'];
    $goods=$db->getRow($sql);
    //$openid=getOpenid($row['parent_id']);
    
    $sql = "UPDATE ". $GLOBALS['aos']->table('order_info') ." SET is_dist = '1' WHERE order_id='".$row['order_id']."'";
    $GLOBALS['db']->query($sql);
    if(empty($row['extension_code'])){
        $dist_money=$goods['comm_shop_price'];
    }else{
        $dist_money=$goods['comm_tuan_price'];
    }
    log_account_change($row['parent_id'], 0, 0, 0, 0, sprintf('订单佣金 %s', $row['order_sn']),99,$dist_money);
    /*$message=getMessage(13);
    $wx_title = "助力活动失败通知";
    $wx_desc = $message[title]."\r\n任务名称：助力享免单\r\n助力商品：".$row[goods_name]."\r\n失败时间：".$time."\r\n".$message['note'];
    //$wx_pic = $aos_url;
    $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);*/
    order_action($row['order_sn'], 1, 0, 2, '佣金', '');
    
    

}

//时间提醒
if($GLOBALS['_CFG']['tuan_time']>5){
    $old_pay_time = gmtime() - ($GLOBALS['_CFG']['tuan_time']-5)*3600;

    $sql="select extension_id,pay_time from ".$GLOBALS['aos']->table('order_info')." where  tuan_first=1 and pay_status=2 and tuan_status in(0,1) and extension_code in ('tuan','lottery','miao')  and pay_time< ".$old_pay_time." and less_alert = 0 order by order_id desc LIMIT 20";

    $order_list=$GLOBALS['db']->getAll($sql);

    if(!empty($order_list) ){
        
        
        foreach($order_list as $v){
           $tuan_mem = get_tuan_mem($v['extension_id']);
           $sql="select g.goods_name,g.goods_id,g.goods_price,o.tuan_num,o.user_id,o.`order_sn`,o.`order_id` from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id  where  o.extension_id=".$v['extension_id']." and  o.order_status = 1  and o.tuan_status in(0,1) ";
            $team_list= $GLOBALS['db']->getAll($sql);
            $wx_url=$aos->url();
            $wx_url.="index.php?c=share&tuan_id=".$v['extension_id'];
            foreach($team_list as $f){
                
                
                //成团失败模板消息
                $cha_num=intval($f['tuan_num'])-intval($tuan_mem);
                
                
                $openid=getOpenid($f['user_id']);
                //$message=getMessage(4);
                $wx_title = "参团人数不足提醒";
                $wx_desc = $message[title]."\r\n您的拼团还有5小时就要到期了，快去叫上身边的小伙伴一起拼吧\r\n拼团商品：".$f[goods_name]."\r\n剩余时间：5小时\r\n还差".$cha_num."人\r\n".$message[note];
                //$wx_pic = $aos_url;
                $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                $arr['less_alert']  = 1;
                update_order($f['order_id'], $arr);

            } 
            
           
        }

    }
}







    

     
        
?>