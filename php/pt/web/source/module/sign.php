<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
assign_template();
	
if ($action == 'index')
{
    $sql="select * from ".$aos->table('sign_options')." where  option_name = 'sign'";
    $options=$db->getRow($sql);


    $sql = "SELECT pay_points, sign_last_time, sign_days FROM " . $aos->table('users') . " WHERE user_id={$user_id}";
    $row = $db->getRow($sql);
    $today = strtotime('today');
    if ($row['sign_last_time'] >= $today) { // 今天已签到
        
        $qiandao=1;
    }

    
    $firstday = strtotime(date('Y-m'));;
    $sql = "SELECT add_time FROM " . $aos->table('sign_log') . " WHERE uid={$user_id} and add_time > '$firstday' ";
    $res=$db->getAll($sql);
    foreach($res as $vo){
        $a .= (date("d",$vo['add_time'])-1).',';
    }
    $a='['.substr($a,0,-1).']';
    $smarty->assign('options',$options);
    $smarty->assign('qiandao',$qiandao);
    $smarty->assign('a',$a);

    $share['title'] = $GLOBALS['_CFG']['sign_title'];
    $share['desc'] = $GLOBALS['_CFG']['sign_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
    
    $smarty->display('sign.htm');
}
if ($action == 'sign')
{
    $user_id=$_SESSION['user_id'];
    $res['err']=0;
    $sql="select * from ".$aos->table('sign_options')." where  option_name = 'sign'";
    $options=$db->getRow($sql);
    if (empty($user_id)) {
        $res['err']=1;
        $res['message']='请先登录！';
        die(json_encode($res));
    }
    
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');
    $sql = "SELECT pay_points, sign_last_time, sign_days FROM " . $aos->table('users') . " WHERE user_id={$user_id}";
    $row = $db->getRow($sql);
    
    if ($row['sign_last_time'] >= $today) { // 今天已签到
        
        $res['err']=1;
        $res['message']='您今天已经签到过了!！';
        die(json_encode($res));
    }
    
    $points = 0;    // 签到所能获得的分数
    if ($row['sign_last_time'] < $today && $row['sign_last_time'] >= $yesterday) {  // 昨天已签到
        if ($options['sign_type'] == 1) {   // 是递增模式
            $points = $options['sign_points'] + $row['sign_days'] * $options['sign_increase_points'];
            // 如果增加积分超过最大值，在将增加积分设为最大值
            if ($points > $options['sign_max_points']) $points = $options['sign_max_points'];
        } else {
            $points = $options['sign_points'];
        }
        $row['sign_days'] += 1;
    } else {    // 多日未签到
        $points = $options['sign_points'];
        $row['sign_days'] = 1;
    }
    //$row['pay_points'] += $points;
    $row['sign_last_time'] = gmtime();
    
    // 更新数据库
    $set_sql = array();
    foreach ($row as $k => $v) {
        $set_sql[] = "$k=$v";
    }
    $sql = "UPDATE " . $aos->table('users') . " SET " . implode(', ', $set_sql) . " WHERE user_id={$user_id}";
    $db->query($sql);

    // 签到日志
    if ($options['save_sign_log']) {
        if ($options['save_days_sign_log']>0) {
            // 删除过期日志
            $expired_time = strtotime('-'.$options['save_days_sign_log'].'days');
            $sql = "DELETE FROM " . $aos->table('hpyer_sign_log') . " WHERE uid={$user_id} AND add_time<".$expired_time;
            $db->query($sql);
        }
        
        // 增加签到日志
        $sql = "INSERT " . $aos->table('sign_log') . " (uid, add_time, points) VALUES ('{$user_id}', '{$row['sign_last_time']}', '{$points}')";
        $db->query($sql);
    }
    $res['message']="恭喜！您已连续签到{$row['sign_days']}天。";
    log_account_change($user_id, 0, 0, 0, $points, '签到赠送积分');
    die(json_encode($res));
}
?>