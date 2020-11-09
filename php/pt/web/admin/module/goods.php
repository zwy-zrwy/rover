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

if ($operation== 'goods_list' || $operation== 'goods_trash')
{
    

    $cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
    $is_on_sale = isset($_REQUEST['is_on_sale']) ? ((empty($_REQUEST['is_on_sale']) && $_REQUEST['is_on_sale'] === 0) ? '' : trim($_REQUEST['is_on_sale'])) : '';

    $smarty->assign('is_on_sale', $is_on_sale);

    /* 模板赋值 */
    $goods_ur = array('' => '商品列表');
    $ur_here = ($operation== 'goods_list') ? $goods_ur : '商品回收站';
    $smarty->assign('ur_here', '商品管理');
    $smarty->assign('cat_list',     cat_list(0, $cat_id));
    $smarty->assign('intro_list',   get_intro_list());
    $smarty->assign('lang',         $_LANG);
    $smarty->assign('list_type',    $operation== 'goods_list' ? 'goods' : 'goods_trash');
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);

    $goods_list = goods_list($operation== 'goods_list' ? 0 : 1);
	
	//print_r($goods_list);

    $smarty->assign('goods_list',   $goods_list['goods']);

    $pager = get_page($goods_list['filter']);

	$smarty->assign('pager',   $pager);


    /* 排序标记 */
    $sort_flag  = sort_flag($goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 显示商品列表页面 */
    
    $htm_file = ($operation== 'goods_list') ?
        'goods_list.htm' : (($operation== 'goods_trash') ? 'goods_trash.htm' : 'group_list.htm');
    $smarty->assign('pageHtml',$htm_file);
    $smarty->display($htm_file);
}

/*------------------------------------------------------ */
//-- 添加新商品 编辑商品
/*------------------------------------------------------ */

elseif ($operation== 'goods_add' || $operation== 'goods_edit')
{
    $is_add = $operation== 'goods_add'; // 添加还是编辑的标识


    /* 如果是安全模式，检查目录是否存在 */
    if (ini_get('safe_mode') == 1 && (!file_exists('../' . IMAGE_DIR) || !is_dir('../' . IMAGE_DIR)))
    {
        if (@!mkdir('../' . IMAGE_DIR, 0777))
        {
            $warning = sprintf('您的服务器运行在安全模式下，而且 %s 目录不存在。您可能需要先行创建该目录才能上传图片。', '../' . IMAGE_DIR);
            $smarty->assign('warning', $warning);
        }
    }

    /* 如果目录存在但不可写，提示用户 */
    elseif (file_exists('../' . IMAGE_DIR) && file_mode_info('../' . IMAGE_DIR) < 2)
    {
        $warning = sprintf('目录 %s 不可写，您需要把该目录设为可写才能上传图片。', '../' . IMAGE_DIR);
        $smarty->assign('warning', $warning);
    }

    /* 取得商品信息 */
    if ($is_add)
    {
        /* 默认值 */
        $last_choose = array(0, 0);
        if (!empty($_COOKIE['AOSCP']['last_choose']))
        {
            $last_choose = explode('|', $_COOKIE['AOSCP']['last_choose']);
        }
        $max_id     = $db->getOne("SELECT MAX(goods_id) + 1 FROM ".$aos->table('goods'));
        $goods_sn   = generate_goods_sn($max_id);
        $goods = array(
            'goods_id'      => 0,
            'goods_sn'      => $goods_sn,
            'goods_desc'    => '',
            'cat_id'        => $last_choose[0],
            'is_on_sale'    => 0,
            'is_shipping' => 0,
            'shop_price'    => 0,
            'market_price'  => 0,
            'commission'  => 0,
            'is_dist'  => 0,
            'comm_shop_price'  => 0,
            'comm_tuan_price'  => 0,
            'restrictions'  => 0,
            'goods_video'  => '',
            'goods_number'  => $_CFG['default_storage'],
            'warn_number'   => 1,
            'goods_weight'  => 0,
            'goods_label'   => '',
            'give_integral' => -1
        );

        /* 属性 */
        $sql = "DELETE FROM " . $aos->table('goods_attr') . " WHERE goods_id = 0";
        $db->query($sql);

        /* 图片列表 */
        $album_list = array();
    }
    else
    {
        /* 商品信息 */
        $sql = "SELECT * FROM " . $aos->table('goods') . " WHERE goods_id = '$_REQUEST[goods_id]'";
        $goods = $db->getRow($sql);

        if (empty($goods) === true)
        {
            /* 默认值 */
            $goods = array(
                'goods_id'      => 0,
                'goods_desc'    => '',
                'cat_id'        => 0,
                'is_on_sale'    => 1,
                'is_shipping' => 0,
                'shop_price'    => 0,
                'market_price'  => 0,
                'commission'  => 0,
                'is_dist'  => 0,
                'comm_shop_price'  => 0,
                'comm_tuan_price'  => 0,
                'restrictions'  => 0,
                'goods_video'  => '',
                'goods_number'  => 1,
                'warn_number'   => 1,
                'goods_weight'  => 0,
                'goods_label'   => '',
                'give_integral' => -1
            );
        }

        /* 根据商品重量的单位重新计算 */
        if ($goods['goods_weight'] > 0)
        {
            $goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
        }

        if (!empty($goods['goods_brief']))
        {
            $goods['goods_brief'] = $goods['goods_brief'];
        }

        if (!empty($goods['keywords']))
        {
            $goods['keywords']    = $goods['keywords'];
        }

        /* 商品图片路径 */
        if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 10))
        {
            $goods['goods_img'] = get_image_path($_REQUEST['goods_id'], $goods['goods_img']);
            $goods['tuan_img'] = get_image_path($_REQUEST['goods_id'], $goods['tuan_img']);
        }

        /* 相册图片列表 */
        $sql = "SELECT * FROM " . $aos->table('goods_album') . " WHERE goods_id = '$goods[goods_id]' order by album_sort asc";
        $album_list = $db->getAll($sql);
        //print_r($album_list);
		
		

        /* 格式化相册图片路径 */
        if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0))
        {
            foreach ($album_list as $key => $album)
            {
                $album[$key]['album_img'] = get_image_path($album['goods_id'], $album['album_img']);
            }
        }
    }

    //print_r($goods);

    /* 模板赋值 */
    $smarty->assign('ur_here', $is_add ? '添加商品' : '修改商品');
    $smarty->assign('goods', $goods);
    $smarty->assign('cat_list', cat_list(0, $goods['cat_id']));

    $goods_label = explode(",", $goods['goods_label']);
    $smarty->assign('goods_label', $goods_label);

    $smarty->assign('label_list', get_label_list());

    $smarty->assign('unit_list', get_unit_list());
    $smarty->assign('weight_unit', $is_add ? '1' : ($goods['goods_weight'] >= 1 ? '1' : '0.001'));
    $smarty->assign('cfg', $_CFG);
    $smarty->assign('form_act', $is_add ? 'goods_insert' : ($operation== 'goods_edit' ? 'goods_update' : 'goods_insert'));
    $smarty->assign('is_add', $is_add);
    $smarty->assign('album_list', $album_list);
    $smarty->assign('goods_attr', get_attr_list($goods['goods_id']));
    $smarty->assign('gd', gd_version());

    $tuan_price_list = '';
    if(isset($_REQUEST['goods_id']))
    {
    $tuan_price_list = get_tuan_price_list($_REQUEST['goods_id']);
    }
    if (empty($tuan_price_list))
    {
        $tuan_price_list = array('0'=>array('number'=>'','price'=>''));
    }
    $smarty->assign('tuan_price_list', $tuan_price_list);
    /* 显示商品信息页面 */
    
    $smarty->display('goods_info.htm');
}

/*------------------------------------------------------ */
//-- 插入商品 更新商品
/*------------------------------------------------------ */

elseif ($operation== 'goods_insert' || $operation== 'goods_update')
{
    if(!empty($_POST['goods_label'])){
        $_POST['goods_label'] = implode(",", $_POST['goods_label']);
    }
    
    /* 检查货号是否重复 */
    if ($_POST['goods_sn'])
    {
        $sql = "SELECT COUNT(*) FROM " . $aos->table('goods') .
                " WHERE goods_sn = '$_POST[goods_sn]' AND is_delete = 0 AND goods_id <> '$_POST[goods_id]'";
        if ($db->getOne($sql) > 0)
        {
            sys_msg('您输入的货号已存在，请换一个', 1, array(), false);
        }
    }

    /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
	/*
    if (isset($_FILES['goods_img']['error']))
    {
        // 最大上传文件大小
        $php_maxsize = ini_get('upload_max_filesize');
        $htm_maxsize = '2M';

        // 商品图片
        if ($_FILES['goods_img']['error'] == 0)
        {
            if (!$image->check_img_type($_FILES['goods_img']['type']))
            {
                sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
            }
        }
        elseif ($_FILES['goods_img']['error'] == 1)
        {
            sys_msg(sprintf($_LANG['goods_img_too_big'], $php_maxsize), 1, array(), false);
        }
        elseif ($_FILES['goods_img']['error'] == 2)
        {
            sys_msg(sprintf($_LANG['goods_img_too_big'], $htm_maxsize), 1, array(), false);
        }

        // 相册图片
        foreach ($_FILES['img_url']['error'] AS $key => $value)
        {
            if ($value == 0)
            {
                if (!$image->check_img_type($_FILES['img_url']['type'][$key]))
                {
                    sys_msg(sprintf($_LANG['invalid_img_url'], $key + 1), 1, array(), false);
                }
            }
            elseif ($value == 1)
            {
                sys_msg(sprintf($_LANG['img_url_too_big'], $key + 1, $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['img_url']['error'] == 2)
            {
                sys_msg(sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize), 1, array(), false);
            }
        }
    }*/

    /* 插入还是更新的标识 */
    $is_insert = $operation== 'goods_insert';

    /* 处理商品图片 */
    $goods_img        = '';  // 初始化商品图片
	$tuan_img        = '';
	

    // 如果上传了商品图片，相应处理
    if (($_FILES['goods_img']['tmp_name'] != '' && $_FILES['goods_img']['tmp_name'] != 'none'))
    {
        if ($_REQUEST['goods_id'] > 0)
        {
            /* 删除原来的图片文件 */
            $sql = "SELECT goods_img FROM " . $aos->table('goods') .
                    " WHERE goods_id = '$_REQUEST[goods_id]'";
            $row = $db->getRow($sql);
            if ($row['goods_img'] != '' && is_file(ROOT_PATH. $row['goods_img']))
            {
                @unlink(ROOT_PATH. $row['goods_img']);
                //oss_delete_file($row['goods_thumb']);
            }
        }
    }
	
	if (($_FILES['tuan_img']['tmp_name'] != '' && $_FILES['tuan_img']['tmp_name'] != 'none'))
    {
        if ($_REQUEST['goods_id'] > 0)
        {
            /* 删除原来的图片文件 */
            $sql = "SELECT tuan_img FROM " . $aos->table('goods') .
                    " WHERE goods_id = '$_REQUEST[goods_id]'";
            $row = $db->getRow($sql);
            if ($row['tuan_img'] != '' && is_file(ROOT_PATH . $row['tuan_img']))
            {
                @unlink(ROOT_PATH . $row['tuan_img']);
                //oss_delete_file($row['goods_thumb']);
            }
        }
    }


	$goods_img   = $image->upload_image($_FILES['goods_img'],'upload'); 
	$tuan_img   = $image->upload_image($_FILES['tuan_img'],'upload');


    /* 如果没有输入商品货号则自动生成一个商品货号 */
    if (empty($_POST['goods_sn']))
    {
        $max_id     = $is_insert ? $db->getOne("SELECT MAX(goods_id) + 1 FROM ".$aos->table('goods')) : $_REQUEST['goods_id'];
        $goods_sn   = generate_goods_sn($max_id);
    }
    else
    {
        $goods_sn   = $_POST['goods_sn'];
    }

    /* 处理商品数据 */
    $shop_price = !empty($_POST['shop_price']) ? $_POST['shop_price'] : 0;
    $market_price = !empty($_POST['market_price']) ? $_POST['market_price'] : 0;
    $commission = !empty($_POST['commission']) ? $_POST['commission'] : 0;
    $comm_shop_price = !empty($_POST['comm_shop_price']) ? $_POST['comm_shop_price'] : 0;
    $comm_tuan_price = !empty($_POST['comm_tuan_price']) ? $_POST['comm_tuan_price'] : 0;
    $restrictions = !empty($_POST['restrictions']) ? $_POST['restrictions'] : 0;
    $goods_video = !empty($_POST['goods_video']) ? $_POST['goods_video'] : '';
    $goods_weight = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
    $goods_label = !empty($_POST['goods_label']) ? $_POST['goods_label'] : '';
    $is_best = isset($_POST['is_best']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
	$is_shipping = isset($_POST['is_shipping']) ? 1 : 0;
    $is_dist = isset($_POST['is_dist']) ? $_POST['is_dist'] : 0;
    $goods_number = isset($_POST['goods_number']) ? $_POST['goods_number'] : 0;
    $warn_number = isset($_POST['warn_number']) ? $_POST['warn_number'] : 0;
    $give_integral = isset($_POST['give_integral']) ? intval($_POST['give_integral']) : '-1';
    $catgory_id = empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);

    /* 入库 */
    if ($is_insert)
    {
            $sql = "INSERT INTO " . $aos->table('goods') . " (goods_name, goods_sn, " .
            "cat_id, shop_price, market_price, commission, is_dist, comm_shop_price, comm_tuan_price, restrictions, goods_video, goods_img, tuan_img, goods_brief,keywords, " .
                    "seller_note, goods_weight, goods_label, goods_number, warn_number, give_integral, is_best, is_new, is_hot, " .
                    "is_on_sale, is_shipping, goods_desc, add_time, last_update)" .
                "VALUES ('$_POST[goods_name]', '$goods_sn', '$catgory_id', " .
                "'$shop_price', '$market_price', '$commission', '$is_dist', '$comm_shop_price', '$comm_tuan_price', '$restrictions', '$goods_video', ".
                    "'$goods_img', '$tuan_img', ".
                    "'$_POST[goods_brief]', '$_POST[keywords]', '$_POST[seller_note]', '$goods_weight', '$goods_label', '$goods_number',".
                    " '$warn_number', '$give_integral', '$is_best', '$is_new', '$is_hot', '$is_on_sale', $is_shipping, ".
                    " '$_POST[goods_desc]', '" . gmtime() . "', '". gmtime() ."')";
    }
    else
    {
        /* 如果有上传图片，删除原来的商品图 */
        $sql = "SELECT goods_img, tuan_img " .
                    " FROM " . $aos->table('goods') .
                    " WHERE goods_id = '$_REQUEST[goods_id]'";
        $row = $db->getRow($sql);
        if ($goods_img && $row['goods_img'])
        {
            @unlink(ROOT_PATH . $row['goods_img']);
        }
		if ($tuan_img && $row['tuan_img'])
        {
            @unlink(ROOT_PATH . $row['tuan_img']);
        }

        $sql = "UPDATE " . $aos->table('goods') . " SET " .
                "goods_name = '$_POST[goods_name]', " .
                "goods_sn = '$goods_sn', " .
                "cat_id = '$catgory_id', " .
                "shop_price = '$shop_price', " .
                "market_price = '$market_price', " .
                "commission = '$commission', " .
                "comm_shop_price = '$comm_shop_price', " .
                "comm_tuan_price = '$comm_tuan_price', " .
                "restrictions = '$restrictions', " .
                "goods_video = '$goods_video', ";

        /* 如果有上传图片，需要更新数据库 */
        if ($goods_img)
        {
            $sql .= "goods_img = '$goods_img', ";
        }
		if ($tuan_img)
        {
            $sql .= "tuan_img = '$tuan_img', ";
        }
        $sql .= "goods_brief = '$_POST[goods_brief]', " .
                "keywords = '$_POST[keywords]', " .
                "seller_note = '$_POST[seller_note]', " .
                "goods_weight = '$goods_weight'," .
                "goods_label = '$goods_label'," .
                "goods_number = '$goods_number', " .
                "warn_number = '$warn_number', " .
                "give_integral = '$give_integral', " .
                "is_best = '$is_best', " .
                "is_new = '$is_new', " .
                "is_hot = '$is_hot', " .
                "is_on_sale = '$is_on_sale', " .
                "is_shipping = '$is_shipping', " .
                "is_dist = '$is_dist', " .
                "goods_desc = '$_POST[goods_desc]', " .
                "last_update = '". gmtime() ."'".
                "WHERE goods_id = '$_REQUEST[goods_id]' LIMIT 1";
    }
    $db->query($sql);

    /* 商品编号 */
    $goods_id = $is_insert ? $db->insert_id() : $_REQUEST['goods_id'];

    /* 记录日志 */
    if ($is_insert)
    {
        admin_log($_POST['goods_name'], 'add', 'goods');
    }
    else
    {
        admin_log($_POST['goods_name'], 'edit', 'goods');
    }

    /* 处理属性 */
    if ((isset($_POST['attr_value_list']) && isset($_POST['attr_price_list'])) || (empty($_POST['attr_value_list']) && empty($_POST['attr_price_list'])))
    {
        
        // 取得原有的属性值
       $goods_attr_list = array();


        /* $sql = "SELECT attr_id FROM " . $aos->table('attribute');

        $attr_res = $db->query($sql);

        $attr_list = array();

        while ($row = $db->fetchRow($attr_res))
        {
            $attr_list[$row['attr_id']] = $row['attr_id'];
        }*/

        $sql = "SELECT g.*
                FROM " . $aos->table('goods_attr') . " AS g WHERE g.goods_id = '$goods_id'";

        $res = $db->query($sql);

        while ($row = $db->fetchRow($res))
        {
            $goods_attr_list[$row['attr_id']] = array('sign' => 'delete', 'attr_id' => $row['attr_id']);
        }
        

        // 循环现有的，根据原有的做相应处理
        if(isset($_POST['attr_id_list']))
        {
            foreach ($_POST['attr_id_list'] AS $key => $attr_id)
            {
                $attr_value = trim($_POST['attr_value_list'][$key]);
                $attr_price = trim($_POST['attr_price_list'][$key]);
                $product_number = intval($_POST['attr_num_list'][$key]);
                $product_sn = trim($_POST['attr_sn_list'][$key]);
				$attr_img = array('name' => $_FILES['attr_img_list']['name'][$key] , 'type' => $_FILES['attr_img_list']['type'][$key] , 'tmp_name' => $_FILES['attr_img_list']['tmp_name'][$key] , 'error' => $_FILES['attr_img_list']['error'][$key], 'size' => $_FILES['attr_img_list']['size'][$key]);
				
				// 如果上传了商品图片，相应处理
				if (($_FILES['attr_img_list']['tmp_name'][$key] != '' && $_FILES['attr_img_list']['tmp_name'][$key] != 'none'))
				{
					$attr_img   = $image->upload_image($attr_img,'upload'); 
					$attr_img = reformat_image_name($goods_id, $attr_img, 'attr');
				}
				else
				{
					$attr_img =  '';
				}
	
                if (!empty($attr_value))
                {
                    if (isset($goods_attr_list[$attr_id]))
                    {
                        // 如果原来有，标记为更新
                        $goods_attr_list[$attr_value]['sign'] = 'update';
                        $goods_attr_list[$attr_value]['attr_price'] = $attr_price;
						$goods_attr_list[$attr_value]['attr_img'] = $attr_img;
                        $goods_attr_list[$attr_value]['product_number'] = $product_number;
                        $goods_attr_list[$attr_value]['product_sn'] = $product_sn;
                        $goods_attr_list[$attr_value]['attr_id'] = $attr_id;
                        $goods_attr_list[$attr_id] = array();
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $goods_attr_list[$attr_value]['sign'] = 'insert';
                        $goods_attr_list[$attr_value]['attr_price'] = $attr_price;
						$goods_attr_list[$attr_value]['attr_img'] = $attr_img;
                        $goods_attr_list[$attr_value]['product_number'] = $product_number;
                        $goods_attr_list[$attr_value]['product_sn'] = $product_sn;
                    }

                }
				
            }
        }


        /* 插入、更新、删除数据 */
        $k_g=0;    
            foreach ($goods_attr_list as $attr_value => $info)
            {
                if(!empty($info)){
                    $k_g=$k_g+1;
                }
                if(empty($info['product_sn'])){
                    $info['product_sn']=$goods_sn."_p".$k_g;
                }
                
                if ($info['sign'] == 'insert')
                {
					if ($info['attr_img'])
                    {
						$sql = "INSERT INTO " .$aos->table('goods_attr'). " (goods_id, attr_value,product_sn,product_number, attr_price, attr_img)".
                            "VALUES ('$goods_id', '$attr_value', '$info[product_sn]', '$info[product_number]', '$info[attr_price]', '$info[attr_img]')";
					}
					else
					{
						$sql = "INSERT INTO " .$aos->table('goods_attr'). " (goods_id, attr_value,product_sn,product_number, attr_price)".
                            "VALUES ('$goods_id', '$attr_value','$info[product_sn]', '$info[product_number]', '$info[attr_price]')";
					}
                }
                elseif ($info['sign'] == 'update')
                {
					if ($info['attr_img'])
                    {
						
						/* 删除原来的图片文件 */
						$attr_img = "SELECT attr_img FROM " . $aos->table('goods_attr') .
								" WHERE attr_id = '$info[attr_id]'";
						$row = $db->getRow($attr_img);
						if ($info['attr_img'] && $row['attr_img']&& is_file(ROOT_PATH . $row['attr_img']))
						{
							@unlink(ROOT_PATH. $row['attr_img']);
						}
						$sql = "UPDATE " .$aos->table('goods_attr'). " SET product_sn = '$info[product_sn]',product_number = '$info[product_number]',attr_price = '$info[attr_price]',attr_img = '$info[attr_img]' WHERE attr_id = '$info[attr_id]' LIMIT 1";
					}
					else
					{
						$sql = "UPDATE " .$aos->table('goods_attr'). " SET product_sn = '$info[product_sn]',product_number = '$info[product_number]',attr_price = '$info[attr_price]' WHERE attr_id = '$info[attr_id]' LIMIT 1";
					}
                }
                else
                {
                    $sql = "DELETE FROM " .$aos->table('goods_attr'). " WHERE attr_id = '$info[attr_id]' LIMIT 1";
					
					if ($info['attr_id'] > 0)
					{
						/* 删除原来的图片文件 */
						$attr_img = "SELECT attr_img FROM " . $aos->table('goods_attr') .
								" WHERE attr_id = '$info[attr_id]'";
								
						$row = $db->getRow($attr_img);
						if ($row['attr_img'] != '' && is_file(ROOT_PATH. $row['attr_img']))
						{
							@unlink(ROOT_PATH . $row['attr_img']);
						}
					}
                }
                $db->query($sql);
            }
        
    }


    /* 处理优惠价格 */
    if (isset($_POST['tuan_number']) && isset($_POST['tuan_price']))
    {
        $temp_num = array_count_values($_POST['tuan_number']);
        foreach($temp_num as $v)
        {
            if ($v > 1)
            {
                sys_msg('团购数量重复！', 1, array(), false);
                break;
            }
        }
        handle_tuan_price($goods_id, $_POST['tuan_number'], $_POST['tuan_price']);
    }
	

    /* 重新格式化图片名称 */
	
	

    $goods_img = reformat_image_name($goods_id, $goods_img, 'goods');
    $tuan_img = reformat_image_name($goods_id, $tuan_img, 'tuan');

	
    if ($goods_img !== false)
    {
        $db->query("UPDATE " . $aos->table('goods') . " SET goods_img = '$goods_img' WHERE goods_id='$goods_id'");
        //oss_upload_file($_FILES['goods_img']);
    }
    if ($tuan_img !== false)
    {
        $db->query("UPDATE " . $aos->table('goods') . " SET tuan_img = '$tuan_img' WHERE goods_id='$goods_id'");
        //oss_upload_file($tuan_img);
    }
    /* 如果有图片，把商品图片加入图片相册 */
	
    if (isset($album))
    {
        /* 重新格式化图片名称 */
        $album = reformat_image_name($goods_id, $album, 'album');

        $sql = "INSERT INTO " . $aos->table('goods_album') . " (goods_id, album_img) " .
                "VALUES ('$goods_id', '$album')";
        $db->query($sql);
        //oss_upload_file($album);
    }
	
	
	

//var_dump($_FILES['desc_img']);exit;
    /* 处理相册图片 */
    handle_album_image($goods_id, $_FILES['album_img']);
	
	

    /* 编辑时处理相册图片排序 */
    if (!$is_insert && isset($_POST['old_album_sort']))
    {
        foreach ($_POST['old_album_sort'] AS $album_id => $album_sort)
        {
            $sql = "UPDATE " . $aos->table('goods_album') . " SET album_sort = '$album_sort' WHERE album_id = '$album_id' LIMIT 1";
            $db->query($sql);
        }
    }
    

    



    if (!$is_insert && isset($_POST['sort_order']))
    {
        foreach($_POST['sort_order'] as $k=>$v){
            $i++;
            $sql="update ".$aos->table('goods_album')." set album_sort = $i where album_id = $k";
            $db->query($sql);
        }
    }
    /* 记录上一次选择的分类和品牌 */
    setcookie('AOSCP[last_choose]', $catgory_id, gmtime() + 86400);
    /* 清空缓存 */
    clear_cache_files();

   
    /* 提示页面 */
    $link = array();
    
    if ($is_insert)
    {
        $link[0] = array('href' => 'index.php?act=goods&op=goods_add', 'text' => '继续添加');
    }else{
        $link[0] = array('href' => 'index.php?act=goods&op=goods_edit&goods_id='.$goods_id, 'text' => '产品详情');
    }
    $link[1] = array('href' => 'index.php?act=goods&op=goods_list', 'text' => '产品列表');

    
    for($i=0;$i<count($link);$i++)
    {
       $key_array[]=$i;
    }
    krsort($link);
    $link = array_combine($key_array, $link);
    
    sys_msg($is_insert ? '添加商品成功。' : '编辑商品成功。', 0, $link);
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($operation== 'goods_batch')
{

    /* 取得要操作的商品编号 */
    $goods_id = !empty($_POST['checkboxes']) ? $_POST['checkboxes'] : 0;

    if (isset($_POST['type']))
    {
        /* 放入回收站 */
        if ($_POST['type'] == 'trash')
        {
            foreach($goods_id as $vo){
                $link1[] = array('href' => 'index.php?act=goods', 'text' => '商品列表');
                $sql="select goods_name from ".$aos->table('goods')." where goods_id = '$vo'";
                $goods_name=$db->getOne($sql);
                $sql="select count(*) from ".$aos->table('seckill')." where goods_id = '$vo'";
                $count=$db->getOne($sql);
                if($count>0){
                    sys_msg("<span style='color:red'>".$goods_name.'</span>存在秒杀产品，请先删除秒杀产品', 0, $link1);
                }
                $sql="select count(*) from ".$aos->table('lottery')." where goods_id = '$vo'";
                $count1=$db->getOne($sql);
                if($count1>0){
                    sys_msg("<span style='color:red'>".$goods_name.'</span>-存在抽奖产品，请先删除抽奖产品', 0, $link1);
                }
                update_goods($vo, 'is_delete', '1');
            }
            

            /* 记录日志 */
            admin_log('', 'batch_trash', 'goods');
        }
        /* 还原 */
        if ($_POST['type'] == 'restore')
        {

            update_goods($goods_id, 'is_delete', '0');

            /* 记录日志 */
            admin_log('', 'batch_trash', 'goods');
        }
        /* 删除 */
        elseif ($_POST['type'] == 'drop')
        {

            delete_goods($goods_id);

            /* 记录日志 */
            admin_log('', 'batch_remove', 'goods');
        }
    }

    /* 清除缓存 */
    clear_cache_files();

    if ($_POST['type'] == 'drop' || $_POST['type'] == 'restore')
    {
        $link[] = array('href' => 'index.php?act=goods&op=goods_trash', 'text' => '商品回收站');
    }
    else
    {
        $link[] = array('href' => 'index.php?act=goods', 'text' => '商品列表');
    }
    sys_msg('批量操作成功。', 0, $link);
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
//-- 修改精品推荐状态
/*------------------------------------------------------ */
elseif ($operation== 'toggle_best')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $is_best        = intval($_POST['val']);

    if ($exc->edit("is_best = '$is_best', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($is_best);
    }
}

/*------------------------------------------------------ */
//-- 修改新品推荐状态
/*------------------------------------------------------ */
elseif ($operation== 'toggle_new')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $is_new         = intval($_POST['val']);

    if ($exc->edit("is_new = '$is_new', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($is_new);
    }
}

/*------------------------------------------------------ */
//-- 修改热销推荐状态
/*------------------------------------------------------ */
elseif ($operation== 'toggle_hot')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $is_hot         = intval($_POST['val']);

    if ($exc->edit("is_hot = '$is_hot', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($is_hot);
    }
}

/*------------------------------------------------------ */
//-- 修改商品排序
/*------------------------------------------------------ */
elseif ($operation== 'edit_sort_order')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $sort_order     = intval($_POST['val']);

    if ($exc->edit("sort_order = '$sort_order', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($sort_order);
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
//-- 放入回收站
/*------------------------------------------------------ */
elseif ($operation== 'goods_remove')
{
    $goods_id = intval($_REQUEST['id']);
    $sql="select count(*) from ".$aos->table('seckill')." where goods_id = '$goods_id'";
    $count=$db->getOne($sql);
    if($count>0){
        make_json_error('存在秒杀产品，请先删除秒杀产品');
    }
    $sql="select count(*) from ".$aos->table('lottery')." where goods_id = '$goods_id'";
    $count1=$db->getOne($sql);
    if($count1>0){
        make_json_error('存在抽奖产品，请先删除抽奖产品');
    }
    /* 检查权限 */
    check_authz_json('remove_back');

    if ($exc->edit("is_delete = 1", $goods_id))
    {
        clear_cache_files();
        $goods_name = $exc->get_name($goods_id);

        admin_log(addslashes($goods_name), 'trash', 'goods'); // 记录日志

        make_json_result($goods_id);
        //sys_msg('恭喜，已加入回收站！', 0, array(array('href'=>'index.php?act=goods&op=goods_list' , 'text' =>'商品管理')));
        exit;
    }else{
        make_json_error('操作失败');
    }
}

/*------------------------------------------------------ */
//-- 还原回收站中的商品
/*------------------------------------------------------ */

elseif ($operation== 'restore_goods')
{
    $goods_id = intval($_REQUEST['id']);

    check_authz_json('remove_back'); // 检查权限

    $exc->edit("is_delete = 0, add_time = '" . gmtime() . "'", $goods_id);
    clear_cache_files();

    $goods_name = $exc->get_name($goods_id);

    admin_log(addslashes($goods_name), 'restore', 'goods'); // 记录日志

    sys_msg('恭喜，商品以还原！', 0, array(array('href'=>'index.php?act=goods&op=goods_list' , 'text' =>'商品管理')));
    exit;
}

/*------------------------------------------------------ */
//-- 彻底删除商品
/*------------------------------------------------------ */
elseif ($operation== 'drop_goods')
{
    // 检查权限
    check_authz_json('remove_back');

    // 取得参数
    $goods_id = intval($_REQUEST['id']);
    if ($goods_id <= 0)
    {
        //make_json_error('invalid params');
        //sys_msg('invalid params');
        
        make_json_error('invalid params');
    }

    /* 取得商品信息 */
    $sql = "SELECT goods_id, goods_name, is_delete, " .
                "goods_img, tuan_img " .
            "FROM " . $aos->table('goods') .
            " WHERE goods_id = '$goods_id'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
        make_json_error('该商品不存在');
    }

    if ($goods['is_delete'] != 1)
    {
        make_json_error('该商品尚未放入回收站，不能删除');
    }

    /* 删除商品图片和轮播图片 */
    if (!empty($goods['goods_img']))
    {
        @unlink(ROOT_PATH . $goods['goods_img']);
        //oss_delete_file($goods['goods_img']);
    }
    if (!empty($goods['tuan_img']))
    {
        @unlink(ROOT_PATH. $goods['tuan_img']);
        //oss_delete_file($goods['tuan_img']);
    }
    

    /* 记录日志 */
    admin_log(addslashes($goods['goods_name']), 'remove', 'goods');

    /* 删除商品相册 */
    $sql = "SELECT album_img " .
            "FROM " . $aos->table('goods_album') .
            " WHERE goods_id = '$goods_id'";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        if (!empty($row['album_img']))
        {
            @unlink(ROOT_PATH . $row['album_img']);
            //oss_delete_file($row['album_img']);
        }
    }

  

    /* 删除商品规格 */
    $sql = "SELECT attr_img " .
            "FROM " . $aos->table('goods_attr') .
            " WHERE goods_id = '$goods_id'";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        if (!empty($row['attr_img']))
        {
            @unlink(ROOT_PATH. $row['attr_img']);
            oss_delete_file($row['attr_img']);
        }
    }

    $sql = "DELETE FROM " . $aos->table('goods_album') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    

    /* 删除相关表记录 */
    $sql = "DELETE FROM " . $aos->table('goods_attr') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $aos->table('comment') . " WHERE id_value = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $aos->table('collect') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    /* 删除商品 */
    $exc->drop($goods_id);

    clear_cache_files();
    make_json_result($goods_id);

    exit;
}


/*------------------------------------------------------ */
//-- 删除图片
/*------------------------------------------------------ */
elseif ($operation== 'drop_album')
{
    check_authz_json('goods_manage');

    $album_id = empty($_REQUEST['album_id']) ? 0 : intval($_REQUEST['album_id']);

    /* 删除图片文件 */
    $sql = "SELECT album_img " .
            " FROM " . $GLOBALS['aos']->table('goods_album') .
            " WHERE album_id = '$album_id'";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row['album_img'] != '' && is_file(ROOT_PATH . $row['album_img']))
    {
        @unlink(ROOT_PATH. $row['album_img']);
    }

    /* 删除数据 */
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('goods_album') . " WHERE album_id = '$album_id' LIMIT 1";
    $GLOBALS['db']->query($sql);

    clear_cache_files();
    make_json_result($album_id);
}

/*------------------------------------------------------ */
//-- 修改商品虚拟数量
/*------------------------------------------------------ */
elseif ($operation== 'edit_virtual_sales')
{
    check_authz_json('goods_manage');

    $goods_id   = intval($_POST['id']);
    $virtual_sales  = intval($_POST['val']);

    if(($virtual_sales < 0 || $virtual_sales == 0) && $_POST['val'] != "$virtual_sales")
    {
        make_json_error('商品虚拟销售数量错误');
    }

    /*if(check_goods_product_exist($goods_id) == 1)
    {
        make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_goods_number']);
    }*/

    if ($exc->edit("virtual_sales = '$virtual_sales', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($virtual_sales);
    }
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

function get_label_list()
{
    $sql = "SELECT * FROM ".$GLOBALS['aos']->table('goods_label')." where enabled = 1 ORDER BY sort_order ASC";
    return $GLOBALS['db']->GetAll($sql);
}
?>