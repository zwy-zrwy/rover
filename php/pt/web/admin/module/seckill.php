<?php

define('IN_AOS', true);
admin_priv('seckill_manage');
/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'seckill_list';
}

if ($operation == 'seckill_list')
{
	$seckill_list = get_seckill_list();

    $smarty->assign('seckill_list',    $seckill_list['arr']);

    $pager = get_page($seckill_list['filter']);

    $smarty->assign('pager',   $pager);
    $smarty->display('seckill_list.htm');
}


elseif ($operation == 'seckill_add')
{
	$next_month = local_strtotime('+1 months');
	$seckill['seck_start_time']    = local_date('Y-m-d H:i:s');
    $seckill['seck_end_time']   = local_date('Y-m-d H:i:s', $next_month);
	$smarty->assign('seckill',   $seckill);

	$smarty->assign('form_act',     'insert');
    $smarty->display('seckill_info.htm');
}

elseif ($operation == 'insert')
{
	$seck=array();
    /* 初始化变量 */
    $seck['goods_id']       = !empty($_POST['goods_id'])       ? intval($_POST['goods_id'])     : 0;
    $seck['seck_start_time']   = !empty($_POST['start_date'])   ? local_strtotime($_POST['start_date']) : '';
    $seck['seck_end_time']     = !empty($_POST['end_date'])     ? local_strtotime($_POST['end_date'])     : '';
    $seck['seck_price'] = !empty($_POST['seckill_price']) ? trim($_POST['seckill_price']) : '';
    $seck['seck_tuan_num']     = !empty($_POST['seck_tuan_num'])     ? intval($_POST['seck_tuan_num'])     : '';
    $seck['seck_desc']      = !empty($_POST['seckill_desc'])      ? trim($_POST['seckill_desc'])    : 0;
    $seck['seckill_number']      = !empty($_POST['seckill_number'])      ? intval($_POST['seckill_number'])    : 0;
    $seck['goods_attr']      = is_array($_POST['goods_attr'])      ? implode(',',$_POST['goods_attr']):$_POST['goods_attr'];
    if(empty($seck['goods_id'])||empty($seck['seck_start_time'])||empty($seck['seck_end_time'])||empty($seck['seck_price'])||empty($seck['seck_tuan_num'])||empty($seck['seckill_number'])){

    	$link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       	sys_msg('请把信息填写完整!', 0, $link);

    }
    $now_time=gmtime();
    $sql="SELECT goods_id from ".$aos->table('seckill')." where goods_id = ".$seck['goods_id']." and seck_end_time > $now_time";
    $res=$db->getOne($sql);
    if ($res)
    {
        
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('该商品存在未结束的秒杀活动!', 0, $link);
    }
	

    /* 入库的操作 */
    if ($db->autoExecute($aos->table('seckill'), $seck) !== false)
    {
        $cat_id = $db->insert_id();
        admin_log($_POST['goods_id'], 'add', 'seckill');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加秒杀';
        $link[0]['href'] = 'index.php?act=seckill&op=seckill_add';

        $link[1]['text'] = '返回列表';
        $link[1]['href'] = 'index.php?act=seckill&op=seckill_list';

        sys_msg('秒杀活动添加成功!', 0, $link);
    }
}


elseif ($operation == 'seckill_edit')
{
	 /* 获取优惠券类型数据 */
    $seckill_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
    $seckill = $db->getRow("SELECT s.*,g.goods_name FROM " .$aos->table('seckill'). " as s left join ".$aos->table('goods')." as g on s.goods_id = g.goods_id WHERE seckill_id = '$seckill_id'");

    $seckill['seck_start_time']   = local_date('Y-m-d H:i:s', $seckill['seck_start_time']);
    $seckill['seck_end_time']     = local_date('Y-m-d H:i:s', $seckill['seck_end_time']);
    
    $seckill['goods_attr']     = explode(',',$seckill['goods_attr']);
    $sql="select attr_value,attr_id from ".$aos->table('goods_attr')." where goods_id = '$seckill[goods_id]'";
    $attr=$db->getAll($sql);

    $smarty->assign('form_act',    'update');
    $smarty->assign('goods_attr',    $attr);
    $smarty->assign('seckill_attr',    $seckill['goods_attr']);
    $smarty->assign('seckill',   $seckill);
    $smarty->assign('seckill_id',   $seckill_id);
    $smarty->display('seckill_info.htm');
}
if ($operation == 'update')
{
	$seck=array();
    /* 初始化变量 */
    $seckill_id              = !empty($_POST['id'])       ? intval($_POST['id'])     : 0;
    $goods_id        = $_POST['goods_id'];
    $seck['seck_start_time']   = !empty($_POST['start_date'])   ? local_strtotime($_POST['start_date']) : '';
    $seck['seck_end_time']     = !empty($_POST['end_date'])     ? local_strtotime($_POST['end_date'])     : '';
    $seck['seck_price'] = !empty($_POST['seckill_price']) ? trim($_POST['seckill_price']) : '';
    $seck['seck_tuan_num']     = !empty($_POST['seck_tuan_num'])     ? intval($_POST['seck_tuan_num'])     : '';
    $seck['seck_desc']      = !empty($_POST['seckill_desc'])      ? trim($_POST['seckill_desc'])    : 0;
    $seck['seckill_number']      = !empty($_POST['seckill_number'])      ? intval($_POST['seckill_number'])    : 0;
    $seck['goods_attr']      = is_array($_POST['goods_attr'])      ? implode(',',$_POST['goods_attr']):$_POST['goods_attr'];
    /* 判断分类名是否重复 */

    if (empty($seckill_id))
    {
        $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
           sys_msg('错误操作!', 0, $link);
        
    }
    
    if(empty($seck['seck_start_time'])||empty($seck['seck_end_time'])||empty($seck['seck_price'])||empty($seck['seck_tuan_num'])||empty($seck['seckill_number'])){

    	$link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       	sys_msg('请把信息填写完整!', 0, $link);

    }
    $now_time=gmtime();
    $sql="SELECT goods_id from ".$aos->table('seckill')." where goods_id = ".$goods_id." and seck_end_time > $now_time and seckill_id != $seckill_id";
    $res=$db->getOne($sql);
    if ($res)
    {
        
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('该商品存在未结束的秒杀活动!', 0, $link);
    }
	
    if ($db->autoExecute($aos->table('seckill'), $seck, 'UPDATE', "seckill_id='$seckill_id'"))
    {
        
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => '返回列表', 'href' => 'index.php?act=seckill&op=seckill_list');
        sys_msg('秒杀活动编辑成功!', 0, $link);
    }
}
if ($operation == 'remove')
{
    check_authz_json('bonus_manage');

    $id = intval($_REQUEST['id']);

    $sql="select order_id from ".$aos->table('order_info')." where act_id = '$id' and extension_code = 'miao'";
    $r=$db->getOne($sql);
    if($r){
        //sys_msg('已存在订单，不能删除！', 0, array(array('href'=>'index.php?act=seckill&op=seckill_list' , 'text' =>'秒杀列表')));
        make_json_error('已存在订单，不能删除！');
    }


    

    /* 删除用户的优惠券 */
    $db->query("DELETE FROM " .$aos->table('seckill'). " WHERE seckill_id = '$id'");

    //$url = 'index.php?act=coupon&op=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    make_json_result($id);
    exit;
}
/* 获得秒杀列表 */
function get_seckill_list()
{
    $now_time = gmtime();
        $filter = array();
        
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? ' k.seckill_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('seckill'). ' AS k '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('goods'). ' AS g ON k.goods_id = g.goods_id '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT k.* , g.goods_name '.
               'FROM ' .$GLOBALS['aos']->table('seckill'). ' AS k '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('goods'). ' AS g ON k.goods_id = g.goods_id '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        set_filter($filter, $sql);
    
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        if($now_time < $rows['seck_start_time'])
        {
            $rows['status']  = '尚未开始';
        }
        else
        {
            if($now_time < $rows['seck_end_time'])
            {
                if($rows['seckill_sales'] >= $rows['seckill_number'])
                {
                    $rows['status']  = '进行中(已售罄)';
                }
                else
                {
                    $rows['status']  = '进行中';
                }
                
            }
            else
            {
                $rows['status']  = '已结束';
            }
        }


        $rows['seck_start_time'] = local_date('Y-m-d H:i:s', $rows['seck_start_time']);
        $rows['seck_end_time'] = local_date('Y-m-d H:i:s', $rows['seck_end_time']);


        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>