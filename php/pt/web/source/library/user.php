<?php

/*用户相关函数库*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}


/**
 * 获取用户中心默认页面所需的数据
 *
 * @access  public
 * @param   int         $user_id            用户ID
 *
 * @return  array       $info               默认页面所需资料数组
 */
function get_user_default($user_id)
{
    $sql = "SELECT nickname,rank_points,pay_points,user_money FROM " .$GLOBALS['aos']->table('users'). " WHERE user_id = '$user_id'";
    $info = $GLOBALS['db']->getRow($sql);
	$info['nickname']   = stripslashes($info['nickname']);
	$info['headimgurl']   = getAvatar($user_id);
    return $info;
}

function get_user_rank()
{
    $sql = "SELECT * FROM " .$GLOBALS['aos']->table('user_rank');
    $res = $GLOBALS['db']->getAll($sql);
    return $res;
}

/**
 * 查询会员余额的数量
 * @access  public
 * @param   int     $user_id        会员ID
 * @return  int
 */
function get_user_surplus($user_id)
{
    $sql = "SELECT SUM(user_money) FROM " .$GLOBALS['aos']->table('account_log').
           " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getOne($sql);
}

/** 查询会员积分*/
function get_user_points($user_id)
{
    $sql = "SELECT pay_points,rank_points FROM " .$GLOBALS['aos']->table('users').
           " WHERE user_id = '$user_id'";

    return $GLOBALS['db']->getRow($sql);
}
/*用户订单统计*/
function get_order_num($user_id,$type)
{
    $where = " user_id = $user_id";
    switch ($type)
    {
        case 'await_pay':
            $where .= order_query_sql('await_pay');
        break;
        case 'await_tuan':
            $where .= order_query_sql('await_tuan');
        break;
        case 'await_veri':
            $where .= order_query_sql('await_veri');
        break;
        case 'await_ship':
            $where .= order_query_sql('await_ship');
        break;
        case 'await_receipt':
            $where .= order_query_sql('await_receipt');
        break;
        case 'await_comment':
            $where .= order_query_sql('await_comment');
        break;
    }
    $num = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM ".$GLOBALS['aos']->table('order_info')." where".$where);
    return $num;
}
/**
 * 插入会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 * @param   string    $amount   余额
 *
 * @return  int
 */
function insert_user_account($surplus, $amount)
{
    $sql = 'INSERT INTO ' .$GLOBALS['aos']->table('user_account').
           ' (user_id, admin_user, amount, add_time, paid_time, admin_note, user_note, process_type, payment_id, payment_name, is_paid)'.
            " VALUES ('$surplus[user_id]', '', '$amount', '".gmtime()."', 0, '', '$surplus[user_note]', '$surplus[process_type]', '$surplus[payment_id]', '$surplus[payment_name]', 0)";
    $GLOBALS['db']->query($sql);

    return $GLOBALS['db']->insert_id();
}

/**
 * 更新会员账目明细
 *
 * @access  public
 * @param   array     $surplus  会员余额信息
 *
 * @return  int
 */
function update_user_account($surplus)
{
    $sql = 'UPDATE ' .$GLOBALS['aos']->table('user_account'). ' SET '.
           "amount     = '$surplus[amount]', ".
           "user_note  = '$surplus[user_note]', ".
           "payment_id    = '$surplus[payment_id]' ".
           "payment_name    = '$surplus[payment_name]' ".
           "WHERE id   = '$surplus[rec_id]'";
    $GLOBALS['db']->query($sql);

    return $surplus['rec_id'];
}

/**
 * 检查充值的金额是否与会员资金管理ID相符
 *
 * @access  public
 * @param   string   $rec_id      会员资金管理ID
 * @param   float    $money       充值的金额
 * @return  true
 */
function check_account_money($rec_id, $user_id, $money)
{
    if(is_numeric($rec_id))
    {
        $sql = 'SELECT amount FROM ' . $GLOBALS['aos']->table('user_account') .
              " WHERE id = '$rec_id' and user_id='$user_id'";
        $amount = $GLOBALS['db']->getOne($sql);
    }
    else
    {
        return false;
    }
    if ($money == $amount)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * 将支付LOG插入数据表
 *
 * @access  public
 * @param   integer     $id         订单编号
 * @param   float       $amount     订单金额
 * @param   integer     $type       支付类型
 * @param   integer     $is_paid    是否已支付
 *
 * @return  int
 */
function insert_pay_log($id, $amount, $type = 1, $is_paid = 0)
{
    $sql = 'INSERT INTO ' .$GLOBALS['aos']->table('pay_log')." (order_id, order_amount, order_type, is_paid)".
            " VALUES  ('$id', '$amount', '$type', '$is_paid')";
    $GLOBALS['db']->query($sql);

     return $GLOBALS['db']->insert_id();
}

/**
 * 取得上次未支付的pay_lig_id
 *
 * @access  public
 * @param   array     $surplus_id  余额记录的ID
 * @param   array     $pay_type    支付的类型：预付款/订单支付
 *
 * @return  int
 */
function get_paylog_id($surplus_id, $pay_type = 1)
{
    $sql = 'SELECT log_id FROM' .$GLOBALS['aos']->table('pay_log').
           " WHERE order_id = '$surplus_id' AND order_type = '$pay_type' AND is_paid = 0";

    return $GLOBALS['db']->getOne($sql);
}

/**
 * 根据ID获取当前余额操作信息
 *
 * @access  public
 * @param   int     $surplus_id  会员余额的ID
 *
 * @return  int
 */
function get_surplus_info($surplus_id)
{
    $sql = 'SELECT * FROM ' .$GLOBALS['aos']->table('user_account').
           " WHERE id = '$surplus_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 * 取得已安装的支付方式(其中不包括线下支付的)
 * @param   bool    $include_balance    是否包含余额支付（冲值时不应包括）
 * @return  array   已安装的配送方式列表
 */
function get_online_payment_list($include_balance = true)
{
    $sql = 'SELECT pay_id, pay_code, pay_name ' .
            'FROM ' . $GLOBALS['aos']->table('payment') .
            " WHERE enabled = 1";
    if (!$include_balance)
    {
        $sql .= " AND pay_code <> 'balance' ";
    }

    $modules = $GLOBALS['db']->getAll($sql);

    return $modules;
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
function get_account_log($user_id, $num, $start)
{
    $account_log = array();
    $sql = 'SELECT * FROM ' .$GLOBALS['aos']->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type " . db_create_in(array(0, 1)) .
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

        return $account_log;
    }
    else
    {
         return false;
    }
}

/**
 * 查询会员积分的操作记录
 *
 * @access  public
 * @param   int     $user_id    会员ID
 * @param   int     $num        每页显示数量
 * @param   int     $start      开始显示的条数
 * @return  array
 */
function get_points_log($user_id, $status)
	{
		$where .= " where user_id='$user_id'";
		switch($status)
		{
	        case 'plus' :
	            $where .= "and pay_points > 0";
		        break;
			case 'minus' :
	            $where .= "and pay_points < 0";
		        break;
			default:
			    $where .= "and pay_points <> 0";
				break;
		}
		$sql = "SELECT * FROM " . $GLOBALS['aos']->table('account_log') . $where .
				" ORDER BY log_id DESC";
		$res = $GLOBALS['db']->query($sql);;
		
		
		$arr = array();
		while ($row = $GLOBALS['db']->fetchRow($res))
		{
			$row['change_time']   = local_date('Y-m-d H:i:s', $row['change_time']);
			$arr[] = $row;
		}
		return $arr;
	}

/**
 *  删除未确认的会员帐目信息
 *
 * @access  public
 * @param   int         $rec_id     会员余额记录的ID
 * @param   int         $user_id    会员的ID
 * @return  boolen
 */
function del_user_account($rec_id, $user_id)
{
    $sql = 'DELETE FROM ' .$GLOBALS['aos']->table('user_account').
           " WHERE is_paid = 0 AND id = '$rec_id' AND user_id = '$user_id'";

    return $GLOBALS['db']->query($sql);
}

/**
 *  获取用户评论
 *
 * @access  public
 * @param   int     $user_id        用户id
 * @param   int     $page_size      列表最大数量
 * @param   int     $start          列表起始页
 * @return  array
 */
function get_comment_list($limit='')
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('comment').
           " WHERE user_id = '$_SESSION[user_id]'";
    $count = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT c.*, g.goods_name,u.nickname,u.headimgurl ".
           " FROM " . $GLOBALS['aos']->table('comment') . " AS c ".
           " LEFT JOIN " . $GLOBALS['aos']->table('goods') . " AS g ".
           " ON c.id_value = g.goods_id ".
           " LEFT JOIN " . $GLOBALS['aos']->table('users') . " AS u ".
           " ON u.user_id = c.user_id ".
           " WHERE c.user_id='$_SESSION[user_id]' ".$limit;
    $list = $GLOBALS['db']->GetAll($sql);

    foreach($list as $idx=>$value)
    {
        $list[$idx]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
    }

    $res['list'] = $list;
    $res['count'] = $count;
    return $res;
}

/**
 *  获取指定用户的收藏商品列表
 *
 * @access  public
 * @param   int     $user_id        用户ID
 * @param   int     $num            列表最大数量
 * @param   int     $start          列表其实位置
 *
 * @return  array   $arr
 */
function get_collection_goods($user_id, $num = 10, $start = 0)
{
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_img, g.market_price, g.shop_price AS org_price, '.
                'c.rec_id' .
            ' FROM ' . $GLOBALS['aos']->table('collect') . ' AS c' .
            " LEFT JOIN " . $GLOBALS['aos']->table('goods') . " AS g ".
                "ON g.goods_id = c.goods_id ".
            " WHERE c.user_id = '$user_id' ORDER BY c.rec_id DESC";
    $res = $GLOBALS['db'] -> selectLimit($sql, $num, $start);

    $goods_list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $goods_list[$row['goods_id']]['rec_id']        = $row['rec_id'];
        $goods_list[$row['goods_id']]['is_attention']  = $row['is_attention'];
        $goods_list[$row['goods_id']]['goods_id']      = $row['goods_id'];
        $goods_list[$row['goods_id']]['goods_name']    = $row['goods_name'];
        $tuan_price_list = get_tuan_price_list($row['goods_id']);
        $goods_list[$row['goods_id']]['min_number'] = min(array_column($tuan_price_list,'number'));
        $goods_list[$row['goods_id']]['tuan_price'] = price_format(max(array_column($tuan_price_list,'price')));
        $goods_list[$row['goods_id']]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
        $goods_list[$row['goods_id']]['url']           = 'index.php?c=goods&id='.$row['goods_id'];
    }


    return $goods_list;
}

/**
 * 获取用户帐号信息
 *
 * @access  public
 * @param   int       $user_id        用户user_id
 *
 * @return void
 */
function get_profile($user_id)
{
    /* 会员帐号信息 */
    $info  = array();
    $sql  = "SELECT * FROM " .$GLOBALS['aos']->table('users') . " WHERE user_id = '$user_id'";
    $info = $GLOBALS['db']->getRow($sql);
    return $info;
}

/**
 * 取得收货人地址列表
 * @param   int     $user_id    用户编号
 * @return  array
 */
function get_address_list($user_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') .
            " WHERE user_id = '$user_id' LIMIT 10";

    return $GLOBALS['db']->getAll($sql);
}

function get_address($address_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') .
            " WHERE address_id = '$address_id'";

    return $GLOBALS['db']->getRow($sql);
}

/**
 *  给指定用户添加一个指定优惠券
 *
 * @access  public
 * @param   int         $user_id        用户ID
 * @param   string      $bouns_sn       优惠券序列号
 *
 * @return  boolen      $result
 */
function add_bonus($user_id, $bouns_sn)
{
    if (empty($user_id))
    {
        $GLOBALS['err']->add('用户未登录。无法完成操作');
        return false;
    }

    /* 查询优惠券序列号是否已经存在 */
    $sql = "SELECT bonus_id, bonus_sn, user_id, bonus_type_id FROM " .$GLOBALS['aos']->table('user_bonus') .
           " WHERE bonus_sn = '$bouns_sn'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row)
    {
        if ($row['user_id'] == 0)
        {
            //优惠券没有被使用
            $sql = "SELECT send_end_date, use_end_date ".
                   " FROM " . $GLOBALS['aos']->table('bonus_type') .
                   " WHERE type_id = '" . $row['bonus_type_id'] . "'";

            $bonus_time = $GLOBALS['db']->getRow($sql);

            $now = gmtime();
            if ($now > $bonus_time['use_end_date'])
            {
                $GLOBALS['err']->add('该优惠券已经过了使用期！');
                return false;
            }

            $sql = "UPDATE " .$GLOBALS['aos']->table('user_bonus') . " SET user_id = '$user_id' ".
                   "WHERE bonus_id = '$row[bonus_id]'";
            $result = $GLOBALS['db'] ->query($sql);
            //var_dump($result );die;
            if ($result)
            {
                 return true;
            }
            else
            {
                return $GLOBALS['db']->errorMsg();
            }
        }
        else
        {
            if ($row['user_id']== $user_id)
            {
                //优惠券已经添加过了。
                $GLOBALS['err']->add('你输入的优惠券你已经领取过了！');
            }
            else
            {
                //优惠券被其他人使用过了。
                $GLOBALS['err']->add('你输入的优惠券已经被其他人领取！');
            }

            return false;
        }
    }
    else
    {
        //优惠券不存在
        $GLOBALS['err']->add('你输入的优惠券不存在');
        return false;
    }

}

/**
 *  获取用户指定范围的订单列表
 *
 * @access  public
 * @param   int         $user_id        用户ID号
 * @param   int         $num            列表最大数量
 * @param   int         $start          列表起始位置
 * @return  array       $order_list     订单列表
 */
function get_user_orders($user_id, $status, $limit)
{
	$where = "user_id = '$user_id' and order_status != 2 ";
	//综合状态
	switch($status)
	{
	    case 'await_pay' : //待付款
	        $where .= order_query_sql('await_pay');
		    break;
		case 'await_ship' : //待发货
	        $where .= order_query_sql('await_ship');
		    break;
		//case 'shipped' : //已发货
	        //$where .= order_query_sql('shipped');
		    //break;
		case 'await_receipt' : //待收货
	        $where .= order_query_sql('await_receipt');
		    break;
		//case 'finished' : //已完成
	        //$where .= order_query_sql('finished');
		    //break;
		case 'await_comment' : //待评价
	        $where .= order_query_sql('await_comment');
		    break;
		//case 'payback' : //退款中
	        //$where .= order_query_sql('payback');
		    //break;
		//case 'canceled' : //已取消
	        //$where .= order_query_sql('canceled');
		    //break;
		//case 'unconfirmed' : //未确认
	        //$where .= order_query_sql('unconfirmed');
		    //break;
        case 'await_veri' : //待核销
            $where .= order_query_sql('await_veri');
            break;
        case 'await_tuan' : //待成团
            $where .= order_query_sql('await_tuan');
            break;
        //case 'all_refund' : //可退款订单
            //$where .= order_query_sql('all_refund');
            //break;
        //case 'refund' : //已申请退款订单
            //$where .= order_query_sql('refund');
            //break;
	    default:
	}

    /*取得订单总量*/

    $sql = "SELECT count(order_id) FROM " .$GLOBALS['aos']->table('order_info') ." WHERE $where ";

    $count = $GLOBALS['db']->getOne($sql);

    /* 取得订单列表 */
    $sql = "SELECT is_luck,shipping_id,order_id, order_sn, order_status,shipping_status, pay_status, add_time, pay_id, order_amount, extension_code, extension_id, tuan_status, comment,tuan_num,assist_num, shipping_fee,money_paid, " .
           "(goods_amount + shipping_fee) AS total_fee ".
           " FROM " .$GLOBALS['aos']->table('order_info') .
           " WHERE $where ORDER BY add_time DESC $limit";
    $res = $GLOBALS['db']->query($sql);
    $result    = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $order_goods = order_goods($row['order_id']);
        $row['order_num'] = count($order_goods);
        $row['shipping_fee'] = $row['shipping_fee'];
        $row['goods_number'] = $order_goods['goods_number'];
        $row['goods_name'] = $order_goods['goods_name'];
        $row['goods_id'] = $order_goods['goods_id'];
        $row['goods_attr'] = $order_goods['goods_attr'];
        $row['goods_img'] = get_goods_img($order_goods['goods_id']);
        $row['goods_price'] = price_format($order_goods['goods_price']);
        $row['goods_url'] = 'index.php?c=goods&id='.$order_goods['goods_id'];
        if($row['order_status'] == 1 && $row['shipping_status'] == 0 && $row['pay_status'] == 0)
        {
            $row['order_status_name'] = '待支付';
            $row['log_id'] = get_pay_log_id($row['order_id']);
            if($row['pay_id'] == 1)
            {
                //处理余额支付
            }
            elseif($row['pay_id'] == 2)
            {
                @$row['handler'] = '<a href="index.php?c=wxpay&out_trade_no=' .$row['log_id'].'">去支付</a>';
                @$row['handler'] .= "<a class='delete hard' href='javascript:;' onclick='cancel_order(".$row['order_id'].")'>删除订单</a>";
            }
            elseif($row['pay_id'] == 3)
            {
                @$row['handler'] = '<a href="index.php?c=alipay&out_trade_no='.$row['log_id'].'&total_fee='.$row['order_amount'].'" >去支付</a>';
                
            }
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
            $sql="select back_id from ".$GLOBALS['aos']->table('back_order')." where order_id = ".$row['order_id'];
            $back_id = $GLOBALS['db']->getOne($sql);
            if($back_id){
              @$row['handler'] = '<a  href="index.php?c=user&a=refund&order_id='.$row['order_id'].'" >查看退款详情</a>';  
            }
        }
        elseif($row['order_status'] == 1 && $row['shipping_status'] == 0 && $row['pay_status'] == 2)
        {
            $ex_array=array('tuan','lottery','miao');
            if($row['extension_code']=='assist' && $row['tuan_status']==4){
                $row['order_status_name'] = '助力失败';
            }
            elseif($row['extension_code']=='assist' && $row['assist_num']<$row['tuan_num']){
                $row['order_status_name'] = '助力中';
            }
            elseif(!empty($row['extension_code']) && in_array($row[extension_code],$ex_array))
            {
                if($row['tuan_status'] == 1)
                {
                    $row['order_status_name'] = '拼团中';
                }
                elseif($row['tuan_status'] == 2)
                {
                    if($row['extension_code'] == 'lottery' && in_array($row['is_luck'], array(0,2,3))){
                        if($row['is_luck'] == 0){

                            $row['order_status_name'] = '已成团，待抽奖';

                        }elseif($row['is_luck'] == 2){

                            $row['order_status_name'] = '已成团，未中奖,待退款';
                            
                        }elseif($row['is_luck'] == 3){

                            $row['order_status_name'] = '已成团，未中奖，已退款';
                            
                        }
                        
                    }
                    elseif($row['shipping_id'] == 1)
                    {
                        $row['order_status_name'] = '已成团，待核销';
                        @$row['handler'] = '<a href="javascript:;" onclick="verification('.$row['order_id'].')">核销</a>';
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
                    @$row['handler'] = '<a href="javascript:;" onclick="verification('.$row['order_id'].')">核销</a>';
                }
                else
                {

                    $row['order_status_name'] = '待发货';
                }
            }
        }
        elseif($row['order_status'] == 5 && $row['shipping_status'] == 1 && $row['pay_status'] == 2){
            $row['order_status_name'] = '待收货';
            //确认收货 查看物流
            @$row['handler'] = '<a id="j_receive_'.$row['order_id'].'" href="javascript:;" onclick="affirm_received('.$row['order_id'].')">确认收货</a><a href="index.php?c=user&a=delivery_info&order_sn='.$row['order_sn'].'">查看物流</a>';
        }
        elseif($row['order_status'] == 5 && $row['shipping_status'] == 2 && $row['pay_status'] == 2 && $row['comment'] == 0){
            $row['order_status_name'] = '待评价';
            @$row['handler'] = '<a class="add_comment" href="javascript:;" onclick="comment('.$row['goods_id'].','.$row['order_id'].')">去评价</a>';
        }
        elseif($row['order_status'] == 5 && $row['shipping_status'] == 2 && $row['pay_status'] == 2 && $row['comment'] == 1){
            $row['order_status_name'] = '已完成';
        }

        //未成团，退款成功
        //二等奖，已退款并送券
		

		






		

        $result['info'][] = array('order_id'       => $row['order_id'],
                       'order_sn'       => $row['order_sn'],
                       'goods_number'    => $row['goods_number'],
					   'goods_name'    => $row['goods_name'],
                       'goods_img'    => $row['goods_img'],
                       'goods_attr'    => $row['goods_attr'],
                       'goods_price'    => $row['goods_price'],
                       'goods_url'    => $row['goods_url'],
					   'order_num'      => $row['order_num'],
                       'order_time'     => local_date($GLOBALS['_CFG']['time_format'], $row['add_time']),
                       'order_status'   => $row['order_status'],
					   'order_status_name'   => $row['order_status_name'],
                       'shipping_status'=> $row['shipping_status'],
                       'shipping_fee'=> $row['shipping_fee'],
                       'total_fee'      => price_format($row['total_fee'], false),
                       'order_amount'      => price_format($row['order_amount'], false),
                       'money_paid'      => price_format($row['money_paid'], false),
                       'pay_status'     =>$row['pay_status'],
                       'log_id'         =>$row['log_id'],
                       'pay_id'         =>$row['pay_id'],
                       'goods_id'         =>$row['goods_id'],
                       'comment'        =>$row['comment'],
                       'handler'        => $row['handler']);

    }
    $result['count']  = $count;
    return $result;
}

/**
 * 取消一个用户订单
 *
 * @access  public
 * @param   int         $order_id       订单ID
 * @param   int         $user_id        用户ID
 *
 * @return void
 */
function cancel_order($order_id, $user_id = 0)
{
    /* 查询订单信息，检查状态 */
    $sql = "SELECT user_id, order_id, order_sn , surplus , integral , bonus_id, order_status, shipping_status, pay_status ,comment FROM " .$GLOBALS['aos']->table('order_info') ." WHERE order_id = '$order_id'";
    $order = $GLOBALS['db']->GetRow($sql);

    if (empty($order))
    {
        $GLOBALS['err']->add('该订单不存在！');
        return false;
    }

    // 如果用户ID大于0，检查订单是否属于该用户
    if ($user_id > 0 && $order['user_id'] != $user_id)
    {
        $GLOBALS['err'] ->add('你没有权限操作他人订单');

        return false;
    }
    if($order['order_status'] != 5 && $order['shipping_status']!=2 && $order['comment'] != 1){

        // 发货状态只能是“未发货”
        if ($order['shipping_status'] != 0)
        {
            $GLOBALS['err']->add('只有在未发货状态下才能取消，你可以与店主联系。');

            return false;
        }

        // 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
        if ($order['pay_status'] != 0)
        {
            $GLOBALS['err']->add('只有未付款状态才能取消，要取消请联系店主。');

            return false;
        }
    }
    

    //将用户订单设置为取消
    $sql = "UPDATE ".$GLOBALS['aos']->table('order_info') ." SET order_status = 2,lastmodify = '".gmtime()."' WHERE order_id = '$order_id'";
    if ($GLOBALS['db']->query($sql))
    {
        /* 记录log */
        order_action($order['order_sn'], 2, $order['shipping_status'], 0,'用户删除','buyer');
        /* 退货用户余额、积分、优惠券 */
        if($order['order_status'] != 5 && $order['shipping_status']!=2 && $order['comment'] != 1){
            
            return_user_surplus_integral_bonus($order);
        }
        return true;
    }
    else
    {
        die($GLOBALS['db']->errorMsg());
    }

}

/**
 * 确认一个用户订单
 *
 * @access  public
 * @param   int         $order_id       订单ID
 * @param   int         $user_id        用户ID
 *
 * @return  bool        $bool
 */
function affirm_received($order_id, $user_id = 0)
{
    /* 查询订单信息，检查状态 */
    $sql = "SELECT user_id, order_sn , order_status, shipping_status, pay_status FROM ".$GLOBALS['aos']->table('order_info') ." WHERE order_id = '$order_id'";

    $order = $GLOBALS['db']->GetRow($sql);

    // 如果用户ID大于 0 。检查订单是否属于该用户
    if ($user_id > 0 && $order['user_id'] != $user_id)
    {
        $GLOBALS['err'] -> add('你没有权限操作他人订单');

        return false;
    }
    /* 检查订单 */
    elseif ($order['shipping_status'] == 2)
    {
        $GLOBALS['err'] ->add('此订单已经确认过了，感谢您在本站购物，欢迎再次光临。');

        return false;
    }
    elseif ($order['shipping_status'] != 1)
    {
        $GLOBALS['err']->add('您提交的订单不正确。');

        return false;
    }
    /* 修改订单发货状态为“确认收货” */
    else
    {
        $sql = "UPDATE " . $GLOBALS['aos']->table('order_info') . " SET shipping_status = '" . 2 . "',lastmodify='".gmtime()."' WHERE order_id = '$order_id'";
        if ($GLOBALS['db']->query($sql))
        {
            /* 记录日志 */
            order_action($order['order_sn'], $order['order_status'], 2, $order['pay_status'], '', "买家");

            return true;
        }
        else
        {
            die($GLOBALS['db']->errorMsg());
        }
    }

}

/**
 *  获取用户指定范围的团列表
 *
 * @access  public
 * @param   int         $user_id        用户ID号
 * @param   int         $num            列表最大数量
 * @param   int         $start          列表起始位置
 * @return  array       $order_list     订单列表
 */
function get_user_tuan($user_id, $status, $limit)
{
	$where = "user_id = '$user_id' AND (extension_code = 'tuan' || extension_code = 'miao') AND tuan_status > 0";
	//综合状态
	switch($status)
	{
	    case 1 : //进行中
	        $where .= " AND tuan_status = 1";
		    break;
		case 2 : //已成团
	        $where .= " AND tuan_status = 2";
		    break;
		case 3 : //已失败
	        $where .= " AND (tuan_status = 3 || tuan_status = 4)";
		    break;
	    default:
	}
    $sql  = "SELECT count(*) FROM ".$GLOBALS['aos']->table('order_info')." WHERE " . $where;
    $count = $GLOBALS['db']->getOne($sql);

    /* 取得订单列表 */
    $sql = "SELECT order_id, extension_code, extension_id, act_id, tuan_status, tuan_num, tuan_first, " .
           "(goods_amount + shipping_fee - discount) AS total_fee ".
           " FROM " .$GLOBALS['aos']->table('order_info') .
           " WHERE $where ORDER BY add_time DESC $limit";
    $result = $GLOBALS['db']->getAll($sql);
    foreach ($result AS $idx => $row)
    {
        $order_goods = order_goods($row['order_id']);
		$goods_tuan_num = get_tuan_number($order_goods['goods_id']);
        $tuan_mem_num = get_tuan_mem_num($row['extension_id']);
        $tuans[$idx]['tuan_mem_num'] = $tuan_mem_num;
        $tuans[$idx]['tuan_statu']    = $row['tuan_status'];
        $tuans[$idx]['order_id']       = $row['order_id'];
        $tuans[$idx]['type']        = $row['extension_code'];
        $tuans[$idx]['tuan_id']        = $row['extension_id'];
        $tuans[$idx]['act_id']        = $row['act_id'];
		$tuans[$idx]['goods_tuan_num'] = min($goods_tuan_num);
        $tuans[$idx]['tuan_num']       = $row['tuan_num'];
		$tuans[$idx]['tuan_first']     = $row['tuan_first'];
        $tuans[$idx]['tuan_status']    = $row['tuan_status'];
        if($row['tuan_status'] == 0)
        {
            $tuans[$idx]['tuan_status_lab']= '待付款';
        }
        elseif($row['tuan_status'] == 1)
        {
            $tuans[$idx]['tuan_status_lab']= '拼团中';
        }
        elseif($row['tuan_status'] == 2)
        {
            $tuans[$idx]['tuan_status_lab']= '拼团成功';
        }
        else
        {
            $tuans[$idx]['tuan_status_lab']= '拼团失败';
        }
        $tuans[$idx]['total_fee']      = price_format($row['total_fee'], false);
        $tuans[$idx]['goods_name']     = $order_goods['goods_name'];
        $tuans[$idx]['goods_img']      = get_goods_img($order_goods['goods_id']);
        $tuans[$idx]['goods_price']    = price_format($order_goods['goods_price']);
        $tuans[$idx]['goods_url']      = 'index.php?c=goods&id='.$order_goods['goods_id'];
    }
    $res['count'] = $count;
    $res['tuans'] = $tuans;
    return $res;
}

/**
 *  获取用户指定范围的抽奖列表
 *
 * @access  public
 * @param   int         $user_id        用户ID号
 * @param   int         $num            列表最大数量
 * @param   int         $start          列表起始位置
 * @return  array       $order_list     订单列表
 */
function get_user_lottery($user_id, $status, $limit)
{
    $now_time = gmtime();
    $where = "user_id = '$user_id' AND o.extension_code = 'lottery' AND o.tuan_status > 0 and o.act_id >0 ";
    //综合状态
    switch($status)
    {
        case 1 : //进行中
            $where .= " AND o.tuan_status < 3 AND $now_time < l.lottery_end_time AND $now_time > l.lottery_start_time AND l.lottery_status = 0";
            break;
        case 2 : //待抽奖
            $where .= " AND $now_time > l.lottery_end_time  AND l.lottery_status = 0 AND o.tuan_status = 2";
            break;
        case 3 : //已抽奖
            $where .= " AND l.lottery_status = 1 AND o.tuan_status = 2";
            break;
        case 4 : //失败团
            $where .= " AND o.tuan_status > 2";
            break;
        default:
    }
    $sql  = "SELECT count(*) FROM ".$GLOBALS['aos']->table('order_info')." AS o ".
    " LEFT JOIN " . $GLOBALS['aos']->table('lottery') . " AS l ".
           " ON o.act_id = l.lottery_id ".
    "WHERE $where";
    $count = $GLOBALS['db']->getOne($sql);

    /* 取得订单列表 */
    $sql = "SELECT o.order_id, o.extension_id, o.tuan_status, o.tuan_num, o.tuan_first, o.is_luck,o.act_id, l.lottery_status, l.lottery_start_time, l.lottery_end_time, " .
           "(o.goods_amount + o.shipping_fee) AS total_fee ".
           " FROM " .$GLOBALS['aos']->table('order_info') . " AS o".
           " LEFT JOIN " . $GLOBALS['aos']->table('lottery') . " AS l ".
           " ON o.act_id = l.lottery_id ".
           " WHERE $where ORDER BY add_time DESC $limit";
    $result = $GLOBALS['db']->getAll($sql);
    foreach ($result AS $idx => $row)
    {
        $order_goods = order_goods($row['order_id']);
        $goods_tuan_num = get_tuan_number($order_goods['goods_id']);
        $tuan_mem_num = get_tuan_mem_num($row['extension_id']);
        $tuans[$idx]['tuan_mem_num'] = $tuan_mem_num;
        $tuans[$idx]['tuan_statu']    = $row['tuan_status'];
        $tuans[$idx]['order_id']       = $row['order_id'];
        $tuans[$idx]['tuan_id']        = $row['extension_id'];
        $tuans[$idx]['act_id']        = $row['act_id'];
        $tuans[$idx]['goods_tuan_num'] = min($goods_tuan_num);
        $tuans[$idx]['tuan_num']       = $row['tuan_num'];
        $tuans[$idx]['tuan_first']     = $row['tuan_first'];
        if($row['tuan_status'] == 0)
        {
            $tuans[$idx]['tuan_status']= '待付款';
        }
        elseif($row['tuan_status'] == 1)
        {
            $tuans[$idx]['tuan_status']= '拼团中';
        }
        elseif($row['tuan_status'] == 2)
        {
            $tuans[$idx]['tuan_status']= '拼团成功';
        }
        else
        {
            $tuans[$idx]['tuan_status']= '拼团失败';
        }
        $tuans[$idx]['lottery_status']     = $row['lottery_status'];
        $tuans[$idx]['total_fee']      = price_format($row['total_fee'], false);
        $tuans[$idx]['goods_name']     = $order_goods['goods_name'];
        $tuans[$idx]['goods_img']      = get_goods_img($order_goods['goods_id']);
        $tuans[$idx]['goods_price']    = price_format($order_goods['goods_price']);
        $tuans[$idx]['goods_url']      = 'index.php?c=goods&id='.$order_goods['goods_id'];
        $tuans[$idx]['is_luck']      = $row['is_luck'];
        if($now_time > $row['lottery_end_time'])
        {
            $tuans[$idx]['status'] = 1;
        }
        
    }
    $res['count'] = $count;
    $res['tuans'] = $tuans;
    return $res;
}

//获取用户助力列表
function get_user_assist($user_id, $limit)
{
    $now_time = gmtime();
    $where = "user_id = '$user_id' AND o.extension_code = 'assist' AND o.act_id >0 ";
    $sql  = "SELECT count(*) FROM ".$GLOBALS['aos']->table('order_info')." AS o ".
    " LEFT JOIN " . $GLOBALS['aos']->table('assist') . " AS a ".
           " ON o.act_id = a.assist_id ".
    "WHERE $where";
    $count = $GLOBALS['db']->getOne($sql);

    /* 取得订单列表 */
    $sql = "SELECT o.order_id,o.assist_num,o.tuan_num, o.extension_id,o.pay_time, o.tuan_status, a.assist_tuan_num,o.act_id, a.assist_start_time, a.assist_end_time" .
           " FROM " .$GLOBALS['aos']->table('order_info') . " AS o".
           " LEFT JOIN " . $GLOBALS['aos']->table('assist') . " AS a ".
           " ON o.act_id = a.assist_id ".
           " WHERE $where ORDER BY add_time DESC $limit";
    $result = $GLOBALS['db']->getAll($sql);
    foreach ($result AS $idx => $row)
    {
        $order_goods = order_goods($row['order_id']);
        $assist[$idx]['order_id']       = $row['order_id'];
        $assist[$idx]['tuan_id']        = $row['extension_id'];
        $assist[$idx]['act_id']        = $row['act_id'];
        $assist[$idx]['assist_tuan_num']  = $row['assist_tuan_num'];
        $assist[$idx]['cha_num']  = $row['assist_tuan_num']-$row['assist_num'];
        $assist[$idx]['goods_name']     = $order_goods['goods_name'];
        $assist[$idx]['goods_img']      = get_goods_img($order_goods['goods_id']);
        $assist[$idx]['assist_price']    = price_format($order_goods['assist_price']);
        $assist[$idx]['tuan_status']       = $row['tuan_status'];
        $old_time=$row['pay_time']+24*60*60;
        if($old_time > $row['assist_end_time']){
            $assist[$idx]['aotime']    = local_date('Y-m-d H:i:s', $row['assist_end_time']);
        }else{
            $assist[$idx]['aotime']    = local_date('Y-m-d H:i:s', $old_time);
        }
        if($row['tuan_status']==1){
            if(($old_time<$now_time || $now_time>$row['assist_end_time']) && $row['assist_num']<$row['tuan_num']){
                $sql = "UPDATE ". $GLOBALS['aos']->table('order_info') ." SET tuan_status = '4' WHERE order_id='".$row['order_id']."'";
                $GLOBALS['db']->query($sql);
                $assist[$idx]['tuan_status']       = '4';
            }elseif($row['assist_num']>=$row['tuan_num']){
                $sql = "UPDATE ". $GLOBALS['aos']->table('order_info') ." SET tuan_status = '2' WHERE order_id='".$row['order_id']."'";
                $GLOBALS['db']->query($sql);
                change_order_goods_storage($row['order_id'], true, 2);
            }
        }
        

    }
    $res['count'] = $count;
    $res['assist'] = $assist;
    return $res;
}

/**
 * 保存用户的收货人信息
 * 如果收货人信息中的 id 为 0 则新增一个收货人信息
 *
 * @access  public
 * @param   array   $consignee
 * @param   boolean $default        是否将该收货人信息设置为默认收货人信息
 * @return  boolean
 */
function save_consignee($consignee, $default=false)
{
    if ($consignee['address_id'] > 0)
    {
        /* 修改地址 */
        $res = $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('user_address'), $consignee, 'UPDATE', 'address_id = ' . $consignee['address_id']." AND `user_id`= '".$_SESSION['user_id']."'");
    }
    else
    {
        /* 添加地址 */
        $res = $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('user_address'), $consignee, 'INSERT');
        $consignee['address_id'] = $GLOBALS['db']->insert_id();
    }

    if ($default)
    {
        /* 保存为用户的默认收货地址 */
        $sql = "UPDATE " . $GLOBALS['aos']->table('users') .
            " SET address_id = '$consignee[address_id]' WHERE user_id = '$_SESSION[user_id]'";

        $res = $GLOBALS['db']->query($sql);
    }

    return $res !== false;
}

/**
 * 删除一个收货地址
 *
 * @access  public
 * @param   integer $id
 * @return  boolean
 */
function drop_consignee($id)
{
    $sql = "SELECT user_id FROM " .$GLOBALS['aos']->table('user_address') . " WHERE address_id = '$id'";
    $uid = $GLOBALS['db']->getOne($sql);

    if ($uid != $_SESSION['user_id'])
    {
        return false;
    }
    else
    {
        $sql = "DELETE FROM " .$GLOBALS['aos']->table('user_address') . " WHERE address_id = '$id'";
        $res = $GLOBALS['db']->query($sql);
        return $res;
    }
}

/**
 *  添加或更新指定用户收货地址
 *
 * @access  public
 * @param   array       $address
 * @return  bool
 */
function update_address($address)
{
    $address_id = intval($address['address_id']);
    unset($address['address_id']);

    if ($address_id > 0)
    {
         /* 更新指定记录 */
        $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('user_address'), $address, 'UPDATE', 'address_id = ' .$address_id . ' AND user_id = ' . $address['user_id']);
    }
    else
    {
        /* 插入一条新记录 */
        $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('user_address'), $address, 'INSERT');
        $address_id = $GLOBALS['db']->insert_id();
    }


    if (isset($address['user_id']))
    {
        $sql = "UPDATE ".$GLOBALS['aos']->table('users') .
                " SET address_id = '".$address_id."' ".
                " WHERE user_id = '" .$address['user_id']. "'";
        $GLOBALS['db'] ->query($sql);
    }
    return true;
}

/**
 *  获取指订单的详情
 *
 * @access  public
 * @param   int         $order_id       订单ID
 * @param   int         $user_id        用户ID
 *
 * @return   arr        $order          订单所有信息的数组
 */
function get_order_detail($order_id, $user_id = 0)
{
    include_once(ROOT_PATH . 'source/library/order.php');
    $order_id = intval($order_id);
    $order = order_info($order_id);
    //print_r($order);
    $area = explode(',',$order['area']);
    $order['area'] = get_region_name($area['1']).get_region_name($area['2']);
	$order_goods = order_goods($order_id);

    $order['goods_id'] = $order_goods['goods_id'];
	$order['goods_name'] = $order_goods['goods_name'];
	$order['goods_img'] = get_goods_img($order_goods['goods_id']);
	$order['goods_price'] = price_format($order_goods['goods_price'], false);
	$order['goods_number'] = $order_goods['goods_number'];
    $order['goods_attr'] = $order_goods['goods_attr'];
	$order['goods_url'] = 'index.php?c=goods&id='.$order_goods['goods_id'];
    $order['store_name'] = return_store_name($order['store_id']);


    $ex_array=array('tuan','lottery','miao');
    if($order['extension_code']=='assist' && $order['tuan_status']==4){
        $order['msg-icon'] = 'cancel';
        $order['msg-tag'] = '助力失败';
    }
    elseif($order['extension_code']=='assist' && $order['assist_num']<$order['tuan_num']){
        $order['msg-icon'] = 'confirmed';
        $order['msg-tag'] = '助力中';
        $order['msg-content'] = "还差".($order['tuan_num']-$order['assist_num'])."人助力成功";
    }
    elseif(!empty($order['extension_code']) && in_array($order[extension_code], $ex_array))
    {
        if($order['order_status'] == 4 && $order['pay_status'] == 2){
            $order['msg-icon'] = 'cancel';
            $order['msg-tag'] = '退款';
        }
        elseif($order['order_status'] == 3){
            $order['msg-icon'] = 'cancel';
            $order['msg-tag'] = '失效';
        }
        elseif($order['tuan_status'] == 0){
            if($order['order_status'] == 1){
                $order['msg-icon'] = 'unpayed';
                $order['msg-tag'] = '待支付';
            }
            elseif($order['order_status'] == 2){
                $order['msg-icon'] = 'cancel';
                $order['msg-tag'] = '订单已取消';
            }
        }
        elseif($order['tuan_status'] == 1){
            $order['msg-icon'] = 'payed';
            $order['msg-tag'] = '拼团还未成功';
            $order['msg-content'] = '让小伙伴们都来拼团吧~';
        }
        elseif($order['tuan_status'] == 2){
            if($order['extension_code'] == 'lottery' && in_array($order['is_luck'], array(0,2,3))){
                if($order['is_luck'] == 0){

                    $order['msg-icon'] = 'confirmed';
                    $order['msg-tag'] = '拼团成功，待抽奖';

                }elseif($order['is_luck'] == 2){

                    $order['msg-icon'] = 'confirmed';
                    $order['msg-tag'] = '拼团成功，未中奖，待退款';
                    
                }elseif($order['is_luck'] == 3){

                    $order['msg-icon'] = 'cancel';
                    $order['msg-tag'] = '拼团成功，未中奖，已退款';
                    
                }
                
            }
            elseif($order['shipping_id'] == 1)
            {
                if($order['shipping_status'] == 0){
                    $order['msg-icon'] = 'confirmed';
                    $order['msg-tag'] = '拼团成功';
                    $order['msg-content'] = '等待卖家核销';
                }
                elseif($order['shipping_status'] == 2)
                {
                    $order['msg-tag'] = '订单已完成'; 
                }
            }
            else
            {
                if($order['shipping_status'] == 0||$order['shipping_status'] == 5){
                    $order['msg-icon'] = 'confirmed';
                    $order['msg-tag'] = '拼团成功';
                    $order['msg-content'] = '等待卖家发货'; 
                }
                elseif($order['shipping_status'] == 1)
                {
                    $order['msg-icon'] = 'shipping';
                    $order['msg-tag'] = '卖家已发货';
                    
                    $end_time =local_date($GLOBALS['_CFG']['time_format'], ($order['shipping_time']+7*3600*24));
                    $order['msg-content'] = "还剩余<span class='aotime' data='$end_time'></span>自动确认"; 
                    $order['aotime']  = $end_time;
                }
                elseif($order['shipping_status'] == 2)
                {
                    $order['msg-tag'] = '订单已完成'; 
                }

            }
        }
        elseif($order['tuan_status'] == 3){
            $order['msg-icon'] = 'cancel';
            $order['msg-tag'] = '未成团，待退款';
        }
        elseif($order['tuan_status'] == 4){
            $order['msg-icon'] = 'received';
            $order['msg-tag'] = '未成团，退款成功';
        }
        else
        {
            $order['msg-tag'] = '团其他状态，待开发';
            //二等奖，已退款并送券
        }
    }
    else
    {
        if($order['order_status'] == 1){
            //
            if($order['pay_status'] == 0){

                $order['msg-icon'] = 'unpayed';
                $order['msg-tag'] = '待支付';

            }elseif($order['pay_status']==2){

                if($order['shipping_id'] == 1)
                {
                    if($order['shipping_status'] == 0){

                        $order['msg-icon'] = 'confirmed';
                        $order['msg-tag'] = '等待卖家核销';

                    }
                }
                else
                {
                    if($order['shipping_status'] == 0||$order['shipping_status'] == 5){

                        $order['msg-icon'] = 'confirmed';
                        $order['msg-tag'] = '下单成功';
                        $order['msg-content'] = '等待卖家发货';

                    }
                    
                }
            }
        }elseif($order['order_status'] == 5){
            if($order['shipping_status'] == 1)
            {
                $order['msg-icon'] = 'shipping';
                $order['msg-tag'] = '卖家已发货';
                //$end_time=$order['shipping_time']+7*3600*24;
                
                $end_time =local_date($GLOBALS['_CFG']['time_format'], ($order['shipping_time']+7*3600*24));
                $order['msg-content'] = "还剩余<span class='aotime' data='$end_time'></span>自动确认";
                $order['aotime']  = $end_time;
            }
            elseif($order['pay_status']==2 && $order['shipping_status'] == 2)
            {

                $order['msg-tag'] = '订单已完成';
            }
        }elseif($order['order_status'] == 2){
            $order['msg-icon'] = 'cancel';
            $order['msg-tag'] = '订单已取消';
        }
        elseif($order['order_status'] == 4 && $order['pay_status'] == 2){
            $order['msg-icon'] = 'cancel';
            $order['msg-tag'] = '退款';
        }
        //暂未开发
    }

	

    //检查订单是否属于该用户
    if ($user_id > 0 && $user_id != $order['user_id'])
    {
        $GLOBALS['err']->add('你没有权限操作他人订单');

        return false;
    }

    /* 只有未确认才允许用户修改订单地址 */
    if ($order['order_status'] == 0)
    {
        $order['allow_update_address'] = 1; //允许修改收货地址
    }
    else
    {
        $order['allow_update_address'] = 0;
    }

    /* 获取订单中商品数量 */
    //$order['exist_real_goods'] = exist_real_goods($order_id);

    /* 如果是未付款状态，生成支付按钮 */
    if ($order['pay_status'] == 0 &&
        ($order['order_status'] == 0 ||
        $order['order_status'] == 1))
    {
        /*
         * 在线支付按钮
         */
        //支付方式信息
        $payment_info = array();
        $payment_info = payment_info($order['pay_id']);



        //无效支付方式
        if ($payment_info === false)
        {
            $order['pay_online'] = '';
        }
        else
        {
            //取得支付信息
            $payment = unserialize_config($payment_info['pay_config']);

            //获取需要支付的log_id
            $order['log_id']    = get_paylog_id($order['order_id'], $pay_type = 0);
            //$order['user_name'] = $_SESSION['user_name'];
            $order['pay_code'] = $payment_info['pay_code'];

            if($order['pay_code'] == 'alipay'){
                $order['pay_url'] = 'index.php?c=alipay&out_trade_no='.$order['log_id'].'&total_fee='.$order['order_amount'];
            }
            if($order['pay_code'] == 'wxpay'){
                $order['pay_url'] = 'index.php?c=wxpay&out_trade_no='.$order['log_id'];
            }
        }
    }
    else
    {
        $order['pay_url'] = '';
    }

    /* 无配送时的处理 */
    $order['shipping_id'] == -1 and $order['shipping_name'] = '无需使用配送方式';


    /* 确认时间 支付时间 发货时间 */
    if ($order['confirm_time'] > 0 && ($order['order_status'] == 1 || $order['order_status'] == 5 || $order['order_status'] == OS_SPLITING_PART))
    {
        $order['confirm_time'] = sprintf('确认于 %s', local_date($GLOBALS['_CFG']['time_format'], $order['confirm_time']));
    }
    else
    {
        $order['confirm_time'] = '';
    }
    if ($order['pay_time'] > 0 && $order['pay_status'] != 0)
    {
        $order['pay_time'] = sprintf('付款于 %s', local_date($GLOBALS['_CFG']['time_format'], $order['pay_time']));
    }
    else
    {
        $order['pay_time'] = '';
    }
    $order['shipping_date_time'] = $order['shipping_time'];
    if ($order['shipping_time'] > 0 && in_array($order['shipping_status'], array(1, 2)))
    {
        $order['shipping_time'] = sprintf('发货于 %s', local_date($GLOBALS['_CFG']['time_format'], $order['shipping_time']));
    }
    else
    {
        $order['shipping_time'] = '';
    }

    return $order;

}

/**
 *  保存用户收货地址
 *
 * @access  public
 * @param   array   $address        array_keys(consignee string, email string, address string, zipcode string, tel string, mobile stirng, sign_building string, best_time string, order_id int)
 * @param   int     $user_id        用户ID
 *
 * @return  boolen  $bool
 */
function save_order_address($address, $user_id)
{
    if (!isset($address['lastmodify']) || !$address['lastmodify']) {
        $address['lastmodify'] = gmtime();
    }
    $GLOBALS['err']->clean();
    /* 数据验证 */
    empty($address['consignee']) and $GLOBALS['err']->add('收货人姓名为空');
    empty($address['address']) and $GLOBALS['err']->add('收货地址详情为空');
    $address['order_id'] == 0 and $GLOBALS['err']->add('未指定订单号');
    if ($GLOBALS['err']->error_no > 0)
    {
        return false;
    }

    /* 检查订单状态 */
    $sql = "SELECT user_id, order_status FROM " .$GLOBALS['aos']->table('order_info'). " WHERE order_id = '" .$address['order_id']. "'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row)
    {
        if ($user_id > 0 && $user_id != $row['user_id'])
        {
            $GLOBALS['err']->add('你没有权限操作他人订单');
            return false;
        }
        if ($row['order_status'] != 0)
        {
            $GLOBALS['err']->add('该订单状态下不能再修改地址');
            return false;
        }
        $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('order_info'), $address, 'UPDATE', "order_id = '$address[order_id]'");
        return true;
    }
    else
    {
        /* 订单不存在 */
        $GLOBALS['err']->add('该订单不存在！');
        return false;
    }
}

/**
 *
 * @access  public
 * @param   int         $user_id         用户ID
 * @param   int         $num             列表显示条数
 * @param   int         $start           显示起始位置
 *
 * @return  array       $arr             红保列表
 */
function get_user_bouns_list($user_id, $status, $limit)
{
    $day = getdate();
    $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
	$where = "u.bonus_type_id = b.type_id AND u.user_id = '$user_id' ";
	//综合状态
	switch($status)
	{
	    case 'not_use' : //未使用
	        $where .= "AND u.order_id = 0 AND b.use_start_date < $cur_date AND $cur_date <  b.use_end_date";
		    break;
		case 'not_start' : //未开始
	        $where .= "AND u.order_id = 0 AND b.use_start_date > $cur_date";
		    break;
		case 'had_use' : //已使用
	        $where .= "AND u.order_id != 0";
		    break;
		case 'overdue' : //已过期
	        $where .= "AND u.order_id = 0 AND b.use_end_date < $cur_date";
		    break;
	    default:
	}
    $sql = "SELECT count(u.bonus_sn) ".
           " FROM " .$GLOBALS['aos']->table('user_bonus'). " AS u ,".
           $GLOBALS['aos']->table('bonus_type'). " AS b".
           //" WHERE u.bonus_type_id = b.type_id AND u.user_id = '" .$user_id. "' $limit";
           " WHERE $where ";
    $count=$GLOBALS['db']->getOne($sql);
    $sql = "SELECT u.bonus_sn, u.order_id, b.type_name,b.send_type, b.type_money, b.min_goods_amount, b.use_start_date, b.use_end_date, b.goods_id ".
           " FROM " .$GLOBALS['aos']->table('user_bonus'). " AS u ,".
           $GLOBALS['aos']->table('bonus_type'). " AS b".
           //" WHERE u.bonus_type_id = b.type_id AND u.user_id = '" .$user_id. "' $limit";
		   " WHERE $where ORDER BY use_end_date $limit";
    $res = $GLOBALS['db']->query($sql);
    $arr = array();
    

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        /* 先判断是否被使用，然后判断是否开始或过期 */
        if (empty($row['order_id']))
        {
            /* 没有被使用 */
            if ($row['use_start_date'] > $cur_date)
            {
                $row['status'] = '未开始';//未开始
				$row['class'] = 'not_start';
            }
            else if ($row['use_end_date'] < $cur_date)
            {
                $row['status'] = '已过期';//已过期
				$row['class'] = 'overdue';
            }
            else
            {
                $row['status'] = '未使用';//未使用
				$row['class'] = 'not_use';
            }
        }
        else
        {
            $row['status'] = '<a href="index.php?c=user&a=order_detail&order_id=' .$row['order_id']. '" >已使用</a>';//已使用
			$row['class'] = 'had_use';
        }
		$row['aa'] = '未使用';
        $row['type_money']   = price_format($row['type_money']);
		$row['min_goods_amount']   = intval($row['min_goods_amount']);
        $row['use_startdate']   = local_date('Y-m-d', $row['use_start_date']);
        $row['use_enddate']     = local_date('Y-m-d', $row['use_end_date']);
        $arr[] = $row;
    }
    $result=array();
    $result['count']=$count;
    $result['arr']=$arr;
    return $result;

}

?>