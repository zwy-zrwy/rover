<?php

if (!defined('IN_AOS'))
{
  die('Hacking attempt');
}
if (!empty($action) && $action == 'ajax')
{
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 0;
  $page = $_REQUEST['page'] ? $_REQUEST['page'] : 0;
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
  $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
	$limit = " limit $last,$amount";//每次加载的个数
    $goodslist = get_seckill_goods($status, $limit);
	foreach($goodslist['info'] as $val){
    $GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/seckill_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}


if ($action == 'index')
{
  $cache_id = sprintf('%X', crc32('seckill'));
  if (!$smarty->is_cached('seckill.htm', $cache_id))
  {
    assign_template();
    $share['title'] = $GLOBALS['_CFG']['seckill_title'];
    $share['desc'] = $GLOBALS['_CFG']['seckill_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
  }
	$smarty->display('seckill.htm', $cache_id);	
}
elseif($action == 'view')
{
	$seckill_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
  $cache_id = sprintf('%X', crc32('seckill-view-'.$seckill_id));
  if (!$smarty->is_cached('seckill_view.htm', $cache_id))
  {
    assign_template();
  	$seckill = get_seckill_info($seckill_id);
  	$seckill['seck_price_formated'] = price_format($seckill['seck_price']);
  	$now_time = gmtime();
  	if($now_time >= $seckill['seck_end_time'])
  	{
  		aos_header("Location: ./index.php?c=seckill\n");
      exit;
  	}
  	$goods_id = $seckill['goods_id'];
  	$miao_price = get_seck_price($goods_id);
  	$goods = get_goods_info($goods_id);
    if ($goods === false)
    {
      aos_header("Location: ./index.php\n");
      exit;
    }
    else
    {
      if(!empty($goods['goods_label'])){
        $smarty->assign('goods_label',      get_label_list($goods['goods_label']));
      }
      $goods_desc = str_replace('<img src="','<img class="lazy" src="uploads/images/no_tuan_picture.jpg" data-original="',$goods['goods_desc']);
      $smarty->assign('goods_desc',$goods_desc); 
      $smarty->assign('goods_sku', get_sku_list($goods_id));
      $smarty->assign('goods_id',            $goods_id);
    	$smarty->assign('seckill',            $seckill);
    	$smarty->assign('goods',              $goods);
    	$smarty->assign('sales', get_cum_sales($goods_id));
    	$smarty->assign('album', get_goods_album($goods_id));
      $smarty->assign('rand_goods', rand_goods($goods_id));


    $share['title'] = mb_substr($goods['goods_name'], 0,30,'utf-8');
    $share['desc'] = mb_substr(str_replace("\r\n","",$goods['goods_brief']), 0,50,'utf-8');

    $imgUrl = get_share_img($goods['goods_id']);
    $share['imgUrl'] = $aos->url().$imgUrl;
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
      
    }
  }
	$smarty->display('seckill_view.htm', $cache_id);	
}

/*获得秒杀商品*/
function get_seckill_goods($status, $limit = 1)
{
	$now_time = gmtime();  
	$where = "$now_time < k.seck_end_time";
	switch($status)
	{
	  case 1 : //进行中
	    $where .= " AND $now_time > k.seck_start_time AND k.seckill_sales < k.seckill_number";
		break;
		case 2 : //未开始
	    $where .= " AND $now_time < k.seck_start_time";
		break;
		case 3 : //已售罄
	    $where .= " AND $now_time > k.seck_start_time AND k.seckill_sales >= k.seckill_number";
		break;
	  default:
	}
    /* 获得商品列表 */
    $sql='SELECT count(*) FROM ' . $GLOBALS['aos']->table('seckill') . ' AS k ' .
      "WHERE $where ";
    $count=$GLOBALS['db']->getOne($sql);
    $sql="select k.*,g.goods_name,g.goods_img,g.shop_price,g.market_price from ". $GLOBALS['aos']->table('seckill') ." as k ".
    "LEFT JOIN " .$GLOBALS['aos']->table('goods'). " AS g ON k.goods_id = g.goods_id WHERE $where $limit ";
    $result = $GLOBALS['db']->getAll($sql);
    $res=array();
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
    $goods[$idx]['market_price']         = price_format($row['market_price']);
    if($now_time < $row['seck_start_time'])//未开始
    {
    	$goods[$idx]['status']  = 2;
    	$goods[$idx]['number']  = $row['seckill_number'];
    }
    else
    {
    	if($now_time < $row['seck_end_time'])
      {
      	if($row['seckill_sales'] >= $row['seckill_number'])
      	{
      		$goods[$idx]['status']  = 3;
      		$goods[$idx]['number']  = 0;
      	}
      	else
      	{
      		$goods[$idx]['status']  = 1;
      		$goods[$idx]['number']  = $row['seckill_number']-$row['seckill_sales'];
      	}
      }
    }
	}
	$res['info'] = array_sort($goods,'status','asc');
	$res['count'] = $count;
  return $res;
}

/*获得秒杀商品详情*/
function get_seckill_info($seckill_id)
{
	$now_time = gmtime();
  $sql='SELECT * FROM ' . $GLOBALS['aos']->table('seckill')." WHERE seckill_id = $seckill_id";
  $seckill = $GLOBALS['db']->getRow($sql);
  if($now_time < $seckill['seck_start_time'])//未开始
  {
  	$seckill['status']  = 2;
  }
  else
  {
  	if($now_time < $seckill['seck_end_time'])
      {
      	if($seckill['seckill_sales'] >= $seckill['seckill_number'])
      	{
      		$seckill['status']  = 3;
      	}
      	else
      	{
      		$seckill['status']  = 1;
      	}
      }
  }
  return $seckill;
}
?>