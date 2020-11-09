<?php

/*支付接口函数库*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 取得返回信息地址
 * @param   string  $code   支付方式代码
 */
function return_url($code)
{
    return $GLOBALS['aos']->url() . 'respond.php?code=' . $code;
}

/**
 *  取得某支付方式信息
 *  @param  string  $code   支付方式代码
 */
function get_payment($code)
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('payment').
           " WHERE pay_code = '$code' AND enabled = '1'";
    $payment = $GLOBALS['db']->getRow($sql);

    if ($payment)
    {
        $config_list = unserialize($payment['pay_config']);

        foreach ($config_list AS $config)
        {
            $payment[$config['name']] = $config['value'];
        }
    }

    return $payment;
}

/**
 *  通过订单sn取得订单ID
 *  @param  string  $order_sn   订单sn
 *  @param  blob    $voucher    是否为会员充值
 */
function get_order_id_by_sn($order_sn, $voucher = 'false')
{
    if ($voucher == 'true')
    {
        if(is_numeric($order_sn))
        {
              return $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['aos']->table('pay_log') . " WHERE order_id=" . $order_sn . ' AND order_type=1');
        }
        else
        {
            return "";
        }
    }
    else
    {
        if(is_numeric($order_sn))
        {
            $sql = 'SELECT order_id FROM ' . $GLOBALS['aos']->table('order_info'). " WHERE order_sn = '$order_sn'";
            $order_id = $GLOBALS['db']->getOne($sql);
        }
        if (!empty($order_id))
        {
            $pay_log_id = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['aos']->table('pay_log') . " WHERE order_id='" . $order_id . "'");
            return $pay_log_id;
        }
        else
        {
            return "";
        }
    }
}

/**
 *  通过订单ID取得订单商品名称
 *  @param  string  $order_id   订单ID
 */
function get_goods_name_by_id($order_id)
{
    $sql = 'SELECT goods_name FROM ' . $GLOBALS['aos']->table('order_goods'). " WHERE order_id = '$order_id'";
    $goods_name = $GLOBALS['db']->getCol($sql);
    return implode(',', $goods_name);
}

/**
 * 检查支付的金额是否与订单相符
 *
 * @access  public
 * @param   string   $log_id      支付编号
 * @param   float    $money       支付接口返回的金额
 * @return  true
 */
function check_money($log_id, $money)
{
    if(is_numeric($log_id))
    {
        $sql = 'SELECT order_amount FROM ' . $GLOBALS['aos']->table('pay_log') .
              " WHERE log_id = '$log_id'";
        // $sql = "SELECT o.order_amount FROM ".$GLOBALS['aos']->table('order_info')." as o left join ".$GLOBALS['aos']->table('pay_log')." as p on o.order_id=p.order_id WHERE p.log_id = '".$log_id."'";
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
 * 修改订单的支付状态
 *
 * @access  public
 * @param   string  $log_id     支付编号
 * @param   integer $pay_status 状态
 * @param   string  $note       备注
 * @return  void
 */
function order_paid($log_id, $pay_status = 2, $note = '',$a=0)
{
    error_log(date("c")."\t".'log_id:'.$log_id.";pay_status:".$pay_status.";\n\n",3,LOG_DIR."/pay_status.log");
    /* 取得支付编号 */
    $log_id = intval($log_id);
    if ($log_id > 0)
    {
        /* 取得要修改的支付记录信息 */
        $sql = "SELECT * FROM " . $GLOBALS['aos']->table('pay_log') .
                " WHERE log_id = '$log_id'";
        $pay_log = $GLOBALS['db']->getRow($sql);
        if ($pay_log && $pay_log['is_paid'] == 0)
        {
            /* 修改此次支付操作的状态为已付款 */
            $sql = 'UPDATE ' . $GLOBALS['aos']->table('pay_log') .
                    " SET is_paid = '1' WHERE log_id = '$log_id'";
            $GLOBALS['db']->query($sql);


            /* 根据记录类型做相应处理 */
            if ($pay_log['order_type'] == 0)
            {

                /* 取得订单信息 */
                $sql = 'SELECT g.goods_id,g.goods_price,o.tuan_first,o.tuan_num,g.goods_name,o.order_id, o.user_id, o.order_sn, o.consignee, o.address, o.shipping_id, o.extension_code, o.extension_id, o.goods_amount, o.order_amount,o.act_id, o.pay_id ' .
                        'FROM ' . $GLOBALS['aos']->table('order_info') .
                       " as o left join ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id WHERE o.order_id = '$pay_log[order_id]'";
                $order    = $GLOBALS['db']->getRow($sql);

                $order_id = $order['order_id'];
                $order_sn = $order['order_sn'];

                /* 修改订单状态为已付款 */
                $ex_array=array('tuan','lottery','miao');
                if(!empty($order['extension_code']) && in_array($order[extension_code], $ex_array)){
                    /* 如果使用库存，且付款时减库存，则减少库存 */
                    if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == 1 && $order[extension_code] != 'lottery'){
                        change_order_goods_storage($order['order_id'], true, 2);
                    }
                    $sql = 'UPDATE ' . $GLOBALS['aos']->table('order_info') .
                                " SET order_status = 1 , " .
                                    " confirm_time = '" . gmtime() . "', " .
                                    " pay_status = '$pay_status', " .
                                    " pay_time = '".gmtime()."', " .
                                    " money_paid = order_amount," .
                                    " order_amount = 0, ".
                                    " tuan_status = 1, ".
                                    " lastmodify = '".gmtime()."' ".
                           "WHERE order_id = '$order_id'";
                }else{
                    if ($GLOBALS['_CFG']['use_storage'] == '1'){
                        change_order_goods_storage($order['order_id'], true, 2);
                    
                    }
                    $sql = 'UPDATE ' . $GLOBALS['aos']->table('order_info') .
                                " SET order_status = 1 , " .
                                    " confirm_time = '" . gmtime() . "', " .
                                    " pay_status = '$pay_status', " .
                                    " pay_time = '".gmtime()."', " .
                                    " money_paid = order_amount," .
                                    " order_amount = 0, ".
                                    " lastmodify = '".gmtime()."' ".
                           "WHERE order_id = '$order_id'";
                }
                $GLOBALS['db']->query($sql);
                global $wechat;
                $aos = new AOS($db_name, $prefix);
                include_once(ROOT_PATH . 'source/library/order.php');
                
                /* 记录订单操作记录 */
                if($a==1){
                    $wx_url=$aos->url();
                }else{
                   $wx_url=substr($aos->url(), 0, -4); 
                }
                
                order_action($order_sn, 1, 0, $pay_status, $note, '买家');
                if(!empty($order['extension_code']) && $order['tuan_first']=='1'){
                    //团成功发送模板消息
                    
                    $openid=getOpenid($order['user_id']);
                    //是否是阶梯团
                    $tuan_price_num = count(get_tuan_number($order['goods_id']));
                    $wx_url.="index.php?c=share&tuan_id=".$order['extension_id'];
                    if($order['extension_code'] == 'miao'){
                        $tuan_type='秒杀团';
                    }elseif($order['extension_code'] == 'lottery'){
                        $tuan_type='抽奖团';
                    }elseif($tuan_price_num>1){
                        $tuan_type='阶梯团';
                    }else{
                        $tuan_type='普通团';
                    }
                    $tuan_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
                    //模版类型
                    
                    $goods_price="¥".$order[goods_price];
                    $tuan_nums=$order['tuan_num'].'人团';
                   
                    $message=getMessage(1);
                    $wx_title = "开团成功通知";
                    $wx_desc = $message[title]."\r\n商品名称：".$order[goods_name]."\r\n商品价格：".$goods_price."\r\n组团人数：".$tuan_nums."\r\n拼团类型：".$tuan_type."\r\n组团时间：".$tuan_time."\r\n".$message[note];
                    
                    $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url); 
                
                }elseif(!empty($order['extension_code']) && $order['tuan_first']=='2'){
                    //参团提醒
                    $sql = "SELECT pay_time FROM " . $GLOBALS['aos']->table('order_info') .
                    " WHERE extension_id = $order[extension_id] and tuan_first = 1 ";
                    $time = $GLOBALS['db']->getOne($sql);
                    
                    $order['tuan_time']=local_date($GLOBALS['_CFG']['time_format'],$time+$GLOBALS['_CFG']['tuan_time']*3600);
                    
                    $order['goods_price']="¥".$order[goods_price];
                    //参团通知
                    $openid=getOpenid($order['user_id']);
                    $wx_url.="index.php?c=share&tuan_id=".$order['extension_id'];
                    $message=getMessage(2);
                    $wx_title = "参团成功通知";
                    $wx_desc = $message[title]."\r\n商品名称：".$order[goods_name]."\r\n商品价格：".$order['goods_price']."\r\n结束时间：".$order['tuan_time']."\r\n".$message[note];
                    $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);  
                
                }else{
                    //订单支付成功提醒
                    //团成功发送模板消息
                    
                    $openid=getOpenid($order['user_id']);
                    
                    $tuan_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
                    
                    $wx_url.="index.php?c=user&a=order_detail&order_id=".$order['order_id'];
                    $order_amount="¥".$order[order_amount];
                     
                    $message=getMessage(5);
                    $wx_title = "支付成功通知";
                    $wx_desc = $message[title]."\r\n商品名称：".$order[goods_name]."\r\n支付时间：".$tuan_time."\r\n支付金额：".$order_amount."\r\n".$message[note];
                    $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                    $sql="select openid from ".$GLOBALS['aos']->table('wxmanage')." where store_id = 0";
                    $guan_user=$GLOBALS['db']->getAll($sql);
                    foreach($guan_user as $gv){
                        $openid=$gv['openid'];
                        $wx_url='';
                        $wx_title = "发货通知";
                        $wx_desc = "用户下单成功\r\n购买商品：".$order[goods_name]."\r\n请及时处理";
                        $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url); 
                    }
                }
                //判断阶梯团成团
                if($order['extension_code'] == 'tuan'){
                    //判断成团
                    $tuan_mem = get_tuan_mem($order['extension_id']);
                    $tuan_num = count($tuan_mem);
                    $goods_tuan_num = max(get_tuan_number($order['goods_id']));
                    
                    if($tuan_num >= $goods_tuan_num){
                        cheng_tuan($order['extension_id'],1);
                    }
                    
                }
                //判断秒杀团成团
                if($order['extension_code'] == 'miao'){
                    //判断成团
                    $sql="select count(order_id) from ".$GLOBALS['aos']->table('order_info')." where extension_id = ".$order['extension_id']." and extension_code = 'miao' and pay_status = 2 and order_status = 1";
                    $miao_num = $GLOBALS['db']->getOne($sql);
                    if($miao_num >= $order['tuan_num']){
                        cheng_tuan($order['extension_id'],1);
                    }
                    
                }
                //判断抽奖团成团
                if($order['extension_code'] == 'lottery'){
                    
                    //判断成团
                    $sql="select count(order_id) from ".$GLOBALS['aos']->table('order_info')." where extension_id = ".$order['extension_id']." and extension_code = 'lottery' and pay_status = 2 and order_status = 1";
                    $lottery_num = $GLOBALS['db']->getOne($sql);
                    if($lottery_num >= $order['tuan_num']){
                        cheng_tuan($order['extension_id'],1);
                    }
                }
            }
            elseif ($pay_log['order_type'] == 1)
            {
                $sql = 'SELECT `id` FROM ' . $GLOBALS['aos']->table('user_account') .  " WHERE `id` = '$pay_log[order_id]' AND `is_paid` = 1  LIMIT 1";
                $res_id=$GLOBALS['db']->getOne($sql);
                if(empty($res_id))
                {
                    /* 更新会员预付款的到款状态 */
                    $sql = 'UPDATE ' . $GLOBALS['aos']->table('user_account') .
                           " SET paid_time = '" .gmtime(). "', is_paid = 1" .
                           " WHERE id = '$pay_log[order_id]' LIMIT 1";
                    $GLOBALS['db']->query($sql);

                    /* 取得添加预付款的用户以及金额 */
                    $sql = "SELECT user_id, amount FROM " . $GLOBALS['aos']->table('user_account') .
                            " WHERE id = '$pay_log[order_id]'";
                    $arr = $GLOBALS['db']->getRow($sql);

                    /* 修改会员帐户金额 */
                    log_account_change($arr['user_id'], $arr['amount'], 0, 0, 0, '充值', 0);

                    $order = $GLOBALS['db']->getRow("select order_sn FROM ".$GLOBALS['aos']->table('order_info')." WHERE order_id=".$pay_log['order_id']);
                }
            }
        }
    }
}

?>