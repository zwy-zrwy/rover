<?php

define('IN_AOS', true);
$exc = new exchange($aos->table('payment'), $db, 'pay_id', 'pay_name');
/* act操作项的初始化 */
if ($operation == 'index' || $operation == 'payment_manage')
{
    $operation = 'list';
}
admin_priv('payment_manage');
/*------------------------------------------------------ */
//-- 支付方式列表 ?act=list
/*------------------------------------------------------ */

if ($operation == 'list')
{
    /* 查询数据库中启用的支付方式 */
    $pay_list = array();
    $sql = "SELECT * FROM " . $aos->table('payment');
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        $pay_list[$row['pay_code']] = $row;
    }
    $smarty->assign('pay_list', $pay_list);
    $smarty->display('payment_list.htm');
}

elseif ($operation == 'get_config')
{
    check_authz_json('payment');

    $code = $_REQUEST['code'];

    /* 取相应插件信息 */
    $set_modules = true;
    include_once(ROOT_PATH.'source/payment/' . $code . '.php');
    $data = $modules[0]['config'];
    $config = "<table class='table'>";
    $range = "";
    foreach($data AS $key => $value)
    {
        $config .= "<tr><td width=100>";
        $config .= $_LANG[$data[$key]['name']];
        $config .= "</td>";
        $config .= "<td><input name='cfg_value[]' type='text' value='" . $data[$key]['value'] . "' size='40'/></td>";
        $config .= "</tr>";
        $config .= "<input name='cfg_name[]' type='hidden' value='" .$data[$key]['name'] . "' />";
        $config .= "<input name='cfg_type[]' type='hidden' value='" .$data[$key]['type'] . "' />";
        $config .= "<input name='cfg_lang[]' type='hidden' value='" .$data[$key]['lang'] . "' />";
    }
    $config .= '</table>';

    make_json_result($config);
}

/*------------------------------------------------------ */
//-- 编辑支付方式 ?act=edit&code={$code}
/*------------------------------------------------------ */
elseif ($operation == 'edit')
{
    admin_priv('payment_manage');

    /* 查询该支付方式内容 */
    if (isset($_REQUEST['code']))
    {
        $_REQUEST['code'] = trim($_REQUEST['code']);
    }
    else
    {
        die('invalid parameter');
    }

    $sql = "SELECT * FROM " . $aos->table('payment') . " WHERE pay_code = '$_REQUEST[code]' AND enabled = '1'";
    $pay = $db->getRow($sql);
    if (empty($pay))
    {
        $links[] = array('text' => "返回列表", 'href' => 'index.php?act=payment&op=list');
        sys_msg("未找到该支付方式", 0, $links);
    }


    /* 取相应插件信息 */
    $set_modules = true;
    include_once(ROOT_PATH . 'source/payment/' . $_REQUEST['code'] . '.php');
    $data = $modules[0];



    /* 取得配置信息 */
    if (is_string($pay['pay_config']))
    {
        $store = unserialize($pay['pay_config']);
        
        /* 取出已经设置属性的code */
        $code_list = array();
        if($store){
           foreach ($store as $key=>$value)
            {
                $code_list[$value['name']] = $value['value'];
            }
        }



        $pay['pay_config'] = array();

        /* 循环插件中所有属性 */
        foreach ($data['config'] as $key => $value)
        {
            $pay['pay_config'][$key]['label'] = $_LANG[$value['name']];
            $pay['pay_config'][$key]['name'] = $value['name'];
            $pay['pay_config'][$key]['type'] = $value['type'];

            if (isset($code_list[$value['name']]))
            {
                $pay['pay_config'][$key]['value'] = $code_list[$value['name']];
            }
            else
            {
                $pay['pay_config'][$key]['value'] = $value['value'];
            }
        }

    }

    $smarty->assign('pay', $pay);
    $smarty->display('payment_edit.htm');
}

/*------------------------------------------------------ */
//-- 提交支付方式 post
/*------------------------------------------------------ */
elseif (isset($_POST['Submit']))
{
    admin_priv('payment_manage');

    /* 检查输入 */
    if (empty($_POST['pay_name']))
    {
        sys_msg("支付方式为空");
    }

    $sql = "SELECT COUNT(*) FROM " . $aos->table('payment') .
            " WHERE pay_name = '$_POST[pay_name]' AND pay_code <> '$_POST[pay_code]'";
    if ($db->GetOne($sql) > 0)
    {
        sys_msg("支付方式名称重复", 1);
    }

    /* 取得配置信息 */
    $pay_config = array();
    if (isset($_POST['cfg_value']) && is_array($_POST['cfg_value']))
    {
        for ($i = 0; $i < count($_POST['cfg_value']); $i++)
        {
            $pay_config[] = array('name'  => trim($_POST['cfg_name'][$i]),
                                  'type'  => trim($_POST['cfg_type'][$i]),
                                  'value' => trim($_POST['cfg_value'][$i])
            );
        }
    }
    $pay_config = serialize($pay_config);

    /* 检查是编辑还是安装 */
    $link[] = array('text' => "返回", 'href' => 'index.php?act=payment&op=list');
    if ($_POST['pay_id'])
    {
        /* 编辑 */
        $sql = "UPDATE " . $aos->table('payment') .
               "SET pay_name = '$_POST[pay_name]',pay_config = '$pay_config'" .
               "WHERE pay_code = '$_POST[pay_code]' LIMIT 1";
        $db->query($sql);

        /* 记录日志 */
        admin_log($_POST['pay_name'], 'edit', 'payment');

        sys_msg("编辑成功", 0, $link);
    }

}

elseif ($operation== 'toggle_enabled')
{
    $pay_id       = intval($_POST['id']);
    $enabled        = intval($_POST['val']);

    if ($exc->edit("enabled = '$enabled'", $pay_id))
    {
        clear_cache_files();
        make_json_result($enabled);
    }else{
       make_json_error('操作失败'); 
    }
}
/*------------------------------------------------------ */
//-- 修改支付方式排序
/*------------------------------------------------------ */

elseif ($operation == 'edit_order')
{

    /* 取得参数 */
    $code = json_str_iconv(trim($_POST['id']));
    $order = intval($_POST['val']);

    /* 更新排序 */
    $exc->edit("pay_order = '$order'", $code);
    make_json_result(stripcslashes($order));
}

?>