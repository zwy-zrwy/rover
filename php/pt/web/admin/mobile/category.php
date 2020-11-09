<?php

define('IN_AOS', true);

include_once(ROOT_PATH . 'source/class/image.class.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($aos->table("category"), $db, 'cat_id', 'cat_name');
admin_priv('category_manage');

/* act操作项的初始化 */
if ($operation == 'index')
{
    $operation = 'category_list';
}

/*------------------------------------------------------ */
//-- 商品分类列表
/*------------------------------------------------------ */
if ($operation == 'category_list')
{
    /* 获取分类列表 */
    $cat_list = cat_list(0, 0, false);

    /* 模板赋值 */
    $smarty->assign('ur_here',      '分类列表');
    $smarty->assign('full_page',    1);
    $smarty->assign('cat_info',     $cat_list);

    /* 列表页面 */
    $smarty->display('category_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($operation == 'query')
{
    $cat_list = cat_list(0, 0, false);
    $smarty->assign('cat_info',     $cat_list);

    make_json_result($smarty->fetch('category_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加商品分类
/*------------------------------------------------------ */
if ($operation == 'category_add')
{
	$parent_id = !empty($_REQUEST['parent_id']) ? intval($_REQUEST['parent_id']) : 0;



    /* 模板赋值 */
    $smarty->assign('ur_here',      '添加分类');
    $smarty->assign('action_link',  array('href' => 'index.php?act=category&op=category_list', 'text' => '分类列表'));

    $smarty->assign('cat_select',   cat_list(0, $parent_id, true, 1));
    $smarty->assign('form_act',     'insert');
    $smarty->assign('cat_info',     array('is_show' => 1));

    /* 显示页面 */
    
    $smarty->display('category_info.htm');
}

/*------------------------------------------------------ */
//-- 商品分类添加时的处理
/*------------------------------------------------------ */
if ($operation == 'insert')
{

    /* 初始化变量 */
    $cat['cat_id']       = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
    $cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
    $cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
    $cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;

    if (cat_exists($cat['cat_name'], $cat['parent_id']))
    {
        /* 同级别下不能有重复的分类名称 */
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('已存在相同的分类名称!', 0, $link);
    }
	$cat['cat_logo'] = basename($image->upload_image($_FILES['cat_logo'],'cat_img'));

    /* 入库的操作 */
    if ($db->autoExecute($aos->table('category'), $cat) !== false)
    {
        $cat_id = $db->insert_id();
        admin_log($_POST['cat_name'], 'add', 'category');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加分类';
        $link[0]['href'] = 'index.php?act=category&op=category_add';

        $link[1]['text'] = '返回列表';
        $link[1]['href'] = 'index.php?act=category&op=category_list';

        sys_msg('新商品分类添加成功!', 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($operation == 'edit')
{
    $cat_id = intval($_REQUEST['cat_id']);
    $cat_info = get_cat_info($cat_id);  // 查询分类信息数据

    /* 模板赋值 */
    $smarty->assign('ur_here',     '修改分类');
    $smarty->assign('action_link', array('text' => '分类列表', 'href' => 'index.php?act=category&op=category_list'));


    $smarty->assign('cat_info',    $cat_info);
    $smarty->assign('form_act',    'update');
    $smarty->assign('cat_select',  cat_list(0, $cat_info['parent_id'], true, 1));

    /* 显示页面 */
    
    $smarty->display('category_info.htm');
}

elseif($operation == 'add_category')
{
    $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
    $category = empty($_REQUEST['cat']) ? '' : json_str_iconv(trim($_REQUEST['cat']));

    if(cat_exists($category, $parent_id))
    {
        make_json_error('已存在相同的分类名称!');
    }
    else
    {
        $sql = "INSERT INTO " . $aos->table('category') . "(cat_name, parent_id, is_show)" .
               "VALUES ( '$category', '$parent_id', 1)";

        $db->query($sql);
        $category_id = $db->insert_id();

        $arr = array("parent_id"=>$parent_id, "id"=>$category_id, "cat"=>$category);

        clear_cache_files();    // 清除缓存

        make_json_result($arr);
    }
}

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($operation == 'update')
{

    /* 初始化变量 */
    $cat_id              = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
    $old_cat_name        = $_POST['old_cat_name'];
    $cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
    $cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
    $cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;

    /* 判断分类名是否重复 */

    if ($cat['cat_name'] != $old_cat_name)
    {
        if (cat_exists($cat['cat_name'],$cat['parent_id'], $cat_id))
        {
           $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
           sys_msg('已存在相同的分类名称!', 0, $link);
        }
    }

    /* 判断上级目录是否合法 */
    $children = array_keys(cat_list($cat_id, 0, false));     // 获得当前分类的所有下级分类
    if (in_array($cat['parent_id'], $children))
    {
        /* 选定的父类是当前分类或当前分类的下级分类 */
       $link[] = array('text' => '返回上一页', 'href' => 'javascript:history.back(-1)');
       sys_msg('所选择的上级分类不能是当前分类或者当前分类的下级分类!', 0, $link);
    }
	$img_name = basename($image->upload_image($_FILES['cat_logo'],'cat_img'));
    if(!empty($img_name))
    {
        $cat['cat_logo'] = $img_name;
        $sql = "UPDATE " . $aos->table('category') . " SET cat_logo = '" . $cat['cat_logo'] . "' WHERE cat_id='$cat_id'";
        $db->query($sql);
    }
    
	$dat = $db->getRow("SELECT cat_name FROM ". $aos->table('category') . " WHERE cat_id = '$cat_id'");
	
    if ($db->autoExecute($aos->table('category'), $cat, 'UPDATE', "cat_id='$cat_id'"))
    {

        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => '返回列表', 'href' => 'index.php?act=category&op=category_list');
        sys_msg('商品分类编辑成功!', 0, $link);
    }
}


/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($operation == 'edit_sort_order')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('sort_order' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($operation == 'toggle_is_show')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('is_show' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 删除商品分类
/*------------------------------------------------------ */
if ($operation == 'remove')
{
    check_authz_json('cat_manage');

    /* 初始化分类ID并取得分类名称 */
    $cat_id   = intval($_POST['id']);
    $cat_name = $db->getOne('SELECT cat_name FROM ' .$aos->table('category'). " WHERE cat_id='$cat_id'");

    /* 当前分类下是否有子分类 */
    $cat_count = $db->getOne('SELECT COUNT(*) FROM ' .$aos->table('category'). " WHERE parent_id='$cat_id'");

    /* 当前分类下是否存在商品 */
    $goods_count = $db->getOne('SELECT COUNT(*) FROM ' .$aos->table('goods'). " WHERE cat_id='$cat_id'");
    /* 如果不存在下级子分类和商品，则删除之 */
    if ($cat_count == 0 && $goods_count == 0)
    {
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$aos->table('category'). " WHERE cat_id = '$cat_id'";
        if ($db->query($sql))
        {
            
            clear_cache_files();
            admin_log($cat_name, 'remove', 'category');
            make_json_result($cat_id);
        }
    }
    else
    {
        make_json_error($cat_name.'不是末级分类或者此分类下还存在有商品,您不能删除!');
        //sys_msg($cat_name.'不是末级分类或者此分类下还存在有商品,您不能删除!');
    }

    
}
//-- 删除分类图片
if ($operation == 'drop_logo')
{
    $cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  
    
    $sql = "SELECT cat_logo FROM " .$aos->table('category'). " WHERE cat_id = '$cat_id'";
    $logo_name = $db->getOne($sql);
  
    if (!empty($logo_name))
    {
        @unlink(ROOT_PATH . '/uploads/cat_img/' .$logo_name);
        $sql = "UPDATE " .$ecs->table('category'). " SET cat_logo = '' WHERE cat_id = '$cat_id'";
        $db->query($sql);
    }
    $link= array(array('text' => '继续编辑', 'href' => 'index.php?act=category&op=edit&id=' . $cat_id), array('text' => '分类列表', 'href' => 'index.php?act=category&op=category_list'));
    sys_msg("成功删除分类图片", 0, $link);
}
if ($operation == 'check_cat_name')
{
    $cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
    $cat_name = empty($_POST["param"]) ? '' : json_str_iconv(trim($_POST["param"]));

    /* 检查是否重复 */
    if (!$exc->is_only('cat_name', $cat_name, $cat_id))
    {
        $result['info']= '分类名称已存在';
    }
    else
    {
        $result['status']= 'y';
    }
    die(json_encode($result));
}   
/**
 * 获得商品分类的所有信息
 *
 * @param   integer     $cat_id     指定的分类ID
 *
 * @return  mix
 */
function get_cat_info($cat_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['aos']->table('category'). " WHERE cat_id='$cat_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 添加商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function cat_update($cat_id, $args)
{
    if (empty($args) || empty($cat_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('category'), $args, 'update', "cat_id='$cat_id'");
}

?>