<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
assign_template();
$order_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
if(empty($order_id)){
  aos_header("location:index.php");
  exit();
}
if ($action == 'index')
{
	$order = order_infos($order_id);
  $smarty->assign('order',  $order);
  $smarty->assign('aos_url',  $aos->url());
  $smarty->display('verification.htm');
}
elseif ($action == 'confirm')
{
	//订单
	$sql = "SELECT `order_id`,`order_sn`,`order_status`,`shipping_status`,`pay_status`,`shipping_id`,`store_id`,`extension_id`,`extension_code`,`tuan_first`,`add_time`,`mobile`,`money_paid`,`bonus`,`surplus`,user_id FROM ".$aos->table('order_info')." WHERE `order_id` = " . $order_id;
	$info = $db->getRow($sql);
	//已经处理过
	if($info['shipping_status'] ==2)
	{
		show_message('您刚才不是已经扫过了吗？','返回我的核销', 'index.php?c=user&a=veri');
		exit();
	}
	elseif($info['pay_status'] ==3)
	{
		show_message('该订单已退款不能再核销了','返回我的核销', 'index.php?c=user&a=veri');
		exit();
		
	}
	elseif($info['order_status'] ==2)
	{
		show_message('该订单已取消不能再核销了','返回我的核销', 'index.php?c=user&a=veri');
		exit();
		
	}
	/*验证店主*/
  $user_id = $_SESSION['user_id'];
  $sql = "select `id` FROM  ".$aos->table('wxmanage')." WHERE `store_id` = '".$info['store_id']."' AND `openid` = '".$_SESSION['openid']."' ";

  $wx_openid = $db->getOne($sql);
  if(!$wx_openid){
	  show_message('您不是该店核销员！','返回我的核销', 'index.php?c=user&a=veri');
	  exit();
  }


	//更新状态
	$sql = "UPDATE ".$aos->table('order_info')." SET `order_status` = 5,`shipping_status` = 2,`pay_status` = 2,`veri_time`= '".time()."',`veri_uname` = '".$_SESSION['nickname']."',`veri_uid` = '".$_SESSION['user_id']."' WHERE `order_id` = " . $order_id;
	$db->query($sql);

	$order_sn        = $info['order_sn'];
	$order_status    = 5;
	$shipping_status = 2;
	$pay_status      = 2;
	$note            = '到店取货二维码核销';
	$username        = $_SESSION['nickname'].'[核销员]';
	
	$sql="select count(*) from ".$GLOBALS['aos']->table('order_action')." where order_id =".$order_id." and shipping_status = 2";
    $cou=$GLOBALS['db']->getOne($sql);
    order_action($order_sn, $order_status, $shipping_status, $pay_status, $note, $username);
    include_once(ROOT_PATH .'source/library/order.php');
    if($cou<1){
    	send_order_bonus($info['order_id']);
    	$integral = integral_to_give($info);

        log_account_change($info['user_id'], 0, 0, intval($integral), intval($integral), sprintf("下单 %s 时赠送积分", $order['order_sn']));
       $arr['integral']=$integral;
       $sql = "UPDATE ".$aos->table('order_info')." SET `integral` = '".$integral."' WHERE `order_id` = " . $order_id;
		$db->query($sql);
        $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$info['order_id'];
        $refund_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
        global $wechat;
        $openid=getOpenid($info['user_id']);
       
        //团长佣金
        $sql="select g.commission,g.goods_name from ".$GLOBALS['aos']->table('order_goods')." as o left join ".$GLOBALS['aos']->table('goods')." as g on o.goods_id = g.goods_id  where o.order_id = $order_id ";
        $row=$GLOBALS['db']->getRow($sql);
        if($info['tuan_first']==1 && $info['extension_code']=='tuan' && $row[commission]>0){
       		 $sql="select g.goods_name,g.goods_id,g.goods_price,o.user_id,o.surplus,o.tuan_num,o.order_status,o.pay_status,o.shipping_status,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.`extension_id`,o.`extension_code`,o.act_id from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id  where  o.order_id =$order_id ";
       		$order_info=$GLOBALS['db']->getRow($sql);
            $r= refunds($order_info,$row[commission],'refund');
            if($r=='wei_true'){
                
                $refund_price='￥'.$row[commission];
                
                $message=getMessage(18);
				$wx_title=$message['title'];
                $wx_desc = "佣金商品：".$row[goods_name]."\r\n佣金金额：".$refund_price."\r\n发放时间：".$refund_time."\r\n".$message['note'];
                //$wx_pic = $aos_url;
                $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                order_action($info['order_sn'], 5, 2, 2, '团长佣金，微信已发', '');
            }elseif($r=='ali_true'){
                order_action($order['order_sn'], 5, 2, 2, '团长佣金，支付宝未发', '');
            }
        }
    }

	//发送消息
	/*
	$sql = "SELECT u.`openid` FROM ".$aos->table('order_info')." as o,".$ecs->table('users')." as u WHERE o.`order_id` = " .$order_id .' AND o.`user_id` = u.`user_id`';
	$openid = $db->getOne($sql);
	if($openid)
	{
		$weixin=new class_weixin($GLOBALS['appid'],$GLOBALS['appsecret']);
		$title = '核销成功';
		$url = 'user.php?act=order_detail&order_id=' . $info['order_id'];
		$description = '您已经成功提货！感谢您的惠顾，记得常来哦!';
		$weixin->send_wxmsg($openid, $title , $url , $description );
	}
	*/
	show_message('核销成功','返回我的核销', 'index.php?c=user&a=veri','succ');
}
function order_infos($order_id)
{
    $sql = "SELECT o.order_id,o.add_time,o.pay_time,o.store_id,og.goods_name,og.goods_number,og.goods_attr FROM " . $GLOBALS['aos']->table('order_info') . " as o left join "
    .$GLOBALS['aos']->table("order_goods")." as og on og.order_id=o.order_id ".
    " WHERE o.order_id = '$order_id'";
    $order = $GLOBALS['db']->getRow($sql);
    if ($order)
    {
        $order['add_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['pay_time']);
		$order['store_name']   = get_store_name($order['store_id']);
    }
    return $order;
}


//打印相关
/*
function handle_print($info){
	global $ecs,$db,$_CFG;
	
	if(! defined('FEYIN_KEY'))
		return;

	$use_surplus = $info['surplus']?"余额支付：".$info['surplus']."元 ". PHP_EOL:'';
	$use_bonus   = $info['bonus']?"优惠券支付：".$info['bonus']."元 ". PHP_EOL:'';
	$money_paid  = "支付金额：".$info['money_paid']."元 ". PHP_EOL . PHP_EOL;
	$amount      = $info['money_paid'] + $info['surplus'] + $info['bonus'];
	$amount      = "合计：".$amount."元 ". PHP_EOL;

	//订单商品
	$sql = "select `goods_name`,`goods_number`,`goods_price` from ".$ecs->table('order_goods')." WHERE `order_id` = '".$info['order_id']."'";
	$rows = $db->getAll($sql);

	//
	 自由格式的打印内容
	$msgNo       = $info['order_sn'].rand(100,999);
	$msgDetail   = "     ".$_CFG['shop_title']."欢迎您订购". PHP_EOL
	. PHP_EOL.
	"条目         单价（元）    数量". PHP_EOL.
	"------------------------------". PHP_EOL;
	foreach ($rows as $key => $goods) {
	    $msgDetail   .= $goods['goods_name']. PHP_EOL;
	    $msgDetail   .= "              ".$goods['goods_price']."          ".$goods['goods_number']. PHP_EOL;
	}


	$msgDetail   .= PHP_EOL .  
	"------------------------------". PHP_EOL . 
	// $use_surplus .//余额
	$use_bonus .//优惠券
	$money_paid .//实际支付
	$amount .//合计
	"客户单号：".$msgNo . PHP_EOL.
	"客户电话：".($info['checked_mobile']?$info['checked_mobile']:$info['mobile']).PHP_EOL .
	"订购时间：".date("Y-m-d H:i:s",$info['add_time']).PHP_EOL .PHP_EOL .
	"取货地址：".SHOP_NAME . PHP_EOL.
	"打印时间：".date("Y-m-d H:i:s");

	$freeMessage = array(
		'memberCode' => MEMBER_CODE, 
		'deviceNo'   => DEVICE_NO, 
		'msgDetail'  => $msgDetail,
		'msgNo'      => $msgNo,
	);

	sendFreeMessage($freeMessage);


}

function sendFreeMessage($msg) {
	$msg['reqTime'] = number_format(1000*time(), 0, '', '');
	$content = $msg['memberCode'].$msg['msgDetail'].$msg['deviceNo'].$msg['msgNo'].$msg['reqTime'].FEYIN_KEY;
	$msg['securityCode'] = md5($content);
	$msg['mode']=2;

	return sendMessage($msg);
}

function sendMessage($msgInfo) {
	$client = new HttpClient(FEYIN_HOST,FEYIN_PORT);
	if(!$client->post('/api/sendMsg',$msgInfo)){ //提交失败
		return 'faild';
	}
	else{
		return $client->getContent();
	}
}
*/
?>