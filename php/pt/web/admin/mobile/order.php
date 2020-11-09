<?php

define('IN_AOS', true);

require_once(ROOT_PATH . 'source/library/order.php');
require_once(ROOT_PATH . 'source/library/goods.php');
admin_priv('order_manage');
/*------------------------------------------------------ */
//-- 订单列表
/*------------------------------------------------------ */

if ($operation == 'order_list')
{
    /* 检查权限 */
    admin_priv('order_manage');


    /* 载入配送方式 */
    $smarty->assign('shipping_list', shipping_list());

    /* 载入支付方式 */
    $smarty->assign('pay_list', payment_list());

    /* 载入国家 */
    $smarty->assign('country_list', get_regions());

    $smarty->assign('os_unconfirmed',   0);
    $smarty->assign('cs_await_pay',     1);
    $smarty->assign('cs_await_ship',    3);
    $smarty->assign('full_page',        1);

    $order_list = order_list();

    $smarty->assign('order_list',   $order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);

    /* 分页 */

    $pager = get_page($order_list['filter']);

    $smarty->assign('pager',   $pager);

    /* 显示模板 */
    
    $smarty->display('order_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    /* 检查权限 */
    admin_priv('order_manage');
    $order_list = order_list();

    $smarty->assign('order_list',   $order_list['orders']);
    $smarty->assign('filter',       $order_list['filter']);
    $smarty->assign('record_count', $order_list['record_count']);
    $smarty->assign('page_count',   $order_list['page_count']);
    $sort_flag  = sort_flag($order_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('order_list.htm'), '', array('filter' => $order_list['filter'], 'page_count' => $order_list['page_count']));
}
elseif($operation == 'refund'){
    $order_id=intval($_REQUEST['order_id']);
    $order = order_info($order_id);
    $ex_array=array('tuan','lottery','miao');
    if($order['extension_code'] == 'assist'){
        sys_msg('助力订单不能退款');
    }
    if($order['pay_status'] != 2){
        sys_msg('该订单还未支付');
    }
    if(!empty($order)){
        if($order['order_status'] == 4){
            sys_msg('不能重复退款');
        }
        if(in_array($order['extension_code'],$ex_array)){
            if($order['tuan_status']==4){
                sys_msg('不能重复退款');
            }
        }
    }else{
        sys_msg('错误操作');
    }
    $order[refund_money_1]=$order['money_paid']+$order['surplus']-$order['shipping_fee'];
    $smarty->assign('back_id', $order_id);
    $smarty->assign('refund', $order);
    $smarty->assign('act', 'order');
    $smarty->assign('op', 'do_refund');
    //assign_query_info();
    $smarty->display('back_refund.htm');
}
elseif($operation == 'do_refund'){
     /* 检查权限 */ 
    $status_refund = '1';
    $order_id   = intval(trim($_REQUEST['back_id']));        // 退换货订单id
    $action_note    = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
    $refund_money_2 = $_REQUEST['refund_money_2'] + $_REQUEST['refund_shipping_fee'];
    $refund_desc = $_REQUEST['refund_desc'] . ($_REQUEST['refund_shipping'] ? '\n（已退运费：'. $_REQUEST['refund_shipping_fee']. '）' : '');
    $sql="select order_id,order_status,pay_status,extension_code,tuan_status from ".$aos->table('order_info')." where order_id = $order_id";
    $check_order=$db->getRow($sql);
    $ex_array=array('tuan','lottery','miao');
    if($check_order['extension_code'] == 'assist'){
        sys_msg('助力订单不能退款');
    }
    if($check_order['pay_status'] != 2){
        sys_msg('该订单还未支付');
    }
    if(!empty($check_order)){
        if($check_order['order_status'] == 4){
            sys_msg('不能重复退款');
        }
        if(in_array($check_order['extension_code'],$ex_array)){
            if($check_order['tuan_status']==4){
                sys_msg('不能重复退款');
            }
        }
    }else{
        sys_msg('错误操作');
    }

    if ($_REQUEST['refund_type'] == '2')
    {
        $desc_back = "订单". $order['order_id'] .'退款';
        $sql="select o.extension_code,g.goods_name,g.goods_id,o.user_id,o.surplus,o.pay_status,o.shipping_status,o.shipping_fee,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.act_id,o.bonus_id,o.integral from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id  where  o.order_id=".$order_id;
        $order_info= $GLOBALS['db']->getRow($sql);
        $refund_money_1=$order_info['money_paid']+$order_info['surplus'];
        if(bccomp($refund_money_2, $refund_money_1, 2)==1){
            sys_msg("退款金额大于最大可退款金额");
        }
        $r= refunds($order_info,$refund_money_2,'refund');
        $array=array();
        $array['order_status']='4';
        if(!empty($order_info['extension_code'])){
            $array['tuan_status']='4';
        }
        update_order($order_id, $array);
        return_user_surplus_integral_bonus($order_info);
        return_order_bonus($order_id);
        if($r=='wei_true'){
            order_action($order['order_sn'], 4, 0, 0, '微信自动退款成功'.$action_note);
            //退款通知
            global $admin_wechat;
            $refund_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
            $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$order_info['order_id'];
            $refund_price='￥'.$refund_money_2;
            $openid=getOpenid($order_info['user_id']);
            $message=getMessage(6);
            $wx_title = "退款成功通知";
            $wx_desc = $message[title]."\r\n退款商品：".$order_info[goods_name]."\r\n退款金额：".$refund_price."\r\n退款时间：".$refund_time."\r\n".$message[note];
            //$wx_pic = $aos_url;
            $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        }elseif($r=='ali_true'){
                /* 记录log */
            order_action($order_info['order_sn'], 4, 0, 0, '支付宝支付，未退款'.$action_note);
        }
        if ($GLOBALS['_CFG']['use_storage'] == '1'){
            change_order_goods_storage($order_info['order_id'], false, 1);
        }
        
    }
    /* 记录log */
    $links[] = array('text' => '订单详情', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
    sys_msg('恭喜，成功操作！', 1, $links);
}
/*------------------------------------------------------ */
//-- 订单详情页面
/*------------------------------------------------------ */

elseif ($operation == 'info')
{
    if (isset($_REQUEST['order_id']))
    {
        $order_id = intval($_REQUEST['order_id']);
        $order = order_info($order_id);
    }
    elseif (isset($_REQUEST['order_sn']))
    {
        $order_sn = trim($_REQUEST['order_sn']);
        $order = order_info(0, $order_sn);
    }
    else
    {
        /* 如果参数不存在，退出 */
        die('invalid parameter');
    }

    /* 如果订单不存在，退出 */
    if (empty($order))
    {
        die('order does not exist');
    }

    /* 根据订单是否完成检查权限 */
    if (order_finished($order))
    {
        //admin_priv('order_view_finished');
    }
    else
    {
        admin_priv('order_manage');
    }


    /* 取得用户名 */
    if ($order['user_id'] > 0)
    {
        $user = user_info($order['user_id']);
        if (!empty($user))
        {
            $order['nickname'] = $user['nickname'];
        }
    }

    /* 取得所有门店 */
    $sql = "SELECT store_id, store_name FROM " . $aos->table('store');
    $smarty->assign('store_list', $GLOBALS['db']->getAll($sql));

    /* 取得所有配送方式 */
    $sql = "SELECT shipping_id, shipping_name FROM " . $aos->table('shipping')." where shipping_code not in ('pickup','express') order by shipping_id desc";
    $smarty->assign('shipping_list', $GLOBALS['db']->getAll($sql));

    $area = explode(',',$order['area']);
    $region['province_name'] = get_region_name($area['0']);
    $region['city_name'] = get_region_name($area['1']);
    $region['district_name'] = get_region_name($area['2']);
    $order['region'] = $region['province_name'].$region['city_name'].$region['district_name'];

    /* 格式化金额 */
    if ($order['order_amount'] < 0)
    {
        $order['money_refund']          = abs($order['order_amount']);
        $order['formated_money_refund'] = price_format(abs($order['order_amount']));
    }
    
    /* 其他处理 */
    $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
    $order['pay_time']      = $order['pay_time'] > 0 ?
        local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][0];
    $order['shipping_time'] = $order['shipping_time'] > 0 ?
        local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][0];
    $order['status']        = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
    $order['invoice_no']    = $order['shipping_status'] == 0 || $order['shipping_status'] == 3 ? $_LANG['ss'][0] : $order['invoice_no'];

    /* 此订单的发货备注(此订单的最后一条操作记录) */
    $sql = "SELECT action_note FROM " . $aos->table('order_action').
           " WHERE order_id = '$order[order_id]' AND shipping_status = 1 ORDER BY log_time DESC";
    $order['invoice_note'] = $GLOBALS['db']->getOne($sql);

    /* 取得订单商品总重量 */
    $weight_price = order_weight_price($order['order_id']);
    $order['total_weight'] = $weight_price['formated_weight'];

    /* 参数赋值：订单 */
    $smarty->assign('order', $order);

    /* 取得用户信息 */
    if ($order['user_id'] > 0)
    {
        // 用户优惠券数量
        $day    = getdate();
        $today  = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
        $sql = "SELECT COUNT(*) " .
                "FROM " . $aos->table('bonus_type') . " AS bt, " . $aos->table('user_bonus') . " AS ub " .
                "WHERE bt.type_id = ub.bonus_type_id " .
                "AND ub.user_id = '$order[user_id]' " .
                "AND ub.order_id = 0 " .
                "AND bt.use_start_date <= '$today' " .
                "AND bt.use_end_date >= '$today'";
        $user['bonus_count'] = $GLOBALS['db']->getOne($sql);
        $smarty->assign('user', $user);

        // 地址信息
        $sql = "SELECT * FROM " . $aos->table('user_address') . " WHERE user_id = '$order[user_id]'";
        $smarty->assign('address_list', $GLOBALS['db']->getAll($sql));
    }

    /* 取得订单商品及货品 */
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, g.*
            FROM " . $aos->table('order_goods') . " AS o
                LEFT JOIN " . $aos->table('goods_attr') . " AS g
                    ON o.attr_id = g.attr_id
            WHERE o.order_id = '$order[order_id]'";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);

        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
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

    $smarty->assign('goods_attr', $attr);
    $smarty->assign('goods_list', $goods_list);

    /* 取得能执行的操作列表 */
    $operable_list = operable_list($order);
    //print_r($operable_list);
    $smarty->assign('operable_list', $operable_list);

    /* 取得订单操作记录 */
    $act_list = array();
    $sql = "SELECT * FROM " . $aos->table('order_action') . " WHERE order_id = '$order[order_id]' ORDER BY log_time DESC,action_id DESC";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $row['order_status']    = $_LANG['os'][$row['order_status']];
        $row['pay_status']      = $_LANG['ps'][$row['pay_status']];
        $row['shipping_status'] = $_LANG['ss'][$row['shipping_status']];
        $row['action_time']     = local_date($_CFG['time_format'], $row['log_time']);
        $act_list[] = $row;
    }
    $smarty->assign('action_list', $act_list);


    /* 是否打印订单，分别赋值 */
    if (isset($_GET['print']))
    {
        $smarty->assign('shop_name',    $_CFG['shop_name']);
        $smarty->assign('shop_url',     $aos->url());
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $smarty->assign('print_time',   local_date($_CFG['time_format']));
        $smarty->assign('action_user',  $_SESSION['admin_name']);

        $smarty->template_dir = '../data';
        $smarty->display('order_print.html');
    }
    else
    {
        /* 模板赋值 */
        $smarty->assign('ur_here', '订单信息');
        $smarty->assign('action_link', array('href' => 'index.php?act=order&op=list&' . list_link_postfix(), 'text' => '订单列表'));

        /* 显示模板 */
        
        $smarty->display('order_info.htm');
    }
}



/*------------------------------------------------------ */
//-- 处理
/*------------------------------------------------------ */

elseif ($operation == 'process')
{
    /* 取得参数 func */
    $func = isset($_GET['func']) ? $_GET['func'] : '';

    /* 删除订单商品 */
    if ('drop_order_goods' == $func)
    {
        /* 检查权限 */
        //admin_priv('order_edit');

        /* 取得参数 */
        $rec_id = intval($_GET['rec_id']);
        $step_act = $_GET['step_act'];
        $order_id = intval($_GET['order_id']);

        /* 如果使用库存，且下订单时减库存，则修改库存 */
        if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == 1)
        {
             $goods = $db->getRow("SELECT goods_id, goods_number FROM " . $aos->table('order_goods') . " WHERE rec_id = " . $rec_id );
             $sql = "UPDATE " . $aos->table('goods') .
                    " SET `goods_number` = goods_number + '" . $goods['goods_number'] . "' " .
                    " WHERE `goods_id` = '" . $goods['goods_id'] . "' LIMIT 1";
             $db->query($sql);
        }

        /* 删除 */
        $sql = "DELETE FROM " . $aos->table('order_goods') .
                " WHERE rec_id = '$rec_id' LIMIT 1";
        $db->query($sql);

        /* 更新商品总金额和订单总金额 */
        update_order($order_id, array('goods_amount' => order_amount($order_id)));
        update_order_amount($order_id);

        /* 跳回订单商品 */
        aos_header("Location: index.php?act=order&op=" . $step_act . "&order_id=" . $order_id . "&step=goods\n");
        exit;
    }

    /* 取消刚添加或编辑的订单 */
    elseif ('cancel_order' == $func)
    {
        $step_act = $_GET['step_act'];
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if ($step_act == 'add')
        {
            /* 如果是添加，删除订单，返回订单列表 */
            if ($order_id > 0)
            {
                $sql = "DELETE FROM " . $aos->table('order_info') .
                        " WHERE order_id = '$order_id' LIMIT 1";
                $db->query($sql);
            }
            aos_header("Location: index.php?act=order&op=list\n");
            exit;
        }
        else
        {
            /* 如果是编辑，返回订单信息 */
            aos_header("Location: index.php?act=order&op=info&order_id=" . $order_id . "\n");
            exit;
        }
    }

    /* 编辑订单时由于订单已付款且金额减少而退款 */
    elseif ('refund' == $func)
    {
        /* 处理退款 */
        $order_id       = $_REQUEST['order_id'];
        $refund_type    = $_REQUEST['refund'];
        $refund_note    = $_REQUEST['refund_note'];
        $refund_amount  = $_REQUEST['refund_amount'];
        $order          = order_info($order_id);
        order_refund($order, $refund_type, $refund_note, $refund_amount);

        /* 修改应付款金额为0，已付款金额减少 $refund_amount */
        update_order($order_id, array('order_amount' => 0, 'money_paid' => $order['money_paid'] - $refund_amount));

        /* 返回订单详情 */
        aos_header("Location: index.php?act=order&op=info&order_id=" . $order_id . "\n");
        exit;
    }

    /* 载入退款页面 */
    elseif ('load_refund' == $func)
    {
        $refund_amount = floatval($_REQUEST['refund_amount']);
        $smarty->assign('refund_amount', $refund_amount);
        $smarty->assign('formated_refund_amount', price_format($refund_amount));

        $anonymous = $_REQUEST['anonymous'];
        $smarty->assign('anonymous', $anonymous); // 是否匿名

        $order_id = intval($_REQUEST['order_id']);
        $smarty->assign('order_id', $order_id); // 订单id

        /* 显示模板 */
        
        $smarty->display('order_refund.htm');
    }

    else
    {
        die('invalid params');
    }
}

/*------------------------------------------------------ */
//-- 操作订单状态（载入页面）
/*------------------------------------------------------ */

elseif ($operation == 'operate')
{
    $order_id = '';
    /* 检查权限 */
    //admin_priv('order_os_edit');

    /* 取得订单id（可能是多个，多个sn）和操作备注（可能没有） */
    if(isset($_REQUEST['order_id']))
    {
        $order_id= $_REQUEST['order_id'];
    }
    $batch          = isset($_REQUEST['batch']); // 是否批处理
    $action_note    = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

    /* 确认 */
    if (isset($_POST['confirm']))
    {
        $require_note   = false;
        $action         = '确认';
        $operation      = 'confirm';
    }
    /* 付款 */
    elseif (isset($_POST['pay']))
    {
        /* 检查权限 */
        //admin_priv('order_ps_edit');
        $require_note   = $_CFG['order_pay_note'] == 1;
        $action         = '付款';
        $operation      = 'pay';
    }
    /* 未付款 */
    elseif (isset($_POST['unpay']))
    {
        /* 检查权限 */
        //admin_priv('order_ps_edit');

        $require_note   = $_CFG['order_unpay_note'] == 1;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
        $action         = '设为未付款';
        $operation      = 'unpay';
    }
    elseif (isset($_POST['to_shipping']))
    {
        $require_note   = false;
        $action         = "一键发货";
        $operation      = 'to_shipping';
        
        send_key_shipping();exit;
    }
    /* 配货 */
    elseif (isset($_POST['prepare']))
    {
        $require_note   = false;
        $action         = '配货';
        $operation      = 'prepare';
    }
    /* 分单 */
    elseif (isset($_POST['ship']))
    {
        /* 查询：检查权限 */
        //admin_priv('order_ss_edit');

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

        /* 查询：根据订单是否完成 检查权限 */
        if (order_finished($order))
        {
            //admin_priv('order_view_finished');
        }
        else
        {
            admin_priv('order_manage');
        }

        /* 查询：如果管理员属于某个门店，检查该订单是否也属于这个门店 */
        $sql = "SELECT store_id FROM " . $aos->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
        $store_id = $db->getOne($sql);
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
                $order['nickname'] = $user['nickname'];
            }
        }

        /* 查询：取得区域名 
        $sql = "SELECT concat(IFNULL(c.region_name, ''), '  ', IFNULL(p.region_name, ''), " .
                    "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
                "FROM " . $aos->table('order_info') . " AS o " .
                    "LEFT JOIN " . $aos->table('region') . " AS c ON o.country = c.region_id " .
                    "LEFT JOIN " . $aos->table('region') . " AS p ON o.province = p.region_id " .
                    "LEFT JOIN " . $aos->table('region') . " AS t ON o.city = t.region_id " .
                    "LEFT JOIN " . $aos->table('region') . " AS d ON o.district = d.region_id " .
                "WHERE o.order_id = '$order[order_id]'";
        $order['region'] = $db->getOne($sql);
        */

        /* 查询：其他处理 */
        $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
        $order['invoice_no']    = $order['shipping_status'] == 0 || $order['shipping_status'] == 3 ? $_LANG['ss'][0] : $order['invoice_no'];

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

                $goods_list[$key]['sended'] = $goods_value['send_number'];
                $goods_list[$key]['send'] = $goods_value['goods_number'] - $goods_value['send_number'];

                $goods_list[$key]['readonly'] = '';
                /* 是否缺货 */
                if ($goods_value['storage'] <= 0 && $_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == 0)
                {
                    $goods_list[$key]['send'] = '商品已缺货';
                    $goods_list[$key]['readonly'] = 'readonly="readonly"';
                }
                elseif ($goods_list[$key]['send'] <= 0)
                {
                    $goods_list[$key]['send'] = '商品已缺货';
                    $goods_list[$key]['readonly'] = 'readonly="readonly"';
                }

            }
        }
        $area = explode(',',$order['area']);
        $region['province_name'] = get_region_name($area['0']);
        $region['city_name'] = get_region_name($area['1']);
        $region['district_name'] = get_region_name($area['2']);
        $order['region'] = $region['province_name'].$region['city_name'].$region['district_name'];

        /* 取得所有配送方式 */
        $sql = "SELECT shipping_id, shipping_name FROM " . $aos->table('shipping')." where shipping_code not in ('pickup','express') order by shipping_id desc";
        $smarty->assign('shipping_list', $GLOBALS['db']->getAll($sql));
        /* 模板赋值 */
        $smarty->assign('order', $order);
        $smarty->assign('goods_attr', $attr);
        $smarty->assign('goods_list', $goods_list);
        $smarty->assign('order_id', $order_id); // 订单id
        $smarty->assign('operation', 'split'); // 订单id
        $smarty->assign('action_note', $action_note); // 发货操作信息


        /* 显示模板 */
        $smarty->assign('ur_here', '订单操作：' . '生成发货单');
        
        $smarty->display('order_delivery_info.htm');
        exit;
    }
    /* 未发货 */
    elseif (isset($_POST['unship']))
    {
        /* 检查权限 */
        //admin_priv('order_ss_edit');

        $require_note   = $_CFG['order_unship_note'] == 1;
        $action         = '生成发货单';
        $operation      = 'unship';
    }
    /* 收货确认 */
    elseif (isset($_POST['receive']))
    {
        $require_note   = $_CFG['order_receive_note'] == 1;
        $action         = "已收货";
        $operation      = 'receive';
    }
    /* 取消 */
    elseif (isset($_POST['cancel']))
    {
        $require_note   = $_CFG['order_cancel_note'] == 1;
        $action         = '取消';
        $operation      = 'cancel';
        $show_cancel_note   = true;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
    }
    /* 无效 */
    elseif (isset($_POST['invalid']))
    {
        $require_note   = $_CFG['order_invalid_note'] == 1;
        $action         = '无效';
        $operation      = 'invalid';
    }
    /* 售后 */
    elseif (isset($_POST['after_service']))
    {
        $require_note   = true;
        $action         = '售后';
        $operation      = 'after_service';
    }
    /* 退货 */
    elseif (isset($_POST['return']))
    {
        $require_note   = $_CFG['order_return_note'] == 1;
        $order          = order_info($order_id);
        if ($order['money_paid'] > 0)
        {
            $show_refund = true;
        }
        $anonymous      = $order['user_id'] == 0;
        $action         = '退货';
        $operation      = 'return';

    }
    /* 指派 */
    elseif (isset($_POST['assign']))
    {
        /* 取得参数 */
        $new_store_id  = isset($_POST['store_id']) ? intval($_POST['store_id']) : 0;
        if ($new_store_id == 0)
        {
            sys_msg('请选择门店');
        }

        /* 查询订单信息 */
        $order = order_info($order_id);

        /* 如果管理员属于某个门店，检查该订单是否也属于这个门店 */
        $sql = "SELECT store_id FROM " . $aos->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
        $admin_store_id = $GLOBALS['db']->getOne($sql);
        if ($admin_store_id > 0)
        {
            if ($order['store_id'] != $admin_store_id)
            {
                sys_msg('对不起,您没有执行此项操作的权限!');
            }
        }

        /* 修改订单相关所属的门店 */
        if ($new_store_id != $order['store_id'])
        {
            $query_array = array('order_info', // 更改订单表的供货商ID
                                 'delivery_order', // 更改订单的发货单供货商ID
                                 'back_order'// 更改订单的退货单供货商ID
            );
            foreach ($query_array as $value)
            {
                $GLOBALS['db']->query("UPDATE " . $aos->table($value) . " SET store_id = '$new_store_id' " .
                    "WHERE order_id = '$order_id'");

            }
        }

        /* 操作成功 */
        $links[] = array('href' => 'index.php?act=order&op=list&' . list_link_postfix(), 'text' => '订单列表');
        sys_msg('操作成功', 0, $links);
    }
    /* 订单删除 */
    elseif (isset($_POST['remove']))
    {
        $require_note = false;
        $operation = 'remove';
        if (!$batch)
        {
            /* 检查能否操作 */
            $order = order_info($order_id);
            $operable_list = operable_list($order);
            if (!isset($operable_list['remove']))
            {
                die('Hacking attempt');
            }

            /* 删除订单 */
            $GLOBALS['db']->query("DELETE FROM ".$aos->table('order_info'). " WHERE order_id = '$order_id'");
            $GLOBALS['db']->query("DELETE FROM ".$aos->table('order_goods'). " WHERE order_id = '$order_id'");
            $GLOBALS['db']->query("DELETE FROM ".$aos->table('order_action'). " WHERE order_id = '$order_id'");
            $action_array = array('delivery', 'back');
            del_delivery($order_id, $action_array);

            /* todo 记录日志 */
            admin_log($order['order_sn'], 'remove', 'order');

            /* 返回 */
            sys_msg('订单删除成功。', 0, array(array('href'=>'index.php?act=order&op=list&' . list_link_postfix(), 'text' => '返回订单列表')));
        }
    }
    /* 发货单删除 */
    elseif (isset($_REQUEST['remove_invoice']))
    {
        // 删除发货单
        $delivery_id=$_REQUEST['delivery_id'];
        $delivery_id = is_array($delivery_id) ? $delivery_id : array($delivery_id);

        foreach($delivery_id as $value_is)
        {
            $value_is = intval(trim($value_is));

            // 查询：发货单信息
            $delivery_order = delivery_order_info($value_is);

            // 如果status不是退货
            if ($delivery_order['status'] != 1)
            {
                /* 处理退货 */
                delivery_return_goods($value_is, $delivery_order);
            }

            // 如果status是已发货并且发货单号不为空
            if ($delivery_order['status'] == 0 && $delivery_order['invoice_no'] != '')
            {
                /* 更新：删除订单中的发货单号 */
                del_order_invoice_no($delivery_order['order_id'], $delivery_order['invoice_no']);
            }

            // 更新：删除发货单
            $sql = "DELETE FROM ".$aos->table('delivery_order'). " WHERE delivery_id = '$value_is'";
            $GLOBALS['db']->query($sql);
        }

        /* 返回 */
        sys_msg('发货单删除成', 0, array(array('href'=>'index.php?act=delivery&op=delivery_list' , 'text' => '返回订单列表')));
    }
     /* 退货单删除 */
    elseif (isset($_REQUEST['remove_back']))
    {
        $back_id = $_REQUEST['back_id'];
        /* 删除退货单 */
        if(is_array($back_id))
        {
        foreach ($back_id as $value_is)
            {
                $sql = "DELETE FROM ".$aos->table('back_order'). " WHERE back_id = '$value_is'";
                $GLOBALS['db']->query($sql);
            }
        }
        else
        {
            $sql = "DELETE FROM ".$aos->table('back_order'). " WHERE back_id = '$back_id'";
            $GLOBALS['db']->query($sql);
        }
        /* 返回 */
        sys_msg('退货单删除成功！', 0, array(array('href'=>'index.php?act=order&op=back_list' , 'text' => '返回订单列表')));
    }
    /* 批量打印订单 */
    elseif (isset($_POST['print']))
    {
        if (empty($_POST['order_id']))
        {
            sys_msg('请选择订单');
        }

        /* 赋值公用信息 */
        $smarty->assign('shop_name',    $_CFG['shop_name']);
        $smarty->assign('shop_url',     $aos->url());
        $smarty->assign('shop_address', $_CFG['shop_address']);
        $smarty->assign('service_phone',$_CFG['service_phone']);
        $smarty->assign('print_time',   local_date($_CFG['time_format']));
        $smarty->assign('action_user',  $_SESSION['admin_name']);

        $html = '';
        if(!is_array($_POST['order_id'])){
            $order_sn_list = explode(',', $_POST['order_id']);
        }else{
            $order_sn_list = $_POST['order_id'];
        }
        
        foreach ($order_sn_list as $order_sn)
        {
            /* 取得订单信息 */
            $order = order_info(0, $order_sn);
            if (empty($order))
            {
                continue;
            }

            /* 根据订单是否完成检查权限 */
            if (order_finished($order))
            {
                /*if (!admin_priv('order_view_finished', '', false))
                {
                    continue;
                }*/
            }
            else
            {
                if (!admin_priv('order_manage', '', false))
                {
                    continue;
                }
            }

            /* 如果管理员属于某个门店，检查该订单是否也属于这个门店 */
            $sql = "SELECT store_id FROM " . $aos->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
            $store_id = $GLOBALS['db']->getOne($sql);
            if ($store_id > 0)
            {
                if ($order['store_id'] != $store_id)
                {
                    continue;
                }
            }

            /* 取得用户名 */
            if ($order['user_id'] > 0)
            {
                $user = user_info($order['user_id']);
                if (!empty($user))
                {
                    $order['nickname'] = $user['nickname'];
                }
            }

            

            /* 其他处理 */
            $order['order_time']    = local_date($_CFG['time_format'], $order['add_time']);
            $order['pay_time']      = $order['pay_time'] > 0 ?
                local_date($_CFG['time_format'], $order['pay_time']) : $_LANG['ps'][0];
            $order['shipping_time'] = $order['shipping_time'] > 0 ?
                local_date($_CFG['time_format'], $order['shipping_time']) : $_LANG['ss'][0];
            $order['status']        = $_LANG['os'][$order['order_status']] . ',' . $_LANG['ps'][$order['pay_status']] . ',' . $_LANG['ss'][$order['shipping_status']];
            $order['invoice_no']    = $order['shipping_status'] == 0 || $order['shipping_status'] == 3 ? $_LANG['ss'][0] : $order['invoice_no'];

            /* 此订单的发货备注(此订单的最后一条操作记录) */
            $sql = "SELECT action_note FROM " . $aos->table('order_action').
                   " WHERE order_id = '$order[order_id]' AND shipping_status = 1 ORDER BY log_time DESC";
            $order['invoice_note'] = $GLOBALS['db']->getOne($sql);

            /* 参数赋值：订单 */
            $smarty->assign('order', $order);

            /* 取得订单商品 */
            $goods_list = array();
            $goods_attr = array();
            $sql = "SELECT o.*, g.goods_number AS storage, o.goods_attr " .
                    "FROM " . $aos->table('order_goods') . " AS o ".
                    "LEFT JOIN " . $aos->table('goods') . " AS g ON o.goods_id = g.goods_id " .
                    "WHERE o.order_id = '$order[order_id]' ";
            $res = $GLOBALS['db']->query($sql);
            while ($row = $GLOBALS['db']->fetchRow($res))
            {
                $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
                $row['formated_goods_price']    = price_format($row['goods_price']);

                $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
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

            $smarty->assign('goods_attr', $attr);
            $smarty->assign('goods_list', $goods_list);

            $smarty->template_dir = ROOT_PATH . 'data';
            $html .= $smarty->fetch('order_print.html') .
                '<div style="PAGE-BREAK-AFTER:always"></div>';
        }

        echo $html;
        exit;
    }
    /* 去发货 */
    elseif (isset($_POST['to_delivery']))
    {
        $url = 'index.php?act=delivery&op=delivery_list&order_sn='.$_REQUEST['order_sn'];

        aos_header("Location: $url\n");
        exit;
    }

    /* 直接处理还是跳到详细页面 */
    if (($require_note && $action_note == '') || isset($show_invoice_no) || isset($show_refund))
    {

        /* 模板赋值 */
        $smarty->assign('require_note', $require_note); // 是否要求填写备注
        $smarty->assign('action_note', $action_note);   // 备注
        $smarty->assign('show_cancel_note', isset($show_cancel_note)); // 是否显示取消原因
        $smarty->assign('show_invoice_no', isset($show_invoice_no)); // 是否显示发货单号
        $smarty->assign('show_refund', isset($show_refund)); // 是否显示退款
        $smarty->assign('order_id', $order_id); // 订单id
        $smarty->assign('batch', $batch);   // 是否批处理
        $smarty->assign('operation', $operation); // 操作

        /* 显示模板 */
        $smarty->assign('ur_here', '订单操作：' . $action);
        
        $smarty->display('new_order_operate.htm');
    }
    else
    {
        /* 直接处理 */
        if (!$batch)
        {
            /* 一个订单 */
            aos_header("Location: index.php?act=order&op=operate_post&order_id=" . $order_id .
                    "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
            exit;
        }
        else
        {
            /* 多个订单 */
            if(is_array($_POST['order_id'])){
                $order_id =implode(',', $_POST['order_id']);
            }else{
                $order_id = $_POST['order_id'];
            }
            aos_header("Location: index.php?act=order&op=batch_operate_post&order_id=" . $order_id .
                    "&operation=" . $operation . "&action_note=" . urlencode($action_note) . "\n");
            exit;
        }
    }
}

/*------------------------------------------------------ */
//-- 操作订单状态（处理批量提交）
/*------------------------------------------------------ */

elseif ($operation == 'batch_operate_post')
{
    /* 检查权限 */
    //admin_priv('order_os_edit');

    /* 取得参数 */
    $order_id   = $_REQUEST['order_id'];        // 订单id（逗号格开的多个订单id）
    $operation  = $_REQUEST['operation'];       // 订单操作
    $action_note= $_REQUEST['action_note'];     // 操作备注

    $order_id_list = explode(',', $order_id);

    /* 初始化处理的订单sn */
    $sn_list = array();
    $sn_not_list = array();

    /* 确认 */
    if ('confirm' == $operation)
    {
        foreach($order_id_list as $id_order)
        {
            $sql = "SELECT * FROM " . $aos->table('order_info') .
                " WHERE order_sn = '$id_order'" .
                " AND order_status = '" . 0 . "'";
            $order = $GLOBALS['db']->getRow($sql);

            if($order)
            {
                 /* 检查能否操作 */
                $operable_list = operable_list($order);
                if (!isset($operable_list[$operation]))
                {
                    $sn_not_list[] = $id_order;
                    continue;
                }

                $order_id = $order['order_id'];

                /* 标记订单为已确认 */
                update_order($order_id, array('order_status' => 1, 'confirm_time' => gmtime()));
                update_order_amount($order_id);

                /* 记录log */
                order_action($order['order_sn'], 1, 0, 0, $action_note);

                $sn_list[] = $order['order_sn'];
            }
            else
            {
                $sn_not_list[] = $id_order;
            }
        }

        $sn_str = '以下订单无法设置为确认状态';
    }
    elseif ('remove' == $operation)
    {
        foreach ($order_id_list as $id_order)
        {
            /* 检查能否操作 */
            $order = order_info('', $id_order);
            $operable_list = operable_list($order);
            if (!isset($operable_list['remove']))
            {
                $sn_not_list[] = $id_order;
                continue;
            }

            /* 删除订单 */
            $GLOBALS['db']->query("DELETE FROM ".$aos->table('order_info'). " WHERE order_id = '$order[order_id]'");
            $GLOBALS['db']->query("DELETE FROM ".$aos->table('order_goods'). " WHERE order_id = '$order[order_id]'");
            $GLOBALS['db']->query("DELETE FROM ".$aos->table('order_action'). " WHERE order_id = '$order[order_id]'");
            $action_array = array('delivery', 'back');
            del_delivery($order['order_id'], $action_array);

            /* todo 记录日志 */
            admin_log($order['order_sn'], 'remove', 'order');

            $sn_list[] = $order['order_sn'];
        }

        $sn_str = '以下订单无法被移除';
    }
    else
    {
        die('invalid params');
    }

    /* 取得备注信息 */
//    $action_note = $_REQUEST['action_note'];

    if(empty($sn_not_list))
    {
        $sn_list = empty($sn_list) ? '' : '更新的订单：' . join($sn_list, ',');
        $msg = $sn_list;
        $links[] = array('text' => '返回订单列表', 'href' => 'index.php?act=order&op=list&' . list_link_postfix());
        sys_msg($msg, 0, $links);
    }
    else
    {
        $order_list_no_fail = array();
        $sql = "SELECT * FROM " . $aos->table('order_info') .
                " WHERE order_sn " . db_create_in($sn_not_list);
        $res = $GLOBALS['db']->query($sql);
        while($row = $GLOBALS['db']->fetchRow($res))
        {
            $order_list_no_fail[$row['order_id']]['order_id'] = $row['order_id'];
            $order_list_no_fail[$row['order_id']]['order_sn'] = $row['order_sn'];
            $order_list_no_fail[$row['order_id']]['order_status'] = $row['order_status'];
            $order_list_no_fail[$row['order_id']]['shipping_status'] = $row['shipping_status'];
            $order_list_no_fail[$row['order_id']]['pay_status'] = $row['pay_status'];

            $order_list_fail = '';
            foreach(operable_list($row) as $key => $value)
            {
                if($key != $operation)
                {
                    $order_list_fail .= $_LANG['op_' . $key] . ',';
                }
            }
            $order_list_no_fail[$row['order_id']]['operable'] = $order_list_fail;
        }

        /* 模板赋值 */
        $smarty->assign('order_info', $sn_str);
        $smarty->assign('action_link', array('href' => 'index.php?act=order&op=list', 'text' => '订单列表'));
        $smarty->assign('order_list',   $order_list_no_fail);

        /* 显示模板 */
        
        $smarty->display('order_operate_info.htm');
    }
}

/*------------------------------------------------------ */
//-- 操作订单状态（处理提交）
/*------------------------------------------------------ */

elseif ($operation == 'operate_post')
{
    /* 检查权限 */
    //admin_priv('order_os_edit');

    /* 取得参数 */
    $order_id   = intval(trim($_REQUEST['order_id']));        // 订单id
    $operation  = $_REQUEST['operation'];       // 订单操作

    /* 查询订单信息 */
    $order = order_info($order_id);

    /* 检查能否操作 */
    $operable_list = operable_list($order);
    if (!isset($operable_list[$operation]))
    {
        die('Hacking attempt');
    }

    /* 取得备注信息 */
    $action_note = $_REQUEST['action_note'];

    /* 初始化提示信息 */
    $msg = '';

    /* 确认 */
    if ('confirm' == $operation)
    {
        /* 标记订单为已确认 */
        update_order($order_id, array('order_status' => 1, 'confirm_time' => gmtime()));
        update_order_amount($order_id);

        /* 记录log */
        order_action($order['order_sn'], 1, 0, 0, $action_note);


    }
    /* 付款 */
    elseif ('pay' == $operation)
    {
        /* 检查权限 */
        //admin_priv('order_ps_edit');

        /* 标记订单为已确认、已付款，更新付款时间和已支付金额，如果是货到付款，同时修改订单为“收货确认” */
        if ($order['order_status'] != 1)
        {
            $arr['order_status']    = 1;
            $arr['confirm_time']    = gmtime();
        }
        $arr['pay_status']  = 2;
        $arr['pay_time']    = gmtime();
        if($order['extension_code']=='tuan'){
            if($order['tuan_first']==1){
                $arr['suc_tuan_time']    = gmtime();
            }
            $arr['tuan_status']    = 1;
        }
        $arr['money_paid']  = $order['money_paid'] + $order['order_amount'];
        $arr['order_amount']= 0;
        $payment = payment_info($order['pay_id']);
        if ($payment['is_cod'])
        {
            $arr['shipping_status'] = 2;
            $order['shipping_status'] = 2;
        }
        update_order($order_id, $arr);
        /* 如果使用库存，则减少库存 */
        if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == 2)
        {
            // 检查此单发货商品数量
            $virtual_goods = array();
            $delivery_stock_sql = "SELECT goods_id, attr_id, SUM(goods_number) AS sums
                FROM " . $GLOBALS['aos']->table('order_goods') . " 
                WHERE order_id = '$order_id'
                GROUP BY goods_id ";
            $order_goods_result = $GLOBALS['db']->getAll($delivery_stock_sql);
            foreach ($order_goods_result as $key => $value)
            {

                //（货品）
                if (!empty($value['attr_id']))
                {
                    $minus_stock_sql = "UPDATE " . $GLOBALS['aos']->table('goods_attr') . "
                                        SET product_number = product_number - " . $value['sums'] . "
                                        WHERE attr_id = " . $value['attr_id'];
                    $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
                }

                $minus_stock_sql = "UPDATE " . $GLOBALS['aos']->table('goods') . "
                                    SET goods_number = goods_number - " . $value['sums'] . "
                                    WHERE goods_id = " . $value['goods_id'];
                $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
            }
        }
        $sql="select goods_id from ".$aos->table('order_goods')." where order_id = $order_id";
        $goods_id=$db->getOne($sql);
        $extension_code=$order[0]['extension_code'];
        $act_id=$order[0]['act_id'];
        if($extension_code=='miao'){
            $now_time=gmtime();
            $sql="SELECT seck_tuan_num from ".$GLOBALS['aos']->table('seckill')." where seckill_id = ".$act_id;
            $tuan_num=$GLOBALS['db']->getOne($sql);
        }elseif($extension_code=='lottery'){
            $now_time=gmtime();
            $sql="SELECT lottery_tuan_num from ".$GLOBALS['aos']->table('lottery')." where lottery_id = ".$act_id;
            $tuan_num=$GLOBALS['db']->getOne($sql);
        }elseif($extension_code=='tuan'){

            $goods_tuan_number=get_tuan_number($goods_id);
            $tuan_num = max($goods_tuan_number);
            
        }
        if(!empty($tuan_num) && $tuan_num>0){
            $sql="select count(order_id) from ".$aos->table('order_goods')." where extension_id = $order[extension_id] and pay_status = 2 and tuan_status = 1";
            $number = $db->getOne($sql);
            if($number>=$tuan_num){
                cheng_tuan($order[extension_id]);
            }
        }
        /* 记录log */
        order_action($order['order_sn'], 1, $order['shipping_status'], 2, $action_note);
    }
    /* 设为未付款 */
    elseif ('unpay' == $operation)
    {
        /* 检查权限 */
        //admin_priv('order_ps_edit');

        /* 标记订单为未付款，更新付款时间和已付款金额 */
        $arr = array(
            'pay_status'    => 0,
            'pay_time'      => 0,
            'money_paid'    => 0,
            'order_amount'  => $order['money_paid']
        );
        update_order($order_id, $arr);

        if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == 2)
        {
            // 检查此单发货商品数量
            $virtual_goods = array();
            $delivery_stock_sql = "SELECT goods_id, attr_id, SUM(goods_number) AS sums
                FROM " . $GLOBALS['aos']->table('order_goods') . " 
                WHERE order_id = '$order_id'
                GROUP BY goods_id ";
            $order_goods_result = $GLOBALS['db']->getAll($delivery_stock_sql);
            foreach ($order_goods_result as $key => $value)
            {

                //（货品）
                if (!empty($value['attr_id']))
                {
                    $minus_stock_sql = "UPDATE " . $GLOBALS['aos']->table('goods_attr') . "
                                        SET product_number = product_number + " . $value['sums'] . "
                                        WHERE attr_id = " . $value['attr_id'];
                    $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
                }

                $minus_stock_sql = "UPDATE " . $GLOBALS['aos']->table('goods') . "
                                    SET goods_number = goods_number + " . $value['sums'] . "
                                    WHERE goods_id = " . $value['goods_id'];
                $GLOBALS['db']->query($minus_stock_sql, 'SILENT');
            }
        }
        /* todo 处理退款 */
        $refund_type = @$_REQUEST['refund'];
        $refund_note = @$_REQUEST['refund_note'];
        order_refund($order, $refund_type, $refund_note);
        /* 如果使用库存，则减少库存 */
        

        /* 记录log */
        order_action($order['order_sn'], 1, 0, 0, $action_note);
    }
    /* 配货 */
    elseif ('prepare' == $operation)
    {
        /* 标记订单为已确认，配货中 */
        if ($order['order_status'] != 1)
        {
            $arr['order_status']    = 1;
            $arr['confirm_time']    = gmtime();
        }
        $arr['shipping_status']     = 3;
        update_order($order_id, $arr);

        /* 记录log */
        order_action($order['order_sn'], 1, 3, $order['pay_status'], $action_note);

        /* 清除缓存 */
        clear_cache_files();
    }
    /* 分单确认 */
    elseif ('split' == $operation)
    {
        /* 检查权限 */
        //admin_priv('order_ss_edit');

        /* 定义当前时间 */
        define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳
        

        /* 获取表单提交数据 */
        array_walk($_REQUEST['delivery'], 'trim_array_walk');
        $delivery = $_REQUEST['delivery'];
        array_walk($_REQUEST['send_number'], 'trim_array_walk');
        array_walk($_REQUEST['send_number'], 'intval_array_walk');
        //判断订单号
        if(empty($delivery['invoice_no'])){
            $links[] = array('href' => 'index.php?act=order&op=info&order_id='.$order_id, 'text' => "返回");
            sys_msg("请填写发货单号", 0, $links);
       }
       //判断快递方式
        $shipping_id=intval($_REQUEST['shipping_id']);
        $sql="select shipping_name,shipping_id from ".$GLOBALS['aos']->table('shipping')." where shipping_id = $shipping_id";
       $shipping_row=$GLOBALS['db']->getRow($sql);
       if(empty($shipping_row)){
            $links[] = array('href' => 'index.php?act=order&op=info&order_id='.$order_id, 'text' => "返回");
            sys_msg("请选择快递方式", 0, $links);
       }
        $send_number = $_REQUEST['send_number'];
        $action_note = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
        $delivery['user_id']  = intval($delivery['user_id']);
        $delivery['area']  = $delivery['area'];
        $delivery['district'] = intval($delivery['district']);
        $delivery['store_id']    = intval($delivery['store_id']);
        $delivery['insure_fee']   = floatval($delivery['insure_fee']);
        $delivery['shipping_fee'] = floatval($delivery['shipping_fee']);
        $delivery['shipping_name']  = $shipping_row['shipping_name'];
        $delivery['shipping_id']  = $shipping_row['shipping_id'];
        /* 订单是否已全部分单检查 */
        if ($order['order_status'] == 5)
        {
            /* 操作失败 */
            $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
            sys_msg(sprintf('您的订单%s,%s正在%s [%s]', $order['order_sn'],
                    $_LANG['os'][5], $_LANG['ss'][5], $GLOBALS['_CFG']['shop_name']), 1, $links);
        }

        /* 取得订单商品 */
        $_goods = get_order_goods(array('order_id' => $order_id, 'order_sn' => $delivery['order_sn']));
        $goods_list = $_goods['goods_list'];

        /* 检查此单发货数量填写是否正确 合并计算相同商品和货品 */
        if (!empty($send_number) && !empty($goods_list))
        {
            $goods_no_package = array();
            foreach ($goods_list as $key => $value)
            {
                /* 去除 此单发货数量 等于 0 的商品 */
                if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list']))
                {
                    // 如果是货品则键值为商品ID与货品ID的组合
                    $_key = empty($value['product_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['product_id']);

                    // 统计此单商品总发货数 合并计算相同ID商品或货品的发货数
                    if (empty($goods_no_package[$_key]))
                    {
                        $goods_no_package[$_key] = $send_number[$value['rec_id']];
                    }
                    else
                    {
                        $goods_no_package[$_key] += $send_number[$value['rec_id']];
                    }

                    //去除
                    if ($send_number[$value['rec_id']] <= 0)
                    {
                        unset($send_number[$value['rec_id']], $goods_list[$key]);
                        continue;
                    }
                }

                /* 发货数量与总量不符 */
                if (!isset($value['package_goods_list']) || !is_array($value['package_goods_list']))
                {
                    $sended = order_delivery_num($order_id, $value['goods_id'], $value['attr_id']);
                    if (($value['goods_number'] - $sended - $send_number[$value['rec_id']]) < 0)
                    {
                        /* 操作失败 */
                        $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
                        sys_msg('此单发货数量不能超出订单商品数量', 1, $links);
                    }
                }
            }
        }
        /* 对上一步处理结果进行判断 兼容 上一步判断为假情况的处理 */
        if (empty($send_number) || empty($goods_list))
        {
            /* 操作失败 */
            $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
            sys_msg('操作失败', 1, $links);
        }

        /* 检查此单发货商品库存缺货情况 */
        /* $goods_list已经过处理 超值礼包中商品库存已取得 */
        $virtual_goods = array();
        $package_virtual_goods = array();
        foreach ($goods_list as $key => $value)
        {

            //如果是货品则键值为商品ID与货品ID的组合
            $_key = empty($value['attr_id']) ? $value['goods_id'] : ($value['goods_id'] . '_' . $value['attr_id']);

            /* （实货） */
            if (empty($value['attr_id']))
            {
                $sql = "SELECT goods_number FROM " . $GLOBALS['aos']->table('goods') . " WHERE goods_id = '" . $value['goods_id'] . "' LIMIT 0,1";
            }
            /* （货品） */
            else
            {
                $sql = "SELECT product_number
                        FROM " . $GLOBALS['aos']->table('goods_attr') ."
                        WHERE goods_id = '" . $value['goods_id'] . "'
                        AND attr_id =  '" . $value['attr_id'] . "'
                        LIMIT 0,1";
            }
            $num = $GLOBALS['db']->GetOne($sql);

            if (($num < $goods_no_package[$_key]) && $_CFG['use_storage'] == '1'  && $_CFG['stock_dec_time'] == 0)
            {
                /* 操作失败 */
                $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
                sys_msg(sprintf('商品已缺货', $value['goods_name']), 1, $links);
            }
 
        }
        $sql="select delivery_id from ". $GLOBALS['aos']->table('delivery_order') ." where order_id = ".$order_id;
        $check_delivery=$GLOBALS['db']->getOne($sql);
        if($check_delivery){
            $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
            sys_msg('此单发货数量不能超出订单商品数量', 1, $links);
        }
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
                               'store_id', 'delivery_sn', 'action_user', 'update_time',
                               'status', 'order_id', 'shipping_name','invoice_no'
                               );
        $_delivery = array();
        foreach ($filter_fileds as $value)
        {
            $_delivery[$value] = $delivery[$value];
        }
        /* 发货单入库 */
        $query = $GLOBALS['db']->autoExecute($aos->table('delivery_order'), $_delivery, 'INSERT', '', 'SILENT');
        $delivery_id = $GLOBALS['db']->insert_id();
        if ($delivery_id)
        {
            $delivery_goods = array();

            //发货单商品入库
            if (!empty($goods_list))
            {
                foreach ($goods_list as $value)
                {
                    // 商品
                    
                    $delivery_goods = array('delivery_id' => $delivery_id,
                                            'goods_id' => $value['goods_id'],
                                            'product_id' => $value['attr_id'],
                                            'product_sn' => $value['product_sn'],
                                            'goods_id' => $value['goods_id'],
                                            'goods_name' => addslashes($value['goods_name']),
                                            'goods_sn' => $value['goods_sn'],
                                            'send_number' => $send_number[$value['rec_id']],
                                            'parent_id' => 0,
                                            'goods_attr' => addslashes($value['goods_attr'])
                                            );

                    /* 如果是货品 */
                    if (!empty($value['attr_id']))
                    {
                        $delivery_goods['product_id'] = $value['attr_id'];
                    }

                    $query = $GLOBALS['db']->autoExecute($aos->table('delivery_goods'), $delivery_goods, 'INSERT', '', 'SILENT');
                    
                }
            }
        }
        else
        {
            /* 操作失败 */
            $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
            sys_msg('操作失败', 1, $links);
        }
        unset($filter_fileds, $delivery, $_delivery, $order_finish);

        /* 定单信息更新处理 */
        if (true)
        {
            /* 定单信息 */
            $_sended = & $send_number;
            foreach ($_goods['goods_list'] as $key => $value)
            {
                unset($_goods['goods_list'][$key]);
            }
            $_goods['goods_list'] = $goods_list + $_goods['goods_list'];
            unset($goods_list);


            /* 更新订单的非虚拟商品信息 即：商品（实货）（货品）、商品（超值礼包）*/
            update_order_goods($order_id, $_sended, $_goods['goods_list']);

            /* 标记订单为已确认 “发货中” */
            /* 更新发货时间 */
            $shipping_status = 5;
            if ($order['order_status'] != 1 && $order['order_status'] != 5 && $order['order_status'] != OS_SPLITING_PART)
            {
                $arr['order_status']    = 1;
                $arr['confirm_time']    = GMTIME_UTC;
            }
            $arr['shipping_name']  = $shipping_row['shipping_name'];
            $arr['shipping_id']  = $shipping_row['shipping_id'];
            $arr['order_status'] = 1; 
            $arr['shipping_status']     = $shipping_status;
            update_order($order_id, $arr);
        }

        /* 记录log */
        order_action($order['order_sn'], $arr['order_status'], $shipping_status, $order['pay_status'], $action_note);

        /* 清除缓存 */
        clear_cache_files();
    }
    /* 设为未发货 */
    elseif ('unship' == $operation)
    {
        /* 检查权限 */
        //admin_priv('order_ss_edit');

        /* 标记订单为“未发货”，更新发货时间, 订单状态为“确认” */
        update_order($order_id, array('shipping_status' => 0, 'shipping_time' => 0, 'invoice_no' => '', 'order_status' => 1));

        /* 记录log */
        order_action($order['order_sn'], $order['order_status'], 0, $order['pay_status'], $action_note);

        /* 如果订单用户不为空，计算积分，并退回 */
        if ($order['user_id'] > 0)
        {
            /* 取得用户信息 */
            $user = user_info($order['user_id']);

            /* 计算并退回积分 */
            //$integral = integral_to_give($order);
            //log_account_change($order['user_id'], 0, 0, (-1) * intval($integral['rank_points']), (-1) * intval($integral['custom_points']), sprintf('由于退货或未发货操作，退回订单 %s 赠送的积分', $order['order_sn']));

            /* todo 计算并退回优惠券 */
            //return_order_bonus($order_id);
        }

        /* 删除发货单 */
        del_order_delivery($order_id);


        /* 清除缓存 */
        clear_cache_files();
    }
    /* 收货确认 */
    elseif ('receive' == $operation)
    {   
        /* 标记订单为“收货确认”，如果是货到付款，同时修改订单为已付款 */
        $arr = array('shipping_status' => 2);
        $payment = payment_info($order['pay_id']);
        if ($payment['is_cod'])
        {
            $arr['pay_status'] = 2;
            $order['pay_status'] = 2;
        }
        update_order($order_id, $arr);

        /* 记录log */
        order_action($order['order_sn'], $order['order_status'], 2, $order['pay_status'], $action_note);
    }
    elseif ('after_service' == $operation)
    {
        /* 记录log */
        order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '[售后] ' . $action_note);
    }
    else
    {
        die('invalid params');
    }

    /* 操作成功 */
    $links[] = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
    sys_msg('操作成功' . $msg, 0, $links);
}
elseif ($operation == 'json')
{
    include_once(ROOT_PATH . 'source/class/json.php');
    $json = new JSON();

    $func = $_REQUEST['func'];
    if ($func == 'get_goods_info')
    {
        /* 取得商品信息 */
        $goods_id = $_REQUEST['goods_id'];
        $sql = "SELECT goods_id, c.cat_name, goods_sn, goods_name, " .
                "goods_number, market_price, shop_price, " .
                "goods_brief, goods_type, is_promote " .
                "FROM " . $aos->table('goods') . " AS g " .
                "LEFT JOIN " . $aos->table('category') . " AS c ON g.cat_id = c.cat_id " .
                " WHERE goods_id = '$goods_id'";
        $goods = $db->getRow($sql);

        /* 取得会员价格 */
        $sql = "SELECT p.user_price, r.rank_name " .
                "FROM " . $aos->table('member_price') . " AS p, " .
                    $aos->table('user_rank') . " AS r " .
                "WHERE p.user_rank = r.rank_id " .
                "AND p.goods_id = '$goods_id' ";
        $goods['user_price'] = $db->getAll($sql);

        /* 取得商品属性 */
        $sql = "SELECT a.attr_id, a.attr_name, g.goods_attr_id, g.attr_value, g.attr_price, a.attr_input_type, a.attr_type " .
                "FROM " . $aos->table('goods_attr') . " AS g, " .
                    $aos->table('attribute') . " AS a " .
                "WHERE g.attr_id = a.attr_id " .
                "AND g.goods_id = '$goods_id' ";
        $goods['attr_list'] = array();
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $goods['attr_list'][$row['attr_id']][] = $row;
        }
        $goods['attr_list'] = array_values($goods['attr_list']);

        echo json_encode($goods);
    }
}

/*------------------------------------------------------ */
//-- 删除订单
/*------------------------------------------------------ */
elseif ($operation == 'remove_order')
{
    /* 检查权限 */
    //admin_priv('order_edit');

    $order_id = intval($_REQUEST['id']);

    /* 检查权限 */
    check_authz_json('order_edit');

    /* 检查订单是否允许删除操作 */
    $order = order_info($order_id);
    $operable_list = operable_list($order);
    if (!isset($operable_list['remove']))
    {
        make_json_error('Hacking attempt');
        exit;
    }

    $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['aos']->table('order_info'). " WHERE order_id = '$order_id'");
    $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['aos']->table('order_goods'). " WHERE order_id = '$order_id'");
    $GLOBALS['db']->query("DELETE FROM ".$GLOBALS['aos']->table('order_action'). " WHERE order_id = '$order_id'");
    $action_array = array('delivery', 'back');
    del_delivery($order_id, $action_array);

    if ($GLOBALS['db'] ->errno() == 0)
    {
        //$url = 'index.php?act=order&op=query&' . str_replace('act=remove_order', '', $_SERVER['QUERY_STRING']);

        //aos_header("Location: $url\n");
        make_json_result($order_id);
        exit;
    }
    else
    {
        make_json_error($GLOBALS['db']->errorMsg());
    }
}


/*------------------------------------------------------ */
//-- 编辑收货单号
/*------------------------------------------------------ */
elseif ($operation == 'edit_invoice_no')
{
    /* 检查权限 */
    check_authz_json('order_edit');

    $no = empty($_POST['val']) ? 'N/A' : json_str_iconv(trim($_POST['val']));
    $no = $no=='N/A' ? '' : $no;
    $order_id = empty($_POST['id']) ? 0 : intval($_POST['id']);

    if ($order_id == 0)
    {
        make_json_error('NO ORDER ID');
        exit;
    }

    $sql = 'UPDATE ' . $GLOBALS['aos']->table('order_info') . " SET invoice_no='$no' WHERE order_id = '$order_id'";
    if ($GLOBALS['db']->query($sql))
    {
        if (empty($no))
        {
            make_json_result('N/A');
        }
        else
        {
            make_json_result(stripcslashes($no));
        }
    }
    else
    {
        make_json_error($GLOBALS['db']->errorMsg());
    }
}

/*------------------------------------------------------ */
//-- 编辑付款备注
/*------------------------------------------------------ */
elseif ($operation == 'edit_pay_note')
{
    /* 检查权限 */
    check_authz_json('order_edit');

    $no = empty($_POST['val']) ? 'N/A' : json_str_iconv(trim($_POST['val']));
    $no = $no=='N/A' ? '' : $no;
    $order_id = empty($_POST['id']) ? 0 : intval($_POST['id']);

    if ($order_id == 0)
    {
        make_json_error('NO ORDER ID');
        exit;
    }

    $sql = 'UPDATE ' . $GLOBALS['aos']->table('order_info') . " SET pay_note='$no' WHERE order_id = '$order_id'";
    if ($GLOBALS['db']->query($sql))
    {
        if (empty($no))
        {
            make_json_result('N/A');
        }
        else
        {
            make_json_result(stripcslashes($no));
        }
    }
    else
    {
        make_json_error($GLOBALS['db']->errorMsg());
    }
}

/*------------------------------------------------------ */
//-- 获取订单商品信息
/*------------------------------------------------------ */
elseif ($operation == 'get_goods_info')
{
    /* 取得订单商品 */
    $order_id = isset($_REQUEST['order_id'])?intval($_REQUEST['order_id']):0;
    if (empty($order_id))
    {
        make_json_response('', 1, '获取订单商品信息错误');
    }
    $goods_list = array();
    $goods_attr = array();
    $sql = "SELECT o.*, g.goods_thumb, g.goods_number AS storage, o.goods_attr " .
            "FROM " . $aos->table('order_goods') . " AS o ".
            "LEFT JOIN " . $aos->table('goods') . " AS g ON o.goods_id = g.goods_id " .
            "WHERE o.order_id = '{$order_id}' ";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        $row['formated_subtotal']       = price_format($row['goods_price'] * $row['goods_number']);
        $row['formated_goods_price']    = price_format($row['goods_price']);
        $_goods_thumb = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $_goods_thumb = (strpos($_goods_thumb, 'http://') === 0) ? $_goods_thumb : $aos->url() . $_goods_thumb;
        $row['goods_thumb'] = $_goods_thumb;
        $goods_attr[] = explode(' ', trim($row['goods_attr'])); //将商品属性拆分为一个数组
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

    $smarty->assign('goods_attr', $attr);
    $smarty->assign('goods_list', $goods_list);
    $str = $smarty->fetch('order_goods_info.htm');
    $goods[] = array('order_id' => $order_id, 'str' => $str);
    make_json_result($goods);
}

/**
 * 取得状态列表
 * @param   string  $type   类型：all | order | shipping | payment
 */
function get_status_list($type = 'all')
{
    global $_LANG;

    $list = array();

    if ($type == 'all' || $type == 'order')
    {
        $pre = $type == 'all' ? 'os_' : '';
        foreach ($_LANG['os'] AS $key => $value)
        {
            $list[$pre . $key] = $value;
        }
    }

    if ($type == 'all' || $type == 'shipping')
    {
        $pre = $type == 'all' ? 'ss_' : '';
        foreach ($_LANG['ss'] AS $key => $value)
        {
            $list[$pre . $key] = $value;
        }
    }

    if ($type == 'all' || $type == 'payment')
    {
        $pre = $type == 'all' ? 'ps_' : '';
        foreach ($_LANG['ps'] AS $key => $value)
        {
            $list[$pre . $key] = $value;
        }
    }
    return $list;
}



/**
 * 更新订单总金额
 * @param   int     $order_id   订单id
 * @return  bool
 */
function update_order_amount($order_id)
{
    include_once(ROOT_PATH . 'source/library/order.php');
    //更新订单总金额
    $sql = "UPDATE " . $GLOBALS['aos']->table('order_info') .
            " SET order_amount = " . order_due_field() .
            " WHERE order_id = '$order_id' LIMIT 1";

    return $GLOBALS['db']->query($sql);
}


/**
 * 处理编辑订单时订单金额变动
 * @param   array   $order  订单信息
 * @param   array   $msgs   提示信息
 * @param   array   $links  链接信息
 */
function handle_order_money_change($order, &$msgs, &$links)
{
    $order_id = $order['order_id'];
    if ($order['pay_status'] == 2 || $order['pay_status'] == 1)
    {
        /* 应付款金额 */
        $money_dues = $order['order_amount'];
        if ($money_dues > 0)
        {
            /* 修改订单为未付款 */
            update_order($order_id, array('pay_status' => 0, 'pay_time' => 0));
            $msgs[]     = '由于您修改了订单，导致订单总金额增加，需要再次付款';
            $links[]    = array('text' => '订单信息', 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
        }
        elseif ($money_dues < 0)
        {
            $anonymous  = $order['user_id'] > 0 ? 0 : 1;
            $msgs[]     = '由于您修改了订单，导致订单总金额减少，需要退款';
            $links[]    = array('text' => '退款', 'href' => 'index.php?act=order&op=process&func=load_refund&anonymous=' .
                $anonymous . '&order_id=' . $order_id . '&refund_amount=' . abs($money_dues));
        }
    }
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
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
        {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }
        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
        $filter['mobile'] = empty($_REQUEST['mobile']) ? '' : trim($_REQUEST['mobile']);
        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
        $filter['composite_status'] = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : -1;
        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
        $where = 'WHERE 1 ';
        if ($filter['order_sn'])
        {
            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['consignee'])
        {
            $where .= " AND o.consignee LIKE '%" . mysql_like_quote($filter['consignee']) . "%'";
        }
        if ($filter['mobile'])
        {
            $where .= " AND o.mobile LIKE '%" .mysql_like_quote($filter['mobile']) . "%'";
        }
        if ($filter['user_id'])
        {
            $where .= " AND o.user_id = '$filter[user_id]'";
        }
        if ($filter['start_time'])
        {
            $where .= " AND o.add_time >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND o.add_time <= '$filter[end_time]'";
        }

        //综合状态
        switch($filter['composite_status'])
        {
            case -1 :
                $where .= '';
                break;
            case 0 :
                $where .= " and order_status = 0 ";
                break;
            case 1 ://待付款
                $where .= order_query_sql('await_pay');
                break;
            case 2 ://待发货
                $where .= order_query_sql('await_ship');
                break;
            case 3 ://待核销
                $where .= order_query_sql('await_veri');
                break;
            case 4 ://已取消
                $where .= " and order_status = 2 ";
                break;
            case 5 ://无效
                $where .= " and order_status = 3 ";
                
                break;
        }


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
            $filter['page_size'] = 10;
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
        $sql = "SELECT o.is_luck,o.shipping_id,o.order_id,o.assist_num, o.order_sn, o.add_time, o.order_status, o.shipping_status, o.order_amount, o.money_paid,o.store_id,o.pay_status, o.consignee, o.address, o.mobile, o.extension_code, o.extension_id, o.tuan_status, o.tuan_num, o.store_id, o.comment, " .
                    "(" . order_amount_field('o.') . ") AS total_fee, " .
                    "u.nickname AS buyer ".
                " FROM " . $GLOBALS['aos']->table('order_info') . " AS o " .
                " LEFT JOIN " .$GLOBALS['aos']->table('users'). " AS u ON u.user_id=o.user_id ". $where .
                " ORDER BY add_time DESC ".
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
        $row[$key]['add_time'] = local_date('Y-m-d H:i', $value['add_time']);
        $order_goods = order_goods($value['order_id']);
        $row[$key]['goods_name'] = $order_goods['goods_name'];
        $row[$key]['goods_img'] = get_goods_img($order_goods['goods_id']);
        $row[$key]['goods_number'] = $order_goods['goods_number'];
        $row[$key]['goods_attr'] = $order_goods['goods_attr'];
        $row[$key]['goods_price'] = price_format($order_goods['goods_price']);
        $row[$key]['store_name'] = get_store_name($value['store_id']);

        if ($value['order_status'] == 3 || $value['order_status'] == 2)
        {
            /* 如果该订单为无效或取消则显示删除链接 */
            $row[$key]['can_remove'] = 1;
        }
        else
        {
            $row[$key]['can_remove'] = 0;
        }
        //订单状态
        if($value['order_status'] == 1 && $value['shipping_status'] == 0 && $value['pay_status'] == 0)
        {
            $row[$key]['order_status_name'] = '待支付';
        }
        elseif($value['order_status'] == 3 && $value['shipping_status'] == 0 && $value['pay_status'] == 0)
        {
            $row[$key]['order_status_name'] = '<font color="red"> 无效</font>';
        }
        elseif($value['order_status'] == 2)
        {
            $row[$key]['order_status_name'] = '<font color="red"> 已删除</font>';
        }
        elseif($value['order_status'] == 4 && $value['pay_status'] == 2)
        {
            $row[$key]['order_status_name'] = '<font color="red"> 退款</font>';
        }
        elseif($value['order_status'] == 1 && $value['shipping_status'] == 0 && $value['pay_status'] == 2)
        {
         
            //团状态
            $ex_array=array('tuan','lottery','miao');
            if($value['extension_code']=='assist' && $value['tuan_status']==4){
                $row[$key]['order_status_name'] = '助力失败';
            }
            elseif($value['extension_code']=='assist' && $value['tuan_status']==1){
                $row[$key]['order_status_name'] = '助力中';
            }
            elseif(!empty($value['extension_code']) && in_array($value['extension_code'], $ex_array))
            {
                if($value['tuan_status'] == 1)
                {
                    $row[$key]['order_status_name'] = '拼团中';
                }
                elseif($value['tuan_status'] == 2)
                {
                    if($value['extension_code'] == 'lottery' && in_array($value['is_luck'], array(0,2,3))){
                        if($value['is_luck'] == 0){

                            $row[$key]['order_status_name'] = '已成团，待抽奖';

                        }elseif($value['is_luck'] == 2){

                            $row[$key]['order_status_name'] = '已成团，未中奖,待退款';
                            
                        }elseif($value['is_luck'] == 3){

                            $row[$key]['order_status_name'] = '已成团，未中奖,已退款';
                            
                        }
                        
                    }
                    elseif(!empty($value['store_id']))
                    {
                        $row[$key]['order_status_name'] = '已成团，待核销';
                    }
                    else
                    {

                        $row[$key]['order_status_name'] = '已成团，待发货';
                        
                    }
                }
                elseif($value['tuan_status'] == 3)
                {
                    $row[$key]['order_status_name'] = '团失败，待退款';
                }
                elseif($value['tuan_status'] == 4)
                {
                    $row[$key]['order_status_name'] = '团失败，已退款';
                }
            }
            //自提
            else
            {
                if(!empty($value['store_id']))
                {
                    $row[$key]['order_status_name'] = '待核销';
                }
                else
                {

                    $row[$key]['order_status_name'] = '待发货';
                }
            }
        }
        elseif($value['order_status'] == 5 && $value['shipping_status'] == 1 && $value['pay_status'] == 2){
            $row[$key]['order_status_name'] = '待收货';
        }
        elseif($value['order_status'] == 5 && $value['shipping_status'] == 2 && $value['pay_status'] == 2 && $value['comment'] == 0){
            $row[$key]['order_status_name'] = '待评价';
        }
        elseif($value['order_status'] == 5 && $value['shipping_status'] == 2 && $value['pay_status'] == 2 && $value['comment'] == 1){
            $row[$key]['order_status_name'] = '已完成';
        }
        $tuihuan_info = $GLOBALS['db']->getRow("select back_type,status_back from " . $GLOBALS['aos']->table('back_order') . " where order_id = '" . $value['order_id']. "' ORDER BY back_id DESC LIMIT 1");
        if (!empty($tuihuan_info))
        {
            switch ($tuihuan_info['back_type'])
            {
                case 1 :
                    $back_type = "退货,";
                    break;
                case 3 :
                    $back_type = "申请维修,";
                    break;
                case 4 :
                    $back_type = "退款(无需退货),";
                    break;
                default :
                    break;
            }

            switch ($tuihuan_info['status_back'])
            {
                case 0 :
                    $status_back = "已通过申请";
                    break;
                case 1 :
                    $status_back = "已收到换回商品";
                    break;
                case 2 :
                    $status_back = "换出商品已寄回";
                    break;
                case 3 :
                    $status_back = "已完成";
                    break;
                case 4 :
                    $status_back = "退款";
                    break;
                case 5 :
                    $status_back = "申请中";
                    break;
                case 6 :
                    $status_back = "已拒绝申请";
                    break;
                case 7 :
                    $status_back = "系统自动取消";
                    break;
                case 8 :
                    $status_back = "用户自行取消";
                    break;
                default :
                    break;
            }

            $row[$key]['back_type'] = $back_type;
            $row[$key]['status_back'] = $status_back;
        }
    }
    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 更新订单对应的 pay_log
 * 如果未支付，修改支付金额；否则，生成新的支付log
 * @param   int     $order_id   订单id
 */
function update_pay_log($order_id)
{
    $order_id = intval($order_id);
    if ($order_id > 0)
    {
        $sql = "SELECT order_amount FROM " . $GLOBALS['aos']->table('order_info') .
                " WHERE order_id = '$order_id'";
        $order_amount = $GLOBALS['db']->getOne($sql);
        if (!is_null($order_amount))
        {
            $sql = "SELECT log_id FROM " . $GLOBALS['aos']->table('pay_log') .
                    " WHERE order_id = '$order_id'" .
                    " AND order_type = '" . 0 . "'" .
                    " AND is_paid = 0";
            $log_id = intval($GLOBALS['db']->getOne($sql));
            if ($log_id > 0)
            {
                /* 未付款，更新支付金额 */
                $sql = "UPDATE " . $GLOBALS['aos']->table('pay_log') .
                        " SET order_amount = '$order_amount' " .
                        "WHERE log_id = '$log_id' LIMIT 1";
            }
            else
            {
                /* 已付款，生成新的pay_log */
                $sql = "INSERT INTO " . $GLOBALS['aos']->table('pay_log') .
                        " (order_id, order_amount, order_type, is_paid)" .
                        "VALUES('$order_id', '$order_amount', '" . 0 . "', 0)";
            }
            $GLOBALS['db']->query($sql);
        }
    }
}




/**
 * 订单单个商品或货品的已发货数量
 *
 * @param   int     $order_id       订单 id
 * @param   int     $goods_id       商品 id
 * @param   int     $product_id     货品 id
 *
 * @return  int
 */
function order_delivery_num($order_id, $goods_id, $product_id = 0)
{
    $sql = 'SELECT SUM(G.send_number) AS sums
            FROM ' . $GLOBALS['aos']->table('delivery_goods') . ' AS G, ' . $GLOBALS['aos']->table('delivery_order') . ' AS O
            WHERE O.delivery_id = G.delivery_id
            AND O.status = 0
            AND O.order_id = ' . $order_id . '
            AND G.goods_id = ' . $goods_id;

    $sql .= ($product_id > 0) ? " AND G.product_id = '$product_id'" : '';

    $sum = $GLOBALS['db']->getOne($sql);

    if (empty($sum))
    {
        $sum = 0;
    }

    return $sum;
}

/**
 * 判断订单是否已发货（含部分发货）
 * @param   int     $order_id  订单 id
 * @return  int     1，已发货；0，未发货
 */
function order_deliveryed($order_id)
{
    $return_res = 0;

    if (empty($order_id))
    {
        return $return_res;
    }

    $sql = 'SELECT COUNT(delivery_id)
            FROM ' . $GLOBALS['aos']->table('delivery_order') . '
            WHERE order_id = \''. $order_id . '\'
            AND status = 0';
    $sum = $GLOBALS['db']->getOne($sql);

    if ($sum)
    {
        $return_res = 1;
    }

    return $return_res;
}

/**
 * 更新订单商品信息
 * @param   int     $order_id       订单 id
 * @param   array   $_sended        Array(‘商品id’ => ‘此单发货数量’)
 * @param   array   $goods_list
 * @return  Bool
 */
function update_order_goods($order_id, $_sended, $goods_list = array())
{
    if (!is_array($_sended) || empty($order_id))
    {
        return false;
    }

    foreach ($_sended as $key => $value)
    {
        // 超值礼包
        if (is_array($value))
        {
            if (!is_array($goods_list))
            {
                $goods_list = array();
            }

            foreach ($goods_list as $goods)
            {
                if (($key != $goods['rec_id']) || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list'])))
                {
                    continue;
                }

                $goods['package_goods_list'] = package_goods($goods['package_goods_list'], $goods['goods_number'], $goods['order_id'], $goods['extension_code'], $goods['goods_id']);
                $pg_is_end = true;

                foreach ($goods['package_goods_list'] as $pg_key => $pg_value)
                {
                    if ($pg_value['order_send_number'] != $pg_value['sended'])
                    {
                        $pg_is_end = false; // 此超值礼包，此商品未全部发货

                        break;
                    }
                }

                // 超值礼包商品全部发货后更新订单商品库存
                if ($pg_is_end)
                {
                    $sql = "UPDATE " . $GLOBALS['aos']->table('order_goods') . "
                            SET send_number = goods_number
                            WHERE order_id = '$order_id'
                            AND goods_id = '" . $goods['goods_id'] . "' ";

                    $GLOBALS['db']->query($sql, 'SILENT');
                }
            }
        }
        // 商品（实货）（货品）
        elseif (!is_array($value))
        {
            /* 检查是否为商品（实货）（货品） */
            foreach ($goods_list as $goods)
            {
                if ($goods['rec_id'] == $key)
                {
                    $sql = "UPDATE " . $GLOBALS['aos']->table('order_goods') . "
                            SET send_number = send_number + $value
                            WHERE order_id = '$order_id'
                            AND rec_id = '$key' ";
                    $GLOBALS['db']->query($sql, 'SILENT');
                    break;
                }
            }
        }
    }

    return true;
}

function trim_array_walk(&$array_value)
{
    if (is_array($array_value))
    {
        array_walk($array_value, 'trim_array_walk');
    }else{
        $array_value = trim($array_value);
    }
}

function intval_array_walk(&$array_value)
{
    if (is_array($array_value))
    {
        array_walk($array_value, 'intval_array_walk');
    }else{
        $array_value = intval($array_value);
    }
}

/**
 * 删除发货单(不包括已退货的单子)
 * @param   int     $order_id  订单 id
 * @return  int     1，成功；0，失败
 */
function del_order_delivery($order_id)
{
    $return_res = 0;

    if (empty($order_id))
    {
        return $return_res;
    }

    $sql = 'DELETE O, G
            FROM ' . $GLOBALS['aos']->table('delivery_order') . ' AS O, ' . $GLOBALS['aos']->table('delivery_goods') . ' AS G
            WHERE O.order_id = \'' . $order_id . '\'
            AND O.delivery_id = G.delivery_id';
    $query = $GLOBALS['db']->query($sql, 'SILENT');

    if ($query)
    {
        $return_res = 1;
    }

    return $return_res;
}

/**
 * 删除订单所有相关单子
 * @param   int     $order_id      订单 id
 * @param   int     $action_array  操作列表 Array('delivery', 'back', ......)
 * @return  int     1，成功；0，失败
 */
function del_delivery($order_id, $action_array)
{
    $return_res = 0;

    if (empty($order_id) || empty($action_array))
    {
        return $return_res;
    }

    $query_delivery = 1;
    $query_back = 1;
    if (in_array('delivery', $action_array))
    {
        $sql = 'DELETE O, G
                FROM ' . $GLOBALS['aos']->table('delivery_order') . ' AS O, ' . $GLOBALS['aos']->table('delivery_goods') . ' AS G
                WHERE O.order_id = \'' . $order_id . '\'
                AND O.delivery_id = G.delivery_id';
        $query_delivery = $GLOBALS['db']->query($sql, 'SILENT');
    }
    if (in_array('back', $action_array))
    {
        $sql = 'DELETE O, G
                FROM ' . $GLOBALS['aos']->table('back_order') . ' AS O, ' . $GLOBALS['aos']->table('back_goods') . ' AS G
                WHERE O.order_id = \'' . $order_id . '\'
                AND O.back_id = G.back_id';
        $query_back = $GLOBALS['db']->query($sql, 'SILENT');
    }

    if ($query_delivery && $query_back)
    {
        $return_res = 1;
    }

    return $return_res;
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


/**
 * 改变订单中商品库存
 * @param   int     $order_id  订单 id
 * @param   array   $_sended   Array(‘商品id’ => ‘此单发货数量’)
 * @param   array   $goods_list
 * @return  Bool
 */
function change_order_goods_storage_split($order_id, $_sended, $goods_list = array())
{
    /* 参数检查 */
    if (!is_array($_sended) || empty($order_id))
    {
        return false;
    }

    foreach ($_sended as $key => $value)
    {
        // 商品（超值礼包）
        if (is_array($value))
        {
            if (!is_array($goods_list))
            {
                $goods_list = array();
            }
            foreach ($goods_list as $goods)
            {
                if (($key != $goods['rec_id']) || (!isset($goods['package_goods_list']) || !is_array($goods['package_goods_list'])))
                {
                    continue;
                }

                // 超值礼包无库存，只减超值礼包商品库存
                foreach ($goods['package_goods_list'] as $package_goods)
                {
                    if (!isset($value[$package_goods['goods_id']]))
                    {
                        continue;
                    }

                    // 减库存：商品（超值礼包）（实货）、商品（超值礼包）（虚货）
                    $sql = "UPDATE " . $GLOBALS['aos']->table('goods') ."
                            SET goods_number = goods_number - '" . $value[$package_goods['goods_id']] . "'
                            WHERE goods_id = '" . $package_goods['goods_id'] . "' ";
                    $GLOBALS['db']->query($sql);
                }
            }
        }
        // 商品（实货）
        elseif (!is_array($value))
        {
            /* 检查是否为商品（实货） */
            foreach ($goods_list as $goods)
            {
                if ($goods['rec_id'] == $key)
                {
                    $sql = "UPDATE " . $GLOBALS['aos']->table('goods') . "
                            SET goods_number = goods_number - '" . $value . "'
                            WHERE goods_id = '" . $goods['goods_id'] . "' ";
                    $GLOBALS['db']->query($sql, 'SILENT');
                    break;
                }
            }
        }
    }

    return true;
}

/**
 * 删除发货单时进行退货
 *
 * @access   public
 * @param    int     $delivery_id      发货单id
 * @param    array   $delivery_order   发货单信息数组
 *
 * @return  void
 */
function delivery_return_goods($delivery_id, $delivery_order)
{
    $sql = "UPDATE " . $GLOBALS['aos']->table('order_info') .
           " SET shipping_status = '0' , order_status = 1".
           " WHERE order_id = '".$delivery_order['order_id']."' LIMIT 1";
    $GLOBALS['db']->query($sql);
}

/**
 * 删除发货单时删除其在订单中的发货单号
 *
 * @access   public
 * @param    int      $order_id              定单id
 * @param    string   $delivery_invoice_no   发货单号
 *
 * @return  void
 */
function del_order_invoice_no($order_id, $delivery_invoice_no)
{
    /* 查询：取得订单中的发货单号 */
    $sql = "SELECT invoice_no
            FROM " . $GLOBALS['aos']->table('order_info') . "
            WHERE order_id = '$order_id'";
    $order_invoice_no = $GLOBALS['db']->getOne($sql);

    /* 如果为空就结束处理 */
    if (empty($order_invoice_no))
    {
        return;
    }

    /* 去除当前发货单号 */
    $order_array = explode('<br>', $order_invoice_no);
    $delivery_array = explode('<br>', $delivery_invoice_no);

    foreach ($order_array as $key => $invoice_no)
    {
        if ($ii = array_search($invoice_no, $delivery_array))
        {
            unset($order_array[$key], $delivery_array[$ii]);
        }
    }

    $arr['invoice_no'] = implode('<br>', $order_array);
    update_order($order_id, $arr);
}

/**
 * 获取站点根目录网址
 *
 * @access  private
 * @return  Bool
 */
function get_site_root_url()
{
    return AOS_HTTP .$_SERVER['HTTP_HOST'] . str_replace('/' . ADMIN_PATH . '/index.php?act=order', '', PHP_SELF);

}
function send_key_shipping()
{
    /*------------------------------------------------------ */
//-- start一键发货
/*------------------------------------------------------ */
        
       $order_id = $_REQUEST['order_id'];
    
       $invoice_no = empty($_REQUEST['invoice_no']) ? '' : trim($_REQUEST['invoice_no']);  //快递单号
       $action_note = empty($_REQUEST['action_note']) ? '' : trim($_REQUEST['action_note']);  //备注
       $shipping_id=$_REQUEST['shipping_id'];
       if(empty($invoice_no)){
            $links[] = array('href' => 'index.php?act=order&op=info&order_id='.$order_id, 'text' => "返回");
            sys_msg("请填写快递单号", 0, $links);
       }
       $sql="select shipping_name,shipping_id from ".$GLOBALS['aos']->table('shipping')." where shipping_id = $shipping_id";
       $shipping_row=$GLOBALS['db']->getRow($sql);
       if(empty($shipping_row)){
            $links[] = array('href' => 'index.php?act=order&op=info&order_id='.$order_id, 'text' => "返回");
            sys_msg("请选择快递方式", 0, $links);
       }
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
        /* 查询：根据订单是否完成 检查权限 */
            if (order_finished($order))
            {
                //admin_priv('order_view_finished');
            }
            else
            {
                admin_priv('order_manage');
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
                $order['nickname'] = $user['nickname'];
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
            $delivery_order['nickname'] = $user['nickname'];
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

    /* 操作成功 */
    $links[] = array('text' => "发货单详情", 'href' => 'index.php?act=delivery&op=delivery_list');
    $links[] = array('text' => "订单详情", 'href' => 'index.php?act=order&op=info&order_id=' . $order_id);
    sys_msg("发货成功", 0, $links);
    
    }
}


?>