<?php

define('IN_ECS', true);

require_once(ROOT_PATH . 'source/library/order.php');
require_once(ROOT_PATH . 'source/library/goods.php');

$back_type_arr=array('0'=>'退货-退回', '1'=>'<font color=#ff3300>换货-退回</font>', '2'=>'<font color=#ff3300>换货-换出</font>', '4'=>'退款-无需退货');

$_REQUEST['act']=$_REQUEST['op'];
//echo date("Y-m-d H:i:s",gmtime());
admin_priv('back_manage');
/*------------------------------------------------------ */
//-- 搜索、排序、分页
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'back_query')
{
    /* 检查权限 */
    admin_priv('back_manage');

    $result = back_list();
    
	if(intval($_REQUEST['supp']) > 0){
    	$suppliers_list = get_supplier_list();
    	$smarty->assign('supp_list',   $suppliers_list);
    }

    $smarty->assign('back_list',   $result['back']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);

    $sort_flag = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);
    make_json_result($smarty->fetch('back_list_2.htm'), '', array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}
elseif ($operation == 'back_list')
{
    /* 检查权限 */
    admin_priv('back_manage');

    /* 查询 */
    $result = back_list();

    /* 模板赋值 */
    $smarty->assign('ur_here', '退货单列表');

    $smarty->assign('os_unconfirmed',   0);
    $smarty->assign('cs_await_pay',     1);
    $smarty->assign('cs_await_ship',    3);
    $smarty->assign('full_page',        1);

    $smarty->assign('back_list',   $result['back']);
    $smarty->assign('filter',       $result['filter']);
    //$smarty->assign('record_count', $result['record_count']);
    //$smarty->assign('page_count',   $result['page_count']);
    $smarty->assign('sort_update_time', '<img src="images/sort_desc.gif">');

    /* 分页 */

    $pager = get_page($result['filter']);

	$smarty->assign('pager',   $pager);
    $smarty->display('new_back_list.htm');
}
elseif ($operation == 'back_info')
{
    /* 检查权限 */
    admin_priv('back_manage');

    $back_id = intval(trim($_REQUEST['back_id']));

    /* 根据发货单id查询发货单信息 */
    if (!empty($back_id))
    {
        $back_order = back_order_info($back_id);
    }
    else
    {
        die('order does not exist');
    }

    if($back_order)
    {
        $base_order = $db->getRow("select * from ". $GLOBALS['aos']->table('order_info') ." where order_id='$back_order[order_id]' ");
        if($base_order)
        {
            $base_order['add_time'] =  local_date($GLOBALS['_CFG']['time_format'], $base_order['add_time']);
            $base_order['shipping_time'] =  local_date($GLOBALS['_CFG']['time_format'], $base_order['shipping_time']);
            $base_order['tel'] = $base_order['tel'] ? "电话：".$base_order['tel'] : "";
            $base_order['tel'] .= $base_order['tel'] ? "&nbsp;&nbsp;&nbsp;&nbsp;" : "";
            $base_order['tel'] .= $base_order['mobile'] ? "手机：".$base_order['mobile'] : "";
            /* 是否保价 */
            $base_order['insure_yn'] = $base_order['insure_fee']>0 ? 1 : 0;
            $smarty->assign('base_order', $base_order);
        }
    }
    else
    {
        die('order does not exist');
    }

    /* 获取原订单-商品信息 */
    $where = " where order_id ='$back_order[order_id]' " . ($back_order['back_type'] == 4 ? "" : " and o.goods_id='$back_order[goods_id]' ");
    $sql = "select o.*,g.product_sn from ". $GLOBALS['aos']->table('order_goods') ." as o left join ".$GLOBALS['aos']->table('goods_attr')." as g on o.attr_id =g.attr_id ".$where;
    $order_goods = $db->getAll($sql);
    $smarty->assign('order_goods', $order_goods);


    /* 取得用户名 */
    if ($back_order['user_id'] > 0)
    {
        $user = user_info($back_order['user_id']);
        if (!empty($user))
        {
            $back_order['user_name'] = $user['nickname'];
        }
    }
    $area = explode(',',$back_order['area']);
    
    $area['province'] = get_region_name($area['0']);
    $area['city'] = get_region_name($area['1']);
    $area['district'] = get_region_name($area['2']);
    $back_order['area']  = $area['province'].$area['city'].$area['district'];
    

    /* 取得退换货商品 */
    $goods_sql = "SELECT b.goods_name,b.goods_attr,b.goods_sn,g.product_sn,b.back_goods_number,b.back_goods_price,b.back_type FROM " . $GLOBALS['aos']->table('back_goods') . 
        " as b left join ".$GLOBALS['aos']->table('goods_attr')." as g on b.product_id = g.attr_id WHERE back_id = " . $back_order['back_id']." order by back_type asc";
    $res_list = $GLOBALS['db']->query($goods_sql );
    $goods_list = array();
    while ($row_list = $db->fetchRow($res_list))
    {
        $row_list['back_type_name'] = $back_type_arr[$row_list['back_type']];
        $row_list['back_goods_money'] = price_format($row_list['back_goods_price'] * $row_list['back_goods_number'], false);
        $goods_list[] = $row_list;
    }
    if ($back_order['imgs'])
    {
        $imgs = explode(",",$back_order['imgs']);   
    }
    $smarty->assign('imgs', $imgs);
    /* 模板赋值 */
    $smarty->assign('back_order', $back_order);
    $smarty->assign('exist_real_goods', $exist_real_goods);
    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('back_id', $back_id); // 发货单id
    
     /* 取得能执行的操作列表 */
    $operable_list = back_operable_list($back_order);
    $smarty->assign('operable_list', $operable_list);

    /* 取得订单操作记录 */
    $act_list = array();
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('back_action') . " WHERE back_id = '$back_id' ORDER BY log_time DESC,action_id DESC";
    $res_act = $db->query($sql);
    while ($row_act = $db->fetchRow($res_act))
    {
        $row_act['status_back']    = $_LANG['bos'][$row_act['status_back']];
        $row_act['status_refund']      = $_LANG['bps'][$row_act['status_refund']];
        $row_act['action_time']     = local_date($_CFG['time_format'], $row_act['log_time']);
        $act_list[] = $row_act;
    }
    $smarty->assign('action_list', $act_list);
   

    /* 显示模板 */
    $smarty->assign('ur_here', '退货单操作：查看');
    $smarty->assign('action_link', array('href' => 'index.php?act=back&op=back_list&' . list_link_postfix(), 'text' => '退货单列表'));
    
    $smarty->display('back_info.htm');
    exit; //
}

/* 操作 */
elseif ($_REQUEST['act'] == 'operate')
{
    /* 检查权限 */
    admin_priv('back_manage');	
	$back_id   = intval(trim($_REQUEST['back_id']));        // 退换货订单id
	$action_note    = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';

	/* 查询订单信息 */
    $order = back_order_info($back_id);
    $operable_list = back_operable_list($order);
    
	 /* 通过申请 */
    if (isset($_POST['ok']))
    {
        /* 检查能否操作 */
        
        if (!$operable_list['ok'])
        {
            die('Hacking attempt');
        }
    	$status_back='5';
    	update_back($back_id, $status_back, $status_refund);
        back_action($back_id, 0, $order['status_refund'],  $action_note);
    }

	 /* 拒绝申请 */
    if (isset($_POST['no']))
    {     
            
            if (!$operable_list['no'])
            {
                die('Hacking attempt');
            }
    		$status_back='6';
    		update_back($back_id, $status_back, $status_refund);
            back_action($back_id, $status_back, $order['status_refund'],  $action_note);
            $sql="select shipping_status from ".$GLOBALS['aos']->table('order_info')." where order_id = ".$order['order_id'];
	    	$shipping_status=$GLOBALS['db']->getOne($sql);
	    	if($shipping_status == 1 || $shipping_status == 2){
	    		$order_status = 5;
	    	}else{
	    		$order_status = 1;
	    	}
	    	update_order($order['order_id'], array('order_status' => $order_status));
	    	order_action($order['order_sn'], $order_status, 2, $shipping_status, "拒绝退款申请");
    }

	 /* 确认 */
    if (isset($_POST['confirm']))
    {   
       
        if (!$operable_list['confirm'])
        {
            die('Hacking attempt');
        }
		  $status_back='1';
		  update_back($back_id, $status_back, $status_refund);
          back_action($back_id, $status_back, $order['status_refund'],  $action_note);
    }
    /* 去退款 */
    elseif (isset($_POST['refund']))
    {
        
        if (!$operable_list['refund'])
        {
            die('Hacking attempt');
        }
		$smarty->assign('ur_here', '退款');
		$sql="select * from ".$aos->table('back_order')." where back_id='$back_id' ";
		$refund = $db->getRow($sql);
        $refund[refund_money_1]=$refund['refund_money_1']-$refund['shipping_fee'];
		$smarty->assign('back_id', $back_id);
		$smarty->assign('refund', $refund);
        $smarty->assign('act', 'back');
        $smarty->assign('op', 'operate_refund');
		//assign_query_info();
        $smarty->display('back_refund.htm');
		exit;
    }
	 /* 换出商品寄回 */
    if (isset($_POST['backshipping']))
    {
        
        if (!$operable_list['backshipping'])
        {
            die('Hacking attempt');
        }
		  $status_back='2';
		  update_back($back_id, $status_back, $status_refund);
          back_action($back_id, $status_back, $order['status_refund'],  $action_note);
    }
	 /* 完成退换货 */
    if (isset($_POST['backfinish']))
    {
       
        if (!$operable_list['backfinish'])
        {
            //die('Hacking attempt');
        }
		  $status_back='3';
		  update_back($back_id, $status_back, $status_refund);
          back_action($back_id, $status_back, $order['status_refund'],  $action_note);
    }
	 /* 售后 */
    if (isset($_POST['after_service']))
    {
		 /* 记录log */
          back_action($back_id, $order['status_back'], $order['status_refund'],  '[售后] ' . $action_note);
    }

	$links[] = array('text' => '返回退款/退货及维修详情', 'href' => 'index.php?act=back&op=back_info&back_id=' . $back_id);
    sys_msg('恭喜，成功操作！', 1, $links);

}

/* 操作--退款 */
elseif ($_REQUEST['act'] == 'operate_refund')
{
	 /* 检查权限 */	
	$status_refund = '1';
	$back_id   = intval(trim($_REQUEST['back_id']));        // 退换货订单id
	$action_note    = isset($_REQUEST['action_note']) ? trim($_REQUEST['action_note']) : '';
	$order = back_order_info($back_id);

    $refund_money_2 = $_REQUEST['refund_money_2'] + $_REQUEST['refund_shipping_fee'];
    if(bccomp($refund_money_2, $order['refund_money_1'], 2)==1){
        sys_msg("退款金额大于最大可退款金额");
    }
	$sql = "update ". $aos->table('back_goods') ." set status_refund='$status_refund'  where back_id='$back_id' and (back_type='0' or back_type='4') ";
	$db->query($sql);
	
	$refund_desc = $_REQUEST['refund_desc'] . ($_REQUEST['refund_shipping'] ? '\n（已退运费：'. $_REQUEST['refund_shipping_fee']. '）' : '');
	$sql2 = "update ". $aos->table('back_order') ." set  status_refund='$status_refund',  refund_money_2='$refund_money_2', refund_type='$_REQUEST[refund_type]', refund_desc='$refund_desc' where back_id='$back_id' ";
	$db->query($sql2);
    //记录日志
    back_action($back_id, $order['status_back'], $status_refund,  $action_note);
    if ($_REQUEST['refund_type'] == '2')
    {
        $desc_back = "订单". $order['order_id'] .'退款';
        $sql="select o.extension_code,g.goods_name,g.goods_id,o.user_id,o.surplus,o.pay_status,o.shipping_status,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.act_id,o.bonus_id,o.integral from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id  where  o.order_id=".$order['order_id'];
        $order_info= $GLOBALS['db']->getRow($sql);
        $r= refunds($order_info,$refund_money_2,'refund');
        return_user_surplus_integral_bonus($order_info);
        return_order_bonus($order['order_id']);
        if($r=='wei_true'){
            if(!empty($order_info['extension_code'])){
                $array=array();
                $array['tuan_status']='4';
                update_order($order['order_id'], $array);
            }
            
            back_action($back_id, $order['status_back'], $status_refund,  '微信自动退款成功');
            //退款通知
            global $admin_wechat;
            $refund_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
            $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$order['order_id'];
            $refund_price='￥'.$refund_money_2;
            
            $openid=getOpenid($order['user_id']);
            $message=getMessage(6);
            $wx_title = "退款成功通知";
            $wx_desc = $message[title]."\r\n退款商品：".$order[goods_name]."\r\n退款金额：".$refund_price."\r\n退款时间：".$refund_time."\r\n".$message[note];
            //$wx_pic = $aos_url;
            $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        
        }elseif($r=='ali_true'){
            back_action($back_id, $order['status_back'], $status_refund,  '支付宝支付，未退款');
        }
        
        if ($GLOBALS['_CFG']['use_storage'] == '1'){
            change_order_goods_storage($order_info['order_id'], false, 1);
        }
   
    }

	/* 退回用户余额 
	if ($_REQUEST['refund_type'] == '1')
	{
		$desc_back = "订单". $order['order_id'] .'退款';
	    log_account_change($order['user_id'], $refund_money_2,0,0,0, $desc_back );
		//是否开启余额变动给客户发短信-退款
		if($_CFG['sms_user_money_change'] == 1)
		{
			$sql = "SELECT user_money,mobile_phone FROM " . $GLOBALS['aos']->table('users') . " WHERE user_id = '" . $order['user_id'] . "'";
			$users = $GLOBALS['db']->getRow($sql); 
			$content = sprintf($_CFG['sms_return_goods_tpl'],$refund_money_2,$users['user_money'],$_CFG['sms_sign']);
			if($users['mobile_phone'])
			{
				include_once('../send.php');
				sendSMS($users['mobile_phone'],$content);
			}
		}
	}*/

    /* 记录log */
	
	$links[] = array('text' => '返回退款/退货及维修详情', 'href' => 'index.php?act=back&op=back_info&back_id=' . $back_id);
    sys_msg('恭喜，成功操作！', 1, $links);
}

/* 删除退换货订单 */
elseif ($_REQUEST['act'] == 'remove_back')
{
		$back_id = $_REQUEST['back_id'];
        /* 删除退货单 */
        if(is_array($back_id))
        {
			$back_id_list = implode(",", $back_id);
            $sql = "DELETE FROM ".$aos->table('back_order'). " WHERE back_id in ($back_id_list)";
            $db->query($sql);    
			$sql = "DELETE FROM ".$aos->table('back_goods'). " WHERE back_id in ($back_id_list)";
            $db->query($sql);
        }
        else
        {
            $sql = "DELETE FROM ".$aos->table('back_order'). " WHERE back_id = '$back_id'";			
            $db->query($sql);
			$sql = "DELETE FROM ".$aos->table('back_goods'). " WHERE back_id = '$back_id'";			
            $db->query($sql);
        }
		//echo $sql;

        /* 返回 */		
        sys_msg('恭喜，记录删除成功！', 0, array(array('href'=>'index.php?act=back&op=back_list' , 'text' =>'返回退款/退货及维修列表')));
}

/* 回复客户留言 */
elseif ($_REQUEST['act'] == 'replay')
{
		$back_id = intval($_REQUEST['back_id']);
		$message = $_POST['message'];
		$add_time = gmtime();
		
		$db->query("INSERT INTO ".$aos->table('back_replay')." (back_id, message, add_time) VALUES ('$back_id', '$message', '$add_time')");
		
        sys_msg('恭喜，回复成功！', 0, array(array('href'=>'index.php?act=back&op=back_info&back_id='.$back_id , 'text' =>'返回')));	
        
}


/**
 * 取得退货单信息
 * @param   int     $back_id   退货单 id（如果 back_id > 0 就按 id 查，否则按 sn 查）
 * @return  array   退货单信息（金额都有相应格式化的字段，前缀是 formated_ ）
 */
function back_order_info($back_id)
{
    $return_order = array();
    if (empty($back_id) || !is_numeric($back_id))
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

    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('back_order') . "
            WHERE back_id = '$back_id'
            $where
            LIMIT 0, 1";
    $back = $GLOBALS['db']->getRow($sql);
    if ($back)
    {
        /* 格式化金额字段 */
        $back['formated_insure_fee']     = price_format($back['insure_fee'], false);
        $back['formated_shipping_fee']   = price_format($back['shipping_fee'], false);

        /* 格式化时间字段 */
        $back['formated_add_time']       = local_date($GLOBALS['_CFG']['time_format'], $back['add_time']);
        $back['formated_update_time']    = local_date($GLOBALS['_CFG']['time_format'], $back['update_time']);
        $back['formated_return_time']    = local_date($GLOBALS['_CFG']['time_format'], $back['return_time']);

        $return_order = $back;
    }

    return $return_order;
}


/**
 * 返回某个订单可执行的操作列表
 */
function back_operable_list($order)
{	
	$os = $order['status_back'];
	$ds = $order['status_refund'];
	/* 根据状态返回可执行操作 */
    $list = array(  'ok'           => true,
					'no'           => true,
					'confirm'      => true,
					'refund'       => true,
					'backshipping' => true,
					'backfinish'   => true );
	if ($os != 5)
	{
		$list['ok']=false;
		$list['no']=false;
	}
	if ($os == '1' || $os == '2' || $os == '3' || $ds == '1')
	{
		$list['confirm']=false; //操作收到用户寄回商品
		if ($os=='2')
		{
			$list['backshipping']=false;
		}
		if ($os=='3')
		{
			$list['refund']=false; //去退款
			$list['backshipping']=false; //换出商品寄出
			$list['backfinish']=false;  //完成
		}
	}
	if($ds=='9' || $ds=='1')
	{
		$list['refund']=false; //去退款
	}
	return $list;
}

/* 更新退换货订单状态 */
function update_back($back_id, $status_back, $status_refund )
{
	$setsql = "";
	if ($status_back)
	{
		$setsql .= $setsql ? "," : "";
		$setsql .= "status_back='$status_back'";
	}
	if ($status_refund)
	{
		$setsql .= $setsql ? "," : "";
		$setsql .= "status_refund='$status_refund'";
	}
	$sql = "update ". $GLOBALS['aos']->table('back_order') ." set  $setsql where back_id='$back_id' ";
	$GLOBALS['db']->query($sql);

	if($status_back =='5') //通过申请
	{
	   $status_b = $GLOBALS['db']->getOne("select back_type from " . $GLOBALS['aos']->table('back_order') . " where back_id='$back_id'");
	   $status_b = ($status_b == 4) ? 4 : 0;
	   $status_bo = $GLOBALS['db']->getOne("select order_sn from " . $GLOBALS['aos']->table('back_order') . " where back_id='$back_id'");
	   $close_order = $GLOBALS['db']->getOne("select shipping_status from " . $GLOBALS['aos']->table('order_info') . " where order_sn = '" . $status_bo . "'");
	   /*if ($close_order < 1)
	   {
		   $sql3="update ". $GLOBALS['aos']->table('order_info') ." set order_status='2', to_buyer='用户对订单内的部分或全部商品申请退款并取消订单' where order_sn = '" . $status_bo . "'";
		   $GLOBALS['db']->query($sql3);
	   }*/
	   
	   $sql="update ". $GLOBALS['aos']->table('back_goods') ." set status_back='$status_b' where back_id='$back_id' ";
	   $GLOBALS['db']->query($sql);
	   $sql2="update ". $GLOBALS['aos']->table('back_order') ." set status_back='$status_b' where back_id='$back_id' ";
	   $GLOBALS['db']->query($sql2);
	}
	if($status_back =='6') //拒绝申请
	{
	   $sql="update ". $GLOBALS['aos']->table('back_goods') ." set status_back='$status_back' where back_id='$back_id' ";
	   $GLOBALS['db']->query($sql);
	   $sql2="update ". $GLOBALS['aos']->table('back_order') ." set status_back='$status_back' where back_id='$back_id' ";
	   $GLOBALS['db']->query($sql2);
	}

	if($status_back =='1' or $status_back =='3') //收到退换回的货物，完成退换货
	{
	   $sql="update ". $GLOBALS['aos']->table('back_goods') ." set status_back='$status_back' where back_id='$back_id' ";
	   $GLOBALS['db']->query($sql);
	   $sql2="UPDATE ". $GLOBALS['aos']->table('back_order') ." SET status_back='$status_back' WHERE back_id='$back_id' ";
	   $GLOBALS['db']->query($sql2);
	   
	   $get_order_id = $GLOBALS['db']->getOne("SELECT order_id FROM " . $GLOBALS['aos']->table('back_order') . " WHERE back_id = '" . $back_id . "'");
	   $get_goods_id = $GLOBALS['db']->getCol("SELECT goods_id FROM " . $GLOBALS['aos']->table('back_order') . " WHERE order_id = '" . $get_order_id . "' AND status_back = '3' AND back_type <> '3'");
	   /*if (count($get_goods_id) > 0)
	   {
    	   
        	$sql3="UPDATE ". $GLOBALS['aos']->table('order_info') ." SET order_status='2' WHERE order_id='" . $get_order_id . "' ";
        	$GLOBALS['db']->query($sql3);
    	   
	   }*/
	   $get_goods_info = $GLOBALS['db']->getRow("SELECT goods_id, back_type FROM " . $GLOBALS['aos']->table('back_goods') . " WHERE back_id = '" . $back_id . "'");
	   if ($status_back == '3' && $get_goods_info['back_type'] != '3') // 退款退货完成时，改变订单中商品的is_back值
	   {
	       //$sql4 = "UPDATE " .$GLOBALS['aos']->table('order_goods') . " SET is_back = 1 WHERE goods_id = '" . $get_goods_info['goods_id'] . "' AND order_id = '" . $get_order_id . "'";
	       //$GLOBALS['db']->query($sql4);
		   
		   //退款完成后，进行返库
		   /*$back_type = $GLOBALS['db']->getOne("SELECT back_type FROM " . $GLOBALS['aos']->table('back_order') . " WHERE back_id = '" . $back_id . "'");
		   $stock_dec_time = $GLOBALS['db']->getOne("SELECT value FROM " . $GLOBALS['aos']->table('shop_config') . " WHERE code =  'stock_dec_time'");
		   if ($back_type == 4 && $stock_dec_time == 1)
		   {
			   $back_go = $GLOBALS['db']->getAll("SELECT * FROM " . $GLOBALS['aos']->table('order_goods') . " WHERE order_id = " . $get_order_id);
			   foreach($back_go as $back_g)
			   {
				   if ($back_g['product_id'] > 0)
				   {
					   $GLOBALS['db']->query("UPDATE " . $GLOBALS['aos']->table('products') . " SET product_number = product_number + " . $back_g['goods_number'] . " WHERE product_id = " . $back_g['product_id']);
				   }
					$GLOBALS['db']->query("UPDATE " . $GLOBALS['aos']->table('goods') . " SET goods_number = goods_number + " . $back_g['goods_number'] . " WHERE goods_id = " . $back_g['goods_id']);
			   }
		   }*/
	   }
	}
	if($status_back =='2') //换出商品寄回
	{
	   $sql="update ". $GLOBALS['aos']->table('back_goods') ." set status_back='$status_back' where back_type in(1,2,3) and back_id='$back_id' ";
	   $GLOBALS['db']->query($sql);
	}
	if($status_refund=='1') //退款
	{
	   $sql="update ". $GLOBALS['aos']->table('back_goods') ." set status_refund='$status_refund' where back_type ='0' and back_id='$back_id' ";
	   $GLOBALS['db']->query($sql);
	}
}

function back_action($back_id, $status_back, $status_refund,  $note = '', $username = null)
{
    if (is_null($username))
    {
        $username = $_SESSION['admin_name'];
    }

    $sql = 'INSERT INTO ' . $GLOBALS['aos']->table('back_action') .
                ' (back_id, action_user, status_back, status_refund,  action_note, log_time) ' .
            'SELECT ' .
                "$back_id, '$username', '$status_back', '$status_refund',  '$note', '" .gmtime() . "' " .
            'FROM ' . $GLOBALS['aos']->table('back_order') . " WHERE back_id = '$back_id'";
    $GLOBALS['db']->query($sql);
}

/**
 *  获取退货单列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function back_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

        /* 过滤信息 */
        $filter['delivery_sn'] = empty($_REQUEST['delivery_sn']) ? '' : trim($_REQUEST['delivery_sn']);
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['order_id'] = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
        if ($aiax == 1 && !empty($_REQUEST['consignee']))
        {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }
        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
        $where = 'WHERE 1 ';
        if ($filter['order_sn'])
        {
            $where .= " AND order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['consignee'])
        {
            $where .= " AND consignee LIKE '%" . mysql_like_quote($filter['consignee']) . "%'";
        }
        if ($filter['delivery_sn'])
        {
            $where .= " AND delivery_sn LIKE '%" . mysql_like_quote($filter['delivery_sn']) . "%'";
        }

        /* 获取管理员信息 */
        $admin_info = admin_info();

        /* 如果管理员属于某个门店，只列出这个门店管辖的发货单 */
        if ($admin_info['store_id'] > 0)
        {
            $where .= " AND store_id = '" . $admin_info['store_id'] . "' ";
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
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('back_order') . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT *
                FROM " . $GLOBALS['aos']->table("back_order") . "
                $where
                ORDER BY add_time DESC
                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式化数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['return_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['return_time']);
        $row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $row[$key]['update_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['update_time']);
        $row[$key]['status_back_val'] = $GLOBALS['_LANG']['bos'][(($value['back_type'] == 4) ? $value['back_type'] : $value['status_back'])]."-" . (($value['back_type'] == 3) ? "申请维修" : $GLOBALS['_LANG']['bps'][$value['status_refund']]);
        if ($value['status'] == 1)
        {
            $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][1];
        }
        else
        {
        $row[$key]['status_name'] = $GLOBALS['_LANG']['delivery_status'][0];
        }
    }
    $arr = array('back' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 取得入驻商列表
 * @return array    二维数组
 */
function get_supplier_list()
{
    $sql = 'SELECT *
            FROM ' . $GLOBALS['aos']->table('supplier') . '
            WHERE status=1 
            ORDER BY supplier_name ASC';
    $res = $GLOBALS['db']->getAll($sql);

    if (!is_array($res))
    {
        $res = array();
    }

    return $res;
}

?>