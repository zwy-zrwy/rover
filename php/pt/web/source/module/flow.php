<?php
/* 购物流程 */
if (!defined('IN_AOS'))
{
  die('Hacking attempt');
}
require(ROOT_PATH . 'source/library/order.php');
if ($action == 'index')
{
  $action = "checkout";
}
assign_template();
$smarty->assign('categories',       get_categories_tree()); // 分类树
$smarty->assign('show_marketprice', $_CFG['show_marketprice']);
$smarty->assign('data_dir',    DATA_DIR);       // 数据目录

/*添加商品到购物车*/
if ($action == 'add_to_cart')
{
  unset($_SESSION['extension_code']);
  unset($_SESSION['extension_id']);
  unset($_SESSION['flow_order']['bonus_id']);
  $_POST['goods'] = strip_tags(urldecode($_POST['goods']));
  $_POST['goods'] = json_str_iconv($_POST['goods']);
  if (!empty($_REQUEST['goods_id']) && empty($_POST['goods']))
  {
    if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)
    {
      aos_header("Location:./index.php\n");
    }
    $goods_id = intval($_REQUEST['goods_id']);
    exit;
  }
  $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
  if (empty($_POST['goods']))
  {
    $result['error'] = 1;
    die(json_encode($result));
  }
  $goods = json_decode(stripslashes($_POST['goods']));
  /* 检查：如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
  
  if (empty($goods->spec) AND empty($goods->quick))
  {
    $sql = "SELECT attr_id, attr_value, attr_price " .
    'FROM ' . $GLOBALS['aos']->table('goods_attr') . 
    "WHERE goods_id = '" . $goods->goods_id . "' " .
    'ORDER BY attr_price, attr_id';
    $res = $GLOBALS['db']->getAll($sql);
    if (!empty($res))
    {
      $spe_arr = array();
      foreach ($res AS $row)
      {
        $spe_arr[$row['attr_id']]['label']  = $row['attr_value'];
        $spe_arr[$row['attr_id']]['id']     = $row['attr_id'];
        $spe_arr[$row['attr_id']]['price']  = $row['attr_price'];
        $spe_arr[$row['attr_id']]['format_price'] = price_format($row['attr_price'], false);
      }
      $i = 0;
      $spe_array = array();
      foreach ($spe_arr AS $row)
      {
        $spe_array[]=$row;
      }
      $goods_row = $GLOBALS['db']->getRow("select goods_name,goods_img from ".$GLOBALS['aos']->table('goods')." where goods_id='".$goods->goods_id."'");
      $result['error']   = 6;
      $result['goods_id'] = $goods->goods_id;
      $result['message'] = '请选择规格';
			$result['goods_img']= $goods_row['goods_img'];
			$result['goods_name'] = $goods_row['goods_name'];
      die(json_encode($result));
    }
  }

  /* 更新：清空购物车 */
  clear_cart();
  if($goods->rec_type == 4){
    $goods->number=1;
  }
  /* 检查：商品数量是否合法 */
  if (!is_numeric($goods->number) || intval($goods->number) <= 0)
  {
      $result['error']   = 1;
      $result['message'] = '对不起，您输入了一个非法的商品数量。';
  }
  /* 更新：购物车 */
  else
  {
        if(!empty($goods->spec))
        {
          foreach ($goods->spec as  $key=>$val )
          {
            $goods->spec[$key]=intval($val);
          }
        }
		$_SESSION['flow_type']=$goods->rec_type;
		if($goods->rec_type){
          if($goods->rec_type == 1){
            $_SESSION['extension_code'] = 'tuan';
          }
          elseif($goods->rec_type == 2){
            $_SESSION['extension_code'] = 'miao';
          }
          elseif($goods->rec_type == 3){
            $_SESSION['extension_code'] = 'lottery';
          }
		  if(!empty($goods->extension_id))
		  {
			$_SESSION['extension_id'] = $goods->extension_id;
		  }
          if($_SESSION['flow_type']==2 || $_SESSION['flow_type']==3){
            if(!empty($goods->act_id))
            {
                $_SESSION['act_id'] = $goods->act_id;
            }else{
                $result['error']   = 1;
                $result['message'] = '请选择活动';
                die(json_encode($result));
            }
          }
        }
    
    // 更新：添加到购物车
    if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->rec_type))
    {
    }
    else
    {
      $result['message']  = $err->last_message();
      $result['error']    = $err->error_no;
      $result['goods_id'] = stripslashes($goods->goods_id);
      $result['product_spec'] = $goods->spec;
    }
  }
  die(json_encode($result));
}
elseif ($action == 'store_list')
{
  $store_list = get_store_list();
  $smarty->assign('store_list',   $store_list);
  $store_info = $_SESSION['flow_store'];
  $smarty->assign('store_id',   $store_info['store_id']);
}
elseif ($action == 'select_store')
{
    $store_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
    $store = get_store($store_id);
    /* 保存到session */
    unset($_SESSION['flow_consignee']);
    unset($_SESSION['flow_store']);
    $_SESSION['flow_store'] = stripslashes_deep($store);
    aos_header("Location: index.php?c=flow&a=checkout\n");
    exit;
}

elseif ($action == 'address_list')
{
	include_once(ROOT_PATH . 'source/library/user.php');
	$consignee_list = get_address_list($_SESSION['user_id']);
	
	foreach($consignee_list as $idx=>$value)
    {

        $area = explode(',',$value['area']);
    
        $area['province'] = get_region_name($area['0']);
        $area['city'] = get_region_name($area['1']);
        $area['district'] = get_region_name($area['2']);
        $consignee_list[$idx]['area']  = $area['province'].$area['city'].$area['district'];
    }

    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$aos->table('users'). " WHERE user_id='$user_id'");
	$smarty->assign('address',          $address_id);
	$smarty->assign('consignee_list', $consignee_list);
	//print_r($consignee_list);
	
}
elseif ($action == 'select_address')
{
	include_once(ROOT_PATH . 'source/library/user.php');
    $address_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
	$consignee = get_address($address_id);
	/* 保存到session */
    unset($_SESSION['flow_store']);
    unset($_SESSION['flow_consignee']);
    $_SESSION['flow_consignee'] = stripslashes_deep($consignee);
    aos_header("Location: index.php?c=flow&a=checkout\n");
    exit;
}
elseif ($action == 'address')
{
    include_once(ROOT_PATH . 'source/library/user.php');
    $address_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
    $consignee = get_address($address_id);
    
    $area = explode(',',$consignee['area']);
    $consignee['province_name'] = get_region_name($area['0']);
    $consignee['city_name'] = get_region_name($area['1']);
    $consignee['district_name'] = get_region_name($area['2']);

    $smarty->assign('consignee', $consignee);
}
/* 添加/编辑收货地址的处理 */
elseif ($action == 'act_edit_address')
{
    include_once(ROOT_PATH . 'source/library/user.php');
    $address = array(
        'user_id'    => $user_id,
        'address_id' => intval($_POST['address_id']),
        'area'    => isset($_POST['area'])   ? compile_str(trim($_POST['area']))    : '',
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        );

    if (update_address($address))
    {
        aos_header("Location: index.php?c=flow&a=address_list\n");
        exit;
    }
}
elseif ($action == 'checkout')
{
    /*------------------------------------------------------ */
    //-- 订单确认
    /*------------------------------------------------------ */

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : 0;

    /* 拼团商品 */
    if ($flow_type == 1)
    {
        $smarty->assign('is_tuan', 1);
    }
    else
    {
        //正常购物流程  清空其他购物流程情况
        $_SESSION['flow_order']['extension_code'] = '';
    }

    if(!empty($_SESSION['flow_store'])){
        $store = $_SESSION['flow_store'];
    }
    else
    {
        $store = get_store(false);
    }
    if(!empty($store)){
        $_SESSION['flow_store'] = $store;
    }
    $smarty->assign('store', $store);



    if(!empty($_SESSION['flow_consignee'])){
        $consignee = $_SESSION['flow_consignee'];
    }
    else
    {
        $consignee = get_consignee($_SESSION['user_id']);
    }
    if(!empty($consignee)){

    	$area = explode(',',$consignee['area']);
    	$consignee['province'] = $area['0'];
    	$consignee['city'] = $area['1'];
    	$consignee['district'] = $area['2'];
    	
    	$consignee['province_name'] = get_region_name($area['0']);
    	$consignee['city_name'] = get_region_name($area['1']);
    	$consignee['district_name'] = get_region_name($area['2']);
    		
        $_SESSION['flow_consignee'] = $consignee;
    }	
	
	
	//$location['country'] = get_region_id($location['country']);
	//$location['province'] = get_region_id($location['province']);
	//$location['city'] = get_region_id($location['city']);
	//$location['district'] = get_region_id($location['district']);
	//print_r($location);

    $smarty->assign('consignee', $consignee);
	
	//print_r($consignee);


    $user_row = $db->getRow('SELECT mobile, realname FROM '.$aos->table("users").' WHERE user_id = '.$_SESSION['user_id']);
    $smarty->assign('user_row', $user_row);


    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $smarty->assign('goods', $cart_goods);
    $smarty->assign('goods_num', count($cart_goods));
    //print_r($cart_goods);

    /*
     * 取得购物流程设置
     */
    $smarty->assign('config', $_CFG);
    /*
     * 取得订单信息
     */
    $order = flow_order_info();
    $smarty->assign('order', $order);
    /*
     * 计算订单的费用
     */
    if($order['extension_code'] == 'tuan' && empty($order['extension_id']))
    {
      $commission =  $db->getOne('SELECT commission FROM '.$aos->table("goods").' WHERE goods_id = '.$cart_goods['goods_id']);
      if($commission > 0){
        $commission = price_format($commission, false);
        $smarty->assign('commission', $commission);
      }
    }

    $location = $_SESSION['flow_consignee'];

    $total = order_fee($order, $cart_goods, $location);
    
    $smarty->assign('total', $total);
    $smarty->assign('shopping_money', sprintf('合计：%s', $total['formated_goods_price']));

    /* 取得配送列表 */
	$region            = array(1, $consignee['province'], $consignee['city'], $consignee['district']);
    $shipping_list     = available_shipping_list();
    $shipping_num = count($shipping_list);
    if($shipping_num == 1){
        $shipping_id = $shipping_list[0]['shipping_id'];
        $smarty->assign('shipping_id',   $shipping_id);
    }
    $cart_weight_price = cart_weight_price($flow_type);


    // 查看购物车中是否全为免运费商品，若是则把运费赋为零
    $sql = 'SELECT count(*) FROM ' . $aos->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `is_shipping` = 0";
    $shipping_count = $db->getOne($sql);
	$shipping_configure = shipping_configure($region);
    $shipping_cfg = unserialize_config($shipping_configure);
    foreach ($shipping_list AS $key => $val)
    {
        $shipping_fee = $shipping_count == 0 ? 0 : shipping_fee($val['shipping_code'],unserialize($shipping_cfg),$cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);
        $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
        $shipping_list[$key]['shipping_fee']        = $shipping_fee;
        $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);
    }

    $smarty->assign('shipping_list',   $shipping_list);
    $smarty->assign('shipping_num',   $shipping_num);

    $shipping = shipping_info($order['shipping_id']);

    /* 取得支付列表 */
    $payment_list = payment_list();
    if(isset($payment_list))
    {
        foreach ($payment_list as $key => $payment)
        {
            /* 如果有余额支付 */
            if ($payment['pay_code'] == 'balance')
            {
                if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id'])
                {
                    $smarty->assign('disable_surplus', 1);
                }
            }
        }
    }
    $smarty->assign('payment_list', $payment_list); 

    $user_info = user_info($_SESSION['user_id']);

    /* 如果使用余额，取得用户余额 */
    if ((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1')
        && $_SESSION['user_id'] > 0
        && $user_info['user_money'] > 0)
    {
        // 能使用余额
        $smarty->assign('allow_use_surplus', 1);
        $smarty->assign('your_surplus', $user_info['user_money']);
    }

    /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */
    if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')
        && $_SESSION['user_id'] > 0
        && $user_info['pay_points'] > 0
        && ($flow_type != 1 && $flow_type != 2))
    {
        // 能使用积分
        $smarty->assign('allow_use_integral', 1);

        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分
    }

    /* 如果使用优惠券，取得用户可以使用的优惠券及用户选择的优惠券 */
    if ((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1')
        && ($flow_type < 2))
    {
        // 取得用户可用优惠券
        $user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price']);

        if (!empty($user_bonus))
        {
            foreach ($user_bonus AS $key => $val)
            {
                if(empty($val['goods_id']) || ($val['goods_id'] ==$cart_goods['goods_id'] && $_SESSION['extension_id']==''&& $_SESSION['extension_code']=='tuan')){

                   $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
                    $user_bonus[$key]['use_start_date'] = date("Y.m.d",$val['use_start_date']);
                    $user_bonus[$key]['use_end_date'] = date("Y.m.d",$val['use_end_date']); 
                }else{
                    unset($user_bonus[$key]);
                }
                
            }
            $smarty->assign('bonus_list', $user_bonus);
        }
        // 能使用优惠券
        $smarty->assign('allow_use_bonus', 1);
    }
    /* 保存 session */
    $_SESSION['flow_order'] = $order;
    //获取当前优惠券金额
    if($order['bonus_id']){
      $order['type_money'] = $GLOBALS['db']->getOne("SELECT type_money FROM ". $GLOBALS['aos']->table('bonus_type') .
    " WHERE type_id = ".$order['bonus_id']); 
    }

    $smarty->assign('order',   $order);
}
elseif ($action == 'select_shipping')
{
    /*------------------------------------------------------ */
    //-- 改变配送方式
    /*------------------------------------------------------ */
    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : 0;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);
    $store = get_store($_SESSION['user_id']);

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = 1;
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();
        if(intval($_REQUEST['shipping_id'])>0){
            $order['shipping_id'] = intval($_REQUEST['shipping_id']);
            if($order['shipping_id'] > 1)
            {
                $regions = array(1, $consignee['province'], $consignee['city'], $consignee['district']);
                $shipping_info = shipping_area_info($regions);
                //print_r($shipping_info);die;
            }
        }
        


        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和优惠券 */
        $smarty->assign('total_integral', cart_amount($flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

		$result['code']     = $order['shipping_id'];
        $result['area']     = $total['area'];
        $result['content']     = $smarty->fetch('inc/order_total.htm');
    }

    echo json_encode($result);
    exit;
}
elseif ($action == 'select_payment')
{
    /*------------------------------------------------------ */
    //-- 改变支付方式
    /*------------------------------------------------------ */

    $result = array('error' => '', 'content' => '', 'payment' => 1);
    // 取得购物类型 
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : 0;

    // 获得收货人信息 
    //$consignee = get_consignee($_SESSION['user_id']);

    // 对商品信息赋值 
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = 1;
    }
    else
    {
        // 取得购物流程设置 
        $smarty->assign('config', $_CFG);

        // 取得订单信息 
        $order = flow_order_info();

        $order['pay_id'] = intval($_REQUEST['payment_id']);
        $payment_info = payment_info($order['pay_id']);
        $result['pay_code'] = $payment_info['pay_code'];

        // 保存 session 
        $_SESSION['flow_order'] = $order;

        // 计算订单的费用 
        //$total = order_fee($order, $cart_goods, $consignee);
        //$smarty->assign('total', $total);

        // 取得可以得到的积分和优惠券 
        //$smarty->assign('total_integral', cart_amount($flow_type) - $total['bonus'] - $total['integral_money']);
        //$smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        //$result['content'] = $smarty->fetch('inc/order_total.htm');
    }

    echo json_encode($result);
    exit;
}

elseif ($action == 'change_bonus')
{
    /*------------------------------------------------------ */
    //-- 改变优惠券
    /*------------------------------------------------------ */
    include_once('source/class/json.php');
    $result = array('error' => '', 'content' => '', 'formated' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : 0;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = 1;
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $bonus = bonus_info(intval($_GET['bonus']));

        if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0)
        {
            $order['bonus_id'] = intval($_GET['bonus']);
        }
        else
        {
            $order['bonus_id'] = 0;
            $result['error'] = 2;
        }

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        $result['formated'] = 0;
        if($order['bonus_id'])
        {
            $result['formated'] = $bonus['type_money'];
        }
        
        $result['content'] = $smarty->fetch('inc/order_total.htm');
    }

    
    die(json_encode($result));
}
/*------------------------------------------------------ */
//-- 完成所有订单操作，提交到数据库
/*------------------------------------------------------ */
elseif ($action == 'done')
{
  include_once('source/library/user.php');
  include_once('source/library/payment.php');

  /* 取得购物类型 */
  $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : 0;

  /* 检查购物车中是否有商品 */
  $sql = "SELECT COUNT(*) FROM " . $aos->table('cart') .
      " WHERE session_id = '" . SESS_ID . "' " .
      "AND rec_type = '$flow_type'";
  if ($db->getOne($sql) == 0)
  {
    show_message('您的购物车中没有商品！', '', '', 'warning');
  }

  $consignee = get_consignee($_SESSION['user_id']);
  $_POST['postscript'] = isset($_POST['postscript']) ? compile_str($_POST['postscript']) : '';
  $bonus_order = flow_order_info();
  $bonus_id = $bonus_order['bonus_id'];
  $order = array(
      'shipping_id'     => intval($_POST['shipping']),
      'pay_id'          => intval($_POST['payment']),
      'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,
      'integral'        => isset($_POST['integral']) ? intval($_POST['integral']) : 0,
      'bonus_id'        => !empty($bonus_id) ? intval($bonus_id) : 0,
      'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,
      'postscript'      => trim($_POST['postscript']),
      'user_id'         => $_SESSION['user_id'],
      'parent_id' => 0,
      'add_time'        => gmtime(),
      'lastmodify'      => gmtime(),
      'order_status'    => 1,
      'shipping_status' => 0,
      'pay_status'      => 0
      );

    /* 扩展信息 */
    if (!empty($_SESSION['flow_type']))
    {
      $order['extension_code'] = $_SESSION['extension_code'];
      $order['extension_id'] = $_SESSION['extension_id'];
      $order['act_id'] = $_SESSION['act_id'];
    }
    else
    {
      $order['extension_code'] = '';
      $order['extension_id'] = 0;
      $order['act_id'] = 0;
    }

    /* 检查积分余额是否合法 */
    $user_id = $_SESSION['user_id'];

    
    if ($user_id > 0)
    {
      $user_info = user_info($user_id);
      $order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
      if ($order['surplus'] < 0)
      {
        $order['surplus'] = 0;
      }
      // 查询用户有多少积分
      $user_points = $user_info['pay_points']; // 用户的积分总数
      $order['integral'] = min($order['integral'], $user_points);
      if ($order['integral'] < 0)
      {
        $order['integral'] = 0;
      }
    }
    else
    {
      $order['surplus']  = 0;
      $order['integral'] = 0;
    }

    /* 检查优惠券是否存在 */
    if ($order['bonus_id'] > 0)
    {
      $bonus = bonus_info($order['bonus_id']);
      if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount($flow_type))
      {
          $order['bonus_id'] = 0;
      }
    }
    elseif (isset($_POST['bonus_sn']))
    {
      $bonus_sn = trim($_POST['bonus_sn']);
      $bonus = bonus_info(0, $bonus_sn);
      $now = gmtime();
      if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount($flow_type) || $now > $bonus['use_end_date'])
      {
      }
      else
      {
        if ($user_id > 0)
        {
            $sql = "UPDATE " . $aos->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
            $db->query($sql);
        }
        $order['bonus_id'] = $bonus['bonus_id'];
        $order['bonus_sn'] = $bonus_sn;
      }
    }

    /* 订单中的商品 */
    $cart_goods = cart_goods($flow_type);


    if (empty($cart_goods))
    {
      show_message('您的购物车中没有商品！', '返回首页', './index.php', 'warning');
    }

    /* 检查商品总额是否达到最低限购金额 */
    if (cart_amount($flow_type) < $_CFG['min_goods_amount'])
    {
      show_message(sprintf('您购买的商品没有达到本店的最低限购金额 %s ，不能提交订单。', price_format($_CFG['min_goods_amount'], false)));
    }
    $sql="SELECT shipping_id FROM " . $aos->table('shipping') . " WHERE shipping_id=".$order['shipping_id'] ." AND enabled =1";
    if(!$db->getOne($sql))
    {
      show_message('您必须选定一个配送方式。');
    }

    /* 订单中的总额 */
    $total = order_fee($order, $cart_goods, $consignee);
    $order['bonus']        = $total['bonus'];
    $order['goods_amount'] = $total['goods_price'];
    $order['discount']     = $total['discount'];
    $order['surplus']      = $total['surplus'];
    $order['integral']      = $total['integral'];


    // 优惠券和积分最多能支付的金额为商品总额
    if ($order['goods_amount'] <= 0)
    {
        $order['bonus_id'] = 0;
    }

    /* 配送方式 */
    if ($order['shipping_id'] > 0)
    {
      $shipping = shipping_info($order['shipping_id']);
      $order['shipping_name'] = addslashes($shipping['shipping_name']);
    }
    $order['shipping_fee'] = $total['shipping_fee'];

    /* 收货人信息 */
    if ($order['shipping_id'] == 1)
    {
      $store = $_SESSION['flow_store'];
      $order['consignee'] = isset($_POST['consignee']) ? compile_str($_POST['consignee']) : '';
      $order['mobile'] = isset($_POST['mobile']) ? compile_str(make_semiangle(trim($_POST['mobile']))) : '';
      $order['store_id'] = $store['store_id'];
    }
    else
    {   
      if(empty($consignee)){
        show_message("请填写收货人");
      }
      foreach ($consignee as $key => $value)
      {
        $order[$key] = addslashes($value);
      }
    }

    /* 支付方式 */
    if ($order['pay_id'] > 0)
    {
      $payment = payment_info($order['pay_id']);
      $order['pay_name'] = addslashes($payment['pay_name']);
    }
    $order['pay_fee'] = $total['pay_fee'];
    $order['cod_fee'] = $total['cod_fee'];
    $order['order_amount']  = number_format($total['amount'], 2, '.', '');

    /* 如果全部使用余额支付，检查余额是否足够 */
    if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)
    {
      if($order['surplus'] >0) //余额支付里如果输入了一个金额
      {
        $order['order_amount'] = $order['order_amount'] + $order['surplus'];
        $order['surplus'] = 0;
      }
      if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
      {
        show_message('您的余额不足以支付整个订单，请选择其他支付方式');
      }
      else
      {
        $order['surplus'] = $order['order_amount'];
        $order['order_amount'] = 0;
      }
    }
    if($flow_type == 4 && $order['integral']>0){
        $point = get_user_points($user_id);
        $points = $point['pay_points'];
        if($points<$order['integral']){
            show_message('积分不足');
        }else{
            $order['order_amount'] = $order['order_amount']-$order['integral'];
            $order['integral_money']   = $total['integral'];
        }
    }

    /* 如果订单金额为0（使用余额或积分或优惠券支付），修改订单状态为已确认、已付款 */
    if ($order['order_amount'] <= 0)
    {
      $order['order_status'] = 1;
      $order['confirm_time'] = gmtime();
      $order['pay_status']   = 2;
      $order['pay_time']     = gmtime();
      $order['order_amount'] = 0;
    }

    
    //$order['integral']         = $total['integral'];


    /* 记录扩展信息 */
    if (!empty($flow_type))
    {
      $order['extension_code'] = $_SESSION['extension_code'];
      $order['extension_id'] = $_SESSION['extension_id'];
      $order['act_id'] = $_SESSION['act_id'];
    }
    
    /*判断开启推广增加用户父级ID*/
    $goods_comm = goods_comm($cart_goods['goods_id']);
    if(($order['extension_code'] == 'tuan' || empty($order['extension_code'])) && $goods_comm['is_dist'] == 1)
    {
      $order['parent_id'] = get_parent_user($user_id);
    }

    /* 插入订单表 */
    $error_no = 0;
    do
    {
      $order['order_sn'] = get_order_sn(); //获取新订单号
      $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('order_info'), $order, 'INSERT');
      $error_no = $GLOBALS['db']->errno();
      if ($error_no > 0 && $error_no != 1062)
      {
        die($GLOBALS['db']->errorMsg());
      }
    }
    while($error_no == 1062); //如果是订单号重复则重新提交数据
    $new_order_id = $db->insert_id();
    $order['order_id'] = $new_order_id;

    /* 插入订单商品 */
    $sql = "INSERT INTO " . $aos->table('order_goods') . "( " .
                "order_id, goods_id, goods_name, goods_sn, goods_number, market_price, ".
                "goods_price, goods_attr, attr_id) ".
            " SELECT '$new_order_id', goods_id, goods_name, goods_sn, goods_number, market_price, ".
                "goods_price, goods_attr, attr_id".
            " FROM " .$aos->table('cart') .
            " WHERE session_id = '".SESS_ID."' AND rec_type = '$flow_type'";
    $db->query($sql);
	
	/*插入团购记录*/
  if (!empty($order['extension_code']))
  {
	$extension_id = get_order_sn();
    if(!empty($order['extension_id']))
    {
			$sql = "UPDATE ". $aos->table('order_info') ." SET tuan_first = 2 WHERE order_id = ".$order['order_id'];
      $db->query($sql);
    }
    else
    {
      $sql = "UPDATE ". $aos->table('order_info') ." SET extension_id = ".$extension_id.", tuan_first = 1 WHERE order_id=".$order['order_id'];
      $db->query($sql);
    }
    if($order['extension_code']=='miao'){
        $sql="select seck_tuan_num from ".$aos->table('seckill')." where seckill_id = ".$order['act_id'];
        $tuan_num = $db->getOne($sql);
    }elseif($order['extension_code']=='lottery'){
        $sql="select lottery_tuan_num from ".$aos->table('lottery')." where lottery_id = ".$order['act_id'];
        $tuan_num = $db->getOne($sql);
    }elseif($order['extension_code']=='tuan'){
        $tuan_num = min(get_tuan_number($cart_goods['goods_id']));
    }
    
    if ($order['order_amount'] <= 0)
    {
      $tuan_status = 1;
    }
    else
    {
      $tuan_status = 0;
    }
    $sql = "UPDATE ". $aos->table('order_info') ." SET tuan_status = ".$tuan_status.", tuan_num = ".$tuan_num." WHERE order_id=".$order['order_id'];
    $db->query($sql); 
  }

  $mobile = $db->getOne('SELECT mobile FROM '.$aos->table("users").' WHERE user_id = '.$user_id);
  if(empty($mobile))
  {
    if(!empty($order['mobile']) && !empty($order['consignee']))
    {
      $sql = "UPDATE ". $aos->table('users') ." SET mobile = ".$order['mobile'].", realname = '".$order['consignee']."' WHERE user_id=".$order['user_id'];
      $db->query($sql);
    }
  }
 
  /* 处理余额、积分、优惠券 */
  if ($order['user_id'] > 0 && $order['surplus'] > 0)
  {
    log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf('支付订单 %s', $order['order_sn']));
  }
  if ($order['user_id'] > 0 && $order['integral'] > 0)
  {
    log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), sprintf('支付订单 %s', $order['order_sn']));
  }
  //if ($order['bonus_id'] > 0 && $temp_amout > 0)
  if ($order['bonus_id'] > 0)
  {
    use_bonus($order['bonus_id'], $new_order_id);
  }

  /* 清空购物车 */
  clear_cart($flow_type);
  /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */
  clear_all_files();

  /* 插入支付日志 */
  $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], 0);

  /* 取得支付信息，生成支付代码 */
  if ($order['order_amount'] > 0)
  {
    if($order['pay_id'] == 2){
      $pay_url = "index.php?c=wxpay&out_trade_no=".$order['log_id'];
    }
    if($order['pay_id'] == 3){
      $pay_url = "index.php?c=alipay&out_trade_no=".$order['log_id'];
    }
    header("Location:".$pay_url."\n");
  }
  else
  {

    order_paid($order['log_id'], $pay_status = 2, $note = '',1);

  if(!empty($order['extension_code']))
  {

    if (!empty($order['extension_id']))
    {
        $extension_id = $order['extension_id'];
    }
    
    $back_url = 'index.php?c=share&tuan_id='.$extension_id;
    
  }
  else
  {
    $back_url = 'index.php?c=user&a=order_detail&order_id='.$order['order_id'];
  }



    //$back_url = "index.php?c=user&a=order_detail&order_id=".$order['order_id'];
    header("Location:".$back_url."\n");
  }
  unset($_SESSION['flow_store']);
  unset($_SESSION['flow_consignee']);
  unset($_SESSION['flow_order']);
  unset($_SESSION['direct_shopping']);
  unset($_SESSION['extension_id']);
  unset($_SESSION['extension_code']);
  unset($_SESSION['act_id']);
}

/*------------------------------------------------------ */
//-- 删除购物车中的商品
/*------------------------------------------------------ */

elseif ($action == 'drop_goods')
{
    $rec_id = intval($_GET['id']);
    flow_drop_cart_goods($rec_id);

    aos_header("Location: index.php?c=flow\n");
    exit;
}
elseif ($action == 'clear')
{
    $sql = "DELETE FROM " . $aos->table('cart') . " WHERE session_id='" . SESS_ID . "'";
    $db->query($sql);

    aos_header("Location:./\n");
}
elseif ($action == 'drop_to_collect')
{
    if ($_SESSION['user_id'] > 0)
    {
        $rec_id = intval($_GET['id']);
        $goods_id = $db->getOne("SELECT  goods_id FROM " .$aos->table('cart'). " WHERE rec_id = '$rec_id' AND session_id = '" . SESS_ID . "' ");
        $count = $db->getOne("SELECT goods_id FROM " . $aos->table('collect_goods') . " WHERE user_id = '$_SESSION[user_id]' AND goods_id = '$goods_id'");
        if (empty($count))
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['aos']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";
            $db->query($sql);
        }
        flow_drop_cart_goods($rec_id);
    }
    aos_header("Location: index.php?c=flow\n");
    exit;
}

$smarty->assign('currency_format', $_CFG['currency_format']);
$smarty->assign('integral_scale',  $_CFG['integral_scale']);
$smarty->assign('action',$action);
$smarty->display('flow.htm');

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */


/**
 * 更新购物车中的商品数量
 *
 * @access  public
 * @param   array   $arr
 * @return  void
 */
function flow_update_cart($arr)
{
    /* 处理 */
    foreach ($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
        if ($val <= 0 || !is_numeric($key))
        {
            continue;
        }

        //查询：
        $sql = "SELECT `goods_id`, `attr_id` FROM" .$GLOBALS['aos']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number ".
                "FROM " .$GLOBALS['aos']->table('goods'). " AS g, ".
                    $GLOBALS['aos']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $row = $GLOBALS['db']->getRow($sql);

        //查询：系统启用了库存，检查输入的商品数量是否有效
        if (intval($GLOBALS['_CFG']['use_storage']) > 0)
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf('非常抱歉，您选择的商品 %s 的库存数量只有 %d，您最多只能购买 %d 件。', $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }
            /* 是货品 */
            $goods['product_id'] = trim($goods['product_id']);
            if (!empty($goods['product_id']))
            {
                $sql = "SELECT product_number FROM " .$GLOBALS['aos']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $goods['product_id'] . "'";

                $product_number = $GLOBALS['db']->getOne($sql);
                if ($product_number < $val)
                {
                    show_message(sprintf('非常抱歉，您选择的数量已经超出库存。请您减少购买量或联系商家。', $row['goods_name'],
                    $product_number['product_number'], $product_number['product_number']));
                    exit;
                }
            }
        }

        /* 查询：检查该项是否为基本件 以及是否存在配件 */
        /* 此处配件是指添加商品时附加的并且是设置了优惠价格的配件 此类配件都有parent_id goods_number为1 */
        $sql = "SELECT b.goods_number, b.rec_id
                FROM " .$GLOBALS['aos']->table('cart') . " a, " .$GLOBALS['aos']->table('cart') . " b
                WHERE a.rec_id = '$key'
                AND a.session_id = '" . SESS_ID . "'
                AND b.parent_id = a.goods_id
                AND b.session_id = '" . SESS_ID . "'";

        $offers_accessories_res = $GLOBALS['db']->query($sql);

        //订货数量大于0
        if ($val > 0)
        {
            /* 判断是否为超出数量的优惠价格的配件 删除*/
            $row_num = 1;
            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))
            {
                if ($row_num > $val)
                {
                    $sql = "DELETE FROM " . $GLOBALS['aos']->table('cart') .
                            " WHERE session_id = '" . SESS_ID . "' " .
                            "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";
                    $GLOBALS['db']->query($sql);
                }

                $row_num ++;
            }

			$attr_id    = empty($goods['attr_id']) ? '' : $goods['attr_id'];
			$goods_price = get_final_price($goods['goods_id'], $val, true, $attr_id);

			//更新购物车中的商品数量
			$sql = "UPDATE " .$GLOBALS['aos']->table('cart').
					" SET goods_number = '$val', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";

        }
        //订货数量等于0
        else
        {
            /* 如果是基本件并且有优惠价格的配件则删除优惠价格的配件 */
            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))
            {
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('cart') .
                        " WHERE session_id = '" . SESS_ID . "' " .
                        "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";
                $GLOBALS['db']->query($sql);
            }

            $sql = "DELETE FROM " .$GLOBALS['aos']->table('cart').
                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "'";
        }

        $GLOBALS['db']->query($sql);
    }
}

/**
 * 检查订单中商品库存
 *
 * @access  public
 * @param   array   $arr
 *
 * @return  void
 */
function flow_cart_stock($arr)
{
    foreach ($arr AS $key => $val)
    {
        $val = intval(make_semiangle($val));
        if ($val <= 0 || !is_numeric($key))
        {
            continue;
        }

        $sql = "SELECT `goods_id`, `attr_id` FROM" .$GLOBALS['aos']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number, c.product_id ".
                "FROM " .$GLOBALS['aos']->table('goods'). " AS g, ".
                    $GLOBALS['aos']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $row = $GLOBALS['db']->getRow($sql);

        //系统启用了库存，检查输入的商品数量是否有效
        if (intval($GLOBALS['_CFG']['use_storage']) > 0)
        {
            if ($row['goods_number'] < $val)
            {
                show_message(sprintf('非常抱歉，您选择的数量已经超出库存。请您减少购买量或联系商家。', $row['goods_name'],
                $row['goods_number'], $row['goods_number']));
                exit;
            }

            /* 是货品 */
            $row['product_id'] = trim($row['product_id']);
            if (!empty($row['product_id']))
            {
                $sql = "SELECT product_number FROM " .$GLOBALS['aos']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $row['product_id'] . "'";
                $product_number = $GLOBALS['db']->getOne($sql);
                if ($product_number < $val)
                {
                    show_message(sprintf('非常抱歉，您选择的数量已经超出库存。请您减少购买量或联系商家。', $row['goods_name'],
                    $row['goods_number'], $row['goods_number']));
                    exit;
                }
            }
        }
    }

}

/**
 * 删除购物车中的商品
 *
 * @access  public
 * @param   integer $id
 * @return  void
 */
function flow_drop_cart_goods($id)
{
    /* 取得商品id */
    $sql = "SELECT * FROM " .$GLOBALS['aos']->table('cart'). " WHERE rec_id = '$id'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row)
    {
        $sql = "DELETE FROM " . $GLOBALS['aos']->table('cart') .
                " WHERE session_id = '" . SESS_ID . "' " .
                "AND rec_id = '$id' LIMIT 1";

        $GLOBALS['db']->query($sql);
        return true;
    }
}
?>
