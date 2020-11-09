<?php

define('IN_AOS', true);

$exc = new exchange($aos->table('shipping_area'), $db, 'shipping_area_id', 'shipping_area_name');

admin_priv('shiparea_manage');
/* act操作项的初始化 */
if ($operation == 'index' || $operation == 'shipping_area_manage')
{
    $operation = 'list';
}
/*------------------------------------------------------ */
//-- 配送区域列表
/*------------------------------------------------------ */
if ($operation == 'list')
{

    $list = get_shipping_area_list();
    $smarty->assign('areas',    $list);

    
    $smarty->display('shipping_area_list.htm');
}

/*------------------------------------------------------ */
//-- 新建配送区域
/*------------------------------------------------------ */

elseif ($operation == 'add')
{
    admin_priv('shiparea_manage');

    $shipping = $db->getRow("SELECT shipping_name, shipping_code FROM " .$aos->table('shipping'));

    $set_modules = 1;
	$i = (isset($modules)) ? count($modules) : 0;
	$modules[$i]['configure'] = array(
	    array('name' => 'item_fee',     'value'=>20),/* 单件商品的配送费用 */
        array('name' => 'base_fee',    'value'=>15), /* 1000克以内的价格   */
        array('name' => 'step_fee',     'value'=>2),  /* 续重每1000克增加的价格 */
    );

    $fields = array();
    foreach ($modules[0]['configure'] AS $key => $val)
    {
        $fields[$key]['name']   = $val['name'];
        $fields[$key]['value']  = $val['value'];
        $fields[$key]['label']  = $_LANG[$val['name']];
    }
    $count = count($fields);
    $fields[$count]['name']     = "free_money";
    $fields[$count]['value']    = "0";
    $fields[$count]['label']    = '免费额度:';

    $shipping_area['free_money']    = 0;

    $smarty->assign('fields',           $fields);
    $smarty->assign('form_action',      'insert');
    $smarty->assign('countries',        get_regions());
    $smarty->assign('default_country',  $_CFG['shop_country']);
    
    $smarty->display('shipping_area_info.htm');
}

elseif ($operation == 'insert')
{
    admin_priv('shiparea_manage');

    /* 检查同类型的配送方式下有没有重名的配送区域 */
    $sql = "SELECT COUNT(*) FROM " .$aos->table("shipping_area").
            " WHERE shipping_area_name='$_POST[shipping_area_name]'";
    if ($db->getOne($sql) > 0)
    {
        sys_msg("名称重复", 1);
    }
    else
    {
		
	    $i = (isset($modules)) ? count($modules) : 0;
	    $modules[$i]['configure'] = array(
	        array('name' => 'item_fee',     'value'=>20),/* 单件商品的配送费用 */
            array('name' => 'base_fee',    'value'=>15), /* 1000克以内的价格   */
            array('name' => 'step_fee',     'value'=>2),  /* 续重每1000克增加的价格 */
        );
	
	
        $config = array();
        foreach ($modules[0]['configure'] AS $key => $val)
        {
            $config[$key]['name']   = $val['name'];
            $config[$key]['value']  = $_POST[$val['name']];
        }

        $count = count($config);
        $config[$count]['name']     = 'free_money';
        $config[$count]['value']    = empty($_POST['free_money']) ? 0 : $_POST['free_money'];
        $count++;
        $config[$count]['name']     = 'fee_compute_mode';
        $config[$count]['value']    = empty($_POST['fee_compute_mode']) ? '' : $_POST['fee_compute_mode'];

        $sql = "INSERT INTO " .$aos->table('shipping_area').
                " (shipping_area_name, configure) ".
                "VALUES".
                " ('$_POST[shipping_area_name]', '" .serialize($config). "')";

        $db->query($sql);

        $new_id = $db->insert_Id();

        /* 添加选定的城市和地区 */
        if (isset($_POST['regions']) && is_array($_POST['regions']))
        {
            foreach ($_POST['regions'] AS $key => $val)
            {
                $sql = "INSERT INTO ".$aos->table('area_region')." (shipping_area_id, region_id) VALUES ('$new_id', '$val')";
                $db->query($sql);
            }
        }

        admin_log($_POST['shipping_area_name'], 'add', 'shipping_area');

        $lnk[] = array('text' => "返回列表", 'href'=>'index.php?act=shipping_area&op=list');
        $lnk[] = array('text' => "继续添加", 'href'=>'index.php?act=shipping_area&op=add');
        sys_msg("添加成功", 0, $lnk);
    }
}

/*------------------------------------------------------ */
//-- 编辑配送区域
/*------------------------------------------------------ */

elseif ($operation == 'edit')
{
    admin_priv('shiparea_manage');

    $sql = "SELECT * FROM " .$aos->table('shipping_area')." WHERE shipping_area_id='$_REQUEST[id]'";
    $row = $db->getRow($sql);

    $set_modules = 1;

    $fields = unserialize($row['configure']);
    foreach ($fields AS $key => $val)
    {
       /* 替换更改的语言项 */
       if ($val['name'] == 'basic_fee')
       {
            $val['name'] = 'base_fee';
       }
       if ($val['name'] == 'item_fee')
       {
           $item_fee = 1;
       }
       if ($val['name'] == 'fee_compute_mode')
       {
           $smarty->assign('fee_compute_mode',$val['value']);
           unset($fields[$key]);
       }
       else
       {
           $fields[$key]['name'] = $val['name'];
           $fields[$key]['label']  = $_LANG[$val['name']];
       }

    }

    if(empty($item_fee))
    {
        $field = array('name'=>'item_fee', 'value'=>'0', 'label'=>empty('单件商品费用：') ? '' : '单件商品费用：');
        array_unshift($fields,$field);
    }

    /* 获得该区域下的所有地区 */
    $regions = array();

    $sql = "SELECT a.region_id, r.region_name ".
            "FROM ".$aos->table('area_region')." AS a, ".$aos->table('region'). " AS r ".
            "WHERE r.region_id=a.region_id AND a.shipping_area_id='$_REQUEST[id]'";
    $res = $db->query($sql);
    while ($arr = $db->fetchRow($res))
    {
        $regions[$arr['region_id']] = $arr['region_name'];
    }


    $smarty->assign('id',               $_REQUEST['id']);
    $smarty->assign('fields',           $fields);
    $smarty->assign('shipping_area',    $row);
    $smarty->assign('regions',          $regions);
    $smarty->assign('form_action',      'update');
    $smarty->assign('countries',        get_regions());
    $smarty->assign('default_country',  1);
    $smarty->display('shipping_area_info.htm');
}

elseif ($operation == 'update')
{
    admin_priv('shiparea_manage');

    /* 检查同类型的配送方式下有没有重名的配送区域 */
    $sql = "SELECT COUNT(*) FROM " .$aos->table("shipping_area").
            " WHERE shipping_area_name='$_POST[shipping_area_name]' AND ".
                    "shipping_area_id<>'$_POST[id]'";
    if ($db->getOne($sql) > 0)
    {
        sys_msg("名称重复", 1);
    }
    else
    {
		$i = (isset($modules)) ? count($modules) : 0;
	    $modules[$i]['configure'] = array(
	        array('name' => 'item_fee',     'value'=>20),/* 单件商品的配送费用 */
            array('name' => 'base_fee',    'value'=>15), /* 1000克以内的价格   */
            array('name' => 'step_fee',     'value'=>2),  /* 续重每1000克增加的价格 */
        );

        $config = array();
        foreach ($modules[0]['configure'] AS $key => $val)
        {
            $config[$key]['name']   = $val['name'];
            $config[$key]['value']  = $_POST[$val['name']];
        }

        $count = count($config);
        
        $config[$count]['name']     = 'free_money';
        $config[$count]['value']    = empty($_POST['free_money']) ? 0 : $_POST['free_money'];
        $count++;
        $config[$count]['name']     = 'fee_compute_mode';
        $config[$count]['value']    = empty($_POST['fee_compute_mode']) ? 0 : $_POST['fee_compute_mode'];
        if ($modules[0]['cod'])
        {
            $count++;
            $config[$count]['name']     = 'pay_fee';
            $config[$count]['value']    =  make_semiangle(empty($_POST['pay_fee']) ? '' : $_POST['pay_fee']);
        }
        $sql = "UPDATE " .$aos->table('shipping_area').
                " SET shipping_area_name='$_POST[shipping_area_name]', ".
                    "configure='" .serialize($config). "' ".
                "WHERE shipping_area_id='$_POST[id]'";

        $db->query($sql);

        admin_log($_POST['shipping_area_name'], 'edit', 'shipping_area');

        /* 过滤掉重复的region */
        $selected_regions = array();
        if (isset($_POST['regions']))
        {
            foreach ($_POST['regions'] AS $region_id)
            {
                $selected_regions[$region_id] = $region_id;
            }
        }

        // 查询所有区域 region_id => parent_id
        $sql = "SELECT region_id, parent_id FROM " . $aos->table('region');
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $region_list[$row['region_id']] = $row['parent_id'];
        }

        // 过滤掉上级存在的区域
        foreach ($selected_regions AS $region_id)
        {
            $id = $region_id;
            while ($region_list[$id] != 0)
            {
                $id = $region_list[$id];
                if (isset($selected_regions[$id]))
                {
                    unset($selected_regions[$region_id]);
                    break;
                }
            }
        }

        /* 清除原有的城市和地区 */
        $db->query("DELETE FROM ".$aos->table("area_region")." WHERE shipping_area_id='$_POST[id]'");

        /* 添加选定的城市和地区 */
        foreach ($selected_regions AS $key => $val)
        {
            $sql = "INSERT INTO ".$aos->table('area_region')." (shipping_area_id, region_id) VALUES ('$_POST[id]', '$val')";
            $db->query($sql);
        }

        $lnk[] = array('text' => "返回列表", 'href'=>'index.php?act=shipping_area&op=list');

        sys_msg("编辑成功", 0, $lnk);
    }
}

/*------------------------------------------------------ */
//-- 批量删除配送区域
/*------------------------------------------------------ */
elseif ($operation == 'multi_remove')
{
    admin_priv('shiparea_manage');

    if (isset($_POST['areas']) && count($_POST['areas']) > 0)
    {
        $i = 0;
        foreach ($_POST['areas'] AS $v)
        {
            $db->query("DELETE FROM " .$aos->table('shipping_area'). " WHERE shipping_area_id='$v'");
            $i++;
        }

        /* 记录管理员操作 */
        admin_log('', 'batch_remove', 'shipping_area');
    }
    /* 返回 */
    $links[0] = array('href'=>'index.php?act=shipping_area&op=list', 'text' => "返回");
    sys_msg("删除成功", 0, $links);
}

/*------------------------------------------------------ */
//-- 编辑配送区域名称
/*------------------------------------------------------ */

elseif ($operation == 'edit_area')
{
    /* 检查权限 */
    check_authz_json('shiparea_manage');

    /* 取得参数 */
    $id  = intval($_POST['id']);
    $val = json_str_iconv(trim($_POST['val']));


    /* 检查是否有重复的配送区域名称 */
    if (!$exc->is_only('shipping_area_name', $val, $id))
    {
        make_json_error('已经存在一个同名的配送区域。');
    }

    /* 更新名称 */
    $exc->edit("shipping_area_name = '$val'", $id);

    /* 记录日志 */
    admin_log($val, 'edit', 'shipping_area');

    /* 返回 */
    make_json_result(stripcslashes($val));
}

/*------------------------------------------------------ */
//-- 删除配送区域
/*------------------------------------------------------ */

elseif ($operation == 'remove_area')
{
    check_authz_json('shiparea_manage');

    $id = intval($_POST['id']);
    $name = $exc->get_name($id);

    $exc->drop($id);
    $db->query('DELETE FROM '.$aos->table('area_region').' WHERE shipping_area_id='.$id);

    admin_log($name, 'remove', 'shipping_area');

    $list = get_shipping_area_list();
    $smarty->assign('areas', $list);
    //make_json_result($smarty->fetch('shipping_area_list.htm'));
    make_json_result($id);
}

/**
 * 取得配送区域列表
 */
function get_shipping_area_list()
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('shipping_area');
    $res = $GLOBALS['db']->query($sql);
    $list = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $sql = "SELECT r.region_name " .
                "FROM " . $GLOBALS['aos']->table('area_region'). " AS a, " .
                    $GLOBALS['aos']->table('region') . " AS r ".
                "WHERE a.region_id = r.region_id ".
                "AND a.shipping_area_id = '$row[shipping_area_id]'";
        $regions = join(', ', $GLOBALS['db']->getCol($sql));

        $row['shipping_area_regions'] = $regions;
        $list[] = $row;
    }

    return $list;
}

?>
