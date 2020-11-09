<?php

/* 首页文件*/
if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
include_once(ROOT_PATH .'source/library/user.php');
include_once(ROOT_PATH .'source/library/order.php');
$user_id = $_SESSION['user_id'];
$smarty->assign('user_id',        $user_id);

if($action == 'index'){
    $info = get_user_default($user_id);
    //获取推广商品数量
    $sql='SELECT count(goods_id) ' .
            'FROM ' . $GLOBALS['aos']->table('goods') .
            "WHERE is_on_sale = 1 AND is_delete = 0 AND is_dist = 1";
    $num['depot'] = $GLOBALS['db']->getOne($sql);

    //获取推广订单数量
    $sql = "SELECT count(order_id) FROM " .$GLOBALS['aos']->table('order_info') ." WHERE parent_id = $user_id";
    $num['order'] = $GLOBALS['db']->getOne($sql);

    //获取用户粉丝数量
    $sql='SELECT count(user_id) ' .
        'FROM ' . $GLOBALS['aos']->table('users')." WHERE parent_id = $user_id";
    $num['fans'] = $GLOBALS['db']->getOne($sql);
    $dist_amount = get_user_dist($user_id);
    $smarty->assign('dist_amount', price_format($dist_amount, false));
    $smarty->assign('info',        $info);
    $smarty->assign('num',        $num);
    $smarty->display('dist_index.htm');
}
//推广仓库
elseif($action == 'depot'){
    $smarty->display('dist_depot.htm');
}
elseif($action == 'depot_ajax'){
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
    $limit = " limit $last,$amount";//每次加载的个数
    $goodslist = get_dist_goods($limit);
    foreach($goodslist['goods'] as $val){
        $GLOBALS['smarty']->assign('page',$page);
        $GLOBALS['smarty']->assign('goods',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/depot_list.htm');
    }
    $res['count']=$goodslist['count'];
    die(json_encode($res));
}
//我的粉丝
elseif($action == 'fans'){
    $smarty->display('dist_fans.htm');
}
elseif($action == 'fans_ajax'){
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
    $limit = " limit $last,$amount";//每次加载的个数
    $fanslist = get_fans_list($user_id,$limit);
    foreach($fanslist['fans'] as $val){
        $GLOBALS['smarty']->assign('page',$page);
        $GLOBALS['smarty']->assign('fans',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/fans_list.htm');
    }
    $res['count']=$fanslist['count'];
    die(json_encode($res));
}
//推广订单
elseif($action == 'order'){
    $status = $_REQUEST['status'] ? $_REQUEST['status'] : 'all';
    $smarty->assign('status',  $status);
    $smarty->display('dist_order.htm');
}
elseif ($action == 'order_ajax')
{
    $status = $_REQUEST['status'] ? $_REQUEST['status'] : 'all';
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $orderslist = get_dist_orders($user_id, $status, $limit);
    $res['count'] = $orderslist['count'];
    foreach($orderslist['info'] as $val){
        $GLOBALS['smarty']->assign('item',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/dist_order_list.htm');
    }
    die(json_encode($res));
}
//我的佣金
elseif($action == 'comm'){
    $account_log = array();
    
    $dist_amount = get_user_dist($user_id);
    $sql = "SELECT dist_money,change_time,change_desc FROM " . $aos->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND dist_money <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, 10, 0);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time']   = local_date('Y-m-d H:i:s', $row['change_time']);
        
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[dist_money];
        $account_log[] = $row;
    }
    $smarty->assign('surplus_amount', price_format($dist_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->display('dist_comm.htm');
}
//佣金明细
elseif($action == 'detail'){
    $smarty->display('dist_detail.htm');
}
//佣金提现
elseif($action == 'cash'){
    $surplus_amount = get_user_dist($user_id);
    //获取余额记录
    $account_log = get_dist_log($user_id, 10, 0);
    $smarty->assign('account_log',    $account_log['info']);
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->display('dist_comm.htm');
}
elseif ($action == 'do_cash')
{
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $account_log = get_dist_log($user_id,$amount,$last);
    $res=array();
    foreach($account_log['info'] as $item){
        $GLOBALS['smarty']->assign('action',$action);
        $GLOBALS['smarty']->assign('item',$item);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/user_dist_comm.htm');
    }
    $res['count']=$account_log['count'];
    die(json_encode($res));
}
elseif ($action == 'do_comm')
{
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
    
    $result=array();
    $sql = "SELECT dist_money,change_time,change_desc FROM " . $aos->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND dist_money <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $amount, $last);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time']   = local_date('Y-m-d H:i:s', $row['change_time']);
        
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[dist_money];
        $GLOBALS['smarty']->assign('action',$action);
        $GLOBALS['smarty']->assign('item',$row);
        $result['info'][]  = $GLOBALS['smarty']->fetch('inc/user_dist_comm.htm');
    }
    
    $result['count']=$db->getOne("select count(*) from ".$aos->table('account_log')." where user_id = '$user_id' AND dist_money <> 0");
    
    die(json_encode($result));
}
//我的名片
elseif($action == 'card'){
    $smarty->display('dist_card.htm');
}
//排行榜
elseif($action == 'rank'){
    $smarty->display('dist_rank.htm');
}
elseif($action == 'account_raply'){
    $surplus_amount = get_user_dist($user_id);
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->display('dist_comm.htm');
}
elseif($action=='act_account')
{
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount < 1)
    {
        show_message('最低提现金额1元');
    }
    $user_note=isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '';
    if (empty($user_note))
    {
        show_message('请填写备注');
    }
    /* 判断是否有足够的余额的进行退款的操作 */
    $sur_amount = get_user_dist($user_id);
    if ($amount > $sur_amount)
    {
        $content = '您要申请提现的金额超过了您现有的余额，此操作将不可进行！';
        show_message($content, '返回上一页', '', 'info');
    }
    /* 变量初始化 */
    $dists = array(
            'user_id'      => $user_id,
            'process_type' => 2,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => $user_note,
            'amount'       => $amount,
    );
    //插入会员账目明细
    $amount = '-'.$amount;
    $dists['payment'] = '';
    $dists['rec_id']  = insert_user_account($dists, $amount);

    /* 如果成功提交 */
    if ($dists['rec_id'] > 0)
    {
        include_once('source/library/wxpay/wxpay.class.php');
        $pay_obj    = new wxpay;
        $payment = payment_info(2);
        
        $pay=unserialize_config($payment['pay_config']);
        $pay['openid']=getOpenid($user_id);
        $pay['amout']=$dists['amount']*100;
        $pay['trade_no']=$dists['rec_id'];
        $pay['desc']=$dists['user_note'];
        $result=json_decode($pay_obj->rebate($pay),true);
        
        if($result['return_code']=='SUCCESS' && $result['result_code']=='SUCCESS'){
            $sql="UPDATE ".$aos->table('user_account')." set is_paid = 1 where id = $dists[rec_id]";
            $res=$db->query($sql);
            if($res){
                log_account_change($user_id, 0, 0, 0, 0, '佣金提现',99,$amount);
                show_message('提现成功');
            }
        }else{
            show_message('提现失败');
        }
        
    }
    else
    {
        $content = '此次操作失败，请返回重试！';
        show_message($content, '返回上一页', '', 'info');
    }exit;
    
}

function get_dist_goods($limit = 1)
{
    $where = "is_on_sale = 1 AND is_delete = 0 AND is_dist = 1 ";

    /* 获得商品列表 */
    $sql='SELECT count(*) ' .
            'FROM ' . $GLOBALS['aos']->table('goods') .
            "WHERE $where ";
    $count=$GLOBALS['db']->getOne($sql);

   $sql="select goods_id, goods_name, goods_img, shop_price,comm_shop_price,comm_tuan_price from ". $GLOBALS['aos']->table('goods').
" WHERE $where ORDER BY goods_id DESC $limit ";
    $result = $GLOBALS['db']->getAll($sql);
    $res=array();
    foreach ($result AS $idx => $row)
    {
        $goods[$idx]['goods_id']           = $row['goods_id'];
        $goods[$idx]['goods_name']         = $row['goods_name'];
        $goods[$idx]['shop_price']   = price_format($row['shop_price']);
        $tuan_price_list = get_tuan_price_list($row['goods_id']);
        $goods[$idx]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
        $goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $goods[$idx]['comm_shop_price']   = price_format($row['comm_shop_price']);
        $goods[$idx]['comm_tuan_price']   = price_format($row['comm_tuan_price']);
        $goods[$idx]['url']          = 'index.php?c=goods&id='.$row['goods_id'];
    }
    $res['goods']=$goods;
    $res['count']=$count;
    return $res;
}

function get_fans_list($user_id,$limit = 1)
{
    $where = "parent_id = $user_id ";

    /* 获得商品列表 */
    $sql='SELECT count(*) ' .
        'FROM ' . $GLOBALS['aos']->table('users')." WHERE $where ";
    $count=$GLOBALS['db']->getOne($sql);

    $sql="select user_id, nickname from ". $GLOBALS['aos']->table('users') ." WHERE $where ORDER BY user_id DESC $limit ";
    $result = $GLOBALS['db']->getAll($sql);
    $res=array();
    foreach ($result AS $idx => $row)
    {
        $fans[$idx]['user_id']       = $row['user_id'];
        $fans[$idx]['nickname']      = $row['nickname'];
        $fans[$idx]['headimgurl']    = getAvatar($row['user_id']);
    }
    $res['fans']=$fans;
    $res['count']=$count;
    return $res;
}

function get_dist_orders($user_id, $status, $limit)
{
    $where = "parent_id = '$user_id'";
    //综合状态
    switch($status)
    {
        case 'await_comm' : //未分佣
            $where .= "  and pay_status = 2 and is_dist = 0 and order_status != 4 and tuan_status != 4";
            break;
        case 'finish_comm' : //已分佣
            $where .= " and is_dist = 1";
            break;
        case 'unfinished' : //未完成
            $where .= " and pay_status = 0 and is_dist = 0 ";
            break;

        default:
    }

    /*取得订单总量*/

    $sql = "SELECT count(order_id) FROM " .$GLOBALS['aos']->table('order_info') ." WHERE $where ";

    $count = $GLOBALS['db']->getOne($sql);

    /* 取得订单列表 */
    $sql = "SELECT order_id, order_sn, order_status,shipping_status, pay_status, add_time, pay_id, order_amount, extension_code, extension_id, tuan_status, comment,tuan_num, shipping_fee, " .
           "(goods_amount + shipping_fee) AS total_fee,is_dist ".
           " FROM " .$GLOBALS['aos']->table('order_info') .
           " WHERE $where ORDER BY add_time DESC $limit";
    $res = $GLOBALS['db']->query($sql);
    $result    = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $order_goods = order_goods($row['order_id']);
        $row['goods_number'] = $order_goods['goods_number'];
        $row['goods_name'] = $order_goods['goods_name'];
        $row['goods_id'] = $order_goods['goods_id'];
        $row['goods_attr'] = $order_goods['goods_attr'];
        $row['goods_img'] = get_goods_img($order_goods['goods_id']);
        $row['goods_price'] = price_format($order_goods['goods_price']);
        $goods_comm = goods_comm($row['goods_id']);
        if($row['extension_code'] == 'tuan')
        {
            $row['goods_comm'] = price_format($goods_comm['comm_tuan_price']);
        }
        else
        {
            $row['goods_comm'] = price_format($goods_comm['comm_shop_price']);
        }

        if($row['order_status'] == 1 && $row['shipping_status'] == 0 && $row['pay_status'] == 0)
        {
            $row['order_status_name'] = '待支付';
        }
        elseif($row['order_status'] == 3 && $row['shipping_status'] == 0 && $row['pay_status'] == 0)
        {
            $row['order_status_name'] = '失效';
        }
        elseif($row['order_status'] == 2 && $row['shipping_status'] == 0 && $row['pay_status'] == 0)
        {
            $row['order_status_name'] = '交易已取消';
        }
        elseif($row['order_status'] == 4 && $row['pay_status'] == 2)
        {
            $row['order_status_name'] = '退款';
        }
        elseif($row['order_status'] == 1 && $row['shipping_status'] == 0 && $row['pay_status'] == 2)
        {
            if($row['extension_code'] == 'tuan')
            {
                if($row['tuan_status'] == 1)
                {
                    $row['order_status_name'] = '拼团中';
                }
                elseif($row['tuan_status'] == 2)
                {

                    if($row['shipping_id'] == 1)
                    {
                        $row['order_status_name'] = '已成团，待核销';
                    }
                    else
                    {

                        $row['order_status_name'] = '已成团，待发货';
                    }
                }
                elseif($row['tuan_status'] == 3)
                {
                    $row['order_status_name'] = '团失败，待退款';
                }
                elseif($row['tuan_status'] == 4)
                {
                    $row['order_status_name'] = '团失败，已退款';
                }
            }
            else
            {
                if($row['shipping_id'] == 1)
                {
                    $row['order_status_name'] = '待核销';
                }
                else
                {
                    $row['order_status_name'] = '待发货';
                }
            }
        }
        elseif($row['order_status'] == 5 && $row['shipping_status'] == 1 && $row['pay_status'] == 2){
            $row['order_status_name'] = '待收货';
        }
        elseif($row['order_status'] == 5 && $row['shipping_status'] == 2 && $row['pay_status'] == 2 && $row['comment'] == 0){
            $row['order_status_name'] = '待评价';
        }
        elseif($row['order_status'] == 5 && $row['shipping_status'] == 2 && $row['pay_status'] == 2 && $row['comment'] == 1){
            $row['order_status_name'] = '已完成';
        }


        $result['info'][] = array(
            'order_id'       => $row['order_id'],
            'order_sn'       => $row['order_sn'],
            'goods_number'    => $row['goods_number'],
           'goods_name'    => $row['goods_name'],
           'goods_img'    => $row['goods_img'],
           'goods_attr'    => $row['goods_attr'],
           'goods_price'    => $row['goods_price'],
           'goods_comm'    => $row['goods_comm'],
           'goods_url'    => $row['goods_url'],
           'is_dist'    => $row['is_dist'],
           'order_num'      => $row['order_num'],
           'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),
           'order_status'   => $row['order_status'],
           'order_status_name'   => $row['order_status_name'],
           'shipping_status'=> $row['shipping_status'],
           'total_fee'      => price_format($row['total_fee'], false),
           'order_amount'      => price_format($row['order_amount'], false),
           'pay_status'     =>$row['pay_status'],
           'log_id'         =>$row['log_id'],
           'pay_id'         =>$row['pay_id'],
           'goods_id'         =>$row['goods_id']
        );
    }
    $result['count']  = $count;
    return $result;
}
function get_user_dist($user_id)
{
    $sql = "SELECT SUM(dist_money) FROM " .$GLOBALS['aos']->table('account_log').
           " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getOne($sql);
}
/**
 * 查询会员余额的操作记录
 *
 * @access  public
 * @param   int     $user_id    会员ID
 * @param   int     $num        每页显示数量
 * @param   int     $start      开始显示的条数
 * @return  array
 */
function get_dist_log($user_id, $num, $start)
{
    $account_log = array();
    $sql = 'SELECT count(*) FROM ' .$GLOBALS['aos']->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type =2 ".
           " ORDER BY add_time DESC";
    $count=$GLOBALS['db']->getOne($sql);
    $sql = 'SELECT * FROM ' .$GLOBALS['aos']->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type =2 ".
           " ORDER BY add_time DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $num, $start);

    if ($res)
    {
        while ($rows = $GLOBALS['db']->fetchRow($res))
        {
            $rows['add_time']         = local_date($GLOBALS['_CFG']['date_format'], $rows['add_time']);
            $rows['admin_note']       = nl2br(htmlspecialchars($rows['admin_note']));
            $rows['short_admin_note'] = ($rows['admin_note'] > '') ? sub_str($rows['admin_note'], 30) : 'N/A';
            $rows['user_note']        = nl2br(htmlspecialchars($rows['user_note']));
            $rows['short_user_note']  = ($rows['user_note'] > '') ? sub_str($rows['user_note'], 30) : 'N/A';
            $rows['pay_status']       = ($rows['is_paid'] == 0) ? '未确认' : '已完成';
            $rows['amount']           = price_format(abs($rows['amount']), false);

            /* 会员的操作类型： 冲值，提现 */
            if ($rows['process_type'] == 0)
            {
                $rows['type'] = '充值';
            }
            else
            {
                $rows['type'] = '提现';
            }

            /* 如果是预付款而且还没有付款, 允许付款 */
            if (($rows['is_paid'] == 0) && ($rows['process_type'] == 0))
            {
                $rows['handle'] = '<a href="index.php?c=user&a=pay&id='.$rows['id'].'&pid='.$rows['payment_id'].'">付款</a>';
            }

            $account_log[] = $rows;
        }
        $return['count']=$count;
        $return['info']=$account_log;
        return $return;
    }
    else
    {
         return false;
    }
}
?>