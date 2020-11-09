<?php
/*抽奖页面*/
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
  $goodslist = get_lottery_goods($status, $limit);
	foreach($goodslist['info'] as $val){
    $GLOBALS['smarty']->assign('page',$page);
		$GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/lottery_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}
if ($action == 'index')
{
  $cache_id = sprintf('%X', crc32('lottery'));
  if (!$smarty->is_cached('lottery.htm', $cache_id))
  {
	  assign_template();
    $share['title'] = $GLOBALS['_CFG']['lottery_title'];
    $share['desc'] = $GLOBALS['_CFG']['lottery_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
  }
  $smarty->display('lottery.htm', $cache_id);
}
elseif ($action == 'view')
{
  $lottery_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
  $cache_id = sprintf('%X', crc32('lottery-view-'.$lottery_id));
  if (!$smarty->is_cached('lottery_view.htm', $cache_id))
  {
    assign_template();
  	
  	$lottery = get_lottery_info($lottery_id);

  	$lottery['lottery_price_formated'] = price_format($lottery['lottery_price']);
  	$goods_id = $lottery['goods_id'];
  	$goods = get_goods_info($goods_id);
  	if ($goods === false)
    {
      aos_header("Location: ./index.php\n");
      exit;
    }
    else
    {
      if(!empty($lottery['goods_attr'])){
        $attr_id = $lottery['goods_attr'];
        $smarty->assign('goods_sku', get_sku_list($attr_id,1));
      }
      if(!empty($goods['goods_label'])){
        $smarty->assign('goods_label',      get_label_list($goods['goods_label']));
      }
      $goods_desc = str_replace('<img src="','<img class="lazy" src="uploads/images/no_tuan_picture.jpg" data-original="',$goods['goods_desc']);
      $smarty->assign('goods_desc',$goods_desc); 
      
    	$smarty->assign('goods_id',            $goods_id);
      $smarty->assign('lottery',            $lottery);
    	$smarty->assign('goods',              $goods);
      $goods_desc = str_replace('<img src="','<img class="lazy" src="uploads/images/no_tuan_picture.jpg" data-original="',$goods['goods_desc']);
      $smarty->assign('goods_desc',$goods_desc); 
    	
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
	$smarty->display('lottery_view.htm', $cache_id);	
}
elseif ($action == 'won')
{
	$lottery_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
  $cache_id = sprintf('%X', crc32('lottery-won-'.$lottery_id));
  if (!$smarty->is_cached('lottery_won.htm', $cache_id))
  {
    assign_template();
    $smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    $smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
  	$lottery = get_lottery_info($lottery_id);
  	$lottery['lottery_price_formated'] = price_format($lottery['lottery_price']);
  	$smarty->assign('lottery',            $lottery);
  	$goods_id = $lottery['goods_id'];
    $goods = get_goods_info($goods_id);
    $smarty->assign('goods',              $goods);
  	$won = get_won_list($lottery_id);
  	$smarty->assign('won', $won);
  }
	$smarty->display('lottery_won.htm', $cache_id);	
}

/*获得抽奖商品*/
function get_lottery_goods($status, $limit = 1)
{
	$now_time = gmtime();
	$where = "1";
	switch($status)
	{
	  case 1 : //进行中
	    $where .= " AND $now_time < l.lottery_end_time AND $now_time > l.lottery_start_time";
		break;
		case 2 : //未开始
	    $where .= " AND $now_time < l.lottery_start_time";
		  break;
		case 3 : //已结束
	    $where .= " AND $now_time > l.lottery_end_time";
		break;
	  default:
	}
  /* 获得商品列表 */
  $sql='SELECT count(*) FROM ' . $GLOBALS['aos']->table('lottery') . ' as l ' .
    "WHERE $where ";
  $count=$GLOBALS['db']->getOne($sql);
  $sql="select l.*,g.goods_name,g.goods_img,g.shop_price from ". $GLOBALS['aos']->table('lottery') ." as l ".
  "LEFT JOIN " .$GLOBALS['aos']->table('goods'). " AS g ON l.goods_id = g.goods_id WHERE $where order by l.lottery_start_time desc $limit ";
  $result = $GLOBALS['db']->getAll($sql);
  $res=array();
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['lottery_id']         = $row['lottery_id'];
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['lottery_status']     = $row['lottery_status'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
		$goods[$idx]['url']          = 'index.php?c=lottery&a=view&id='.$row['lottery_id'];
		$goods[$idx]['lottery_start_time'] = local_date('Y-m-d H:i:s', $row['lottery_start_time']);
    $goods[$idx]['lottery_end_time'] = local_date('Y-m-d H:i:s', $row['lottery_end_time']);
    $goods[$idx]['lottery_price']         = price_format($row['lottery_price']);
    $goods[$idx]['shop_price']         = price_format($row['shop_price']);
    $goods[$idx]['lottery_tuan_num']    = $row['lottery_tuan_num'];
    if($now_time < $row['lottery_start_time'])//未开始
    {
    	$goods[$idx]['status']  = 2;
    }
    else
    {
    	if($now_time < $row['lottery_end_time'])
      {
      	$goods[$idx]['status']  = 1;
      }
      else
      {
      	if($goods[$idx]['lottery_status'] == 1)
      	{
      		$goods[$idx]['status']  = 4;
      	}
      	else
      	{
      		$goods[$idx]['status']  = 3;
      	}
      }
    }
	}
	$res['info'] = array_sort($goods,'status','asc');
	$res['count'] = $count;
  return $res;
}

/*获得抽奖商品详情*/
function get_lottery_info($lottery_id)
{
	$now_time = gmtime();
  $sql='SELECT * FROM ' . $GLOBALS['aos']->table('lottery')." WHERE lottery_id = $lottery_id";
  $lottery = $GLOBALS['db']->getRow($sql);

  if($now_time < $lottery['lottery_start_time'])//未开始
  {
  	$lottery['status']  = 2;
  }
  else
  {
	  if($now_time < $lottery['lottery_end_time'])
    {
    	$lottery['status']  = 1;
    }
    else
    {
    	if($lottery['lottery_status'] == 1)
      {
      	$lottery['status']  = 4;
      }
      else
      {
      	$lottery['status']  = 3;
      }
    }
  }
  return $lottery;
}
function get_won_list($lottery_id)
{
	$sql='SELECT lottery_won FROM ' . $GLOBALS['aos']->table('lottery')." WHERE lottery_id = $lottery_id";
  $won_str = $GLOBALS['db']->getOne($sql);
  $won_arr = explode(',',$won_str);
  if($won_str){
    foreach ($won_arr AS $idx => $row)
    {
      $won_info = get_won_info($row);
      $won[$idx]['nickname']  = $won_info['nickname'];
      $won[$idx]['headimgurl']= $won_info['headimgurl'];
      $won[$idx]['order_sn']  = $won_info['order_sn'];
      $won[$idx]['mobile']    = $str = substr_replace($won_info['mobile'],'****',3,4);
    }
    return $won;
  }else{
    return;
  }
  
}
/*根据订单id查询 用户、订单号、手机号*/
function get_won_info($order_id)
{
	$sql='SELECT o.order_sn,o.mobile,u.nickname,u.headimgurl FROM ' . $GLOBALS['aos']->table('order_info')." AS o ".
	"LEFT JOIN " .$GLOBALS['aos']->table('users'). " AS u ON o.user_id = u.user_id ".
	" WHERE order_id = $order_id";
	return $GLOBALS['db']->getRow($sql);
}
?>