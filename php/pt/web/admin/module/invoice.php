<?php

define('IN_AOS', true);
require_once(ROOT_PATH . 'source/library/order.php');
$exc = new exchange($aos->table('shipping'), $db, 'shipping_id', 'shipping_name');
/* act操作项的初始化 */
if ($operation == 'index' || $operation == 'invoice_list')
{
    $operation = 'list';
}
/*------------------------------------------------------ */
//-- 配送方式列表
/*------------------------------------------------------ */
admin_priv('invoice_manage');
if ($operation == 'list')
{
    $shipping_list = invoice_shipping_list();
    $order_list = order_list();
    $smarty->assign('shipping_list', $shipping_list);

    //print_r($order_list['orders']);
    
    $smarty->assign('order_list',   $order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);
    $pager = get_page($order_list['filter']);
    $smarty->assign('pager',   $pager);

    $smarty->display('invoice_list.htm');
}
elseif ($operation == 'post')
{
  //print_r($_POST);die;
  if(isset($_POST['shipping_id']))
  {
    $ids= count($_POST['shipping_id']);
  }
  $order_arr=$_POST['order_id'];
  $shipping_arr=$_POST['shipping_id'];
  $invoice_arr=$_POST['invoice_no'];
  $b=0;
  for($i=0; $i<$ids; $i++)
  {
    $order_id=$order_arr[$i];
    $shipping_id=$shipping_arr[$i];
    $invoice_on=$invoice_arr[$i];
    if(!empty($order_id) && !empty($shipping_id) && !empty($invoice_on)){
        send_key_shipping($order_id,$invoice_on,$shipping_id);
        $b++;
    }
    //$sql = "UPDATE " . $aos->table('shipping') . " SET shipping_name = '".$_POST[shipping_name][$i]."',shipping_code = '".$_POST[shipping_code][$i]."' WHERE shipping_id = ".$_POST[shipping_id][$i];
    //$db->query($sql);
    
  }
  
    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=invoice&op=invoice_list');
    sys_msg('共处理'.$b.'个订单', 0, $links);
  
}
elseif ($operation == 'export')
{
    $export_order_list = export_order_list();
    //print_r($export_order_list);die;
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=待发货订单.csv");
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    //标题
    $data = '订单ID' . ",";
    $data .= "订单编号,";
    $data .= "商品名称,";
    $data .= "规格,";
    $data .= "数量,";
    $data .= "单价,";
    $data .= "总价,";
    $data .= "收货人,";
    $data .= "电话,";
    $data .= "地址,";
    $data .= "留言,";
    $data .= "快递公司,";
    $data .= "快递单号,\n";

    foreach($export_order_list as $k=>$v){
        $data .= $v['order_id']. ",";
        $data .= $v['order_sn']. "\t,";
        $data .= $v['goods_name']. ",";
        $data .= $v['goods_attr']. ",";
        $data .= $v['goods_number']. "\t,";
        $data .= $v['goods_price']. "\t,";
        $data .= $v['goods_amount']. "\t,";
        $data .= $v['consignee']. ",";
        $data .= $v['mobile']. "\t,";
        $data .= $v['area'].' '.$v['address']. ",";
        $data .= $v['postscript']. ",";
        $data .= ",";
        $data .= ",\n";

    }
    echo aos_iconv(AO_CHARSET, 'GB2312', $data) . "\n";
    exit;
}
/**
 * 取得配送方式
 * @return  array   配送方式
 */
function invoice_shipping_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('shipping') . " WHERE shipping_id > 2";
    return $GLOBALS['db']->getAll($sql);
}

/**
 *  获取订单列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function order_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $where = 'WHERE 1 ';
        $where .= order_query_sql('await_ship');

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['AOSCP']['page_size']) && intval($_COOKIE['AOSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['AOSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        if ($filter['user_name'])
        {
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('order_info') . " AS o ,".
                   $GLOBALS['aos']->table('users') . " AS u " . $where;
        }
        else
        {
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('order_info') . " AS o ". $where;
        }

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT o.is_luck,o.order_id, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid,o.store_id," .
                    "o.pay_status, o.consignee, o.address, o.mobile, o.extension_code, o.extension_id, o.tuan_status, o.tuan_num, o.store_id, " .
                    "u.nickname AS buyer ".
                " FROM " . $GLOBALS['aos']->table('order_info') . " AS o " .
                " LEFT JOIN " .$GLOBALS['aos']->table('users'). " AS u ON u.user_id=o.user_id ". $where .
                " ORDER BY suc_tuan_time DESC, add_time DESC ".
                " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        foreach (array('order_sn', 'consignee', 'address', 'mobile', 'user_name') AS $val)
        {
            $filter[$val] = stripslashes($filter[$val]);
        }
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式话数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['formated_order_amount'] = price_format($value['order_amount']);
        $row[$key]['formated_money_paid'] = price_format($value['money_paid']);
        $row[$key]['formated_total_fee'] = price_format($value['total_fee']);
        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
        //$row[$key]['shipping_list'] = shipping_list();
        
    }
    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

//所有待发货订单
function export_order_list()
{
        $where = 'WHERE 1 ';
        $where .= order_query_sql('await_ship');

        /* 查询 */
        $sql = "SELECT o.order_id, o.order_sn, o.consignee, o.area, o.address, o.mobile, o.postscript, o.goods_amount, og.goods_name, og.goods_attr, og.goods_number, og.goods_price ".
        "FROM " . $GLOBALS['aos']->table('order_info') ." AS o " .
        " LEFT JOIN " .$GLOBALS['aos']->table('order_goods'). " AS og ON og.order_id=o.order_id ".$where .
                " ORDER BY add_time DESC ";

    $res = $GLOBALS['db']->getAll($sql);
    /* 格式话数据 */
    foreach ($res AS $key => $value)
    {
        $area = explode(',',$value['area']);
    
        $area['province'] = get_region_name($area['0']);
        $area['city'] = get_region_name($area['1']);
        $area['district'] = get_region_name($area['2']);
        $res[$key]['area']  = $area['province'].' '.$area['city'].' '.$area['district'];  
    }
    return $res;
}
function send_key_shipping($order_id,$invoice_no,$shipping_id)
{
    /*------------------------------------------------------ */
//-- start一键发货
/*------------------------------------------------------ */
       $action_note = empty($_REQUEST['action_note']) ? '' : trim($_REQUEST['action_note']);  //备注
       if(empty($invoice_no)){
            $links[] = array('href' => 'index.php?act=invoice&op=invoice_list', 'text' => "返回");
            sys_msg("请填写快递单号", 0, $links);
       }
       $sql="select shipping_name,shipping_id from ".$GLOBALS['aos']->table('shipping')." where shipping_id = $shipping_id";
       $shipping_row=$GLOBALS['db']->getRow($sql);
       
       /*------------------------------------------------------ */
//-- start一键发货
/*------------------------------------------------------ */
    
       if (!empty($invoice_no))
        {
            $order_id = intval(trim($order_id));

            $action_note = trim($action_note);

        /* 查询：根据订单id查询订单信息 */
            if (!empty($order_id))
            {
                $order = order_info($order_id);
            }
            else
            {
                die('order does not exist');
            }
       
        $operation      = 'to_shipping';
        $operable_list = operable_list($order);
        if (!isset($operable_list[$operation]))
        {
            die('Hacking attempt');
        }
        /* 查询：如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
        $sql = "SELECT store_id FROM " . $GLOBALS['aos']->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
        $store_id = $GLOBALS['db']->getOne($sql);
        if ($store_id > 0)
        {
            if ($order['store_id'] != $store_id)
            {
                sys_msg('对不起,您没有执行此项操作的权限!', 0);
            }
        }
        /* 查询：取得用户名 */
        if ($order['user_id'] > 0)
        {
            $user = user_info($order['user_id']);
            if (!empty($user))
            {
                $order['user_name'] = $user['user_name'];
            }
        }
        /* 查询：取得区域名 */
        
        //$order['region'] = $GLOBALS['db']->getOne($sql);

        /* 查询：其他处理 */
        $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
        $order['invoice_no']    = $order['shipping_status'] == 0 || $order['shipping_status'] == 3 ? $_LANG['ss'][0] : $order['invoice_no'];

        /* 查询：是否保价 */
        $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;
       

        
        /* 查询：取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order['order_id'], 'order_sn' =>$order['order_sn']));

        $attr = $_goods['attr'];
        $goods_list = $_goods['goods_list'];
        unset($_goods);

        /* 查询：商品已发货数量 此单可发货数量 */
        if ($goods_list)
        {
            
            foreach ($goods_list as $key=>$goods_value)
            {
                if (!$goods_value['goods_id'])
                {
                    continue;
                }

               
                
                $goods_list[$key]['send'] = $goods_value['goods_number'];
                $goods_list[$key]['readonly'] = '';
                /* 是否缺货 */
                if ($goods_value['storage'] <= 0 && $_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == 0)
                {
                    $goods_list[$key]['send'] = '商品已缺货';
                    $goods_list[$key]['readonly'] = 'readonly="readonly"';
                }
                elseif ($goods_list[$key]['send'] <= 0)
                {
                    $goods_list[$key]['send'] = '货已发完';
                    $goods_list[$key]['readonly'] = 'readonly="readonly"';
                }
                
            }
        }
        $suppliers_id = 0;
        
        $delivery['order_sn'] = trim($order['order_sn']);
        $delivery['add_time'] = trim($order['order_time']);
        $delivery['user_id'] = intval(trim($order['user_id']));
        $delivery['how_oos'] = trim($order['how_oos']);
        $delivery['shipping_id'] = $shipping_row['shipping_id'];
        $delivery['shipping_fee'] = trim($order['shipping_fee']);
        $delivery['consignee'] = trim($order['consignee']);
        $delivery['address'] = trim($order['address']);
        $delivery['area'] = $order['area'];
        $delivery['district'] = intval(trim($order['district']));
        $delivery['sign_building'] = trim($order['sign_building']);
        $delivery['email'] = trim($order['email']);
        $delivery['zipcode'] = trim($order['zipcode']);
        $delivery['tel'] = trim($order['tel']);
        $delivery['mobile'] = trim($order['mobile']);
        $delivery['best_time'] = trim($order['best_time']);
        $delivery['postscript'] = trim($order['postscript']);
        $delivery['how_oos'] = trim($order['how_oos']);
        $delivery['insure_fee'] = floatval(trim($order['insure_fee']));
        $delivery['shipping_fee'] = floatval(trim($order['shipping_fee']));
        $delivery['agency_id'] = intval(trim($order['store_id']));
        $delivery['shipping_name'] = trim($shipping_row['shipping_name']);

    /* 查询订单信息 */
    $order = order_info($order_id);
    /* 检查能否操作 */
    $operable_list = operable_list($order);
    
    /* 初始化提示信息 */
   $msg = '';

        /* 定义当前时间 */
        define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳

        /* 取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
        $goods_list = $_goods['goods_list'];
    

                /* 检查此单发货商品库存缺货情况 */
        /* $goods_list已经过处理 超值礼包中商品库存已取得 */
        $virtual_goods = array();
        $package_virtual_goods = array();
        /* 生成发货单 */
        /* 获取发货单号和流水号 */
        $delivery['delivery_sn'] = get_delivery_sn();
        $delivery_sn = $delivery['delivery_sn'];

        /* 获取当前操作员 */
        $delivery['action_user'] = $_SESSION['admin_name'];

        /* 获取发货单生成时间 */
        $delivery['update_time'] = GMTIME_UTC;
        $delivery_time = $delivery['update_time'];
        $sql ="select add_time from ". $GLOBALS['aos']->table('order_info') ." WHERE order_sn = '" . $delivery['order_sn'] . "'";
        $delivery['add_time'] =  $GLOBALS['db']->GetOne($sql);


        /* 设置默认值 */
        $delivery['status'] = 2; // 正常
        $delivery['order_id'] = $order_id;

        /* 过滤字段项 */
        $filter_fileds = array(
                               'order_sn', 'add_time', 'user_id', 'how_oos', 'shipping_id', 'shipping_fee',
                               'consignee', 'address', 'area', 'district', 'sign_building',
                               'email', 'zipcode', 'tel', 'mobile', 'best_time', 'postscript', 'insure_fee',
                               'agency_id', 'delivery_sn', 'action_user', 'update_time',
                               'status', 'order_id', 'shipping_name'
                               );
        $_delivery = array();
        foreach ($filter_fileds as $value)
        {
            $_delivery[$value] = $delivery[$value];
        }
        /* 发货单入库 */
        $query = $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('delivery_order'), $_delivery, 'INSERT', '', 'SILENT');
        $delivery_id = $GLOBALS['db']->insert_id();
        if ($delivery_id)
        {

            $delivery_goods = array();
            
            //发货单商品入库
            if (!empty($goods_list))
            {
                foreach ($goods_list as $value)
                {
                    
                    $delivery_goods = array('delivery_id' => $delivery_id,
                                            'goods_id' => $value['goods_id'],
                                            'product_id' => $value['attr_id'],
                                            'product_sn' => $value['product_sn'],
                                            'goods_id' => $value['goods_id'],
                                            'goods_name' => $value['goods_name'],
                                            'brand_name' => $value['brand_name'],
                                            'goods_sn' => $value['goods_sn'],
                                            'send_number' => $value['goods_number'],
                                            'parent_id' => 0,
                                            'is_real' => $value['is_real'],
                                            'goods_attr' => $value['goods_attr']
                                            );
                    /* 如果是货品 */
                    if (!empty($value['attr_id']))
                    {
                        $delivery_goods['product_id'] = $value['attr_id'];

                    }
                    $query = $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
                    
                }
            }
        }
        else
        {
            /* 操作失败 */
            $links[] = array('text' => '订单信息', 'href' => 'order.php?act=info&order_id=' . $order_id);
            sys_msg('操作失败', 1, $links);
        }
        unset($filter_fileds, $delivery, $_delivery, $order_finish);

        /* 定单信息更新处理 */
        if (true)
        {

            /* 标记订单为已确认 “发货中” */
            /* 更新发货时间 */
            
            $shipping_status = 5;
            if ($order['order_status'] != 1 && $order['order_status'] != 5 && $order['order_status'] != OS_SPLITING_PART)
            {
                $arr['order_status']    = 1;
                $arr['confirm_time']    = GMTIME_UTC;
            }
            
            $arr['shipping_status']     = $shipping_status;
            update_order($order_id, $arr);
        }

        /* 记录log */
        order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note);

        /* 清除缓存 */
        clear_cache_files();

    /* 根据发货单id查询发货单信息 */
        if (!empty($delivery_id))
        {
            $delivery_order = delivery_order_info($delivery_id);
        }
        elseif (!empty($order_sn))
        {

            $delivery_id = $GLOBALS['db']->getOne("SELECT delivery_id FROM " . $GLOBALS['aos']->table('delivery_order') . " WHERE order_sn = " . $order_sn );
            $delivery_order = delivery_order_info($delivery_id);
        }
        else
        {
            die('order does not exist');
        }

    /* 如果管理员属于某个办事处，检查该订单是否也属于这个办事处 */
    $sql = "SELECT store_id FROM " . $GLOBALS['aos']->table('admin_user') . " WHERE user_id = '" . $_SESSION['admin_id'] . "'";
    $agency_id = $GLOBALS['db']->getOne($sql);
    if ($agency_id > 0)
    {
        if ($delivery_order['agency_id'] != $agency_id)
        {
            sys_msg('对不起,您没有执行此项操作的权限!');
        }

        /* 取当前办事处信息 */
        $sql = "SELECT store_name FROM " . $GLOBALS['aos']->table('store') . " WHERE store_id = '$agency_id' LIMIT 0, 1";
        $agency_name = $GLOBALS['db']->getOne($sql);
        $delivery_order['agency_name'] = $agency_name;
    }

    /* 取得用户名 */
    if ($delivery_order['user_id'] > 0)
    {
        $user = user_info($delivery_order['user_id']);
        if (!empty($user))
        {
            $delivery_order['user_name'] = $user['user_name'];
        }
    }

    if(!empty($order['area'])){
        $area = explode(',',$order['area']);
        $region['province_name'] = get_region_name($area['0']);
        $region['city_name'] = get_region_name($area['1']);
        $region['district_name'] = get_region_name($area['2']);
        $delivery_order['region'] = $region['province_name'].$region['city_name'].$region['district_name'];
    }
    /* 是否保价 */
    $order['insure_yn'] = empty($order['insure_fee']) ? 0 : 1;

    /* 取得发货单商品 */
    $goods_sql = "SELECT *
                  FROM " . $GLOBALS['aos']->table('delivery_goods') . "
                  WHERE delivery_id = " . $delivery_order['delivery_id'];
    $goods_list = $GLOBALS['db']->getAll($goods_sql);

   

    /* 取得订单操作记录 */
    $act_list = array();
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('order_action') . " WHERE order_id = '" . $delivery_order['order_id'] . "' AND action_place = 1 ORDER BY log_time DESC,action_id DESC";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['order_status']    = $_LANG['os'][$row['order_status']];
        $row['pay_status']      = $_LANG['ps'][$row['pay_status']];
        $row['shipping_status'] = ($row['shipping_status'] == 5) ? $_LANG['ss_admin'][5] : $_LANG['ss'][$row['shipping_status']];
        $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
        $act_list[] = $row;
    }

    /*同步发货*/
    /*判断支付方式是否支付宝*/
    $alipay    = false;
    $order     = order_info($delivery_order['order_id']);  //根据订单ID查询订单信息，返回数组$order
    $payment   = payment_info($order['pay_id']);           //取得支付方式信息

    /* 定义当前时间 */
    define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳

    /* 根据发货单id查询发货单信息 */
    if (!empty($delivery_id))
    {
        $delivery_order = delivery_order_info($delivery_id);
    }
    else
    {
        die('order does not exist');
    }

    /* 查询订单信息 */
    $order = order_info($order_id);

    /* 检查此单发货商品库存缺货情况 */
    $virtual_goods = array();
    $delivery_stock_sql = "SELECT G.commission,DG.goods_id, DG.is_real, DG.product_id, SUM(DG.send_number) AS sums, IF(DG.product_id > 0, P.product_number, G.goods_number) AS storage, G.goods_name, DG.send_number
        FROM " . $GLOBALS['aos']->table('delivery_goods') . " AS DG, " . $GLOBALS['aos']->table('goods') . " AS G, " . $GLOBALS['aos']->table('goods_attr') . " AS P
        WHERE DG.goods_id = G.goods_id
        AND DG.delivery_id = '$delivery_id'
        AND DG.product_id = P.attr_id
        GROUP BY DG.product_id ";

    $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);

    /* 如果商品存在规格就查询规格，如果不存在规格按商品库存查询 */
    if(!empty($delivery_stock_result))
    {
        foreach ($delivery_stock_result as $value)
        {
            if (($value['sums'] > $value['storage'] || $value['storage'] <= 0) && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == 0) || ($_CFG['use_storage'] == '0' && $value['is_real'] == 0)))
            {
                /* 操作失败 */
                $links[] = array('text' => '订单信息', 'href' => 'index.php?act=delivery&op=delivery_info&delivery_id=' . $delivery_id);
                sys_msg(sprintf('商品已缺货', $value['goods_name']), 1, $links);
                break;
            }

        }
    }
    else
    {
        $delivery_stock_sql = "SELECT G.commission,DG.goods_id, DG.is_real, SUM(DG.send_number) AS sums, G.goods_number, G.goods_name, DG.send_number
        FROM " . $GLOBALS['aos']->table('delivery_goods') . " AS DG, " . $GLOBALS['aos']->table('goods') . " AS G
        WHERE DG.goods_id = G.goods_id
        AND DG.delivery_id = '$delivery_id'
        GROUP BY DG.goods_id ";
        $delivery_stock_result = $GLOBALS['db']->getAll($delivery_stock_sql);
        foreach ($delivery_stock_result as $value)
        {
            if (($value['sums'] > $value['goods_number'] || $value['goods_number'] <= 0) && (($_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == 0) || ($_CFG['use_storage'] == '0' && $value['is_real'] == 0)))
            {
                /* 操作失败 */
                $links[] = array('text' => '订单信息', 'href' => 'index.php?act=delivery&op=delivery_info&delivery_id=' . $delivery_id);
                sys_msg(sprintf('商品已缺货', $value['goods_name']), 1, $links);
                break;
            }

           
        }
    }

    /* 发货 */


    /* 如果使用库存，且发货时减库存，则修改库存 */
  
    if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == 0)
    {
        
        foreach ($delivery_stock_result as $value)
        {

            /* 商品 */
          
                if (!empty($value['product_id']))
                {
                    $minus_stock_sql = "UPDATE " . $GLOBALS['aos']->table('goods_attr') . "
                                        SET product_number = product_number - " . $value['sums'] . "
                                        WHERE attr_id = " . $value['product_id'];
                    $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
                }

                $minus_stock_sql = "UPDATE " . $GLOBALS['aos']->table('goods') . "
                                    SET goods_number = goods_number - " . $value['sums'] . "
                                    WHERE goods_id = " . $value['goods_id'];

                $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
            
        }
    }
    $sql="select count(*) from ".$GLOBALS['aos']->table('order_action')." where order_id =".$order['order_id']." and shipping_status = 1";
    $cou=$GLOBALS['db']->getOne($sql);
    $order[goods_name]=$delivery_stock_result[0][goods_name];
    if($cou<1){
        send_order_bonus($order['order_id']);
        $integral = integral_to_give($order);

        log_account_change($order['user_id'], 0, 0, intval($integral), intval($integral), sprintf("下单 %s 时赠送积分", $order['order_sn']));
       $arr['integral']=$integral;
        $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$order['order_id'];
        $refund_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
        global $admin_wechat;
        $openid=getOpenid($order['user_id']);
        $wx_title = "发货通知";
        $message=getMessage(11);
        $wx_desc = $message[title]."\r\n发货商品：".$order[goods_name]."\r\n发货时间：".$refund_time."\r\n".$message[note];
        //$wx_pic = $aos_url;
        $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        //团长佣金
        if($order['tuan_first']==1 && $order['extension_code']=='tuan' && $delivery_stock_result[0][commission]>0){
       
            $r= refunds($order,$delivery_stock_result[0][commission],'refund');
            
            if($r=='wei_true'){
                
                
                $refund_price='￥'.$delivery_stock_result[0][commission];
                
                $message=getMessage(18);
                $wx_title=$message['title'];
                $wx_desc = "佣金商品：".$order[goods_name]."\r\n佣金金额：".$refund_price."\r\n发放时间：".$refund_time."\r\n".$message['note'];
                //$wx_pic = $aos_url;
                $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '团长佣金，微信已发', '');
            }elseif($r=='ali_true'){
                order_action($order['order_sn'], $order['order_status'], 1, $order['pay_status'], '团长佣金，支付宝未发', '');
            }
        }
    }
    /* 修改发货单信息 */
    $invoice_no = trim($invoice_no);
    $_delivery['invoice_no'] = $invoice_no;
    $_delivery['status'] = 0; // 0，为已发货
    $query = $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('delivery_order'), $_delivery, 'UPDATE', "delivery_id = $delivery_id", 'SILENT');
    if (!$query)
    {
        /* 操作失败 */
        $links[] = array('text' => '查看发货单', 'href' => 'index.php?act=delivery&op=delivery_info&delivery_id=' . $delivery_id);
        sys_msg('操作失败', 1, $links);
    }

    /* 标记订单为已确认 “已发货” */
    /* 更新发货时间 */
    
    $shipping_status = 1;
    $arr['shipping_status']     = $shipping_status;
    $arr['order_status']     = 5;
    $arr['shipping_time']       = GMTIME_UTC; // 发货时间
    $arr['shipping_name']       = $shipping_row['shipping_name'];
    $arr['shipping_id']       = $shipping_row['shipping_id'];
    $arr['invoice_no']          = trim($order['invoice_no'] . '<br>' . $invoice_no, '<br>');
    update_order($order_id, $arr);

    /* 发货单发货记录log */
    order_action($order['order_sn'], 1,$arr['order_status'], $shipping_status, $order['pay_status'], $action_note, null, 1);
  
    /* 清除缓存 */
    clear_cache_files();

    }
}

/**
 * 取得发货单信息
 * @param   int     $delivery_order   发货单id（如果delivery_order > 0 就按id查，否则按sn查）
 * @param   string  $delivery_sn      发货单号
 * @return  array   发货单信息（金额都有相应格式化的字段，前缀是formated_）
 */
function delivery_order_info($delivery_id, $delivery_sn = '')
{
    $return_order = array();
    if (empty($delivery_id) || !is_numeric($delivery_id))
    {
        return $return_order;
    }

    $where = '';
    /* 获取管理员信息 */
    $admin_info = admin_info();

    /* 如果管理员属于某个门店，只列出这个门店管辖的发货单 */
    if ($admin_info['store_id'] > 0)
    {
        $where .= " AND store_id = '" . $admin_info['store_id'] . "' ";
    }

    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('delivery_order');
    if ($delivery_id > 0)
    {
        $sql .= " WHERE delivery_id = '$delivery_id'";
    }
    else
    {
        $sql .= " WHERE delivery_sn = '$delivery_sn'";
    }

    $sql .= $where;
    $sql .= " LIMIT 0, 1";
    $delivery = $GLOBALS['db']->getRow($sql);
    if ($delivery)
    {
        /* 格式化金额字段 */
        $delivery['formated_insure_fee']     = price_format($delivery['insure_fee'], false);
        $delivery['formated_shipping_fee']   = price_format($delivery['shipping_fee'], false);

        /* 格式化时间字段 */
        $delivery['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $delivery['add_time']);
        $delivery['formated_update_time']    = local_date($GLOBALS['_CFG']['time_format'], $delivery['update_time']);

        $return_order = $delivery;
    }

    return $return_order;
}
?>