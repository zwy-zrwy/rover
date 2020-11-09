<?php

define('IN_AOS', true);
admin_priv('lottery_manage');
$exc = new exchange($aos->table('lottery'), $db, 'lottery_id', 'lottery_desc');
/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'lottery_list';
}

if ($operation == 'lottery_list')
{
	$lottery_list = get_lottery_list();

    $smarty->assign('lottery_list',    $lottery_list['arr']);

    $pager = get_page($lottery_list['filter']);

    $smarty->assign('pager',   $pager);
    $smarty->display('lottery_list.htm');
}


elseif ($operation == 'lottery_add')
{
	$next_month = local_strtotime('+1 months');
	$lottery['lottery_start_time']    = local_date('Y-m-d H:i:s');
    $lottery['lottery_end_time']   = local_date('Y-m-d H:i:s', $next_month);
	$smarty->assign('lottery',   $lottery);

	$smarty->assign('form_act',     'insert');
    $smarty->display('lottery_info.htm');
}

elseif ($operation == 'insert')
{
	$lottery=array();
    /* 初始化变量 */
    $lottery['goods_id']       = !empty($_POST['goods_id'])       ? intval($_POST['goods_id'])     : 0;
    $lottery['lottery_start_time']   = !empty($_POST['start_date'])   ? local_strtotime($_POST['start_date']) : '';
    $lottery['lottery_end_time']     = !empty($_POST['end_date'])     ? local_strtotime($_POST['end_date'])     : '';
    $lottery['lottery_price'] = !empty($_POST['lottery_price']) ? trim($_POST['lottery_price']) : '';
    $lottery['lottery_tuan_num']     = !empty($_POST['lottery_tuan_num'])     ? intval($_POST['lottery_tuan_num'])     : '';
    $lottery['lottery_desc']      = !empty($_POST['lottery_desc'])      ? trim($_POST['lottery_desc'])    : 0;
    $lottery['lottery_number']      = !empty($_POST['lottery_number'])      ? intval($_POST['lottery_number'])    : 0;
    $lottery['goods_attr']      = !empty($_POST['goods_attr'])       ? intval($_POST['goods_attr'])     : 0;
    if(empty($lottery['goods_id'])||empty($lottery['lottery_start_time'])||empty($lottery['lottery_end_time'])||empty($lottery['lottery_price'])||empty($lottery['lottery_tuan_num'])||empty($lottery['lottery_number'])){

    	$link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       	sys_msg('请把信息填写完整!', 0, $link);

    }
    /*$sql="SELECT goods_id from ".$aos->table('lottery')." where goods_id = ".$lottery['goods_id'];
    $res=$db->getOne($sql);
    if ($res)
    {
        
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('已添加过该商品!', 0, $link);
    }*/
	

    /* 入库的操作 */
    if ($db->autoExecute($aos->table('lottery'), $lottery) !== false)
    {
        $cat_id = $db->insert_id();
        admin_log($_POST['goods_id'], 'add', 'lottery');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加抽奖';
        $link[0]['href'] = 'index.php?act=lottery&op=lottery_add';

        $link[1]['text'] = '返回列表';
        $link[1]['href'] = 'index.php?act=lottery&op=lottery_list';

        sys_msg('抽奖活动添加成功!', 0, $link);
    }
}


elseif ($operation == 'lottery_edit')
{
	 /* 获取优惠券类型数据 */
    $lottery_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
    $lottery = $db->getRow("SELECT s.*,g.goods_name FROM " .$aos->table('lottery'). " as s left join ".$aos->table('goods')." as g on s.goods_id = g.goods_id WHERE lottery_id = '$lottery_id'");

    $lottery['lottery_start_time']   = local_date('Y-m-d H:i:s', $lottery['lottery_start_time']);
    $lottery['lottery_end_time']     = local_date('Y-m-d H:i:s', $lottery['lottery_end_time']);
    $sql="select attr_value,attr_id from ".$aos->table('goods_attr')." where goods_id = '$lottery[goods_id]'";
    $attr=$db->getAll($sql);
    $smarty->assign('goods_attr',    $attr);
    $smarty->assign('lottery_attr',    $lottery['goods_attr']);
    $smarty->assign('form_act',    'update');
    $smarty->assign('lottery',   $lottery);
    $smarty->assign('lottery_id',   $lottery_id);
    $smarty->display('lottery_info.htm');
}
if ($operation == 'update')
{
	$lottery=array();
    /* 初始化变量 */
    $lottery_id              = !empty($_POST['id'])       ? intval($_POST['id'])     : 0;
    $old_cat_name        = $_POST['old_cat_name'];
    $lottery['lottery_start_time']   = !empty($_POST['start_date'])   ? local_strtotime($_POST['start_date']) : '';
    $lottery['lottery_end_time']     = !empty($_POST['end_date'])     ? local_strtotime($_POST['end_date'])     : '';
    $lottery['lottery_price'] = !empty($_POST['lottery_price']) ? trim($_POST['lottery_price']) : '';
    $lottery['lottery_tuan_num']     = !empty($_POST['lottery_tuan_num'])     ? intval($_POST['lottery_tuan_num'])     : '';
    $lottery['lottery_desc']      = !empty($_POST['lottery_desc'])      ? trim($_POST['lottery_desc'])    : 0;
    $lottery['lottery_number']      = !empty($_POST['lottery_number'])      ? intval($_POST['lottery_number'])    : 0;
    $lottery['goods_attr']      = !empty($_POST['goods_attr'])       ? intval($_POST['goods_attr'])     : 0;
    /* 判断分类名是否重复 */

    if (empty($lottery_id))
    {
        $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
           sys_msg('错误操作!', 0, $link);
        
    }
    
    if(empty($lottery['lottery_start_time'])||empty($lottery['lottery_end_time'])||empty($lottery['lottery_price'])||empty($lottery['lottery_tuan_num'])||empty($lottery['lottery_number'])){

    	$link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       	sys_msg('请把信息填写完整!', 0, $link);

    }

	
    if ($db->autoExecute($aos->table('lottery'), $lottery, 'UPDATE', "lottery_id='$lottery_id'"))
    {
        
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => '返回列表', 'href' => 'index.php?act=lottery&op=lottery_list');
        sys_msg('抽奖活动编辑成功!', 0, $link);
    }
}
if ($operation == 'remove')
{
    check_authz_json('bonus_manage');

    $id = intval($_REQUEST['id']);

    $sql="select order_id from ".$aos->table('order_info')." where act_id = '$id' and extension_code = 'lottery'";
    $r=$db->getOne($sql);
    if($r){
        //sys_msg('已存在订单，不能删除！', 0, array(array('href'=>'index.php?act=lottery&op=lottery_list' , 'text' =>'抽奖列表')));
        make_json_error('已存在订单，不能删除！');
    }


    

    /* 删除用户的优惠券 */
    $db->query("DELETE FROM " .$aos->table('lottery'). " WHERE lottery_id = '$id'");

    //$url = 'index.php?act=coupon&op=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    make_json_result($id);
    exit;
}
if ($operation == 'view')
{
    $lottery_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
    $smarty->assign('status_list', $_LANG['ts']);
    $smarty->assign('full_page',        1);
    $tuan_list = get_lottery_tuan($lottery_id);
    $smarty->assign('tuan_list',    $tuan_list['orders']);
    $sql = 'SELECT lottery_start_time,lottery_end_time,lottery_status '.
       'FROM ' .$GLOBALS['aos']->table('lottery'). ' AS k '.
       ''.
       "WHERE lottery_id = $lottery_id ";
    $lottery=$db->getRow($sql);
    //可以进行抽奖的用户
    $sql="select o.order_id,u.nickname from ".$aos->table('order_info')." as o left join ".$aos->table('users')." as u on o.user_id = u.user_id  where o.tuan_status = 2 and o.act_id = $lottery_id and o.extension_code = 'lottery'";
    $can_lottery=$db->getAll($sql);
    
    $now_time = gmtime();
    if($now_time < $lottery['lottery_start_time'])
    {
        $lottery['status']  = '1';
    }
    else
    {
        if($now_time < $lottery['lottery_end_time'])
        {
            $lottery['status']  = '2';
            
        }
        else
        {
            $lottery['status']  = '3';
        }
    }
    /* 分页 */

    $pager = get_page($tuan_list['filter']);

    $smarty->assign('pager',   $pager);
    $smarty->assign('lottery_id',   $lottery_id);
    $smarty->assign('lottery',   $lottery);
    $smarty->display('lottery_view.htm');
}
/**
 * 抽奖
 */
if($operation == 'luck_start'){
    $lottery_id=intval($_REQUEST['id']);
    if($_REQUEST['luck_user']==1){
        
        $sql="SELECT o.order_id,o.user_id,u.nickname from ".$aos->table('order_info')." as o LEFT JOIN ".$aos->table('users')." as u on o.user_id = u.user_id where act_id = $lottery_id and order_status = 1 and extension_code = 'lottery'  and tuan_status = 2 and is_luck = 0";
        $succ=$db->getAll($sql);

        //print_r($succ);

        $smarty->assign('succ',$succ);
        $smarty->assign('lottery_id',$lottery_id);
        $smarty->display('luck_start.htm');
        die;
    }
    if(empty($lottery_id)){
        sys_msg('错误的入口!，请返回');
    }
    $zjuser=$_POST['zjuser'];
    $now_time = gmtime();
    
    $sql="SELECT lottery_number,lottery_end_time,lottery_status from ".$aos->table('lottery')." where lottery_id = $lottery_id ";
    $lottery=$db->getRow($sql);
    if(empty($lottery)){
        sys_msg('错误的入口!，请返回');
    }
    $lottery_number=$lottery['lottery_number'];
    if($lottery['lottery_end_time'] > $now_time ){
        sys_msg('活动时间还未结束', 0, array(array('href'=>"index.php?act=lottery&op=view&id=$lottery_id" , 'text' =>'返回')));
    }
    if($lottery['lottery_status'] == 1 ){
        sys_msg('请勿重复抽奖', 0, array(array('href'=>"index.php?act=lottery&op=view&id=$lottery_id" , 'text' =>'返回')));
    }
    //判断选中用户是否合法
    if(!empty($zjuser)){
        //内定用户
        $zjuser=implode(',',$zjuser);
        $sql="SELECT order_id from ".$aos->table('order_info')." where act_id = $lottery_id and order_status = 1 and extension_code = 'lottery'  and tuan_status = 2 and is_luck = 0 and order_id in ($zjuser)";
        $order_id=$db->getCol($sql);
        if(count($order_id)>$lottery_number){
            sys_msg('选择用户过多，最多选'.$lottery_number.'个中奖用户');
        }
    }
    $where = '';
    if(!empty($order_id)){
        //内定用户不用重新选择
        $nei_order=implode(',',$order_id);
        $where = " and order_id not in ($nei_order)";
    }
    $sql="SELECT order_id from ".$aos->table('order_info')." where act_id = $lottery_id and order_status = 1 and extension_code = 'lottery'  and tuan_status = 2 and is_luck = 0 ".$where;
    $succ_order=$db->getCol($sql);
    if(empty($succ_order)){
        sys_msg('没有成团用户，不能抽奖', 0, array(array('href'=>"index.php?act=lottery&op=view&id=$lottery_id" , 'text' =>'返回')));
    }
    //处理未成团订单

    $sql="SELECT order_id,order_sn,pay_status,bonus_id,integral,user_id from ".$aos->table('order_info')." where order_status in (0,1)  and act_id = $lottery_id and extension_code = 'lottery' and tuan_status in (0,1) ";
    $refund_order=$db->getALL($sql);
    foreach($refund_order as $vo){
        return_user_surplus_integral_bonus($vo);
        if($vo['pay_status'] == 2){
            $sql="update ".$aos->table('order_info')." set tuan_status = 3 where order_id = $vo[order_id]";
            $db->query($sql);
            order_action($vo['order_sn'], 1, 0, $vo['pay_status'], '未成团抽奖失败', '');
        }else{
            $sql="update ".$aos->table('order_info')." set order_status = 3 and tuan_status = 4 where order_id = $vo[order_id]";
            $db->query($sql);
            order_action($vo['order_sn'], 1, 0, $vo['pay_status'], '未支付抽奖失败，无效', '');
        }
    }
    $count_succ=count($succ_order);

    if($count_succ<$lottery_number){
        $lottery_number=count($succ_order);
    }
    if($lottery_number<1){
        sys_msg('最少设置一个中奖用户');
    }
    $lottery_won='';
    global $admin_wechat;
    //如果有选定用户抽奖减去数量
    $time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
    if(!empty($order_id)){
        $number=count($order_id);
        $lottery_number=$lottery_number-$number;
        foreach($order_id as $vo){
            $sql="update ".$aos->table('order_info')." set is_luck = 1 where order_id = ".$vo;
            $db->query($sql);
            $lottery_won.=$vo.',';
            $sql="select g.goods_name,o.order_id,o.user_id from ".$aos->table('order_info')." as o left join ".$aos->table('order_goods')." as g on o.order_id = g.order_id where o.order_id = ".$vo;
            $row=$db->getRow($sql);
            $message=getMessage(8);
            $wx_title='成团中奖通知';
            $openid=getOpenid($row['user_id']);
            $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$vo;
            $wx_desc = $message['title']."\r\n中奖产品：".$row['goods_name']."\r\n中奖时间：".$time."\r\n".$message['note'];
            //$wx_pic = $aos_url;
            $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        }
    }else{
       $order_id=array(); 
    }
    if($lottery_number>0){
       $random_keys=array_rand($succ_order,$lottery_number);
        if(!is_array($random_keys)){
            $random_keys=array(0=>$random_keys);
        } 
    }else{
        $random_keys=array(0=>$random_keys);
    }
    
    
    
    foreach($succ_order as $key=>$val){
        if($lottery_number>0 && in_array($key, $random_keys)){
        
            $sql="update ".$aos->table('order_info')." set is_luck = 1 where order_id = ".$val;
            $db->query($sql);
            $lottery_won.=$val.',';
            //中奖发送模版消息
            $sql="select g.goods_name,o.order_id,o.user_id from ".$aos->table('order_info')." as o left join ".$aos->table('order_goods')." as g on o.order_id = g.order_id where o.order_id = ".$val;
            $row=$db->getRow($sql);
            $message=getMessage(8);
            $wx_title='成团中奖通知';
            $openid=getOpenid($row['user_id']);
            $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$val;
            $wx_desc = $message['title']."\r\n中奖产品：".$row['goods_name']."\r\n中奖时间：".$time."\r\n".$message['note'];
            //$wx_pic = $aos_url;
            $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        
        }else{
            //if(!in_array($val, $order_id)){
               $sql="update ".$aos->table('order_info')." set is_luck = 2 where order_id = ".$val;
                $db->query($sql);
                $sql="select g.goods_name,g.goods_id,g.goods_price,o.user_id,o.surplus,o.tuan_num,o.order_status,o.pay_status,o.shipping_status,o.`order_sn`,o.`order_id`,o.`pay_id`,o.`money_paid`,o.`order_amount`,o.`extension_id`,o.`extension_code`,o.act_id,o.bonus_id,o.integral from ".$GLOBALS['aos']->table('order_info')." as o  LEFT JOIN ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id where o.order_id =$val ";
                $lottery_ref= $GLOBALS['db']->getRow($sql);
                //未中奖发送模版消息
                $message=getMessage(9);
                $wx_title='成团未中奖通知';
                $openid=getOpenid($lottery_ref['user_id']);
                $wx_url=$GLOBALS['aos']->url()."index.php?c=user&a=order_detail&order_id=".$val;
                $wx_desc = $message['title']."\r\n未中奖产品：".$lottery_ref['goods_name']."\r\n抽奖时间：".$time."\r\n".$message['note'];
                //$wx_pic = $aos_url;
                $aaa = $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                $r= refunds($lottery_ref,$lottery_ref['money_paid'],'lottery'); 
                return_user_surplus_integral_bonus($lottery_ref);
            //}
            
        } 
    }
    
    $lottery_won=substr($lottery_won,0,-1);
    $sql="update ".$aos->table('lottery')." set lottery_won = '$lottery_won',lottery_status = 1 where lottery_id = ".$lottery_id;
    $db->query($sql);
    sys_msg('抽奖完成', 0, array(array('href'=>"index.php?act=lottery&op=luck_users&id=$lottery_id" , 'text' =>'查看中奖名单')));

}
/**
 * 中奖用户
 */
if($operation == 'luck_users'){

    $lottery_id=intval($_REQUEST['id']);
    $sql="SELECT lottery_status from ".$aos->table('lottery')." where lottery_id = $lottery_id ";
    $lottery_status = $db->getOne($sql);
    if($lottery_status != 1){
        sys_msg('还未进行抽奖', 0, array(array('href'=>"index.php?act=lottery&op=view&id=6" , 'text' =>'活动详情')));
    }
    $now_time = gmtime();
    
    $sql="SELECT u.nickname,o.order_id,o.mobile,o.user_id from ".$aos->table('order_info')." as o left join ".$aos->table('users')." as u on o.user_id = u.user_id where o.act_id = $lottery_id and o.is_luck = 1";
    $lottery=$db->getAll($sql);
    $smarty->assign('lottery_list',$lottery);
    
    $smarty->display('lottery_users.htm');

}
elseif ($operation== 'toggle_enabled')
{
    $lottery_id       = intval($_POST['id']);
    $enabled        = intval($_POST['val']);

    if ($exc->edit("enabled = '$enabled'", $lottery_id))
    {
        clear_cache_files();
        make_json_result($enabled);
    }
}
/* 获得抽奖列表 */
function get_lottery_list()
{
    $now_time = gmtime();
        $filter = array();
        
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? ' k.lottery_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('lottery'). ' AS k '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('goods'). ' AS g ON k.goods_id = g.goods_id '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT k.* , g.goods_name '.
               'FROM ' .$GLOBALS['aos']->table('lottery'). ' AS k '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('goods'). ' AS g ON k.goods_id = g.goods_id '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        set_filter($filter, $sql);
    
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {


        if($now_time < $rows['lottery_start_time'])
        {
            $rows['status']  = '尚未开始';
        }
        else
        {
            if($now_time < $rows['lottery_end_time'])
            {
                $rows['status']  = '进行中';
                
            }
            else
            {
                $rows['status']  = '已结束';
            }
        }
        $rows['lottery_start_time'] = local_date('Y-m-d H:i:s', $rows['lottery_start_time']);
        $rows['lottery_end_time'] = local_date('Y-m-d H:i:s', $rows['lottery_end_time']);

        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}
/* 获得抽奖列表 */
function get_lottery_tuan($lottery_id)
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
        {
            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
        }
        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
        $filter['composite_status'] = isset($_REQUEST['status']) ? intval($_REQUEST['status']) : -1;
        $where = "WHERE o.act_id = $lottery_id AND o.extension_code = 'lottery' AND o.tuan_first = 1  AND o.pay_status = 2 ";
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
    }
    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
?>