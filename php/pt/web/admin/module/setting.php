<?php

define('IN_AOS', true);
/*------------------------------------------------------ */
//-- 列表编辑 ?act=setting_manage
/*------------------------------------------------------ */
if ($operation == 'setting_manage')
{
    /* 检查权限 */
    admin_priv('config_manage');
    require_once(ROOT_PATH . 'source/language/admin/setting.php');

    /* 可选语言 */
    $dir = opendir('../source/language');
    $lang_list = array();
    while (@$file = readdir($dir))
    {
        if ($file != '.' && $file != '..' &&  $file != '.svn' && $file != '_svn' && is_dir('../source/language/' .$file))
        {
            $lang_list[] = $file;
        }
    }
    @closedir($dir);
    echo $file;

    $smarty->assign('lang_list',    $lang_list);
    $smarty->assign('ur_here',      '站点设置');
    $smarty->assign('group_list',   get_settings(null, array('6')));
    $smarty->assign('countries',    get_regions());

    if ($_CFG['shop_country'] > 0)
    {
        $smarty->assign('provinces', get_regions(1, $_CFG['shop_country']));
        if ($_CFG['shop_province'])
        {
            $smarty->assign('cities', get_regions(2, $_CFG['shop_province']));
        }
    }
    $smarty->assign('cfg', $_CFG);
    $smarty->display('shop_config.htm');
}

/*------------------------------------------------------ */
//-- 提交   ?act=post
/*------------------------------------------------------ */
elseif ($operation == 'post')
{
    $type = empty($_POST['type']) ? '' : $_POST['type'];

    /* 检查权限 */
    admin_priv('config_manage');

    /* 允许上传的文件类型 */
    $allow_file_types = '|GIF|JPG|PNG|BMP|SWF|DOC|XLS|PPT|MID|WAV|ZIP|RAR|PDF|CHM|RM|TXT|CERT|';

    /* 保存变量值 */
    $count = count($_POST['value']);

    $arr = array();
    $sql = 'SELECT id, value FROM ' . $aos->table('shop_config');
    $res= $db->query($sql);
    while($row = $db->fetchRow($res))
    {
        $arr[$row['id']] = $row['value'];
    }
    foreach ($_POST['value'] AS $key => $val)
    {
        if($arr[$key] != $val)
        {
            $sql = "UPDATE " . $aos->table('shop_config') . " SET value = '" . trim($val) . "' WHERE id = '" . $key . "'";
            $db->query($sql);
        }
    }


    /* 处理上传文件 */
    $file_var_list = array();
    $sql = "SELECT * FROM " . $aos->table('shop_config') . " WHERE parent_id > 0 AND type = 'file'";
    $res = $db->query($sql);

    while ($row = $db->fetchRow($res))
    {
        $file_var_list[$row['code']] = $row;
    }
    foreach ($_FILES AS $code => $file)
    {
        
        /* 判断用户是否选择了文件 */
        if ((isset($file['error']) && $file['error'] == 0) || (!isset($file['error']) && $file['tmp_name'] != 'none'))
        {
            /* 检查上传的文件类型是否合法 */
            if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types))
            {
                sys_msg(sprintf('您上传了一个非法的文件类型。该文件名为：%s', $file['name']));
            }
            else
            {
                $store_dir = $file_var_list[$code]['store_dir'];
                if ($code == 'shop_logo')
                {
                    $file_name_arr = explode('.', $file['name']);
                    $ext = array_pop($file_name_arr);
                    $file_name = 'shop_logo.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                elseif($code == 'wap_logo')
                {
					$file_name_arr = explode('.', $file['name']);
                    $ext = array_pop($file_name_arr);
                    $file_name = 'wap_logo.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                elseif($code == 'code_logo')
                {
                    $file_name_arr = explode('.', $file['name']);
                    $ext = array_pop($file_name_arr);
                    $file_name = 'code_logo.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                elseif($code == 'share_logo')
                {
                    $file_name_arr = explode('.', $file['name']);
                    $ext = array_pop($file_name_arr);
                    $file_name = 'share_logo.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                elseif($code == 'no_picture')
                {
                    $file_name_arr = explode('.', $file['name']);
                    $ext = array_pop($file_name_arr);
                    $file_name = 'no_picture.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                elseif($code == 'no_tuan_picture')
                {
                    $file_name_arr = explode('.', $file['name']);
                    $ext = array_pop($file_name_arr);
                    $file_name = 'no_tuan_picture.' . $ext;
                    if (file_exists($file_var_list[$code]['value']))
                    {
                        @unlink($file_var_list[$code]['value']);
                    }
                }
                else
                {
                    $file_name = $file['name'];
                }

                /* 判断是否上传成功 */
                if (move_upload_file($file['tmp_name'], $store_dir.$file_name))
                {
                    $sql = "UPDATE " . $aos->table('shop_config') . " SET value = '$file_name' WHERE code = '$code'";
                    $db->query($sql);
                }
                else
                {
                    sys_msg(sprintf('上传文件 %s 失败，请检查 %s 目录是否可写。', $file['name'], $file_var_list[$code]['store_dir']));
                }
            }
        }
    }


    /* 记录日志 */
    admin_log('', 'edit', 'shop_config');

    /* 清除缓存 */
    clear_all_files();

    $_CFG = load_config();

    $shop_country   = $db->getOne("SELECT region_name FROM ".$aos->table('region')." WHERE region_id='$_CFG[shop_country]'");
    $shop_province  = $db->getOne("SELECT region_name FROM ".$aos->table('region')." WHERE region_id='$_CFG[shop_province]'");
    $shop_city      = $db->getOne("SELECT region_name FROM ".$aos->table('region')." WHERE region_id='$_CFG[shop_city]'");


    $links[] = array('text' => '返回商店设置', 'href' => 'index.php?act=setting&op=setting_manage');
        sys_msg('保存商店设置成功。'.$spt, 0, $links);
}

/**
 * 设置系统设置
 *
 * @param   string  $key
 * @param   string  $val
 *
 * @return  boolean
 */
function update_configure($key, $val='')
{
    if (!empty($key))
    {
        $sql = "UPDATE " . $GLOBALS['aos']->table('shop_config') . " SET value='$val' WHERE code='$key'";

        return $GLOBALS['db']->query($sql);
    }

    return true;
}

/**
 * 获得设置信息
 *
 * @param   array   $groups     需要获得的设置组
 * @param   array   $excludes   不需要获得的设置组
 *
 * @return  array
 */
function get_settings($groups=null, $excludes=null)
{
    global $db, $aos, $_LANG;

    $config_groups = '';
    $excludes_groups = '';

    if (!empty($groups))
    {
        foreach ($groups AS $key=>$val)
        {
            $config_groups .= " AND (id='$val' OR parent_id='$val')";
        }
    }

    if (!empty($excludes))
    {
        foreach ($excludes AS $key=>$val)
        {
            $excludes_groups .= " AND (parent_id<>'$val' AND id<>'$val')";
        }
    }

    /* 取出全部数据：分组和变量 */
    $sql = "SELECT * FROM " . $aos->table('shop_config') .
            " WHERE type<>'hidden' $config_groups $excludes_groups ORDER BY parent_id, sort_order, id";
    $item_list = $db->getAll($sql);

    /* 整理数据 */
    $group_list = array();
    foreach ($item_list AS $key => $item)
    {
        $pid = $item['parent_id'];
        $item['name'] = isset($_LANG['cfg_name'][$item['code']]) ? $_LANG['cfg_name'][$item['code']] : $item['code'];
        $item['desc'] = isset($_LANG['cfg_desc'][$item['code']]) ? $_LANG['cfg_desc'][$item['code']] : '';

        if ($item['code'] == 'sms_shop_mobile')
        {
            $item['url'] = 1;
        }
        if ($pid == 0)
        {
            /* 分组 */
            if ($item['type'] == 'group')
            {
                $group_list[$item['id']] = $item;
            }
        }
        else
        {
            /* 变量 */
            if (isset($group_list[$pid]))
            {
                if ($item['store_range'])
                {
                    $item['store_options'] = explode(',', $item['store_range']);

                    foreach ($item['store_options'] AS $k => $v)
                    {
                        $item['display_options'][$k] = isset($_LANG['cfg_range'][$item['code']][$v]) ?
                                $_LANG['cfg_range'][$item['code']][$v] : $v;
                    }
                }
                $group_list[$pid]['vars'][] = $item;
            }
        }

    }

    return $group_list;
}

?>