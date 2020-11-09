<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 取得推荐类型列表
 * @return  array   推荐类型列表
 */
function get_intro_list()
{
    return array(
        'is_best'    => '精品',
        'is_new'     => '新品',
        'is_hot'     => '热销',
        'all_type' => '全部推荐',
    );
}

/**
 * 取得重量单位列表
 * @return  array   重量单位列表
 */
function get_unit_list()
{
    return array(
        '1'     => '千克',
        '0.001' => '克',
    );
}

/**
 * 插入或更新商品属性
 *
 * @param   int     $goods_id           商品编号
 * @param   array   $id_list            属性编号数组
 * @param   array   $is_spec_list       是否规格数组 'true' | 'false'
 * @param   array   $value_price_list   属性值数组
 * @return  array                       返回受到影响的goods_attr_id数组
 */
function handle_goods_attr($goods_id, $id_list, $is_spec_list, $value_price_list)
{
    $goods_attr_id = array();

    /* 循环处理每个属性 */
    foreach ($id_list AS $key => $id)
    {
        $is_spec = $is_spec_list[$key];
        if ($is_spec == 'false')
        {
            $value = $value_price_list[$key];
            $price = '';
        }
        else
        {
            $value_list = array();
            $price_list = array();
            if ($value_price_list[$key])
            {
                $vp_list = explode(chr(13), $value_price_list[$key]);
                foreach ($vp_list AS $v_p)
                {
                    $arr = explode(chr(9), $v_p);
                    $value_list[] = $arr[0];
                    $price_list[] = $arr[1];
                }
            }
            $value = join(chr(13), $value_list);
            $price = join(chr(13), $price_list);
        }

        // 插入或更新记录
        $sql = "SELECT goods_attr_id FROM " . $GLOBALS['aos']->table('goods_attr') . " WHERE goods_id = '$goods_id' AND attr_id = '$id' AND attr_value = '$value' LIMIT 0, 1";
        $result_id = $GLOBALS['db']->getOne($sql);
        if (!empty($result_id))
        {
            $sql = "UPDATE " . $GLOBALS['aos']->table('goods_attr') . "
                    SET attr_value = '$value'
                    WHERE goods_id = '$goods_id'
                    AND attr_id = '$id'
                    AND goods_attr_id = '$result_id'";

            $goods_attr_id[$id] = $result_id;
        }
        else
        {
            $sql = "INSERT INTO " . $GLOBALS['aos']->table('goods_attr') . " (goods_id, attr_id, attr_value, attr_price) " .
                    "VALUES ('$goods_id', '$id', '$value', '$price')";
        }

        $GLOBALS['db']->query($sql);

        if ($goods_attr_id[$id] == '')
        {
            $goods_attr_id[$id] = $GLOBALS['db']->insert_id();
        }
    }

    return $goods_attr_id;
}

/**
 * 保存某商品的相册图片
 * @param   int     $goods_id
 * @param   array   $image_files
 * @return  void
 */
function handle_album_image($goods_id, $image_files)
{
	foreach ($image_files['name'] AS $key => $img_sort)
    {
        /* 是否成功上传 */
        $flag = false;
        if (isset($image_files['error']))
        {
            if ($image_files['error'][$key] == 0)
            {
                $flag = true;
            }
        }
        else
        {
            if ($image_files['tmp_name'][$key] != 'none')
            {
                $flag = true;
            }
        }

        if ($flag)
        {

            $upload = array(
                'name' => $image_files['name'][$key],
                'type' => $image_files['type'][$key],
                'tmp_name' => $image_files['tmp_name'][$key],
                'size' => $image_files['size'][$key],
            );
            if (isset($image_files['error']))
            {
                $upload['error'] = $image_files['error'][$key];
            }
            $album = $GLOBALS['image']->upload_image($upload,'upload');
            if ($album === false)
            {
                sys_msg($GLOBALS['image']->error_msg(), 1, array(), false);
            }

            /* 重新格式化图片名称 */
            $album = reformat_image_name($goods_id, $album, 'album');
            $sql = "INSERT INTO " . $GLOBALS['aos']->table('goods_album') . " (goods_id, album_img) " .
                    "VALUES ('$goods_id', '$album')";
            $GLOBALS['db']->query($sql);
        }
	}
}


/**
 * 修改商品某字段值
 * @param   string  $goods_id   商品编号，可以为多个，用 ',' 隔开
 * @param   string  $field      字段名
 * @param   string  $value      字段值
 * @return  bool
 */
function update_goods($goods_id, $field, $value)
{
    if ($goods_id)
    {
        /* 清除缓存 */
        clear_cache_files();

        $sql = "UPDATE " . $GLOBALS['aos']->table('goods') .
                " SET $field = '$value' , last_update = '". gmtime() ."' " .
                "WHERE goods_id " . db_create_in($goods_id);
        return $GLOBALS['db']->query($sql);
    }
    else
    {
        return false;
    }
}

/**
 * 从回收站删除多个商品
 * @param   mix     $goods_id   商品id列表：可以逗号格开，也可以是数组
 * @return  void
 */
function delete_goods($goods_id)
{
    if (empty($goods_id))
    {
        return;
    }

    /* 取得有效商品id */
    $sql = "SELECT DISTINCT goods_id FROM " . $GLOBALS['aos']->table('goods') .
            " WHERE goods_id " . db_create_in($goods_id) . " AND is_delete = 1";
    $goods_id = $GLOBALS['db']->getCol($sql);
    if (empty($goods_id))
    {
        return;
    }

    /* 删除商品图片和轮播图片文件 */
    $sql = "SELECT goods_img, tuan_img " .
            "FROM " . $GLOBALS['aos']->table('goods') .
            " WHERE goods_id " . db_create_in($goods_id);
    $res = $GLOBALS['db']->query($sql);
    while ($goods = $GLOBALS['db']->fetchRow($res))
    {
        if (!empty($goods['goods_img']))
        {
            @unlink(ROOT_PATH. $goods['goods_img']);
        }
        if (!empty($goods['tuan_img']))
        {
            @unlink(ROOT_PATH . $goods['tuan_img']);
        }
    }

    /* 删除商品 */
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('goods') .
            " WHERE goods_id " . db_create_in($goods_id);
    $GLOBALS['db']->query($sql);

    /* 删除商品属性的图片文件 */
    $sql = "SELECT attr_img " .
            "FROM " . $GLOBALS['aos']->table('goods_attr') .
            " WHERE goods_id " . db_create_in($goods_id);
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if (!empty($row['attr_img']))
        {
            @unlink(ROOT_PATH . $row['attr_img']);
        }
    }

    /* 删除商品属性 */
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('goods_attr') . " WHERE goods_id " . db_create_in($goods_id);
    $GLOBALS['db']->query($sql);


    /* 删除商品相册的图片文件 */
    $sql = "SELECT album_img " .
            "FROM " . $GLOBALS['aos']->table('goods_album') .
            " WHERE goods_id " . db_create_in($goods_id);
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if (!empty($row['album_img']))
        {
            @unlink(ROOT_PATH . $row['album_img']);
        }
    }
    /* 删除商品详情的图片文件 */


    /* 删除商品相册 */
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('goods_album') . " WHERE goods_id " . db_create_in($goods_id);
    $GLOBALS['db']->query($sql);

    /* 删除相关表记录 */
    $sql = "DELETE FROM " . $GLOBALS['aos']->table('collect') . " WHERE goods_id " . db_create_in($goods_id);
    $GLOBALS['db']->query($sql);


    $sql = "DELETE FROM " . $GLOBALS['aos']->table('comment') . " WHERE id_value " . db_create_in($goods_id);
    $GLOBALS['db']->query($sql);

    /* 清除缓存 */
    clear_cache_files();
}

/**
 * 为某商品生成唯一的货号
 * @param   int     $goods_id   商品编号
 * @return  string  唯一的货号
 */
function generate_goods_sn($goods_id)
{
    $goods_sn = $GLOBALS['_CFG']['sn_prefix'] . str_repeat('0', 6 - strlen($goods_id)) . $goods_id;

    $sql = "SELECT goods_sn FROM " . $GLOBALS['aos']->table('goods') .
            " WHERE goods_sn LIKE '" . mysql_like_quote($goods_sn) . "%' AND goods_id <> '$goods_id' " .
            " ORDER BY LENGTH(goods_sn) DESC";
    $sn_list = $GLOBALS['db']->getCol($sql);
    if (in_array($goods_sn, $sn_list))
    {
        $max = pow(10, strlen($sn_list[0]) - strlen($goods_sn) + 1) - 1;
        $new_sn = $goods_sn . mt_rand(0, $max);
        while (in_array($new_sn, $sn_list))
        {
            $new_sn = $goods_sn . mt_rand(0, $max);
        }
        $goods_sn = $new_sn;
    }

    return $goods_sn;
}

/**
 * 商品货号是否重复
 *
 * @param   string     $goods_sn        商品货号；请在传入本参数前对本参数进行SQl脚本过滤
 * @param   int        $goods_id        商品id；默认值为：0，没有商品id
 * @return  bool                        true，重复；false，不重复
 */
function check_goods_sn_exist($goods_sn, $goods_id = 0)
{
    $goods_sn = trim($goods_sn);
    $goods_id = intval($goods_id);
    if (strlen($goods_sn) == 0)
    {
        return true;    //重复
    }

    if (empty($goods_id))
    {
        $sql = "SELECT goods_id FROM " . $GLOBALS['aos']->table('goods') ."
                WHERE goods_sn = '$goods_sn'";
    }
    else
    {
        $sql = "SELECT goods_id FROM " . $GLOBALS['aos']->table('goods') ."
                WHERE goods_sn = '$goods_sn'
                AND goods_id <> '$goods_id'";
    }

    $res = $GLOBALS['db']->getOne($sql);

    if (empty($res))
    {
        return false;    //不重复
    }
    else
    {
        return true;    //重复
    }

}

/**
 * 取得通用属性和某分类的属性，以及某商品的属性值
 * @param   int     $cat_id     分类编号
 * @param   int     $goods_id   商品编号
 * @return  array   规格与属性列表
 */
function get_attr_list($goods_id = 0)
{
    // 查询属性值及商品的属性值
    $sql = "SELECT attr_id, attr_value, attr_price, attr_img,product_number,product_sn ".
            "FROM " .$GLOBALS['aos']->table('goods_attr').
            " WHERE goods_id = '$goods_id' ".
            "ORDER BY product_sn";

    $row = $GLOBALS['db']->GetAll($sql);

    return $row;
}

/**
 * 根据属性数组创建属性的表单   即将作废
 *
 * @access  public
 * @param   int     $cat_id     分类编号
 * @param   int     $goods_id   商品编号
 * @return  string
 */
function build_attr_html($goods_id = 0)
{
    $attr = get_attr_list($goods_id);
    //print_r($attr);
    $html = '<table id="attrTable">';

    foreach ($attr AS $key => $val)
    {
        $html .= "<tr>";
            
        $html .= "<td><input type='hidden' name='attr_id_list[]' value='$val[attr_id]' />";
        $html .= '<input name="attr_value_list[]" type="text" value="' .htmlspecialchars($val['attr_value']). '" /> ';

        $html .= ' <input type="text" name="attr_price_list[]" value="' . $val['attr_price'] . '" />';

            if($val['attr_img'])
            {
			    $html .= '<img src="../'.$val['attr_img'].'"><input type="file" id="attr_img_'.($key+1).'" name="attr_img_list[]" />';
            }
            else
            {
                $html .= '<input type="file" id="attr_img_'.($key+1).'" name="attr_img_list[]" />';
            }

        
        $html .= '</td></tr>';
    }
    $html .= '</table>';
    return $html;
}

/**
 * 获得商品列表
 *
 * @access  public
 * @params  integer $isdelete
 * @params  integer $conditions
 * @return  array
 */
function goods_list($is_delete, $conditions = '')
{
    /* 过滤条件 */
    $param_str = '-' . $is_delete;
	
    $result = get_filter($param_str);
    if ($result === false)
    {
        $day = getdate();
        $today = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

        $filter['cat_id']           = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
        $filter['intro_type']       = empty($_REQUEST['intro_type']) ? '' : trim($_REQUEST['intro_type']);
        $filter['stock_warning']    = empty($_REQUEST['stock_warning']) ? 0 : intval($_REQUEST['stock_warning']);
        $filter['goods_name']          = empty($_REQUEST['search_goods_name']) ? '' : trim($_REQUEST['search_goods_name']);
        $filter['goods_sn']          = empty($_REQUEST['search_commonid']) ? '' : trim($_REQUEST['search_commonid']);
        $filter['is_on_sale'] = isset($_REQUEST['is_on_sale']) ? ((empty($_REQUEST['is_on_sale']) && $_REQUEST['is_on_sale'] === 0) ? '' : trim($_REQUEST['is_on_sale'])) : '';
        
        $filter['sort_by']          = empty($_REQUEST['sort_by']) ? 'goods_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order']       = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
        $filter['is_delete']        = $is_delete;

        $where = $filter['cat_id'] > 0 ? " AND " . get_children($filter['cat_id']) : '';

        /* 推荐类型 */
        switch ($filter['intro_type'])
        {
            case 'is_best':
                $where .= " AND is_best=1";
                break;
            case 'is_hot':
                $where .= ' AND is_hot=1';
                break;
            case 'is_new':
                $where .= ' AND is_new=1';
                break;
            case 'all_type';
                $where .= " AND (is_best=1 OR is_hot=1 OR is_new=1)";
        }

        /* 库存警告 */
        if ($filter['stock_warning'])
        {
            $where .= ' AND goods_number <= warn_number ';
        }

        /* 关键字 */
        if (!empty($filter['goods_name']))
        {
            $where .= " AND   goods_name LIKE '%" . mysql_like_quote($filter['goods_name']) . "%' ";
        }
        //货号
        if (!empty($filter['goods_sn']))
        {
            $where .= " AND  goods_sn LIKE '%" . mysql_like_quote($filter['goods_sn']) . "%' ";
        }

        /* 上架 */
        if ($filter['is_on_sale'] !== '')
        {
            $where .= " AND (is_on_sale = '" . $filter['is_on_sale'] . "')";
        }

        $where .= $conditions;

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('goods'). " AS g WHERE is_delete='$is_delete' $where";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        /* 分页大小 */
        $filter = page_and_size($filter);

        $sql = "SELECT goods_id, goods_name, goods_img, goods_sn, virtual_sales, shop_price, is_on_sale, is_best, is_new, is_hot, sort_order, goods_number " .
                    " FROM " . $GLOBALS['aos']->table('goods') . " AS g WHERE is_delete='$is_delete' $where" .
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


/**
 * 获得商品的货品总库存
 *
 * @access      public
 * @params      integer     $goods_id       商品id
 * @params      string      $conditions     sql条件，AND语句开头
 * @return      string number
 */
function product_number_count($goods_id, $conditions = '')
{
    if (empty($goods_id))
    {
        return -1;  //$goods_id不能为空
    }

    $sql = "SELECT SUM(product_number)
            FROM " . $GLOBALS['aos']->table('goods_attr') . "
            WHERE goods_id = '$goods_id'
            " . $conditions;
    $nums = $GLOBALS['db']->getOne($sql);
    $nums = empty($nums) ? 0 : $nums;

    return $nums;
}


/**
 * 格式化商品图片名称（按目录存储）
 *
 */
function reformat_image_name($goods_id, $goods_img, $position='')
{
    $rand_name = gmtime() . sprintf("%03d", mt_rand(1,999));
	$img_name = $goods_id . '_' .$rand_name;
    $img_ext = substr($goods_img, strrpos($goods_img, '.'));
    $dir = 'uploads';
    if (defined('IMAGE_DIR'))
    {
        $dir = IMAGE_DIR;
    }
    if (!make_dir(ROOT_PATH.$dir))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/goods_img'))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/tuan_img'))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/album_img'))
    {
        return false;
    }
	if (!make_dir(ROOT_PATH.$dir.'/attr_img'))
    {
        return false;
    }
    if (!make_dir(ROOT_PATH.$dir.'/exc_img'))
    {
        return false;
    }
    
	if (move_image_file(ROOT_PATH.$goods_img, ROOT_PATH.$dir.'/'.$position.'_img/'.$img_name.$img_ext))
    {
        return $dir.'/'.$position.'_img/'.$img_name.$img_ext;
    }
    return false;
}

function move_image_file($source, $dest)
{
    if (@copy($source, $dest))
    {
        @unlink($source);
        return true;
    }
    return false;
}

?>