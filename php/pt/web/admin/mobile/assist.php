<?php

define('IN_AOS', true);
admin_priv('assist_manage');
/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'assist_list';
}

if ($operation == 'assist_list')
{
	$assist_list = get_assist_list();

    $smarty->assign('assist_list',    $assist_list['arr']);

    $pager = get_page($assist_list['filter']);

    $smarty->assign('pager',   $pager);
    $smarty->display('assist_list.htm');
}


elseif ($operation == 'assist_add')
{
	$next_month = local_strtotime('+1 months');
	$assist['assist_start_time']    = local_date('Y-m-d H:i:s');
    $assist['assist_end_time']   = local_date('Y-m-d H:i:s', $next_month);
	$smarty->assign('assist',   $assist);

	$smarty->assign('form_act',     'insert');
    $smarty->display('assist_info.htm');
}

elseif ($operation == 'insert')
{
	$assist=array();
    /* 初始化变量 */
    $assist['goods_id']       = !empty($_POST['goods_id'])       ? intval($_POST['goods_id'])     : 0;
    $assist['assist_start_time']   = !empty($_POST['start_date'])   ? local_strtotime($_POST['start_date']) : '';
    $assist['assist_end_time']     = !empty($_POST['end_date'])     ? local_strtotime($_POST['end_date'])     : '';
    $assist['assist_tuan_num']     = !empty($_POST['assist_tuan_num'])     ? intval($_POST['assist_tuan_num'])     : '';
    $assist['assist_number']      = !empty($_POST['assist_number'])      ? intval($_POST['assist_number'])    : 0;
    $assist['goods_attr']      = !empty($_POST['goods_attr'])       ? intval($_POST['goods_attr'])     : 0;
    
    if(empty($assist['goods_id'])||empty($assist['assist_start_time'])||empty($assist['assist_end_time'])||empty($assist['assist_tuan_num'])||empty($assist['assist_number'])){

    	$link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       	sys_msg('请把信息填写完整!', 0, $link);

    }
    $now_time=gmtime();
    $sql="SELECT goods_id from ".$aos->table('assist')." where goods_id = ".$assist['goods_id']." and assist_end_time > $now_time";
    $res=$db->getOne($sql);
    if ($res)
    {
        
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('该商品存在未结束的助力活动!', 0, $link);
    }
	

    /* 入库的操作 */
    if ($db->autoExecute($aos->table('assist'), $assist) !== false)
    {
        $cat_id = $db->insert_id();
        admin_log($_POST['goods_id'], 'add', 'assist');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加助力';
        $link[0]['href'] = 'index.php?act=assist&op=assist_add';

        $link[1]['text'] = '返回列表';
        $link[1]['href'] = 'index.php?act=assist&op=assist_list';

        sys_msg('助力活动添加成功!', 0, $link);
    }
}


elseif ($operation == 'assist_edit')
{
	 /* 获取优惠券类型数据 */
    $assist_id = !empty($_GET['id']) ? intval($_GET['id']) : 0;
    $assist = $db->getRow("SELECT s.*,g.goods_name FROM " .$aos->table('assist'). " as s left join ".$aos->table('goods')." as g on s.goods_id = g.goods_id WHERE assist_id = '$assist_id'");

    $assist['assist_start_time']   = local_date('Y-m-d H:i:s', $assist['assist_start_time']);
    $assist['assist_end_time']     = local_date('Y-m-d H:i:s', $assist['assist_end_time']);
    
    $sql="select attr_value,attr_id from ".$aos->table('goods_attr')." where goods_id = '$assist[goods_id]'";
    $attr=$db->getAll($sql);

    $smarty->assign('form_act',    'update');
    $smarty->assign('goods_attr',    $attr);
    $smarty->assign('assist_attr',    $assist['goods_attr']);
    $smarty->assign('assist',   $assist);
    $smarty->assign('assist_id',   $assist_id);
    $smarty->display('assist_info.htm');
}
if ($operation == 'update')
{
	$assist=array();
    /* 初始化变量 */
    $assist_id              = !empty($_POST['id'])       ? intval($_POST['id'])     : 0;
    $goods_id        = $_POST['goods_id'];
    $assist['assist_start_time']   = !empty($_POST['start_date'])   ? local_strtotime($_POST['start_date']) : '';
    $assist['assist_end_time']     = !empty($_POST['end_date'])     ? local_strtotime($_POST['end_date'])     : '';
    $assist['assist_tuan_num']     = !empty($_POST['assist_tuan_num'])     ? intval($_POST['assist_tuan_num'])     : '';
    $assist['assist_number']      = !empty($_POST['assist_number'])      ? intval($_POST['assist_number'])    : 0;
    $assist['goods_attr']      = !empty($_POST['goods_attr'])       ? intval($_POST['goods_attr'])     : 0;
    /* 判断分类名是否重复 */

    if (empty($assist_id))
    {
        $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
           sys_msg('错误操作!', 0, $link);
        
    }
    
    if(empty($assist['assist_start_time'])||empty($assist['assist_end_time'])||empty($assist['assist_tuan_num'])||empty($assist['assist_number'])){

    	$link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       	sys_msg('请把信息填写完整!', 0, $link);

    }
    $now_time=gmtime();
    $sql="SELECT goods_id from ".$aos->table('assist')." where goods_id = ".$goods_id." and assist_end_time > $now_time and assist_id != $assist_id";
    $res=$db->getOne($sql);
    if ($res)
    {
        
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('该商品存在未结束的助力活动!', 0, $link);
    }
	
    if ($db->autoExecute($aos->table('assist'), $assist, 'UPDATE', "assist_id='$assist_id'"))
    {
        
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => '返回列表', 'href' => 'index.php?act=assist&op=assist_list');
        sys_msg('助力活动编辑成功!', 0, $link);
    }
}
if ($operation == 'remove')
{
    check_authz_json('bonus_manage');

    $id = intval($_REQUEST['id']);

    $sql="select order_id from ".$aos->table('order_info')." where act_id = '$id' and extension_code = 'miao'";
    $r=$db->getOne($sql);
    if($r){
        //sys_msg('已存在订单，不能删除！', 0, array(array('href'=>'index.php?act=assist&op=assist_list' , 'text' =>'秒杀列表')));
        make_json_error('已存在订单，不能删除！');
    }

    $db->query("DELETE FROM " .$aos->table('assist'). " WHERE assist_id = '$id'");

    make_json_result($id);
    exit;
}

function get_assist_list()
{
    $now_time = gmtime();
        $filter = array();
        
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? ' k.assist_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = '';
        

        /* 文章总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['aos']->table('assist'). ' AS k '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('goods'). ' AS g ON k.goods_id = g.goods_id '.
               'WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取文章数据 */
        $sql = 'SELECT k.* , g.goods_name '.
               'FROM ' .$GLOBALS['aos']->table('assist'). ' AS k '.
               'LEFT JOIN ' .$GLOBALS['aos']->table('goods'). ' AS g ON k.goods_id = g.goods_id '.
               'WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        set_filter($filter, $sql);
    
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
        if($now_time < $rows['assist_start_time'])
        {
            $rows['status']  = '尚未开始';
        }
        else
        {
            if($now_time < $rows['assist_end_time'])
            {
                if($rows['assist_sales'] >= $rows['assist_number'])
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


        $rows['assist_start_time'] = local_date('Y-m-d H:i:s', $rows['assist_start_time']);
        $rows['assist_end_time'] = local_date('Y-m-d H:i:s', $rows['assist_end_time']);


        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>