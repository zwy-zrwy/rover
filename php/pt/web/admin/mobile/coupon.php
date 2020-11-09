<?php

define('IN_AOS', true);


/* 初始化$exc对象 */
$exc = new exchange($aos->table('bonus_type'), $db, 'type_id', 'type_name');
admin_priv('coupon_manage');
/*------------------------------------------------------ */
//-- 优惠券类型列表页面
/*------------------------------------------------------ */
if ($operation == 'bonus_type')
{

    $list = get_type_list();

    $smarty->assign('type_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 分页 */
  
    $pager = get_page($list['filter']);

    $smarty->assign('pager',   $pager);

    
    $smarty->display('bonus_type.htm');
}

/*------------------------------------------------------ */
//-- 翻页、排序
/*------------------------------------------------------ */

if ($operation == 'query')
{
    $list = get_type_list();

    $smarty->assign('type_list',    $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('bonus_type.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 编辑优惠券类型名称
/*------------------------------------------------------ */

if ($operation == 'edit_type_name')
{
    check_authz_json('bonus_manage');

    $id = intval($_POST['id']);
    $val = json_str_iconv(trim($_POST['val']));

    /* 检查优惠券类型名称是否重复 */
    if (!$exc->is_only('type_name', $id, $val))
    {
        make_json_error('此类型的名称已经存在!');
    }
    else
    {
        $exc->edit("type_name='$val'", $id);

        make_json_result(stripslashes($val));
    }
}

/*------------------------------------------------------ */
//-- 编辑优惠券金额
/*------------------------------------------------------ */

if ($operation == 'edit_type_money')
{
    check_authz_json('bonus_manage');

    $id = intval($_POST['id']);
    $val = floatval($_POST['val']);

    /* 检查优惠券类型名称是否重复 */
    if ($val <= 0)
    {
        make_json_error('金额必须是数字并且不能小于 0 !');
    }
    else
    {
        $exc->edit("type_money='$val'", $id);

        make_json_result(number_format($val, 2));
    }
}

/*------------------------------------------------------ */
//-- 删除优惠券类型
/*------------------------------------------------------ */
if ($operation == 'remove')
{
    check_authz_json('bonus_manage');

    $id = intval($_REQUEST['id']);

    $sql="select bonus_id from ".$aos->table('user_bonus')." where bonus_type_id = '$id'";
    $r=$db->getOne($sql);
    if($r){
        make_json_error('存在已发放优惠劵，不能删除！');
    }

    $exc->drop($id);

    /* 更新商品信息 */
    $db->query("UPDATE " .$aos->table('goods'). " SET bonus_type_id = 0 WHERE bonus_type_id = '$id'");

    /* 删除用户的优惠券 */
    $db->query("DELETE FROM " .$aos->table('user_bonus'). " WHERE bonus_type_id = '$id'");

    //$url = 'index.php?act=coupon&op=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    make_json_result($id);
    exit;
}

/*------------------------------------------------------ */
//-- 优惠券类型添加页面
/*------------------------------------------------------ */
if ($operation == 'add')
{

    $smarty->assign('lang',         $_LANG);
    $smarty->assign('action',       'add');

    $smarty->assign('form_act',     'insert');
    $smarty->assign('cfg_lang',     $_CFG['lang']);

    $next_month = local_strtotime('+1 months');
    $bonus_arr['send_start_date']   = local_date('Y-m-d H:i:s');
    $bonus_arr['use_start_date']    = local_date('Y-m-d H:i:s');
    $bonus_arr['send_end_date']     = local_date('Y-m-d H:i:s', $next_month);
    $bonus_arr['use_end_date']      = local_date('Y-m-d H:i:s', $next_month);

    $smarty->assign('bonus_arr',    $bonus_arr);

    
    $smarty->display('bonus_type_info.htm');
}

/*------------------------------------------------------ */
//-- 优惠券类型添加的处理
/*------------------------------------------------------ */
if ($operation == 'insert')
{
    /* 去掉优惠券类型名称前后的空格 */
    $type_name   = !empty($_POST['type_name']) ? trim($_POST['type_name']) : '';

    /* 初始化变量 */
    $type_id     = !empty($_POST['type_id'])    ? intval($_POST['type_id'])    : 0;
    $min_amount  = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;
    $integral  = !empty($_POST['integral']) ? intval($_POST['integral']) : 0;
    $goods_id  = !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;

    /* 检查类型是否有重复 */
    $sql = "SELECT COUNT(*) FROM " .$aos->table('bonus_type'). " WHERE type_name='$type_name'";
    if ($db->getOne($sql) > 0)
    {
        $link[] = array('text' => "返回", 'href' => 'javascript:history.back(-1)');
        sys_msg("类型名称重复", 0, $link);
    }

    /* 获得日期信息 */
    $send_startdate = local_strtotime($_POST['send_start_date']);
    $send_enddate   = local_strtotime($_POST['send_end_date']);
    $use_startdate  = local_strtotime($_POST['use_start_date']);
    $use_enddate    = local_strtotime($_POST['use_end_date']);

    /* 插入数据库。 */
    $sql = "INSERT INTO ".$aos->table('bonus_type')." (type_name, type_money,send_start_date,send_end_date,use_start_date,use_end_date,send_type,min_amount,min_goods_amount,integral,goods_id)
    VALUES ('$type_name',
            '$_POST[type_money]',
            '$send_startdate',
            '$send_enddate',
            '$use_startdate',
            '$use_enddate',
            '$_POST[send_type]',
            '$min_amount',
            '" . floatval($_POST['min_goods_amount']) . "',
            '$integral','$goods_id')";

    $db->query($sql);
    /* 记录管理员操作 */
    admin_log($_POST['type_name'], 'add', 'bonustype');

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    $link[0]['text'] = "继续添加";
    $link[0]['href'] = 'index.php?act=coupon&op=add';

    $link[1]['text'] = "返回";
    $link[1]['href'] = 'index.php?act=coupon&op=bonus_type';

    sys_msg("添加" . "&nbsp;" .$_POST['type_name'] . "&nbsp;" . "成功",0, $link);

}

/*------------------------------------------------------ */
//-- 优惠券类型编辑页面
/*------------------------------------------------------ */
if ($operation == 'edit')
{

    /* 获取优惠券类型数据 */
    $type_id = !empty($_GET['type_id']) ? intval($_GET['type_id']) : 0;



    $bonus_arr = $db->getRow("SELECT b.*,g.goods_name FROM " .$aos->table('bonus_type'). " as b left join ".$aos->table('goods')." as g on b.goods_id = g.goods_id WHERE type_id = '$type_id'");

    $bonus_arr['send_start_date']   = local_date('Y-m-d H:i:s', $bonus_arr['send_start_date']);
    $bonus_arr['send_end_date']     = local_date('Y-m-d H:i:s', $bonus_arr['send_end_date']);
    $bonus_arr['use_start_date']    = local_date('Y-m-d H:i:s', $bonus_arr['use_start_date']);
    $bonus_arr['use_end_date']      = local_date('Y-m-d H:i:s', $bonus_arr['use_end_date']);

    $smarty->assign('lang',        $_LANG);
    $smarty->assign('form_act',    'update');
    $smarty->assign('bonus_arr',   $bonus_arr);

    
    $smarty->display('bonus_type_info.htm');
}

/*------------------------------------------------------ */
//-- 优惠券类型编辑的处理
/*------------------------------------------------------ */
if ($operation == 'update')
{
    /* 获得日期信息 */
    $send_startdate = local_strtotime($_POST['send_start_date']);
    $send_enddate   = local_strtotime($_POST['send_end_date']);
    $use_startdate  = local_strtotime($_POST['use_start_date']);
    $use_enddate    = local_strtotime($_POST['use_end_date']);

    /* 对数据的处理 */
    $type_name   = !empty($_POST['type_name'])  ? trim($_POST['type_name'])    : '';
    $type_id     = !empty($_POST['type_id'])    ? intval($_POST['type_id'])    : 0;
    $min_amount  = !empty($_POST['min_amount']) ? intval($_POST['min_amount']) : 0;
    $integral  = !empty($_POST['integral']) ? intval($_POST['integral']) : 0;
    $goods_id  = !empty($_POST['goods_id']) ? intval($_POST['goods_id']) : 0;

    $sql = "UPDATE " .$aos->table('bonus_type'). " SET ".
           "type_name       = '$type_name', ".
           "type_money      = '$_POST[type_money]', ".
           "send_start_date = '$send_startdate', ".
           "send_end_date   = '$send_enddate', ".
           "use_start_date  = '$use_startdate', ".
           "use_end_date    = '$use_enddate', ".
           "send_type       = '$_POST[send_type]', ".
           "min_amount      = '$min_amount', " .
           "min_goods_amount = '" . floatval($_POST['min_goods_amount']) . "', ".
           "integral      = '$integral', " .
           "goods_id      = '$goods_id' " .
           "WHERE type_id   = '$type_id'";

   $db->query($sql);
   /* 记录管理员操作 */
   admin_log($_POST['type_name'], 'edit', 'bonustype');

   /* 清除缓存 */
   clear_cache_files();

   /* 提示信息 */
   $link[] = array('text' => "返回列表", 'href' => 'index.php?act=coupon&op=bonus_type&' . list_link_postfix());
   sys_msg("编辑" .' '.$_POST['type_name'].' '. "成功", 0, $link);

}

/*------------------------------------------------------ */
//-- 优惠券发送页面
/*------------------------------------------------------ */
if ($operation == 'send')
{

    /* 取得参数 */
    $id = !empty($_REQUEST['id'])  ? intval($_REQUEST['id'])  : '';

    if ($_REQUEST['send_by'] == 0)
    {
        $smarty->assign('id',           $id);
        $smarty->display('bonus_by_user.htm');
    }
    elseif ($_REQUEST['send_by'] == 1)
    {
        /* 查询此优惠券类型信息 */
        $bonus_type = $db->GetRow("SELECT type_id, type_name FROM ".$aos->table('bonus_type').
            " WHERE type_id='$_REQUEST[id]'");

        /* 查询优惠券类型的商品列表 */
        $goods_list = get_bonus_goods($_REQUEST['id']);
        $smarty->assign('id',intval($_REQUEST['id']));
        /* 查询其他优惠券类型的商品 */
        $sql = "SELECT goods_id FROM " .$aos->table('goods').
               " WHERE bonus_type_id > 0 AND bonus_type_id <> '$_REQUEST[id]'";
        $other_goods_list = $db->getCol($sql);
        $smarty->assign('other_goods', join(',', $other_goods_list));

        /* 模板赋值 */
        $smarty->assign('cat_list',    cat_list());

        $smarty->assign('bonus_type',  $bonus_type);
        $smarty->assign('goods_list',  $goods_list);

        $smarty->display('bonus_by_goods.htm');
    }
    elseif ($_REQUEST['send_by'] == 3)
    {
        $smarty->assign('type_list',    get_bonus_type());

        $smarty->display('bonus_by_print.htm');
    }
}

/*------------------------------------------------------ */
//-- 处理优惠券的发送页面
/*------------------------------------------------------ */
if ($operation == 'send_by_user')
{
    $user_list  = array();
    $start      = empty($_REQUEST['start']) ? 0 : intval($_REQUEST['start']);
    $limit      = empty($_REQUEST['limit']) ? 10 : intval($_REQUEST['limit']);
    $send_count = 0;

    if (isset($_REQUEST['send_user']))
    {
        /* 按会员列表发放优惠券 */
        /* 如果是空数组，直接返回 */
        if (empty($_REQUEST['user']))
        {
            sys_msg("没有选择会员", 1);
        }

        $user_array = (is_array($_REQUEST['user'])) ? $_REQUEST['user'] : explode(',', $_REQUEST['user']);
        $send_count = count($user_array);

        $id_array   = array_slice($user_array, $start, $limit);

        /* 根据会员ID取得用户名和邮件地址 */
        $sql = "SELECT user_id, nickname FROM " .$aos->table('users').
               " WHERE user_id " .db_create_in($id_array);
        $user_list  = $db->getAll($sql);
        $count = count($user_list);
    }

    /* 发送优惠券 */
    $loop       = 0;
    $bonus_type = bonus_type_info($_REQUEST['id']);
    //模板消息
    $use_time=local_date("m月d日", $bonus_type['use_start_date']).'-'.local_date("m月d日", $bonus_type['use_end_date']);
    $wx_title = "获得优惠劵通知";
    $wx_desc = "恭喜您获得优惠劵\r\n优惠劵金额：".$bonus_type['type_money']."元\r\n有效期：".$use_time;
    foreach ($user_list AS $key => $val)
    {
        /* 更新数据库 */
        /*$sql="select user_id from ".$aos->table('user_bonus')." where user_id = $val[user_id] and bonus_type_id = $_REQUEST[id]";
        $r=$db->getOne($sql);
        if($r){
            break;
        }*/
        $sql = "INSERT INTO " . $aos->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id) " .
                "VALUES ('$_REQUEST[id]', 0, '$val[user_id]', 0, 0)";
        $db->query($sql);
        $openid=getOpenid($val[user_id]);
        
        $admin_wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        if ($loop >= $limit)
        {
            break;
        }
        else
        {
            $loop++;
        }
    }

    //admin_log(addslashes($_LANG['send_bonus']), 'add', 'bonustype');
    if ($send_count > ($start + $limit))
    {
        /*  */
        $href = "index.php?act=coupon&op=send_by_user&start=" . ($start+$limit) . "&limit=$limit&id=$_REQUEST[id]&";

        if (isset($_REQUEST['send_rank']))
        {
            $href .= "send_rank=1&rank_id=$rank_id";
        }

        if (isset($_REQUEST['send_user']))
        {
            $href .= "send_user=1&user=" . implode(',', $user_array);
        }

        $link[] = array('text' => '继续发放优惠券', 'href' => $href);
    }

    $link[] = array('text' => "返回列表", 'href' => 'index.php?act=coupon&op=bonus_type');

    sys_msg("成功发放".$count."个优惠劵", 0, $link);
}

/*------------------------------------------------------ */
//-- 按印刷品发放优惠券
/*------------------------------------------------------ */

if ($operation == 'send_by_print')
{
    @set_time_limit(0);

    /* 红下优惠券的类型ID和生成的数量的处理 */
    $bonus_typeid = !empty($_POST['bonus_type_id']) ? $_POST['bonus_type_id'] : 0;
    $bonus_sum    = !empty($_POST['bonus_sum'])     ? $_POST['bonus_sum']     : 1;

    /* 生成优惠券序列号 */
    $num = $db->getOne("SELECT MAX(bonus_sn) FROM ". $aos->table('user_bonus'));
    $num = $num ? floor($num / 10000) : 100000;

    for ($i = 0, $j = 0; $i < $bonus_sum; $i++)
    {
        $bonus_sn = ($num + $i) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $db->query("INSERT INTO ".$aos->table('user_bonus')." (bonus_type_id, bonus_sn) VALUES('$bonus_typeid', '$bonus_sn')");

        $j++;
    }

    /* 记录管理员操作 */
    admin_log($bonus_sn, 'add', 'userbonus');

    /* 清除缓存 */
    clear_cache_files();

    /* 提示信息 */
    $link[0]['text'] = "返回";
    $link[0]['href'] = 'index.php?act=coupon&op=bonus_list&bonus_type=' . $bonus_typeid;

    sys_msg("成功创建了" . $j . "个线下优惠劵", 0, $link);
}

/*------------------------------------------------------ */
//-- 导出线下发放的信息
/*------------------------------------------------------ */
if ($operation == 'gen_excel')
{
    @set_time_limit(0);

    /* 获得此线下优惠券类型的ID */
    $tid  = !empty($_GET['tid']) ? intval($_GET['tid']) : 0;
    $type_name = $db->getOne("SELECT type_name FROM ".$aos->table('bonus_type')." WHERE type_id = '$tid'");

    /* 文件名称 */
    $bonus_filename = $type_name .'_bonus_list';
    if (AO_CHARSET != 'gbk')
    {
        $bonus_filename = aos_iconv('UTF8', 'GB2312',$bonus_filename);
    }

    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=$bonus_filename.xls");

    /* 文件标题 */
    if (AO_CHARSET != 'gbk')
    {
        echo aos_iconv('UTF8', 'GB2312', '线下优惠券信息列表') . "\t\n";
        /* 优惠券序列号, 优惠券金额, 类型名称(优惠券名称), 使用结束日期 */
        echo aos_iconv('UTF8', 'GB2312', '优惠券序列号') ."\t";
        echo aos_iconv('UTF8', 'GB2312', '优惠券金额') ."\t";
        echo aos_iconv('UTF8', 'GB2312', '类型名称') ."\t";
        echo aos_iconv('UTF8', 'GB2312', '使用结束日期') ."\t\n";
    }
    else
    {
        echo '线下优惠券信息列表' . "\t\n";
        /* 优惠券序列号, 优惠券金额, 类型名称(优惠券名称), 使用结束日期 */
        echo '优惠券金额' ."\t";
        echo '优惠券金额' ."\t";
        echo '类型名称' ."\t";
        echo '使用结束日期' ."\t\n";
    }

    $val = array();
    $sql = "SELECT ub.bonus_id, ub.bonus_type_id, ub.bonus_sn, bt.type_name, bt.type_money, bt.use_end_date ".
           "FROM ".$aos->table('user_bonus')." AS ub, ".$aos->table('bonus_type')." AS bt ".
           "WHERE bt.type_id = ub.bonus_type_id AND ub.bonus_type_id = '$tid' ORDER BY ub.bonus_id DESC";
    $res = $db->query($sql);

    $code_table = array();
    while ($val = $db->fetchRow($res))
    {
        echo $val['bonus_sn'] . "\t";
        echo $val['type_money'] . "\t";
        if (!isset($code_table[$val['type_name']]))
        {
            if (AO_CHARSET != 'gbk')
            {
                $code_table[$val['type_name']] = aos_iconv('UTF8', 'GB2312', $val['type_name']);
            }
            else
            {
                $code_table[$val['type_name']] = $val['type_name'];
            }
        }
        echo $code_table[$val['type_name']] . "\t";
        echo local_date('Y-m-d H:i:s', $val['use_end_date']);
        echo "\t\n";
    }
}

/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($operation == 'get_goods_list')
{
    $keyword=trim($_REQUEST['keywords']);
    $cat_id =intval($_REQUEST['cat_id']);

    $arr = get_goods_list($keyword,$cat_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => $val['shop_price']);
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 添加发放优惠券的商品
/*------------------------------------------------------ */
if ($operation == 'add_bonus_goods')
{
    check_authz_json('bonus_manage');

    $add_ids    = $_REQUEST['target_select'];
    $type_id    = intval($_POST['id']);
    /*if(is_array($add_ids)){
        foreach ($add_ids AS $key => $val)
        {
            $sql = "UPDATE " .$aos->table('goods'). " SET bonus_type_id='$type_id' WHERE goods_id='$val'";
            $db->query($sql, 'SILENT');
        }
    }*/


    $sql="select bonus_type_id,goods_id from ".$aos->table('goods')." where bonus_type_id = $type_id";
    $type_array=$db->getAll($sql);
    foreach ($add_ids AS $key => $val)

    {
        $a=0;
        foreach($type_array as $key=>$vo){
            if($vo[goods_id]==$val){
                $a=1;
                $type_array[$key]=array();
            }
        }
        if($a==0){
            $sql = "UPDATE " .$aos->table('goods'). " SET bonus_type_id='$type_id' WHERE goods_id='$val'";
            $db->query($sql, 'SILENT');

        }
        
    }
    foreach($type_array as $key=>$vo){
        if(!empty($vo['bonus_type_id'])){
            $sql = "UPDATE " .$aos->table('goods'). " SET bonus_type_id= 0 WHERE goods_id=$vo[goods_id]";
            $db->query($sql, 'SILENT');
        }
        
    }

    /* 重新载入 */
    /*$arr = get_bonus_goods($type_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);*/
    $link[] = array('text' => '返回列表', 'href' => 'index.php?act=coupon&op=bonus_type');

    sys_msg("发放成功", 0, $link);
}

/*------------------------------------------------------ */
//-- 删除发放优惠券的商品
/*------------------------------------------------------ */
if ($operation == 'drop_bonus_goods')
{
    check_authz_json('bonus_manage');
    $drop_goods     = json_decode($_GET['drop_ids']);
    $drop_goods_ids = db_create_in($drop_goods);
    $arguments      = jso_decode($_GET['JSON']);
    $type_id        = $arguments[0];

    $db->query("UPDATE ".$aos->table('goods')." SET bonus_type_id = 0 ".
                "WHERE bonus_type_id = '$type_id' AND goods_id " .$drop_goods_ids);

    /* 重新载入 */
    $arr = get_bonus_goods($type_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 搜索用户
/*------------------------------------------------------ */
if ($operation == 'search_users')
{
    $keywords = json_str_iconv(trim($_GET['keywords']));

    $sql = "SELECT user_id, nickname FROM " . $aos->table('users') .
            " WHERE nickname LIKE '%" . mysql_like_quote($keywords) . "%' OR user_id LIKE '%" . mysql_like_quote($keywords) . "%'";
    $row = $db->getAll($sql);

    make_json_result($row);
}

/*------------------------------------------------------ */
//-- 优惠券列表
/*------------------------------------------------------ */

if ($operation == 'bonus_list')
{

    $list = get_bonus_list();

    /* 赋值是否显示优惠券序列号 */
    $bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
    if ($bonus_type['send_type'] == 3)
    {
        $smarty->assign('show_bonus_sn', 1);
    }

    /* 赋值是否显示发邮件操作和是否发过邮件 */
    elseif ($bonus_type['send_type'] == 0)
    {
        $smarty->assign('show_mail', 1);
    }

    $smarty->assign('bonus_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    /* 分页 */
  
    $pager = get_page($list['filter']);

    $smarty->assign('pager',   $pager);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    
    $smarty->display('bonus_list.htm');
}

/*------------------------------------------------------ */
//-- 优惠券列表翻页、排序
/*------------------------------------------------------ */

if ($operation == 'query_bonus')
{
    $list = get_bonus_list();

    /* 赋值是否显示优惠券序列号 */
    $bonus_type = bonus_type_info(intval($_REQUEST['bonus_type']));
    if ($bonus_type['send_type'] == 3)
    {
        $smarty->assign('show_bonus_sn', 1);
    }

    /* 赋值是否显示发邮件操作和是否发过邮件 */
    elseif ($bonus_type['send_type'] == 0)
    {
        $smarty->assign('show_mail', 1);
    }

    $smarty->assign('bonus_list',   $list['item']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    $sort_flag  = sort_flag($list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('bonus_list.htm'), '',
        array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

/*------------------------------------------------------ */
//-- 删除优惠券
/*------------------------------------------------------ */
elseif ($operation == 'remove_bonus')
{
    check_authz_json('bonus_manage');

    $id = intval($_REQUEST['id']);

    $db->query("DELETE FROM " .$aos->table('user_bonus'). " WHERE bonus_id='$id'");

    make_json_result($id);
    exit;
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */
if ($operation == 'batch')
{

    /* 去掉参数：优惠券类型 */
    $bonus_type_id = intval($_REQUEST['bonus_type']);

    /* 取得选中的优惠券id */
    if (isset($_POST['checkboxes']))
    {
        $bonus_id_list = $_POST['checkboxes'];

        /* 删除优惠券 */
        if (isset($_POST['drop']))
        {
            $sql = "DELETE FROM " . $aos->table('user_bonus'). " WHERE bonus_id " . db_create_in($bonus_id_list);
            $db->query($sql);

            admin_log(count($bonus_id_list), 'remove', 'userbonus');

            clear_cache_files();

            $link[] = array('text' => "返回列表",
                'href' => 'index.php?act=coupon&op=bonus_list&bonus_type='. $bonus_type_id);
            sys_msg("删除成功", 0, $link);
        }
    }
    else
    {
        sys_msg("没有选择优惠劵", 1);
    }
}

/**
 * 获取优惠券类型列表
 * @access  public
 * @return void
 */
function get_type_list()
{
    /* 获得所有优惠券类型的发放数量 */
    $sql = "SELECT bonus_type_id, COUNT(*) AS sent_count".
            " FROM " .$GLOBALS['aos']->table('user_bonus') .
            " GROUP BY bonus_type_id";
    $res = $GLOBALS['db']->query($sql);

    $sent_arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $sent_arr[$row['bonus_type_id']] = $row['sent_count'];
    }

    /* 获得所有优惠券类型的发放数量 */
    $sql = "SELECT bonus_type_id, COUNT(*) AS used_count".
            " FROM " .$GLOBALS['aos']->table('user_bonus') .
            " WHERE used_time > 0".
            " GROUP BY bonus_type_id";
    $res = $GLOBALS['db']->query($sql);

    $used_arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $used_arr[$row['bonus_type_id']] = $row['used_count'];
    }

    $result = get_filter();
    if ($result === false)
    {
        /* 查询条件 */
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'type_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['aos']->table('bonus_type');
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        $sql = "SELECT * FROM " .$GLOBALS['aos']->table('bonus_type'). " ORDER BY $filter[sort_by] $filter[sort_order]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        switch ($row['send_type'])
        {
            case 0 :
                $row['send_by'] = '用户发放';
            break;
            case 1 :
                $row['send_by'] = '商品发放';
            break;
            case 2 :
                $row['send_by'] = '订单发放';
            break;
            case 3 :
                $row['send_by'] = '线下发放';
            break;
            case 4 :
                $row['send_by'] = '关注发放';
            break;
            case 5 :
                $row['send_by'] = '团长优惠';
            break;
            case 6 :
                $row['send_by'] = '积分兑换';
            break;
        }
        $row['send_count'] = isset($sent_arr[$row['type_id']]) ? $sent_arr[$row['type_id']] : 0;
        $row['use_count'] = isset($used_arr[$row['type_id']]) ? $used_arr[$row['type_id']] : 0;

        $arr[] = $row;
    }

    $arr = array('item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 查询优惠券类型的商品列表
 *
 * @access  public
 * @param   integer $type_id
 * @return  array
 */
function get_bonus_goods($type_id)
{
    $sql = "SELECT goods_id, goods_name FROM " .$GLOBALS['aos']->table('goods').
            " WHERE bonus_type_id = '$type_id'";
    $row = $GLOBALS['db']->getAll($sql);

    return $row;
}

/**
 * 获取用户优惠券列表
 * @access  public
 * @param   $page_param
 * @return void
 */
function get_bonus_list()
{
    /* 查询条件 */
    $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'bonus_type_id' : trim($_REQUEST['sort_by']);
    $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
    $filter['bonus_type'] = empty($_REQUEST['bonus_type']) ? 0 : intval($_REQUEST['bonus_type']);

    $where = empty($filter['bonus_type']) ? '' : " WHERE bonus_type_id='$filter[bonus_type]'";

    $sql = "SELECT COUNT(*) FROM ".$GLOBALS['aos']->table('user_bonus'). $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = "SELECT ub.*, u.nickname, o.order_sn, bt.type_name ".
          " FROM ".$GLOBALS['aos']->table('user_bonus'). " AS ub ".
          " LEFT JOIN " .$GLOBALS['aos']->table('bonus_type'). " AS bt ON bt.type_id=ub.bonus_type_id ".
          " LEFT JOIN " .$GLOBALS['aos']->table('users'). " AS u ON u.user_id=ub.user_id ".
          " LEFT JOIN " .$GLOBALS['aos']->table('order_info'). " AS o ON o.order_id=ub.order_id $where ".
          " ORDER BY ".$filter['sort_by']." ".$filter['sort_order'].
          " LIMIT ". $filter['start'] .", $filter[page_size]";
    $row = $GLOBALS['db']->getAll($sql);

    foreach ($row AS $key => $val)
    {
        $row[$key]['used_time'] = $val['used_time'] == 0 ?
            '未使用' : local_date($GLOBALS['_CFG']['date_format'], $val['used_time']);
        if($val['emailed'] == 0)
        {
            $row[$key]['emailed'] = '未发';
        }
        elseif($val['emailed'] == 1)
        {
            $row[$key]['emailed'] = '已发成功';
        }
        elseif($val['emailed'] == 2)
        {
            $row[$key]['emailed'] = '已发失败';
        }
    }

    $arr = array('item' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

/**
 * 取得优惠券类型信息
 * @param   int     $bonus_type_id  优惠券类型id
 * @return  array
 */
function bonus_type_info($bonus_type_id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('bonus_type') .
            " WHERE type_id = '$bonus_type_id'";

    return $GLOBALS['db']->getRow($sql);
}

?>