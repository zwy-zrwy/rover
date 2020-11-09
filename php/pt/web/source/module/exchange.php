<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
include_once(ROOT_PATH .'source/library/user.php');
assign_template();
if (!empty($action) && $action == 'ajax')
{
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
	$limit = " limit $last,$amount";//每次加载的个数

	$couponlist = get_exc_coupon($limit);

	foreach($couponlist['coupon'] as $key=> $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('coupon',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/exchange_list.htm');
	}
	if($couponlist['count']>99){
		$couponlist['count']=99;
	}
	$res['count']=$couponlist['count'];
	//print_r($couponlist);

	die(json_encode($res));
}
if ($action == 'index')
{

    $points = get_user_points($user_id);
    $smarty->assign('points', $points['pay_points']);
    $share['title'] = $GLOBALS['_CFG']['exchange_title'];
    $share['desc'] = $GLOBALS['_CFG']['exchange_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);

	$smarty->display('exchange.htm');
}
elseif ($action == 'integral')
{
	
	
}
elseif ($action == 'confirm')
{
	include_once(ROOT_PATH .'source/library/user.php');
	/*$points = get_user_points($user_id);
	$goods_id = !empty($_POST['goods_id'])?intval($_POST['goods_id']):'0';
	$price = $db->getOne("select goods_price from ". $aos->table('exchange') ." WHERE goods_id = $goods_id");
	$res = array();
	$res['points'] = $points;
	$res['price'] = $price;
	die(json_encode($res));*/
	$id = !empty($_POST['goods_id'])?intval($_POST['goods_id']):'0';
	if(empty($_SESSION['user_id'])){
		$res['err']=1;
		$res['message']='请先登录';
	}
	$user_id=$_SESSION['user_id'];
	$point = get_user_points($user_id);
	$points = $point['pay_points'];
	$time=gmtime();

	$sql="select integral from ".$aos->table('bonus_type')." where send_type = 6 and send_start_date < '$time' and send_end_date > '$time' and type_id = ".$id;
	$goods_price=$db->getOne($sql);

	if($points < $goods_price){
		$res['err']=1;
		$res['message']='积分不足';
	}elseif($goods_price){
		$sql="insert into ".$aos->table('user_bonus')." (bonus_type_id,user_id) values ('$id','$user_id') ";
		$db->query($sql);
		$change_desc="积分商城兑换".$goods_price."积分";
		$time=gmtime();
        log_account_change($user_id, 0, 0, 0, -$goods_price, $change_desc, 99);
		$res['err']=0;
		$res['message']='兑换成功';
	}else{
		$res['err']=1;
		$res['message']='活动结束';
	}
	die(json_encode($res));
}

function get_exc_coupon($limit = 1)
{
	$cur_time = gmtime();
    $where = "send_type = 6 AND send_start_date < $cur_time AND $cur_time <  send_end_date";
	$sql='SELECT count(*) ' .
            'FROM ' . $GLOBALS['aos']->table('bonus_type') .
            "WHERE $where";
    $count=$GLOBALS['db']->getOne($sql);

    $sql="select * from ". $GLOBALS['aos']->table('bonus_type') ." WHERE $where";
    $result = $GLOBALS['db']->getAll($sql);
    $res=array();
    foreach ($result AS $idx => $row)
	{
		$coupon[$idx]['type_id']           = $row['type_id'];
		$coupon[$idx]['type_name']         = $row['type_name'];
		$coupon[$idx]['type_money']         = $row['type_money'];
		$coupon[$idx]['min_amount']         = $row['min_amount'];
		$coupon[$idx]['send_start_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['send_start_date']);
		$coupon[$idx]['send_end_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['send_end_date']);
		$coupon[$idx]['use_start_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['use_start_date']);
		$coupon[$idx]['use_end_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['use_end_date']);
		$coupon[$idx]['min_goods_amount']         = number_format($row['min_goods_amount']);
		$coupon[$idx]['integral']         = number_format($row['integral']);

		
	}
    $res['coupon']=$coupon;
	$res['count']=$count;
    return $res;
}
?>