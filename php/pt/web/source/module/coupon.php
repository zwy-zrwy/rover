<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
assign_template();
if (!empty($action) && $action == 'ajax')
{
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
	$limit = " limit $last,$amount";//每次加载的个数

	$couponlist = get_coupons_list($limit);

	foreach($couponlist['coupon'] as $key=> $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('coupon',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/coupon_list.htm');
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
	$cache_id = sprintf('%X', crc32('coupon'));
	if (!$smarty->is_cached('coupon.htm', $cache_id))
	{
        $share['title'] = $GLOBALS['_CFG']['coupon_title'];
	    $share['desc'] = $GLOBALS['_CFG']['coupon_desc'];
	    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
	    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	    $smarty->assign('share',       $share);
	}
	$smarty->display('coupon.htm', $cache_id);
}
elseif ($action == 'receive')
{
    $type_id = !empty($_POST['type_id'])?intval($_POST['type_id']):'0';
	if(empty($_SESSION['user_id'])){
		$res['err']=1;
		$res['message']='请先登录';
	}
	$user_id=$_SESSION['user_id'];

	$sql="select goods_id from ".$aos->table('bonus_type')." where type_id = ".$type_id;
	$goods_id=$db->getOne($sql);

	$sql="select count(*) from ".$aos->table('user_bonus')." where bonus_type_id = $type_id AND user_id=$user_id";
	$is_bonus=$db->getOne($sql);
	if($is_bonus)
	{
		$res['err']=1;
		$res['message']='您领取过该优惠券';
	}
	else
	{
		$sql="insert into ".$aos->table('user_bonus')." (bonus_type_id,user_id) values ('$type_id','$user_id') ";
		$db->query($sql);
		$res['err']=0;
		$res['message']='领取成功';
		$res['goods_id']=$goods_id;
	}
	die(json_encode($res));
}
function get_coupons_list($limit = 1)
{
	$time=gmtime();
    $where = "send_type = 5 AND use_start_date < $time AND $time <  use_end_date AND goods_id > 0";
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
		$coupon[$idx]['type_money']         = price_format($row['type_money']);
		$coupon[$idx]['min_amount']         = $row['min_amount'];
		$coupon[$idx]['send_start_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['send_start_date']);
		$coupon[$idx]['send_end_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['send_end_date']);
		$coupon[$idx]['use_start_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['use_start_date']);
		$coupon[$idx]['use_end_date']         = local_date($GLOBALS['_CFG']['date_format'], $row['use_end_date']);
		$coupon[$idx]['min_goods_amount']         = number_format($row['min_goods_amount']);
		$coupon[$idx]['goods_id']         = $row['goods_id'];

		
	}
    $res['coupon']=$coupon;
	$res['count']=$count;
    return $res;
}