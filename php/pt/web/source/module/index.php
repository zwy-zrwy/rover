<?php

/* 首页文件*/
if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

if ($action == 'index_goods')
{
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
	$limit = "limit $last,$amount";//每次加载的个数
	$goodslist = get_index_goods($limit);
	$res=array();
	foreach($goodslist['info'] as $val){
		$GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/tuan_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}
if ($action == 'share')
{
	$share_types=array("1"=>"朋友","2"=>"朋友圈");
	$share_status=array("1"=>"成功","2"=>"取消");
	$link = trim($_POST['link']);
    $share_type = $share_types[intval($_POST['share_type'])];
    $share_statu = $share_status[intval($_POST['share_statu'])];
    $sql="insert into ".$aos->table('share_info')." (user_id,share_status,share_type,link_url,add_time) value ('$_SESSION[user_id]','$share_statu','$share_type',".

	" '$link',".gmtime()." ) ";

	$r=$db->query($sql);
}
if ($action == 'order_time_ajax')
{
	//查询是否有新订单
	//-start

	$add_time = gmtime()-10;
    $sql="select order_id from ".$GLOBALS['aos']->table('order_info')." where extension_code='tuan' and tuan_status = 1 and pay_time > $add_time and pay_status = 2";
    $once=$GLOBALS['db']->getOne($sql);
	
	//加载团过产品
	include_once(ROOT_PATH . 'source/library/order.php');
	if($once){
		$sql="select o.add_time,o.pay_time,u.province,u.`headimgurl`,u.`nickname`,g.goods_name,g.tuan_img,g.goods_id,o.extension_id from ".$GLOBALS['aos']->table('order_info')." as o LEFT JOIN ".$GLOBALS['aos']->table('users')." as u on u.`user_id` = o.`user_id` left join ".$GLOBALS['aos']->table('order_goods')." as og on o.order_id = og.order_id left join ".$GLOBALS['aos']->table('goods')." as g on og.goods_id = g.goods_id where o.order_id = $once order by  o.order_id desc";
		$info=$GLOBALS['db']->getRow($sql);
		$res['err']=0;
		if($info){
			$time=$add_time-$info['pay_time']+10;
			$GLOBALS['smarty']->assign('time',$time);
			$GLOBALS['smarty']->assign('info',$info);
			$res['info']  = $GLOBALS['smarty']->fetch('inc/common_hint.htm');
		}else{
			$res['err']=1;
		}
	}else{
		$res['err']=1;
	}
	die(json_encode($res));
}

  assign_template();
	$categories = get_categories_tree();
	$seckill_goods = get_seckill_goods();
	$lottery_goods = get_lottery_goods();
	//print_r($lottery_goods);
    $smarty->assign('menu_list', get_menu_list());
	$smarty->assign('categories', $categories);
	$smarty->assign('seckill_goods', $seckill_goods);
	$smarty->assign('lottery_goods', $lottery_goods);

$smarty->display('index.htm');

function get_menu_list()
{
	$sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('menu')." WHERE enabled = 1 AND type = 0 ORDER BY sort_order";
	return $GLOBALS['db']->getAll($sql);
}
function get_index_goods($limit)
{
	$where = "is_on_sale = 1 AND is_delete = 0";
	$sql="SELECT count(goods_id) from ".$GLOBALS['aos']->table('goods')."WHERE $where";
	$count=$GLOBALS['db']->getOne($sql);
    /* 获得商品列表 */
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('goods')." WHERE $where ORDER BY sort_order DESC,goods_id DESC $limit";
    $result = $GLOBALS['db']->getAll($sql);
	
	$res=array();
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_brief']        = $row['goods_brief'];
		$goods[$idx]['sales']       = get_cum_sales($row['goods_id']);
		$goods[$idx]['goods_video']       = $row['goods_video'];
		$goods[$idx]['market_price'] = price_format($row['market_price']);
		$goods[$idx]['shop_price']   = price_format($row['shop_price']);
		$tuan_price_list = get_tuan_price_list($row['goods_id']);
		$goods[$idx]['min_number'] = min(array_column($tuan_price_list,'number'));
		$goods[$idx]['max_number'] = max(array_column($tuan_price_list,'number'));
		$goods[$idx]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
		$goods[$idx]['tuan_img']    = get_image_path($row['goods_id'], $row['tuan_img']);
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
		$goods[$idx]['url']          = 'index.php?c=goods&id='.$row['goods_id'];
		$is_attr = is_attr($row['goods_id']);
		if($is_attr)
		{
			$goods[$idx]['goods_number'] = get_sku_num($row['goods_id']);
		}
		else
		{
			$goods[$idx]['goods_number']       = $row['goods_number'];
		}
		$goods[$idx]['ing']          = assign_tuan_ing($row['goods_id'],$_SESSION['user_id'],2);
	}
	$res['info']=$goods;
	$res['count']=$count;
    return $res;
}
/*获得秒杀商品*/
function get_seckill_goods()
{
	$now_time = gmtime();  
	$where = "$now_time < k.seck_end_time AND $now_time > k.seck_start_time AND k.seckill_sales < k.seckill_number";
    /* 获得商品列表 */
    $sql="select k.*,g.goods_name,g.goods_img,g.shop_price from ". $GLOBALS['aos']->table('seckill') ." as k ".
    "LEFT JOIN " .$GLOBALS['aos']->table('goods'). " AS g ON k.goods_id = g.goods_id WHERE $where limit 4";
    $result = $GLOBALS['db']->getAll($sql);
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['seckill_id']         = $row['seckill_id'];
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
		$goods[$idx]['url']          = 'index.php?c=seckill&a=view&id='.$row['seckill_id'];
		$goods[$idx]['seck_start_time'] = local_date('Y-m-d H:i:s', $row['seck_start_time']);
	    $goods[$idx]['seck_end_time'] = local_date('Y-m-d H:i:s', $row['seck_end_time']);
	    $goods[$idx]['seck_price']         = price_format($row['seck_price']);
	    $goods[$idx]['shop_price']         = price_format($row['shop_price']);
	    $goods[$idx]['sales']       = get_cum_sales($row['goods_id']);
	}
  return $goods;
}
/*获得抽奖商品*/
function get_lottery_goods()
{
	$now_time = gmtime();  
	$where = "$now_time < l.lottery_end_time AND $now_time > l.lottery_start_time AND l.enabled = 1";
    /* 获得商品列表 */
    $sql="select l.*,g.goods_name,g.tuan_img,g.shop_price from ". $GLOBALS['aos']->table('lottery') ." as l ".
    "LEFT JOIN " .$GLOBALS['aos']->table('goods'). " AS g ON l.goods_id = g.goods_id WHERE $where limit 4";
    $result = $GLOBALS['db']->getAll($sql);
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['lottery_id']         = $row['lottery_id'];
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['lottery_status']     = $row['lottery_status'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['tuan_img']    = get_image_path($row['goods_id'], $row['tuan_img']);
		$goods[$idx]['url']          = 'index.php?c=lottery&a=view&id='.$row['lottery_id'];
		$goods[$idx]['lottery_start_time'] = local_date('Y-m-d H:i:s', $row['lottery_start_time']);
        $goods[$idx]['lottery_price']         = price_format($row['lottery_price']);
        $goods[$idx]['shop_price']         = price_format($row['shop_price']);
        $goods[$idx]['lottery_tuan_num']    = $row['lottery_tuan_num'];
        $goods[$idx]['sales']       = get_cum_sales($row['goods_id']);
	}
  return $goods;
}
?>