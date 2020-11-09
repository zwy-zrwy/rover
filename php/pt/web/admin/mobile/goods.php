<?php

define('IN_AOS', true);

require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
include_once(ROOT_PATH . '/source/class/image.class.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($aos->table('goods'), $db, 'goods_id', 'goods_name');
admin_priv('goods_manage');
/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'goods_list';
}

/*------------------------------------------------------ */
//-- 商品列表，商品回收站
/*------------------------------------------------------ */

if ($operation== 'goods_list')
{
    $is_on_sale = isset($_REQUEST['is_on_sale']) ? ((empty($_REQUEST['is_on_sale']) && $_REQUEST['is_on_sale'] === 0) ? '' : trim($_REQUEST['is_on_sale'])) : '';

    $smarty->assign('is_on_sale', $is_on_sale);

    /* 模板赋值 */
    $goods_ur = array('' => '商品列表');
    $smarty->assign('ur_here', '商品管理');
    $smarty->assign('lang',         $_LANG);
    $smarty->assign('list_type',    'goods');
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);

    $goods_list = goods_list_m();
	
	//print_r($goods_list);

    $smarty->assign('goods_list',   $goods_list['goods']);

    $pager = get_page($goods_list['filter']);

	$smarty->assign('pager',   $pager);


    /* 排序标记 */
    $sort_flag  = sort_flag($goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示商品列表页面 */
    
    $htm_file = 'goods_list.htm';
    $smarty->assign('pageHtml',$htm_file);
    $smarty->display($htm_file);
}

elseif ($operation== 'check_goods_name')
{
    $goods_id = intval($_REQUEST['goods_id']);
    $goods_name = htmlspecialchars(json_str_iconv(trim($_POST["param"])));

    /* 检查是否重复 */
    if (!$exc->is_only('goods_name', $goods_name, $goods_id))
    {
        $result['info']= '您填写的商品名称已存在';
    }
    else
    {
        $result['status']= 'y';
    }
    die(json_encode($result));
}
elseif ($operation== 'check_goods_sn')
{
    $goods_id = intval($_REQUEST['goods_id']);
    $goods_sn = htmlspecialchars(json_str_iconv(trim($_POST["param"])));

    /* 检查是否重复 */
    if (!$exc->is_only('goods_sn', $goods_sn, $goods_id))
    {
        $result['info']= '您输入的货号已存在';
        //make_json_error('您输入的货号已存在，请换一个');
    }
    else
    {
        //$result['info']= '验证通过！';
        $result['status']= 'y';
    }
    die(json_encode($result));
}
elseif ($operation== 'check_products_goods_sn')
{
    check_authz_json('goods_manage');

    $goods_id = intval($_REQUEST['goods_id']);
    $goods_sn = json_str_iconv(trim($_REQUEST['goods_sn']));
    $products_sn=explode('||',$goods_sn);
    if(!is_array($products_sn))
    {
        make_json_result('');
    }
    else
    {
        foreach ($products_sn as $val)
        {
            if(empty($val))
            {
                 continue;
            }
            if(is_array($int_arry))
            {
                if(in_array($val,$int_arry))
                {
                     make_json_error($val.'您输入的货号已存在，请换一个');
                }
            }
            $int_arry[]=$val;
            if (!$exc->is_only('goods_sn', $val, '0'))
            {
                make_json_error($val.'您输入的货号已存在，请换一个');
            }
            $sql="SELECT goods_id FROM ". $aos->table('products')."WHERE product_sn='$val'";
            if($db->getOne($sql))
            {
                make_json_error($val.'您输入的货号已存在，请换一个');
            }
        }
    }
    /* 检查是否重复 */
    make_json_result('');
}

/*------------------------------------------------------ */
//-- 修改商品库存数量
/*------------------------------------------------------ */
elseif ($operation== 'edit_goods_number')
{
    check_authz_json('goods_manage');

    $goods_id   = intval($_POST['id']);
    $goods_num  = intval($_POST['val']);

    if($goods_num < 0 || $goods_num == 0 && $_POST['val'] != "$goods_num")
    {
        make_json_error('商品库存数量错误');
    }

    if(is_sku($goods_id) == 1)
    {
        make_json_error('错误：此商品存在货品，不能修改商品库存');
    }

    if ($exc->edit("goods_number = '$goods_num', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($goods_num);
    }
}

/*------------------------------------------------------ */
//-- 修改上架状态
/*------------------------------------------------------ */
elseif ($operation== 'toggle_on_sale')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $on_sale        = intval($_POST['val']);

    if ($exc->edit("is_on_sale = '$on_sale', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($on_sale);
    }
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($operation== 'query')
{
    $is_delete = empty($_REQUEST['is_delete']) ? 0 : intval($_REQUEST['is_delete']);
    $goods_list = goods_list($is_delete);

    $smarty->assign('goods_list',   $goods_list['goods']);
    $smarty->assign('filter',       $goods_list['filter']);
    $smarty->assign('record_count', $goods_list['record_count']);
    $smarty->assign('page_count',   $goods_list['page_count']);
    $smarty->assign('list_type',    $is_delete ? 'trash' : 'goods');
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);

    /* 排序标记 */
    $sort_flag  = sort_flag($goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    $tpl = $is_delete ? 'goods_trash.htm' : 'goods_list.htm';

    make_json_result($smarty->fetch($tpl), '',
        array('filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count']));
}



/*------------------------------------------------------ */
//-- 查询商品规格
/*------------------------------------------------------ */
elseif ($operation== 'goods_attr')
{
    check_authz_json('goods_manage');

    $goods_id   = intval($_POST['id']);
    $sql="select attr_value,attr_id from ".$aos->table('goods_attr')." where goods_id = '$goods_id'";
    $attr=$db->getAll($sql);
    if(!empty($attr)){
        make_json_result($attr);
    }else{
        make_json_error('');
    }
    
}

/**
 * 保存某商品的优惠价格
 * @param   int     $goods_id    商品编号
 * @param   array   $number_list 优惠数量列表
 * @param   array   $price_list  价格列表
 * @return  void
 */
function handle_tuan_price($goods_id, $number_list, $price_list)
{
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('tuan_price') .
           " WHERE price_type = '1' AND goods_id = '$goods_id'";
    $GLOBALS['db']->query($sql);


    /* 循环处理每个优惠价格 */
    foreach ($price_list AS $key => $price)
    {
        /* 价格对应的数量上下限 */
        $tuan_number = $number_list[$key];

        if (!empty($tuan_number))
        {
            $sql = "INSERT INTO " . $GLOBALS['aos']->table('tuan_price') .
                   " (price_type, goods_id, tuan_number, tuan_price) " .
                   "VALUES ('1', '$goods_id', '$tuan_number', '$price')";
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 修改商品库存
 * @param   string  $goods_id   商品编号，可以为多个，用 ',' 隔开
 * @param   string  $value      字段值
 * @return  bool
 */
function update_goods_stock($goods_id, $value)
{
    if ($goods_id)
    {
        /* $res = $goods_number - $old_product_number + $product_number; */
        $sql = "UPDATE " . $GLOBALS['aos']->table('goods') . "
                SET goods_number = goods_number + $value,
                    last_update = '". gmtime() ."'
                WHERE goods_id = '$goods_id'";
        $result = $GLOBALS['db']->query($sql);

        /* 清除缓存 */
        clear_cache_files();

        return $result;
    }
    else
    {
        return false;
    }
}
















/**
 * 获得商品列表
 *
 * @access  public
 * @params  integer $isdelete
 * @params  integer $conditions
 * @return  array
 */
function goods_list_m($conditions = '')
{
    /* 过滤条件 */
    $param_str = '-';
    
    $result = get_filter($param_str);
    if ($result === false)
    {
        $day = getdate();
        $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);


        $filter['goods_name']          = empty($_REQUEST['search_goods_name']) ? '' : trim($_REQUEST['search_goods_name']);
        
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'goods_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = ' 1 ';


        /* 关键字 */
        if (!empty($filter['goods_name']))
        {
            $where .= " AND   goods_name LIKE '%" . mysql_like_quote($filter['goods_name']) . "%' ";
        }


        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('goods'). " AS g WHERE $where";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        $sql = "SELECT goods_id, goods_name, goods_img, goods_sn, virtual_sales, shop_price, is_on_sale, is_best, is_new, is_hot, sort_order, goods_number " .
                    " FROM " . $GLOBALS['aos']->table('goods') . " AS g WHERE $where" .
                    " ORDER BY $filter[sort_by] $filter[sort_order] ".
                    " LIMIT " . $filter['start'] . ",$filter[page_size]";

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql, $param_str);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $row = $GLOBALS['db']->getAll($sql);
    return array('goods' => $row, 'filter' => $filter);
}
?>