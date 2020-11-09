<?php

/*购物流程函数库*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 处理序列化的支付、配送的配置参数
 * 返回一个以name为索引的数组
 *
 * @access  public
 * @param   string       $cfg
 * @return  void
 */
function unserialize_config($cfg)
{
	
    if (is_string($cfg) && ($arr = unserialize($cfg)) !== false)
    {
        $config = array();

        foreach ($arr AS $key => $val)
        {
            $config[$val['name']] = $val['value'];
        }

        return $config;
    }
    else
    {
        return false;
    }
}
/**
 * 取得已安装的配送方式
 * @return  array   已安装的配送方式
 */
function shipping_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('shipping') .
            ' WHERE enabled = 1';
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得可用的配送方式列表
 * @param   array   $region_id_list     收货人地区id数组（包括国家、省、市、区）
 * @return  array   配送方式数组
 */
function available_shipping_list()
{
    $where = " enabled = 1 AND shipping_id < 3";
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('shipping') .
            " WHERE $where ORDER BY shipping_id";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得配送区域信息
 */
function shipping_configure($region_id_list)
{
    $sql = 'SELECT a.configure FROM ' . 
	$GLOBALS['aos']->table('shipping_area') . ' AS a, ' .
	$GLOBALS['aos']->table('area_region') . ' AS r ' .
	'WHERE r.region_id ' . db_create_in($region_id_list) . 
	' AND r.shipping_area_id = a.shipping_area_id';
    return $GLOBALS['db']->getOne($sql);
}

/**
 * 取得配送方式信息
 * @param   int     $shipping_id    配送方式id
 * @return  array   配送方式信息
 */
function shipping_info($shipping_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('shipping') .
            " WHERE shipping_id = '$shipping_id' " .
            'AND enabled = 1';

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得配送方式对应收货地址的区域信息*/
function shipping_area_info($region_id_list)
{
    $sql = 'SELECT a.configure ' . 'FROM ' . $GLOBALS['aos']->table('shipping_area') . ' AS a, ' .
                $GLOBALS['aos']->table('area_region') . ' AS r ' .
				'WHERE r.region_id ' . db_create_in($region_id_list) .
            ' AND r.shipping_area_id = a.shipping_area_id';
    $row = $GLOBALS['db']->getRow($sql);

    if (!empty($row))
    {
        $shipping_config = unserialize_config($row['configure']);
    }

    return $row;
}



/**
 * 计算运费
 * @param   mix     $shipping_config    配送方式配置信息
 * @param   float   $goods_weight       商品重量
 * @param   float   $goods_amount       商品金额
 * @param   float   $goods_number       商品数量
 * @return  float   运费
 */
function shipping_fee($shipping_config, $goods_weight, $goods_amount, $goods_number='')
{
    if (!is_array($shipping_config))
    {
        $shipping_config = unserialize($shipping_config);
    }
	//return 100;
    include_once(ROOT_PATH . 'source/class/express.class.php');
    
    $obj = new express();
    return $obj->calculate($shipping_config, $goods_weight, $goods_amount, $goods_number);
}


/**
 * 取得已安装的支付方式列表
 * @return  array   已安装的配送方式列表
 */
function payment_list()
{
    $sql = 'SELECT pay_id, pay_name, pay_code ' .
            'FROM ' . $GLOBALS['aos']->table('payment') .
            ' WHERE enabled = 1 AND pay_id > 1';

    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得支付方式信息
 * @param   int     $pay_id     支付方式id
 * @return  array   支付方式信息
 */
function payment_info($pay_id)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('payment') .
            " WHERE pay_id = '$pay_id' AND enabled = 1";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得订单信息
 * @param   int     $order_id   订单id（如果order_id > 0 就按id查，否则按sn查）
 * @param   string  $order_sn   订单号
 * @return  array   订单信息（金额都有相应格式化的字段，前缀是formated_）
 */
function order_info($order_id, $order_sn = '')
{
    /* 计算订单各种费用之和的语句 */
    $total_fee = " (goods_amount - discount + shipping_fee) AS total_fee ";
    $order_id = intval($order_id);
    if ($order_id > 0)
    {
        $sql = "SELECT *, " . $total_fee . " FROM " . $GLOBALS['aos']->table('order_info') .
                " WHERE order_id = '$order_id'";
    }
    else
    {
        $sql = "SELECT *, " . $total_fee . "  FROM " . $GLOBALS['aos']->table('order_info') .
                " WHERE order_sn = '$order_sn'";
    }
    $order = $GLOBALS['db']->getRow($sql);

    /* 格式化金额字段 */
    if ($order)
    {
        $order['formated_goods_amount']   = price_format($order['goods_amount'], false);
        $order['formated_discount']       = price_format($order['discount'], false);
        $order['formated_shipping_fee']   = price_format($order['shipping_fee'], false);
        $order['formated_total_fee']      = price_format($order['total_fee'], false);
        $order['formated_money_paid']     = price_format($order['money_paid'], false);
        $order['formated_bonus']          = price_format($order['bonus'], false);
        $order['formated_integral_money'] = price_format($order['integral_money'], false);
        $order['formated_surplus']        = price_format($order['surplus'], false);
        $order['formated_order_amount']   = price_format(abs($order['order_amount']), false);
        $order['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['add_time']);
        $order['formated_pay_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['pay_time']);
        $order['formated_suc_tuan_time']       = local_date($GLOBALS['_CFG']['time_format'], $order['suc_tuan_time']);
        $order['lastmodify']       = local_date($GLOBALS['_CFG']['time_format'], $order['lastmodify']);
    }
    return $order;
}

function tuan_info($extension_id)
{
    $sql = "SELECT o.extension_code, o.extension_id, o.tuan_num, o.tuan_first, o.pay_time, o.tuan_status, o.order_id, o.act_id, o.order_sn, o.user_id, g.goods_name, u.nickname, u.headimgurl,o.order_status,o.pay_status,o.shipping_status FROM ". $GLOBALS['aos']->table('order_info') . " AS o " .
    " LEFT JOIN " .$GLOBALS['aos']->table('order_goods'). " AS g ON g.order_id=o.order_id ".
    " LEFT JOIN " .$GLOBALS['aos']->table('users'). " AS u ON u.user_id=o.user_id ".
    " WHERE o.extension_id = '" .$extension_id."'";
    $tuan = $GLOBALS['db']->getAll($sql);
    foreach ($tuan AS $key => $value)
    {
        $tuan[$key]['start_time'] = local_date('Y-m-d H:i', $value['pay_time']);
        
        if($value['pay_time']){
            $tuan[$key]['end_time'] = local_date('Y-m-d H:i', $value['pay_time'] + $GLOBALS['_CFG']['tuan_time']*3600);
        }
    }
    return $tuan;
}

/**
 * 判断订单是否已完成
 * @param   array   $order  订单信息
 * @return  bool
 */
function order_finished($order)
{
    return $order['order_status']  == 1 &&
        ($order['shipping_status'] == 1 || $order['shipping_status'] == 2) &&
        ($order['pay_status']      == 2   || $order['pay_status'] == 1);
}

/**
 * 取得订单商品
 * @param   int     $order_id   订单id
 * @return  array   订单商品数组
 */
function order_goods($order_id)
{
    $sql = "SELECT rec_id, goods_id, goods_name, goods_sn, market_price, goods_number, " .
            "goods_price, goods_attr, " .
            "goods_price * goods_number AS subtotal " .
            "FROM " . $GLOBALS['aos']->table('order_goods') .
            " WHERE order_id = '$order_id'";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得订单总金额
 * @param   int     $order_id   订单id
 * @param   bool    $include_gift   是否包括赠品
 * @return  float   订单总金额
 */
function order_amount($order_id, $include_gift = true)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['aos']->table('order_goods') .
            " WHERE order_id = '$order_id'";

    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单商品总重量和总金额（对应 cart_weight_price）
 * @param   int     $order_id   订单id
 * @return  array   ('weight' => **, 'amount' => **, 'formated_weight' => **)
 */
function order_weight_price($order_id)
{
    $sql = "SELECT SUM(g.goods_weight * o.goods_number) AS weight, " .
                "SUM(o.goods_price * o.goods_number) AS amount ," .
                "SUM(o.goods_number) AS number " .
            "FROM " . $GLOBALS['aos']->table('order_goods') . " AS o, " .
                $GLOBALS['aos']->table('goods') . " AS g " .
            "WHERE o.order_id = '$order_id' " .
            "AND o.goods_id = g.goods_id";

    $row = $GLOBALS['db']->getRow($sql);
    $row['weight'] = floatval($row['weight']);
    $row['amount'] = floatval($row['amount']);
    $row['number'] = intval($row['number']);

    /* 格式化重量 */
    $row['formated_weight'] = formated_weight($row['weight']);

    return $row;
}

/**
 * 获得订单中的费用信息
 *
 * @access  public
 * @param   array   $order
 * @param   array   $goods
 * @param   array   $consignee
 * @return  array
 */
function order_fee($order, $goods, $consignee)
{
    /* 初始化订单的扩展code */
    if (!isset($order['extension_code']))
    {
        $order['extension_code'] = '';
    }

    $total  = array('goods_price'      => 0,
                    'shipping_fee'     => 0,
                    'bonus'            => 0,
                    'surplus'          => 0);
    $weight = 0;

    /* 商品总价 */

    $total['goods_price']  += $goods['goods_price'] * $goods['goods_number'];
    $total['goods_price_formated']  = price_format($total['goods_price'], false);
    /* 优惠券 */
    if (!empty($order['bonus_id']))
    {
        $bonus          = bonus_info($order['bonus_id']);
        $total['bonus'] = $bonus['type_money'];
    }
    $total['bonus_formated'] = price_format($total['bonus'], false);

    /* 线下优惠券 */
     if (!empty($order['bonus_kill']))
    {
        $bonus          = bonus_info(0,$order['bonus_kill']);
        $total['bonus_kill'] = $order['bonus_kill'];
        $total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
    }
    /* 配送费用 */
    $shipping_cod_fee = NULL;
    if ($order['shipping_id'] > 1)
    {
        $region['country']  = 1;//$consignee['country'];
        $region['province'] = $consignee['province'];//28;//
        $region['city']     = $consignee['city'];//324;//
        $region['district'] = $consignee['district'];//3243;//
        $shipping_info = shipping_area_info($region);
        if (empty($shipping_info))
        {
            $total['area'] = 1;
        }
        else
        {
            $weight_price = cart_weight_price($_SESSION['flow_type']);

            // 查看购物车中是否为免运费商品，若是则把运费赋为零
            $sql = 'SELECT count(*) FROM ' . $GLOBALS['aos']->table('cart') . " WHERE  `session_id` = '" . SESS_ID. "' AND `is_shipping` = 0";
            $shipping_count = $GLOBALS['db']->getOne($sql);
            $total['shipping_fee'] = ($shipping_count == 0) ? 0 :  shipping_fee($shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);
        }
    }

    $total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);

    // 购物车中的商品能享受优惠券支付的总额
    //$bonus_amount = compute_discount_amount();
    // 优惠券和积分最多能支付的金额为商品总额
    $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;

    /* 计算订单总额 */
        $total['amount'] = $total['goods_price'] + $total['shipping_fee'];

        // 减去优惠券金额
        $use_bonus        = min($total['bonus'], $max_amount); // 实际减去的优惠券金额
        if(isset($total['bonus_kill']))
        {
            $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
            $total['amount'] -=  $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
        }

        $total['bonus']   = $use_bonus;
        $total['bonus_formated'] = price_format($total['bonus'], false);

        $total['amount'] -= $use_bonus; // 还需要支付的订单金额
        $max_amount      -= $use_bonus; // 积分最多还能支付的金额


    /* 余额 */
    $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
    if ($total['amount'] > 0)
    {
        if (isset($order['surplus']) && $order['surplus'] > $total['amount'])
        {
            $order['surplus'] = $total['amount'];
            $total['amount']  = 0;
        }
        else
        {
            $total['amount'] -= floatval($order['surplus']);
        }
    }
    else
    {
        $order['surplus'] = 0;
        $total['amount']  = 0;
    }
    $total['surplus'] = $order['surplus'];
    $total['surplus_formated'] = price_format($order['surplus'], false);

    /* 保存订单信息 */
    $_SESSION['flow_order'] = $order;
    $se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';
    $total['amount_formated']  = price_format($total['amount'], false);
    $total['will_get_integral'] = get_give_integral($goods);
    $total['will_get_bonus']        = price_format(get_total_bonus(), false);
    $total['formated_goods_price']  = price_format($total['goods_price'], false);
    return $total;
}
function app_order_fee($order, $goods, $consignee)
{
    /* 初始化订单的扩展code */
    if (!isset($order['extension_code']))
    {
        $order['extension_code'] = '';
    }

    $total  = array('goods_price'      => 0,
                    'shipping_fee'     => 0,
                    'bonus'            => 0,
                    'surplus'          => 0);
    $weight = 0;

    /* 商品总价 */

    $total['goods_price']  += $goods['goods_price'] * $goods['goods_number'];
    $total['goods_price_formated']  = price_format($total['goods_price'], false);
    /* 优惠券 */
    if (!empty($order['bonus_id']))
    {
        $bonus          = bonus_info($order['bonus_id']);
        $total['bonus'] = $bonus['type_money'];
    }
    $total['bonus_formated'] = price_format($total['bonus'], false);

    /* 线下优惠券 */
     if (!empty($order['bonus_kill']))
    {
        $bonus          = bonus_info(0,$order['bonus_kill']);
        $total['bonus_kill'] = $order['bonus_kill'];
        $total['bonus_kill_formated'] = price_format($total['bonus_kill'], false);
    }

    /* 配送费用 */
    $shipping_cod_fee = NULL;
    if ($order['shipping_id'] > 1)
    {
        $region['country']  = 1;//$consignee['country'];
        $region['province'] = $consignee['province'];//28;//
        $region['city']     = $consignee['city'];//324;//
        $region['district'] = $consignee['district'];//3243;//
        $shipping_info = shipping_area_info($region);
        
        if (empty($shipping_info))
        {
            $total['area'] = 1;
        }
        else
        {
            $weight_price = app_cart_weight_price($_SESSION['flow_type']);

            // 查看购物车中是否为免运费商品，若是则把运费赋为零
            $sql = 'SELECT count(*) FROM ' . $GLOBALS['aos']->table('cart') . " WHERE  `user_id` = '" .$_SESSION['user_id']. "' and session_id = '" . APP . "'  AND `is_shipping` = 0 and rec_type = '".$_SESSION['flow_type']."'";
            $shipping_count = $GLOBALS['db']->getOne($sql);
            $total['shipping_fee'] = ($shipping_count == 0) ? 0 :  shipping_fee($shipping_info['configure'], $weight_price['weight'], $total['goods_price'], $weight_price['number']);

        }
    }

    $total['shipping_fee_formated']    = price_format($total['shipping_fee'], false);

    // 购物车中的商品能享受优惠券支付的总额
    //$bonus_amount = compute_discount_amount();
    // 优惠券和积分最多能支付的金额为商品总额
    $max_amount = $total['goods_price'] == 0 ? $total['goods_price'] : $total['goods_price'] - $bonus_amount;

    /* 计算订单总额 */
        $total['amount'] = $total['goods_price'] + $total['shipping_fee'];

        // 减去优惠券金额
        $use_bonus        = min($total['bonus'], $max_amount); // 实际减去的优惠券金额
        if(isset($total['bonus_kill']))
        {
            $use_bonus_kill   = min($total['bonus_kill'], $max_amount);
            $total['amount'] -=  $price = number_format($total['bonus_kill'], 2, '.', ''); // 还需要支付的订单金额
        }

        $total['bonus']   = $use_bonus;
        $total['bonus_formated'] = price_format($total['bonus'], false);

        $total['amount'] -= $use_bonus; // 还需要支付的订单金额
        $max_amount      -= $use_bonus; // 积分最多还能支付的金额


    /* 余额 */
    $order['surplus'] = $order['surplus'] > 0 ? $order['surplus'] : 0;
    if ($total['amount'] > 0)
    {
        if (isset($order['surplus']) && $order['surplus'] > $total['amount'])
        {
            $order['surplus'] = $total['amount'];
            $total['amount']  = 0;
        }
        else
        {
            $total['amount'] -= floatval($order['surplus']);
        }
    }
    else
    {
        $order['surplus'] = 0;
        $total['amount']  = 0;
    }
    $total['surplus'] = $order['surplus'];
    $total['surplus_formated'] = price_format($order['surplus'], false);

    /* 保存订单信息 */
    $_SESSION['flow_order'] = $order;

    $se_flow_type = isset($_SESSION['flow_type']) ? $_SESSION['flow_type'] : '';
    

    $total['amount_formated']  = price_format($total['amount'], false);

    /* 取得可以得到的积分和优惠券 */
     $total['will_get_integral'] = get_give_integral($goods);
    $total['will_get_bonus']        = price_format(get_total_bonus(), false);
    $total['formated_goods_price']  = price_format($total['goods_price'], false);

    return $total;
}

/**
 * 修改订单
 * @param   int     $order_id   订单id
 * @param   array   $order      key => value
 * @return  bool
 */
function update_order($order_id, $order)
{
    $order['lastmodify'] = gmtime();
    $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('order_info'),
        $order, 'UPDATE', "order_id = '$order_id'");
    return true;
}

/**
 * 得到新订单号
 * @return  string
 */
function get_order_sn()
{
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 取得购物车商品
 * @param   int     $type   类型：默认普通商品
 * @return  array   购物车商品数组
 */
function cart_goods($type = 0)
{
    $sql = "SELECT rec_id, user_id, goods_id, goods_name, goods_sn, goods_number, " .
            "market_price, goods_price, goods_attr, is_shipping, " .
            "goods_price * goods_number AS subtotal " .
            "FROM " . $GLOBALS['aos']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '$type'";

    $goods = $GLOBALS['db']->getRow($sql);
	$goods['goods_img'] = get_goods_img($goods['goods_id']);
    $goods['formated_market_price'] = price_format($goods['market_price'], false);
    $goods['formated_goods_price']  = price_format($goods['goods_price'], false);
    $goods['formated_subtotal']     = price_format($goods['subtotal'], false);
    return $goods;
}

/**
 * 取得购物车总金额
 * @params  boolean $include_gift   是否包括赠品
 * @param   int     $type           类型：默认普通商品
 * @return  float   购物车总金额
 */
function cart_amount($type = 0)
{
    $sql = "SELECT SUM(goods_price * goods_number) " .
            " FROM " . $GLOBALS['aos']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '$type' ";
    return floatval($GLOBALS['db']->getOne($sql));
}

/**
 * 获得购物车中商品的总重量、总价格、总数量
 *
 * @access  public
 * @param   int     $type   类型：默认普通商品
 * @return  array
 */
function cart_weight_price($type = 0)
{
    /* 获得购物车中商品的总重量 */
    $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, ' .
                    'SUM(c.goods_price * c.goods_number) AS amount, ' .
                    'SUM(c.goods_number) AS number '.
                'FROM ' . $GLOBALS['aos']->table('cart') . ' AS c '.
                'LEFT JOIN ' . $GLOBALS['aos']->table('goods') . ' AS g ON g.goods_id = c.goods_id '.
                "WHERE c.session_id = '" . SESS_ID . "' " .
                "AND rec_type = '$type' AND g.is_shipping = 0 ";
    $row = $GLOBALS['db']->getRow($sql);

    $result['weight'] = floatval($row['weight']);
    $result['amount'] = floatval($row['amount']);
    $result['number'] = intval($row['number']);
    /* 格式化重量 */
    $result['formated_weight'] = formated_weight($result['weight']);

    return $result;
}

/**
 * 获得购物车中商品的总重量、总价格、总数量
 *
 * @access  public
 * @param   int     $type   类型：默认普通商品
 * @return  array
 */
function app_cart_weight_price($type = 0)
{
    /* 获得购物车中商品的总重量 */
    $sql    = 'SELECT SUM(g.goods_weight * c.goods_number) AS weight, ' .
                    'SUM(c.goods_price * c.goods_number) AS amount, ' .
                    'SUM(c.goods_number) AS number '.
                'FROM ' . $GLOBALS['aos']->table('cart') . ' AS c '.
                'LEFT JOIN ' . $GLOBALS['aos']->table('goods') . ' AS g ON g.goods_id = c.goods_id '.
                "WHERE c.session_id = '" . APP . "' and c.user_id = '" . $_SESSION['user_id'] . "' " .
                "AND rec_type = '$type' AND g.is_shipping = 0 ";
    $row = $GLOBALS['db']->getRow($sql);

    $result['weight'] = floatval($row['weight']);
    $result['amount'] = floatval($row['amount']);
    $result['number'] = intval($row['number']);
    /* 格式化重量 */
    $result['formated_weight'] = formated_weight($result['weight']);

    return $result;
}

/**
 * 添加商品到购物车
 *
 * @access  public
 * @param   integer $goods_id   商品编号
 * @param   integer $num        商品数量
 * @param   array   $spec       规格值对应的id数组
 * @param   integer $parent     基本件
 * @return  boolean
 */
function addto_cart($goods_id, $num = 1, $spec = 0, $rec_type = 0)
{
    $GLOBALS['err']->clean();

    /* 取得商品信息 */
    
       $sql = "SELECT goods_name, goods_sn, is_on_sale, ".
                "market_price, shop_price AS org_price, ".
                "goods_weight, extension_code, ".
                "goods_number, is_shipping,restrictions ".
            " FROM " .$GLOBALS['aos']->table('goods').
            " WHERE goods_id = '$goods_id'" .
            " AND is_delete = 0";
        $goods = $GLOBALS['db']->getRow($sql); 
        
    

    if (empty($goods))
    {
        $GLOBALS['err']->add('对不起，指定的商品不存在', 1);

        return false;
    }

    /* 是否正在销售 */
    if ($goods['is_on_sale'] == 0)
    {
        $GLOBALS['err']->add('对不起，该商品已经下架。', 3);

        return false;
    }

    if (is_sku($spec))
    {
        $product_info = get_sku_info($goods_id, $spec);
    }
    if (empty($product_info))
    {
        $product_info['product_number'] = '';
    }

    /* 检查：库存 */
    if ($GLOBALS['_CFG']['use_storage'] == 1)
    {
        if(empty($product_info['product_number']))
        {
            //检查：商品购买数量是否大于总库存
            if ($num > $goods['goods_number'])
            {
                $GLOBALS['err']->add(sprintf("对不起，该商品已经库存不足".$num, $goods['goods_number']), 2);

                return false;
            }
        }
        else
        {
            if ($num > $product_info['product_number'])
            {
                $GLOBALS['err']->add(sprintf("对不起，该商品已经库存不足".$num, $product_info['product_number']), 2);

                return false;
            }
        } 
    }
    if(!empty($goods['restrictions'])){
        if($num>$goods['restrictions']){
            $GLOBALS['err']->add("该商品限购数量".$goods['restrictions']."件");

            return false;
        }
    }
    /* 计算商品的促销价格 */
    $spec_price             = sku_price($spec);
    $goods_price            = get_final_price($goods_id, $num, true, $spec, $rec_type);
    $goods['market_price'] += $spec_price;
    $goods_attr             = get_sku_name($spec);
    $attr_id          = $spec;
    if(defined("APP")){
       $sess=APP; 
    }else{
        $sess=SESS_ID;
    }
    /* 初始化要插入购物车的数据 */
    $parent = array(
        'user_id'       => $_SESSION['user_id'],
        'session_id'    => $sess,
        'goods_id'      => $goods_id,
        'goods_sn'      => addslashes($goods['goods_sn']),
        'goods_name'    => addslashes($goods['goods_name']),
        'market_price'  => $goods['market_price'],
        'goods_attr'    => $goods_attr,
        'attr_id'       => $attr_id,
        'extension_code'=> $goods['extension_code'],
        'is_shipping'   => $goods['is_shipping'],
        'rec_type'      => $rec_type
    );

    //print_r($parent);die;
	
    if ($num > 0)
    {
        $sql = "SELECT goods_number FROM " .$GLOBALS['aos']->table('cart').
                " WHERE session_id = '" .SESS_ID. "' AND goods_id = '$goods_id' ".
                " AND goods_attr = '" .get_sku_name($spec). "' ";

        $row = $GLOBALS['db']->getRow($sql);
		$goods_price = get_final_price($goods_id, $num, true, $spec, $rec_type);
		$parent['goods_price']  = max($goods_price, 0);
		$parent['goods_number'] = $num;
		$GLOBALS['db']->autoExecute($GLOBALS['aos']->table('cart'), $parent, 'INSERT');
        return $GLOBALS['db']->insert_id();
    }


    return true;
}

/**
 * 清空购物车
 * @param   int     $type   类型：默认普通商品
 */
function clear_cart()
{
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "'";
    $GLOBALS['db']->query($sql);
}

/**
 * 取得用户信息
 * @param   int     $user_id    用户id
 * @return  array   用户信息
 */
function user_info($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('users') .
            " WHERE user_id = '$user_id'";
    $user = $GLOBALS['db']->getRow($sql);

    /* 格式化帐户余额 */
    if ($user)
    {
        $user['formated_user_money'] = price_format($user['user_money'], false);
        $user['formated_frozen_money'] = price_format($user['frozen_money'], false);
    }
    return $user;
}

/**
 * 修改用户
 * @param   int     $user_id   订单id
 * @param   array   $user      key => value
 * @return  bool
 */
function update_user($user_id, $user)
{
    return $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('users'),
        $user, 'UPDATE', "user_id = '$user_id'");
}

/**
 * 取得用户地址列表
 * @param   int     $user_id    用户id
 * @return  array
 */
function address_list($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') ." WHERE user_id = '$user_id'";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得用户地址信息
 * @param   int     $address_id     地址id
 * @return  array
 */
function address_info($address_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') ." WHERE address_id = '$address_id'";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得用户当前可用优惠券
 * @param   int     $user_id        用户id
 * @param   float   $goods_amount   订单商品金额
 * @return  array   优惠券数组
 */
function user_bonus($user_id, $goods_amount = 0)
{
    $today  = gmtime();
    $sql = "SELECT * " .
            "FROM " . $GLOBALS['aos']->table('bonus_type') . " AS t," .
                $GLOBALS['aos']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id " .
            "AND t.use_start_date <= '$today' " .
            "AND t.use_end_date >= '$today' " .
            "AND t.min_goods_amount <= '$goods_amount' " .
            "AND b.user_id<>0 " .
            "AND b.user_id = '$user_id' " .
            "AND b.order_id = 0";
    return $GLOBALS['db']->getAll($sql);
}

/**
 * 取得优惠券信息
 * @param   int     $bonus_id   优惠券id
 * @param   string  $bonus_sn   优惠券序列号
 * @param   array   优惠券信息
 */
function bonus_info($bonus_id, $bonus_sn = '')
{
    $sql = "SELECT t.*, b.* " .
            "FROM " . $GLOBALS['aos']->table('bonus_type') . " AS t," .
                $GLOBALS['aos']->table('user_bonus') . " AS b " .
            "WHERE t.type_id = b.bonus_type_id ";
    if ($bonus_id > 0)
    {
        $sql .= "AND b.bonus_id = '$bonus_id'";
    }
    else
    {
        $sql .= "AND b.bonus_sn = '$bonus_sn'";
    }

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 检查优惠券是否已使用
 * @param   int $bonus_id   优惠券id
 * @return  bool
 */
function bonus_used($bonus_id)
{
    $sql = "SELECT order_id FROM " . $GLOBALS['aos']->table('user_bonus') ." WHERE bonus_id = '$bonus_id'";
    return  $GLOBALS['db']->getOne($sql) > 0;
}

/**
 * 设置优惠券为已使用
 * @param   int     $bonus_id   优惠券id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function use_bonus($bonus_id, $order_id)
{
    $sql = "UPDATE " . $GLOBALS['aos']->table('user_bonus') .
            " SET order_id = '$order_id', used_time = '" . gmtime() . "' " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";
    return  $GLOBALS['db']->query($sql);
}

/**
 * 设置优惠券为未使用
 * @param   int     $bonus_id   优惠券id
 * @param   int     $order_id   订单id
 * @return  bool
 */
function unuse_bonus($bonus_id)
{
    $sql = "UPDATE " . $GLOBALS['aos']->table('user_bonus') .
            " SET order_id = 0, used_time = 0 " .
            "WHERE bonus_id = '$bonus_id' LIMIT 1";
    return  $GLOBALS['db']->query($sql);
}

/**
 * 计算积分的价值（能抵多少钱）
 * @param   int     $integral   积分
 * @return  float   积分价值
 */
function value_of_integral($integral)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);
    return $scale > 0 ? round(($integral / 100) * $scale, 2) : 0;
}

/**
 * 计算指定的金额需要多少积分
 *
 * @access  public
 * @param   integer $value  金额
 * @return  void
 */
function integral_of_value($value)
{
    $scale = floatval($GLOBALS['_CFG']['integral_scale']);
    return $scale > 0 ? round($value / $scale * 100) : 0;
}

/**
 * 订单退款
 * @param   array   $order          订单
 * @param   int     $refund_type    退款方式 1 到帐户余额 2 到退款申请（先到余额，再申请提款） 3 不处理
 * @param   string  $refund_note    退款说明
 * @param   float   $refund_amount  退款金额（如果为0，取订单已付款金额）
 * @return  bool
 */
function order_refund($order, $refund_type, $refund_note, $refund_amount = 0)
{
    /* 检查参数 */
    $user_id = $order['user_id'];
    if ($user_id == 0 && $refund_type == 1)
    {
        die('anonymous, cannot return to account balance');
    }

    $amount = $refund_amount > 0 ? $refund_amount : $order['money_paid'];
    if ($amount <= 0)
    {
        return true;
    }

    if (!in_array($refund_type, array(1, 2, 3)))
    {
        die('invalid params');
    }

    /* 备注信息 */
    if ($refund_note)
    {
        $change_desc = $refund_note;
    }
    else
    {
        $change_desc = sprintf('应退款金额', $order['order_sn']);
    }

    /* 处理退款 */
    if (1 == $refund_type)
    {
        log_account_change($user_id, $amount, 0, 0, 0, $change_desc);

        return true;
    }
    elseif (2 == $refund_type)
    {
        /* 如果非匿名，退回余额 */
        if ($user_id > 0)
        {
            log_account_change($user_id, $amount, 0, 0, 0, $change_desc);
        }

        /* user_account 表增加提款申请记录 */
        $account = array(
            'user_id'      => $user_id,
            'amount'       => (-1) * $amount,
            'add_time'     => gmtime(),
            'user_note'    => $refund_note,
            'process_type' => 1,
            'admin_user'   => $_SESSION['admin_name'],
            'admin_note'   => sprintf('应退款金额', $order['order_sn']),
            'is_paid'      => 0
        );
        $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('user_account'), $account, 'INSERT');

        return true;
    }
    else
    {
        return true;
    }
}

/**
 * 获得购物车中的商品
 *
 * @access  public
 * @return  array
 */
function get_cart_goods($rec_type = 0)
{
    /* 初始化 */
    $goods_list = array();
    $total = array(
        'goods_price'  => 0, // 本店售价合计（有格式）
        'market_price' => 0, // 市场售价合计（有格式）
        'goods_amount' => 0, // 本店售价合计（无格式）
    );

    /* 循环、统计 */
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('cart') . " " .
            " WHERE session_id = '" . SESS_ID . "' AND rec_type = '" . $rec_type . "'";
    $res = $GLOBALS['db']->query($sql);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $total['goods_price']  += $row['goods_price'] * $row['goods_number'];
        $total['market_price'] += $row['market_price'] * $row['goods_number'];

        $row['subtotal']     = price_format($row['goods_price'] * $row['goods_number'], false);
        $row['goods_price']  = price_format($row['goods_price'], false);
        $row['market_price'] = price_format($row['market_price'], false);

        /* 查询规格 */
        if (trim($row['goods_attr']) != '')
        {
            $row['goods_attr']=addslashes($row['goods_attr']);
            $sql = "SELECT attr_value FROM " . $GLOBALS['aos']->table('goods_attr') . " WHERE attr_id " .
            db_create_in($row['goods_attr']);
            $attr_list = $GLOBALS['db']->getCol($sql);
            foreach ($attr_list AS $attr)
            {
                $row['goods_name'] .= ' [' . $attr . '] ';
            }
        }

        $goods_img = $GLOBALS['db']->getOne("SELECT `goods_img` FROM " . $GLOBALS['aos']->table('goods') . " WHERE `goods_id`='{$row['goods_id']}'");
        $row['goods_img'] = get_image_path($row['goods_id'], $goods_img, true);
        $goods_list[] = $row;
    }
    $total['goods_amount'] = $total['goods_price'];
    $total['saving']       = price_format($total['market_price'] - $total['goods_price'], false);
    if ($total['market_price'] > 0)
    {
        $total['save_rate'] = $total['market_price'] ? round(($total['market_price'] - $total['goods_price']) *
        100 / $total['market_price']).'%' : 0;
    }
    $total['goods_price']  = price_format($total['goods_price'], false);
    $total['market_price'] = price_format($total['market_price'], false);
    return array('goods_list' => $goods_list, 'total' => $total);
}

/**
 * 取得收货人信息
 * @param   int     $user_id    用户编号
 * @return  array
 */
function get_consignee($user_id)
{
    if (isset($_SESSION['flow_consignee']))
    {
        /* 如果存在session，则直接返回session中的收货人信息 */

        return $_SESSION['flow_consignee'];
    }
    else
    {
        /* 如果不存在，则取得用户的默认收货人信息 */
        $arr = array();
        if ($user_id > 0)
        {
            /* 取默认地址 */
            $sql = "SELECT address_id FROM ".$GLOBALS['aos']->table('users')." WHERE user_id='$user_id'";
            $address_id = $GLOBALS['db']->getOne($sql);
            if($address_id)
            {
                $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') . 
                        " WHERE user_id = '$user_id' AND address_id = '$address_id'";
            }
            else
            {
                $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') . 
                        " WHERE user_id = '$user_id' ORDER BY address_id DESC LIMIT 1";
            }
            $arr = $GLOBALS['db']->getRow($sql);
        }
        return $arr;
    }
}

/**
 * 获得上一次用户采用的支付和配送方式
 *
 * @access  public
 * @return  void
 */
function last_shipping_and_payment()
{
    $sql = "SELECT shipping_id, pay_id " .
            " FROM " . $GLOBALS['aos']->table('order_info') .
            " WHERE user_id = '$_SESSION[user_id]' " .
            " ORDER BY order_id DESC LIMIT 1";
    $row = $GLOBALS['db']->getRow($sql);
    if($row){
        //监测是否开启
        $sql="select shipping_id from ".$GLOBALS['aos']->table('shipping')." where shipping_id  = $row[shipping_id] and enabled = 1";
        $row['shipping_id']=$GLOBALS['db']->getOne($sql);
        if(!$row['shipping_id']){
            $sql="select shipping_id from ".$GLOBALS['aos']->table('shipping')." where shipping_id  in (1,2) and enabled = 1"; 
            $row['shipping_id']=$GLOBALS['db']->getOne($sql);
        }
        $sql="select pay_id from ".$GLOBALS['aos']->table('payment')." where pay_id  = $row[pay_id] and enabled = 1";
        $row['pay_id']=$GLOBALS['db']->getOne($sql);
        if(!$row['pay_id']){
            $sql="select pay_id from ".$GLOBALS['aos']->table('payment')." where  enabled = 1"; 
            $row['pay_id']=$GLOBALS['db']->getOne($sql);
        }
    }else{
        $sql="select shipping_id from ".$GLOBALS['aos']->table('shipping')." where shipping_id  in (1,2) and enabled = 1"; 
        $row['shipping_id']=$GLOBALS['db']->getOne($sql);
        $sql="select pay_id from ".$GLOBALS['aos']->table('payment')." where  enabled = 1"; 
        $row['pay_id']=$GLOBALS['db']->getOne($sql);
    }

    return $row;
}

/**
 * 取得当前用户应该得到的优惠券总额
 */
function get_total_bonus()
{
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    /* 按商品发的优惠券 */
    $sql = "SELECT SUM(c.goods_number * t.type_money)" .
            "FROM " . $GLOBALS['aos']->table('cart') . " AS c, "
                    . $GLOBALS['aos']->table('bonus_type') . " AS t, "
                    . $GLOBALS['aos']->table('goods') . " AS g " .
            "WHERE c.session_id = '" . SESS_ID . "' " .
            "AND c.goods_id = g.goods_id " .
            "AND g.bonus_type_id = t.type_id " .
            "AND t.send_type = '" . 1 . "' " .
            "AND t.send_start_date <= '$today' " .
            "AND t.send_end_date >= '$today' " .
            "AND c.rec_type = '" . 0 . "'";
    $goods_total = floatval($GLOBALS['db']->getOne($sql));

    /* 取得购物车中非赠品总金额 */
    $sql = "SELECT SUM(goods_price * goods_number) " .
            "FROM " . $GLOBALS['aos']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            " AND rec_type = '" . 0 . "'";
    $amount = floatval($GLOBALS['db']->getOne($sql));

    /* 按订单发的优惠券 */
    $sql = "SELECT FLOOR('$amount' / min_amount) * type_money " .
            "FROM " . $GLOBALS['aos']->table('bonus_type') .
            " WHERE send_type = '" . 2 . "' " .
            " AND send_start_date <= '$today' " .
            "AND send_end_date >= '$today' " .
            "AND min_amount > 0 ";
    $order_total = floatval($GLOBALS['db']->getOne($sql));

    return $goods_total + $order_total;
}

/**
 * 处理优惠券（下订单时设为使用，取消（无效，退货）订单时设为未使用
 * @param   int     $bonus_id   优惠券编号
 * @param   int     $order_id   订单号
 * @param   int     $is_used    是否使用了
 */
function change_user_bonus($bonus_id, $order_id, $is_used = true)
{
    if ($is_used)
    {
        $sql = 'UPDATE ' . $GLOBALS['aos']->table('user_bonus') . ' SET ' .
                'used_time = ' . gmtime() . ', ' .
                "order_id = '$order_id' " .
                "WHERE bonus_id = '$bonus_id'";
    }
    else
    {
        $sql = 'UPDATE ' . $GLOBALS['aos']->table('user_bonus') . ' SET ' .
                'used_time = 0, ' .
                'order_id = 0 ' .
                "WHERE bonus_id = '$bonus_id'";
    }
    $GLOBALS['db']->query($sql);
}

/**
 * 获得订单信息
 *
 * @access  private
 * @return  array
 */
function flow_order_info()
{
    $order = isset($_SESSION['flow_order']) ? $_SESSION['flow_order'] : array();

    /* 初始化配送和支付方式 */
    if (!isset($order['shipping_id']) || !isset($order['pay_id']))
    {
        /* 如果还没有设置配送和支付 */
        if ($_SESSION['user_id'] > 0)
        {
            /* 用户已经登录了，则获得上次使用的配送和支付 */
            $arr = last_shipping_and_payment();

            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = $arr['shipping_id'];
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = $arr['pay_id'];
            }
        }
        else
        {
            if (!isset($order['shipping_id']))
            {
                $order['shipping_id'] = 1;
            }
            if (!isset($order['pay_id']))
            {
                $order['pay_id'] = 0;
            }
        }
    }

    if (!isset($order['bonus']))
    {
        $order['bonus'] = 0;    // 初始化优惠券
    }
    if (!isset($order['integral']))
    {
        $order['integral'] = 0; // 初始化积分
    }
    if (!isset($order['surplus']))
    {
        $order['surplus'] = 0;  // 初始化余额
    }

    /* 扩展信息 */
    if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != 0)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }

    return $order;
}


/**
 * 查询配送区域属于哪个办事处管辖
 * @param   array   $regions    配送区域（1、2、3、4级按顺序）
 * @return  int     办事处id，可能为0
 */
function get_agency_by_regions($regions)
{
    if (!is_array($regions) || empty($regions))
    {
        return 0;
    }

    $arr = array();
    $sql = "SELECT region_id, agency_id " .
            "FROM " . $GLOBALS['aos']->table('region') .
            " WHERE region_id " . db_create_in($regions) .
            " AND region_id > 0 AND agency_id > 0";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $arr[$row['region_id']] = $row['agency_id'];
    }
    if (empty($arr))
    {
        return 0;
    }

    $agency_id = 0;
    for ($i = count($regions) - 1; $i >= 0; $i--)
    {
        if (isset($arr[$regions[$i]]))
        {
            return $arr[$regions[$i]];
        }
    }
}

/**
 * 获取配送插件的实例
 * @param   int   $shipping_id    配送插件ID
 * @return  object     配送插件对象实例
 */
function &get_shipping_object($shipping_id)
{
    $shipping  = shipping_info($shipping_id);
    if (!$shipping)
    {
        $object = new stdClass();
        return $object;
    }

    $file_path = ROOT_PATH.'includes/modules/shipping/' . $shipping['shipping_code'] . '.php';

    include_once($file_path);

    $object = new $shipping['shipping_code'];
    return $object;
}

/**
 * 改变订单中商品库存
 * @param   int     $order_id   订单号
 * @param   bool    $is_dec     是否减少库存
 * @param   bool    $storage     减库存的时机，1，下订单时；0，发货时；
 */
function change_order_goods_storage($order_id, $is_dec = true, $storage = 0)
{
    $sql="select extension_code,act_id from ".$GLOBALS['aos']->table('order_info')." where order_id = '$order_id'";
    $order=$GLOBALS['db']->getRow($sql);
    /* 查询订单商品信息 */
    $sql = "SELECT goods_id, SUM(goods_number) AS num, attr_id FROM " . $GLOBALS['aos']->table('order_goods') .
                    " WHERE order_id = '$order_id' GROUP BY goods_id, attr_id";

    $row = $GLOBALS['db']->getRow($sql);
    if($row){
        if ($is_dec)
        {
            if($order['extension_code']=='miao'){
                $sql = "UPDATE " . $GLOBALS['aos']->table('seckill') ."
                SET seckill_sales = seckill_sales + ".$row['num']."
                WHERE seckill_id = $order[act_id]";
                $GLOBALS['db']->query($sql);
            }elseif($order['extension_code']=='assist'){
                $sql = "UPDATE " . $GLOBALS['aos']->table('assist') ."
                SET assist_sales = assist_sales + ".$row['num']."
                WHERE assist_id = $order[act_id]";
                $GLOBALS['db']->query($sql);
            }
            if($order['extension_code']!='exchange'){
                change_goods_storage($row['goods_id'], $row['attr_id'], - $row['num']);
            }
            
        }
        else
        {
            if($order['extension_code']=='miao'){
                $sql = "UPDATE " . $GLOBALS['aos']->table('seckill') ."
                SET seckill_sales = seckill_sales - ".$row['num']."
                WHERE seckill_id = $order[act_id]";
                $GLOBALS['db']->query($sql);
            }elseif($order['extension_code']=='assist'){
                $sql = "UPDATE " . $GLOBALS['aos']->table('assist') ."
                SET assist_sales = assist_sales - ".$row['num']."
                WHERE assist_id = $order[act_id]";
                $GLOBALS['db']->query($sql);
            }
            if($order['extension_code']!='exchange'){
                change_goods_storage($row['goods_id'], $row['attr_id'], $row['num']);
            }
        }
        
	}

}

/**
 * 商品库存增与减 货品库存增与减
 *
 * @param   int    $good_id         商品ID
 * @param   int    $product_id      货品ID
 * @param   int    $number          增减数量，默认0；
 *
 * @return  bool               true，成功；false，失败；
 */
function change_goods_storage($good_id, $product_id, $number = 0)
{
    if ($number == 0)
    {
        return true; // 值为0即不做、增减操作，返回true
    }

    if (empty($good_id) || empty($number))
    {
        return false;
    }

    $number = ($number > 0) ? '+ ' . $number : $number;

    /* 处理货品库存 */
    $products_query = true;
    if (!empty($product_id))
    {
        $sql = "UPDATE " . $GLOBALS['aos']->table('goods_attr') ."
                SET product_number = product_number $number
                WHERE goods_id = '$good_id'
                AND attr_id = '$product_id'
                LIMIT 1";
        $query = $GLOBALS['db']->query($sql);
    }else{
       /* 处理商品库存 */
        $sql = "UPDATE " . $GLOBALS['aos']->table('goods') ."
                SET goods_number = goods_number $number
                WHERE goods_id = '$good_id'
                LIMIT 1";
        $query = $GLOBALS['db']->query($sql); 
    }

    

    if ($query)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 生成查询订单的sql
 * @param   string  $type   类型
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_query_sql($type = 'finished', $alias = '')
{
    /* 已完成订单 */
    if ($type == 'finished')
    {
        return " AND {$alias}order_status " . db_create_in(array(1, 5)) .
               " AND {$alias}shipping_status " . db_create_in(array(1, 2)) .
               " AND {$alias}pay_status " . db_create_in(array(2, 1)) . " ";
    }
    /* 待发货订单 */
    elseif ($type == 'await_ship')
    {
        return " AND   {$alias}order_status = 1" .
               //" AND ( {$alias}pay_status " . db_create_in(array(2, 1)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(true)) . ") ";
			   " AND   {$alias}pay_status = 2"  .
               " AND   {$alias}shipping_status ". db_create_in(array(0, 5)) .
               " and ({$alias}tuan_status =2 or {$alias}extension_code not in ('tuan','miao','lottery','assist') ) ".
               " and ({$alias}extension_code != 'lottery' || {$alias}is_luck = 1)".
               " and {$alias}shipping_id != 1 ";
    }
    /* 待付款订单 */
    elseif ($type == 'await_pay')
    {
        return " AND   {$alias}order_status = 1 " .
               " AND   {$alias}pay_status = 0 "
               //" AND ( {$alias}shipping_status " . db_create_in(array(1, 2)) . " OR {$alias}pay_id " . db_create_in(payment_id_list(false)) .
               ;
    }
    /* 未确认订单 */
    elseif ($type == 'unconfirmed')
    {
        return " AND {$alias}order_status = '" . 0 . "' ";
    }
    /* 未处理订单：用户可操作 */
    elseif ($type == 'unprocessed')
    {
        return " AND {$alias}shipping_status = '" . 0 . "'" .
               " AND {$alias}pay_status = '" . 0 . "' ".
               " AND {$alias}order_status " . db_create_in(array(0, 1));
    }
    /* 未付款未发货订单：管理员可操作 */
    elseif ($type == 'unpay_unship')
    {
        return " AND {$alias}pay_status = '" . 0 . "' ".
               " AND {$alias}order_status " . db_create_in(array(0, 1)) .
               " AND {$alias}shipping_status " . db_create_in(array(0, 3))
               ;
    }
    /* 已发货订单：不论是否付款 */
    elseif ($type == 'shipped')
    {
        return " AND {$alias}order_status = '" . 1 . "'" .
               " AND {$alias}shipping_status " . db_create_in(array(1, 2)) . " ";
    }
	/* 已取消订单 */
    elseif ($type == 'canceled')
    {
        return " AND {$alias}order_status " . db_create_in(array(2, 3)) . " ";
    }
    /* 退款中订单 */
    elseif ($type == 'payback')
    {
        return " AND {$alias}pay_status = '" . PS_PAYBACK . "' ";
    }	
    /* 待评论订单 */
    elseif ($type == 'await_comment')
    {
		$where = " AND {$alias}order_status ='" . 5 . "' AND {$alias}shipping_status = '" . 2 . "' and {$alias}pay_status = '" . 2 . "'and {$alias}comment = 0 ";
        return $where;
    }
    /* 待收货订单 */
    elseif ($type == 'await_receipt'){
        return " AND {$alias}order_status = 5 " .
               " AND {$alias}shipping_status = '" . 1 . "' ".
               "  and {$alias}shipping_id != 1  ";
    }
    //待核销
    elseif ($type == 'await_veri'){
        return " AND {$alias}shipping_status = 0 ".
                " AND {$alias}order_status = 1 " .
               " and {$alias}pay_status = 2 ".
               "  and {$alias}shipping_id = 1  ".
               " and ({$alias}extension_code != 'lottery' || {$alias}is_luck = 1)".
               " and ({$alias}tuan_status =2 or {$alias}extension_code not in ('tuan','miao','lottery') ) ";
    }
    //待成团
    elseif ($type == 'await_tuan'){
        return " AND {$alias}shipping_status = 0 ".
                " AND {$alias}order_status = 1 " .
               " and {$alias}pay_status = 2 ".
               " and {$alias}tuan_status = 1 ".
               " and {$alias}extension_code in ('tuan','miao','lottery')"
                ;
    }
    //已申请退款订单
    elseif ($type == 'refund'){
        return " AND {$alias}order_status =4 ".
               " and {$alias}pay_status = 2 ";
               //" and ({$alias}tuan_status =2 or {$alias}extension_code != 'tuan' ) ";
    }
    else
    {
        die('函数 order_query_sql 参数错误');
    }
}

/**
 * 生成查询订单总金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_amount_field($alias = '')
{
    return "{$alias}goods_amount + {$alias}shipping_fee - {$alias}discount";
}

/**
 * 生成计算应付款金额的字段
 * @param   string  $alias  order表的别名（包括.例如 o.）
 * @return  string
 */
function order_due_field($alias = '')
{
    return order_amount_field($alias) ." - {$alias}money_paid - {$alias}surplus - {$alias}integral_money - {$alias}bonus - {$alias}discount ";
}


/**
 * 取得购物车该赠送的积分数
 * @return  int     积分数
 */
function get_give_integral()
{
        $sql = "SELECT SUM(c.goods_number * IF(g.give_integral > -1, g.give_integral, c.goods_price))" .
                "FROM " . $GLOBALS['aos']->table('cart') . " AS c, " .
                          $GLOBALS['aos']->table('goods') . " AS g " .
                "WHERE c.goods_id = g.goods_id " .
                "AND c.session_id = '" . SESS_ID . "' " .
                "AND c.goods_id > 0 " .
                "AND c.rec_type = 0 ";

        return intval($GLOBALS['db']->getOne($sql));
}

/**
 * 取得某订单应该赠送的积分数
 * @param   array   $order  订单
 * @return  int     积分数
 */
function integral_to_give($order)
{
    $sql = "SELECT SUM(og.goods_number * IF(g.give_integral > -1, g.give_integral, og.goods_price)) AS rank_points " .
            "FROM " . $GLOBALS['aos']->table('order_goods') . " AS og, " .
                      $GLOBALS['aos']->table('goods') . " AS g " .
            "WHERE og.goods_id = g.goods_id " .
            "AND og.order_id = '$order[order_id]' " .
            "AND og.goods_id > 0 ";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 发优惠券：发货时发优惠券
 * @param   int     $order_id   订单号
 * @return  bool
 */
function send_order_bonus($order_id)
{
    /* 取得订单应该发放的优惠券 */
    $bonus_list = order_bonus($order_id);

    /* 如果有优惠券，统计并发送 */
    if ($bonus_list)
    {
        global $wechat,$admin_wechat;
        if(empty($wechat)){
            $wechat=$admin_wechat;
        }
        /* 用户信息 */
        $sql = "SELECT o.user_id, g.goods_name " .
                "FROM " . $GLOBALS['aos']->table('order_info') . " AS o left join " .
                          $GLOBALS['aos']->table('order_goods') . " AS g on o.order_id = g.order_id " .
                "WHERE o.order_id = '$order_id' ";
        $user = $GLOBALS['db']->getRow($sql);

        /* 统计 */
        $count = 0;
        $money = '';
        foreach ($bonus_list AS $bonus)
        {
            $count += $bonus['number'];
            $money .= price_format($bonus['type_money']) . ' [' . $bonus['number'] . '], ';

            /* 修改用户优惠券 */
            $sql = "INSERT INTO " . $GLOBALS['aos']->table('user_bonus') . " (bonus_type_id, user_id) " .
                    "VALUES('$bonus[type_id]', '$user[user_id]')";
            for ($i = 0; $i < $bonus['number']; $i++)
            {
                if (!$GLOBALS['db']->query($sql))
                {
                    return $GLOBALS['db']->errorMsg();
                }
                //发送消息
                $openid=getOpenid($user[user_id]);
                $use_time=local_date("m月d日", $bonus['use_start_date']).'-'.local_date("m月d日", $bonus['use_end_date']);
                $wx_title = "获得优惠劵通知";
                $wx_desc = "恭喜您购买的".$user['goods_name']."获得优惠劵\r\n优惠劵金额：".$bonus['type_money']."元\r\n有效期：".$use_time;
                
                $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
            }
        }
    }

    return true;
}

/**
 * 返回订单发放的优惠券
 * @param   int     $order_id   订单id
 */
function return_order_bonus($order_id)
{
    /* 取得订单应该发放的优惠券 */
    $bonus_list = order_bonus($order_id);

    /* 删除 */
    if ($bonus_list)
    {
        /* 取得订单信息 */
        $order = order_info($order_id);
        $user_id = $order['user_id'];

        foreach ($bonus_list AS $bonus)
        {
            $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_bonus') .
                    " WHERE bonus_type_id = '$bonus[type_id]' " .
                    "AND user_id = '$user_id' " .
                    "AND order_id = '0' LIMIT " . $bonus['number'];
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 取得订单应该发放的优惠券
 * @param   int     $order_id   订单id
 * @return  array
 */
function order_bonus($order_id)
{
    /* 查询按商品发的优惠券 */
    $day    = getdate();
    $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $sql = "SELECT b.type_id, b.type_money,b.use_start_date,b.use_end_date,SUM(o.goods_number) AS number " .
            "FROM " . $GLOBALS['aos']->table('order_goods') . " AS o, " .
                      $GLOBALS['aos']->table('goods') . " AS g, " .
                      $GLOBALS['aos']->table('bonus_type') . " AS b " .
            " WHERE o.order_id = '$order_id' " .
            " AND o.goods_id = g.goods_id " .
            " AND g.bonus_type_id = b.type_id " .
            " AND b.send_type = '" . 1 . "' " .
            " AND b.send_start_date <= '$today' " .
            " AND b.send_end_date >= '$today' " .
            " GROUP BY b.type_id ";
    $list = $GLOBALS['db']->getAll($sql);

    /* 查询定单中非赠品总金额 */
    $amount = order_amount($order_id, false);

    /* 查询订单日期 */
    $sql = "SELECT add_time " .
            " FROM " . $GLOBALS['aos']->table('order_info') .
            " WHERE order_id = '$order_id' LIMIT 1";
    $order_time = $GLOBALS['db']->getOne($sql);

    /* 查询按订单发的优惠券 */
    $sql = "SELECT type_id, type_money,use_start_date,use_end_date, IFNULL(FLOOR('$amount' / min_amount), 1) AS number " .
            "FROM " . $GLOBALS['aos']->table('bonus_type') .
            "WHERE send_type = '" . 2 . "' " .
            "AND send_start_date <= '$order_time' " .
            "AND send_end_date >= '$order_time' ";
    $list = array_merge($list, $GLOBALS['db']->getAll($sql));

    return $list;
}

/**
 * 得到新发货单号
 * @return  string
 */
function get_delivery_sn()
{
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/*获取门店名称*/
function return_store_name($store_id)
{
    $sql = "SELECT store_name FROM " . $GLOBALS['aos']->table('store') .
                    " WHERE store_id = '$store_id' ";
    return $GLOBALS['db']->getOne($sql);
}
function operable_list($order)
{
    /* 取得订单状态、发货状态、付款状态 */
    $os = $order['order_status'];
    $ss = $order['shipping_status'];
    $ps = $order['pay_status'];

    /* 取得订单操作权限 */
    $actions = $_SESSION['action_list'];
    if ($actions == 'all')
    {
        $priv_list  = array('os' => true, 'ss' => true, 'ps' => true, 'edit' => true);
    }
    else
    {
        $actions    = ',' . $actions . ',';
        $priv_list  = array(
            'os'    => strpos($actions, ',order_os_edit,') !== false,
            'ss'    => strpos($actions, ',order_ss_edit,') !== false,
            'ps'    => strpos($actions, ',order_ps_edit,') !== false,
            'edit'  => strpos($actions, ',order_edit,') !== false
        );
    }
    $list = array();
    if($order['order_status'] == 2){
        //删除的订单
        
        $list['remove'] = true;  // 删除
    }elseif($order['order_status'] == 3){
        //删除的订单
        
        $list['remove'] = true;  // 无效
    }elseif($order['order_status'] == 4){
        //退款的订单
    }else{
        $ex_array=array('tuan','lottery','miao');
        if($order['extension_code']=='assist' && $order['tuan_status'] == 1){
            
        }
        elseif(!empty($order['extension_code']) && in_array($order[extension_code], $ex_array))
        {
            if($order['extension_code']=='lottery' && $order['tuan_status'] == 2 && $order['is_luck']=='2'){
                //团成功，未中奖，待退款
            }
            elseif($order['extension_code']=='lottery' && $order['tuan_status'] == 2 && $order['is_luck']=='0'){
                //团成功，待抽奖
            }elseif($order['extension_code']=='lottery' && $order['tuan_status'] == 2 && $order['is_luck']=='3'){
                //团成功，未中奖，已退款
            }
            elseif($order['tuan_status'] == 0){
                if($order['order_status'] == 1){
                    //未付款
                    $list['pay'] = true;  // 付款
                    //$list['cancel'] = true; // 取消
                    
                }
                elseif($order['order_status'] == 2){
                    //订单已取消
                    $list['confirm']    = true; // 确认
                    $list['invalid']    = true; // 无效
                    
                }
            }
            elseif($order['tuan_status'] == 1){
                //拼团进行中
                if($order['pay_status'] == 0){
                    //未付款
                    $list['pay'] = true;  // 付款
                    //$list['cancel'] = true; // 取消
          
                }
                elseif($order['pay_status'] == 2)
                {
                    //已付款
                    $list['tuan'] = true; // 设为未付款 
                }
            }
            elseif($order['tuan_status'] == 2){
                if($order['shipping_id'] == 1)
                {
                    
                    if($order['shipping_status'] == 0){
                        //待核销
                        
                        $list['verifi'] = true; //去核销
              
                    }
                    elseif($order['shipping_status'] == 2)
                    {   //已核销
                        
                        $list['unverifi'] = true; //未核销
                    }
                }
                else
                {
                    if($order['shipping_status'] == 0 && $order['pay_status'] == 2){
                        //待发货
                        $list['split'] = true; // 生成发货单
                       
                        $list['to_shipping'] = true; //一键发货

                        //$list['unpay'] = true; // 设为未付款
              
                    }
                    elseif($order['shipping_status'] == 1)
                    {
                        //已发货
                        $list['receive'] = true; // 收货确认
                        //$list['unship'] = true; // 设为未发货  
                    }
                    elseif($order['shipping_status'] == 3)
                    {
                        //配完货
                        $list['split'] = true; // 生成发货单
                        //$list['unship'] = true; // 设为未发货  
                    }
                    elseif($order['shipping_status'] == 5)
                    {   //生成发货单后
                        
                        $list['to_delivery'] = true; //去发货s
                        //$list['unship'] = true; // 设为未发货  
                    }
                    elseif($order['shipping_status'] == 2)
                    {   //已收货
                        
                        //$list['return'] = true; //退货
                    }
                    
                }
            }
            elseif($order['tuan_status'] == 3){
                //未成团，待退款
                $list['refund'] = true; //去退款
            }
            elseif($order['tuan_status'] == 4){

                //未成团，退款成功
                $list['unrefund'] = true; //未退款
                
            }
            else
            {
                //团其他状态，待开发
                
                //二等奖，已退款并送券
            }
        }else{

            if($order['pay_status']==0){
                //未付款
                $list['pay'] = true;  // 付款
                //$list['cancel'] = true; // 取消
            }elseif($order['pay_status']==2){
                if($order['shipping_id'] == 1)
                {
                    if($order['shipping_status'] == 0){
                        //待核销
                        
                        $list['verifi'] = true; //去核销
              
                    }
                    elseif($order['shipping_status'] == 2)
                    {   //已核销
                        
                        $list['unverifi'] = true; //未核销
                    }
                }
                else
                {
                    if($order['shipping_status'] == 0){

                        //未发货
                        $list['unpay'] = true; // 设为未付款
                        $list['to_shipping'] = true; //一键发货
                        $list['split'] = true; // 生成发货单
                        
                        
                    }
                    elseif($order['shipping_status'] == 3)
                    {
                        //配完货
                        
                        $list['split'] = true; // 生成发货单
                    }
                    elseif($order['shipping_status'] == 1)
                    {
                        //卖家已发货
                        $list['receive'] = true; // 收货确认
                        $list['unship'] = true; // 设为未发货  
                    }
                    elseif($order['shipping_status'] == 5)
                    {   //生成过发货单
                        
                        $list['to_delivery'] = true; //去发货 
                        //$list['unship'] = true; // 设为未发货 
                    }
                    elseif($order['shipping_status'] == 2)
                    {   //已收货
                        
                        //$list['return'] = true; //退货
                    }

                }
            }
        } 
            
    }


 

    /* 售后 */
    $list['after_service'] = true;

    return $list;
}
/**
 * 取得订单商品
 * @param   array     $order  订单数组
 * @return array
 */
function get_order_goods($order)
{
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, IF(o.attr_id > 0, p.product_number, g.goods_number) AS storage, o.goods_attr, p.product_sn " .
            "FROM " . $GLOBALS['aos']->table('order_goods') . " AS o ".
            "LEFT JOIN " . $GLOBALS['aos']->table('goods_attr') . " AS p ON o.attr_id = p.attr_id " .
            "LEFT JOIN " . $GLOBALS['aos']->table('goods') . " AS g ON o.goods_id = g.goods_id " .
            "WHERE o.order_id = '$order[order_id]' ";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);

        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组

        //处理货品id
        $row['product_id'] = empty($row['product_id']) ? 0 : $row['product_id'];

        $goods_list[] = $row;
    }

    $attr = array();
    $arr  = array();
    foreach ($goods_attr AS $index => $array_val)
    {
        foreach ($array_val AS $value)
        {
            $arr = explode(':', $value);//以 : 号将属性拆开
            $attr[$index][] =  @array('name' => $arr[0], 'value' => $arr[1]);
        }
    }

    return array('goods_list' => $goods_list, 'attr' => $attr);
}
/**
 * 退回余额、积分、优惠券（取消、无效、退货时），把订单使用余额、积分、优惠券设为0
 * @param   array   $order  订单信息
 */
function return_user_surplus_integral_bonus($order)
{
    /* 处理余额、积分、优惠券 */
    $order['integral']=intval($order['integral']);
    if ($order['user_id'] > 0 && $order['integral'] > 0)
    {
        log_account_change($order['user_id'], 0, 0, '-'.$order['integral'], '-'.$order['integral'], sprintf('由于退货操作，扣点下单 %s 时赠送的积分', $order['order_sn']));
    }

    if ($order['bonus_id'] > 0)
    {
        unuse_bonus($order['bonus_id']);
    }

    /* 修改订单 */
    $arr = array(
        'bonus_id'  => 0,
        'bonus'     => 0,
        'integral'  => 0,
        'integral_money'    => 0,
    );
    update_order($order['order_id'], $arr);
}
?>