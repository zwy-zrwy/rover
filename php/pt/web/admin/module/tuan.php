<?php

define('IN_AOS', true);

require_once(ROOT_PATH . 'source/library/order.php');
require_once(ROOT_PATH . 'source/library/goods.php');
admin_priv('tuan_manage');
if ($operation == 'tuan_list')
{
    /* 检查权限 */
    

    $tuan_list = tuan_list();
    //print_r($tuan_list['orders']);
    $smarty->assign('tuan_list',    $tuan_list['orders']);
    $smarty->assign('filter',       $tuan_list['filter']);

    /* 分页 */
    
    $pager = get_page($tuan_list['filter']);

    $smarty->assign('pager',   $pager);
    /* 显示模板 */
    
    $smarty->display('tuan_list.htm');
}


/*团详情*/
elseif ($operation == 'tuan_info')
{
    if (isset($_REQUEST['extension_id']))
    {
        $extension_id = trim($_REQUEST['extension_id']);
        $tuan_info = tuan_info($extension_id);
        //print_r($tuan_info);
        foreach($tuan_info as $key=>$value){
        	$tuan_info[$key]['status']        = $_LANG['os'][$value['order_status']] . ',' . $_LANG['ps'][$value['pay_status']] . ',' . $_LANG['ss'][$value['shipping_status']];
        }

        $smarty->assign('tuan_info',   $tuan_info);
        //print_r($tuan_info);
    }
    else
    {
        /* 如果参数不存在，退出 */
        die('invalid parameter');
    }
    $smarty->display('tuan_info.htm');
}

/*团详情*/
elseif ($operation == 'tuan_refund')
{
    if (isset($_REQUEST['extension_id']))
    {
    	$extension_id = trim($_REQUEST['extension_id']);

    	$sql="select order_id from ".$GLOBALS['aos']->table('order_info')." where tuan_first = 1 and order_status = 1 and extension_code != '' and extension_id = '$extension_id' and pay_status = 2";
    	$res=$GLOBALS['db']->getOne($sql);
    	if(!$res){
    		$links[] = array('text' => '返回', 'href' => 'index.php?act=tuan&op=tuan_info&extension_id=' . $extension_id);
    		sys_msg('不能操作！', 1, $links);
    	}
    	$sql="select g.goods_name,g.goods_id,o.user_id,o.surplus,o.pay_status,o.shipping_status,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.bonus_id,o.integral from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id  where  o.extension_id=".$extension_id." and o.order_status in (0,1) and o.tuan_status in (0,1,2)";
        $order_info= $GLOBALS['db']->getAll($sql);
        foreach($order_info as $vo){
        	$arr=array();
                return_user_surplus_integral_bonus($vo);
                return_order_bonus($vo['order_id']);
                if($vo['pay_status']!=2){
                	$arr['tuan_status']  = 4;
                    $arr['order_status']  = 3;
                    update_order($vo['order_id'], $arr);
                    order_action($vo['order_sn'], $arr['order_status'], $vo['shipping_status'], $vo['pay_status'], '成团未支付订单设无效', '');
                }else{
                	$arr['tuan_status']  = 4;
                    update_order($vo['order_id'], $arr);
                    if ($GLOBALS['_CFG']['use_storage'] == '1'){
                        change_order_goods_storage($vo['order_id'], false, 2);
                    }
                    $r= refunds($vo,$vo['money_paid'],'refund');
                    if($r=='wei_true'){
                        //退款通知
                        global $admin_wechat;
                        $refund_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
                        $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$vo['order_id'];
                        $refund_price='￥'.$vo[money_paid];
                        
                        $openid=getOpenid($vo['user_id']);
                        $message=getMessage(6);
                        $wx_title = "退款成功通知";
                        $wx_desc = $message[title]."\r\n退款商品：".$vo[goods_name]."\r\n退款金额：".$refund_price."\r\n退款时间：".$refund_time."\r\n".$message[note];
                        //$wx_pic = $aos_url;
                        $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
    		        }
                }
            
        }
        $links[] = array('text' => '返回', 'href' => 'index.php?act=tuan&op=tuan_info&extension_id=' . $extension_id);
    		sys_msg('操作成功！', 1, $links);
                
        
        //print_r($tuan_info);
    }
    else
    {
        /* 如果参数不存在，退出 */
        die('invalid parameter');
    }
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
 *  获取团购订单列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */

function tuan_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['extension_id'] = empty($_REQUEST['extension_id']) ? '' : trim($_REQUEST['extension_id']);
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
        {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }
        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
        $filter['composite_status'] = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : -1;
        $where = "WHERE o.extension_code in ('tuan','miao','lottery') AND o.tuan_first = 1 ";
        if ($filter['extension_id'])
        {
            $where .= " AND o.extension_id LIKE '%" . mysql_like_quote($filter['extension_id']) . "%'";
        }
        if ($filter['user_id'])
        {
            $where .= " AND o.user_id = '$filter[user_id]'";
        }
        if ($filter['composite_status'] != -1)
        {
            $where .= " AND o.tuan_status = '$filter[composite_status]'";
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
        $sql = "SELECT o.order_id, o.pay_time, o.extension_code, o.extension_id, o.tuan_status, o.tuan_num, g.goods_name, u.nickname AS buyer ".
                " FROM " . $GLOBALS['aos']->table('order_info') . " AS o " .
                " LEFT JOIN " .$GLOBALS['aos']->table('order_goods'). " AS g ON g.order_id=o.order_id ".
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
        $row[$key]['start_time'] = local_date('m-d H:i', $value['pay_time']);
        if($value['pay_time']){
            $row[$key]['end_time'] = local_date('m-d H:i', $value['pay_time'] + $GLOBALS['_CFG']['tuan_time']*3600);
        }
        $tuan_mem_num = get_tuan_mem_num($value['extension_id']);//参团人数
        $row[$key]['difference'] = $value['tuan_num'] - $tuan_mem_num; //还差
    }
    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>