<?php

/*公用函数库*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/**
 * 创建像这样的查询: "IN('a','b')";
 *
 * @access   public
 * @param    mix      $item_list      列表数组或字符串
 * @param    string   $field_name     字段名称
 *
 * @return   void
 */
function db_create_in($item_list, $field_name = '')
{
    if (empty($item_list))
    {
        return $field_name . " IN ('') ";
    }
    else
    {
        if (!is_array($item_list))
        {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item)
        {
            if ($item !== '')
            {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp))
        {
            return $field_name . " IN ('') ";
        }
        else
        {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 检查是否为一个合法的时间格式
 *
 * @access  public
 * @param   string  $time
 * @return  void
 */
function is_time($time)
{
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}

/**
 * 创建地区的返回信息
 *
 * @access  public
 * @param   array   $arr    地区数组 *
 * @return  void
 */
function region_result($parent, $sel_name, $type)
{
    global $cp;

    $arr = get_regions($type, $parent);
    foreach ($arr AS $v)
    {
        $region      =& $cp->add_node('region');
        $region_id   =& $region->add_node('id');
        $region_name =& $region->add_node('name');

        $region_id->set_data($v['region_id']);
        $region_name->set_data($v['region_name']);
    }
    $select_obj =& $cp->add_node('select');
    $select_obj->set_data($sel_name);
}

/**
 * 获得所有省份
 *
 * @access      public
 * @param       int     country    国家的编号
 * @return      array
 */
function get_regions($type = 0, $parent = 0)
{
    $sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['aos']->table('region') .
            " WHERE region_type = '$type' AND parent_id = '$parent'";

    return $GLOBALS['db']->GetAll($sql);
}

function get_region_id($region_name)
{
    $sql = 'SELECT region_id FROM ' . $GLOBALS['aos']->table('region') .
            " WHERE region_name = '$region_name'";

    return $GLOBALS['db']->GetOne($sql);
}

function get_region_name($region_id)
{
    $sql = 'SELECT region_name FROM ' . $GLOBALS['aos']->table('region') .
            " WHERE region_id = '$region_id'";

    return $GLOBALS['db']->GetOne($sql);
}

/**
 * 获得配送区域中指定的配送方式的配送费用的计算参数
 *
 * @access  public
 * @param   int     $area_id        配送区域ID
 *
 * @return array;
 */
function get_shipping_config($area_id)
{
    /* 获得配置信息 */
    $sql = 'SELECT configure FROM ' . $GLOBALS['aos']->table('shipping_area') . " WHERE shipping_area_id = '$area_id'";
    $cfg = $GLOBALS['db']->GetOne($sql);

    if ($cfg)
    {
        /* 拆分成配置信息的数组 */
        $arr = unserialize($cfg);
    }
    else
    {
        $arr = array();
    }

    return $arr;
}

/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
 * @return  mix
 */
function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true)
{
    $sql = "SELECT c.cat_id, c.cat_name, c.parent_id, c.is_show, c.sort_order, COUNT(s.cat_id) AS has_children ".
        'FROM ' . $GLOBALS['aos']->table('category') . " AS c ".
        "LEFT JOIN " . $GLOBALS['aos']->table('category') . " AS s ON s.parent_id=c.cat_id ".
        "GROUP BY c.cat_id ".
        'ORDER BY c.parent_id, c.sort_order ASC';
    $res = $GLOBALS['db']->getAll($sql);

    if (empty($res) == true)
    {
        return $re_type ? '' : array();
    }

    $options = cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

    $children_level = 3; //大于这个分类的将被删除
    if ($is_show_all == false)
    {
        foreach ($options as $key => $val)
        {
            if ($val['level'] > $children_level)
            {
                unset($options[$key]);
            }
            else
            {
                if ($val['is_show'] == 0)
                {
                    unset($options[$key]);
                    if ($children_level > $val['level'])
                    {
                        $children_level = $val['level']; //标记一下，这样子分类也能删除
                    }
                }
                else
                {
                    $children_level = 3; //恢复初始值
                }
            }
        }
    }

    /* 截取到指定的缩减级别 */
    if ($level > 0)
    {
        if ($cat_id == 0)
        {
            $end_level = $level;
        }
        else
        {
            $first_item = reset($options); // 获取第一个元素
            $end_level  = $first_item['level'] + $level;
        }

        /* 保留level小于end_level的部分 */
        foreach ($options AS $key => $val)
        {
            if ($val['level'] >= $end_level)
            {
                unset($options[$key]);
            }
        }
    }

    if ($re_type == true)
    {
        $select = '';
        foreach ($options AS $var)
        {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0)
            {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
        }

        return $select;
    }
    else
    {
        foreach ($options AS $key => $value)
        {
            $options[$key]['url'] = 'index.php?c=category&id='.$value['cat_id'];
        }

        return $options;
    }
}

/**
 * 过滤和排序所有分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $cat_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function cat_options($spec_cat_id, $arr)
{
    static $cat_options = array();

    if (isset($cat_options[$spec_cat_id]))
    {
        return $cat_options[$spec_cat_id];
    }

    if (!isset($cat_options[0]))
    {
        $level = $last_cat_id = 0;
        $options = $cat_id_array = $level_array = array();
        while (!empty($arr))
        {
            foreach ($arr AS $key => $value)
            {
                $cat_id = $value['cat_id'];
                if ($level == 0 && $last_cat_id == 0)
                {
                    if ($value['parent_id'] > 0)
                    {
                        break;
                    }

                    $options[$cat_id]          = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id']    = $cat_id;
                    $options[$cat_id]['name']  = $value['cat_name'];
                    unset($arr[$key]);

                    if ($value['has_children'] == 0)
                    {
                        continue;
                    }
                    $last_cat_id  = $cat_id;
                    $cat_id_array = array($cat_id);
                    $level_array[$last_cat_id] = ++$level;
                    continue;
                }

                if ($value['parent_id'] == $last_cat_id)
                {
                    $options[$cat_id]          = $value;
                    $options[$cat_id]['level'] = $level;
                    $options[$cat_id]['id']    = $cat_id;
                    $options[$cat_id]['name']  = $value['cat_name'];
                    unset($arr[$key]);

                    if ($value['has_children'] > 0)
                    {
                        if (end($cat_id_array) != $last_cat_id)
                        {
                            $cat_id_array[] = $last_cat_id;
                        }
                        $last_cat_id    = $cat_id;
                        $cat_id_array[] = $cat_id;
                        $level_array[$last_cat_id] = ++$level;
                    }
                }
                elseif ($value['parent_id'] > $last_cat_id)
                {
                    break;
                }
            }

            $count = count($cat_id_array);
            if ($count > 1)
            {
                $last_cat_id = array_pop($cat_id_array);
            }
            elseif ($count == 1)
            {
                if ($last_cat_id != end($cat_id_array))
                {
                    $last_cat_id = end($cat_id_array);
                }
                else
                {
                    $level = 0;
                    $last_cat_id = 0;
                    $cat_id_array = array();
                    continue;
                }
            }

            if ($last_cat_id && isset($level_array[$last_cat_id]))
            {
                $level = $level_array[$last_cat_id];
            }
            else
            {
                $level = 0;
            }
        }
        $cat_options[0] = $options;
    }
    else
    {
        $options = $cat_options[0];
    }

    if (!$spec_cat_id)
    {
        return $options;
    }
    else
    {
        if (empty($options[$spec_cat_id]))
        {
            return array();
        }

        $spec_cat_id_level = $options[$spec_cat_id]['level'];

        foreach ($options AS $key => $value)
        {
            if ($key != $spec_cat_id)
            {
                unset($options[$key]);
            }
            else
            {
                break;
            }
        }

        $spec_cat_id_array = array();
        foreach ($options AS $key => $value)
        {
            if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                ($spec_cat_id_level > $value['level']))
            {
                break;
            }
            else
            {
                $spec_cat_id_array[$key] = $value;
            }
        }
        $cat_options[$spec_cat_id] = $spec_cat_id_array;

        return $spec_cat_id_array;
    }
}

/**
 * 载入配置信息
 *
 * @access  public
 * @return  array
 */
function load_config()
{
    $arr = array();

    $data = read_static_cache('shop_config');
    if ($data === false)
    {
        $sql = 'SELECT code, value FROM ' . $GLOBALS['aos']->table('shop_config') . ' WHERE parent_id > 0';
        $res = $GLOBALS['db']->getAll($sql);
        foreach ($res AS $row)
        {
            $arr[$row['code']] = $row['value'];
        }

        /* 对数值型设置处理 */

        $arr['cache_time']           = intval($arr['cache_time']);

        $arr['comments_number']      = intval($arr['comments_number']) > 0 ? intval($arr['comments_number']) : 5;
        $arr['page_size']            = intval($arr['page_size'])       > 0 ? intval($arr['page_size'])       : 10;
        $arr['default_storage']      = isset($arr['default_storage']) ? intval($arr['default_storage']) : 1;
        $arr['min_goods_amount']     = isset($arr['min_goods_amount']) ? floatval($arr['min_goods_amount']) : 0;

        if (!isset($GLOBALS['_CFG']['aos_version']))
        {
            /* 如果没有版本号则默认为1.0.0 */
            $GLOBALS['_CFG']['aos_version'] = 'v1.0.0';
        }
        write_static_cache('shop_config', $arr);
    }
    else
    {

        $arr = $data;
    }

    return $arr;
}

/**
 * 获得指定分类下所有底层分类的ID
 *
 * @access  public
 * @param   integer     $cat        指定的分类ID
 * @return  string
 */
function get_children($cat = 0)
{
    return 'g.cat_id ' . db_create_in(array_unique(array_merge(array($cat), array_keys(cat_list($cat, 0, false)))));
}

/**
 * 获得指定文章分类下所有底层分类的ID
 *
 * @access  public
 * @param   integer     $cat        指定的分类ID
 *
 * @return void
 */
function get_article_children ($cat = 0)
{
    return db_create_in(array_unique(array_merge(array($cat), array_keys(article_cat_list($cat, 0, false)))), 'cat_id');
}

/**
 * 记录订单操作记录
 *
 * @access  public
 * @param   string  $order_sn           订单编号
 * @param   integer $order_status       订单状态
 * @param   integer $shipping_status    配送状态
 * @param   integer $pay_status         付款状态
 * @param   string  $note               备注
 * @param   string  $username           用户名，用户自己的操作则为 buyer
 * @return  void
 */
function order_action($order_sn, $order_status, $shipping_status, $pay_status, $note = '', $username = null, $place = 0)
{
    if (is_null($username))
    {
        $username = $_SESSION['admin_name'];
    }

    $sql = 'INSERT INTO ' . $GLOBALS['aos']->table('order_action') .
                ' (order_id, action_user, order_status, shipping_status, pay_status, action_place, action_note, log_time) ' .
            'SELECT ' .
                "order_id, '$username', '$order_status', '$shipping_status', '$pay_status', '$place', '$note', '" .gmtime() . "' " .
            'FROM ' . $GLOBALS['aos']->table('order_info') . " WHERE order_sn = '$order_sn'";
    $GLOBALS['db']->query($sql);
}

/**
 * 格式化商品价格
 *
 * @access  public
 * @param   float   $price  商品价格
 * @return  string
 */
function price_format($price, $change_price = true)
{
    if($price==='')
    {
     $price=0;
    }
    if ($change_price && defined('AOS_ADMIN') === false)
    {
        switch ($GLOBALS['_CFG']['price_format'])
        {
            case 0:
                $price = number_format($price, 2, '.', '');
                break;
            case 1: // 保留不为 0 的尾数
                $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));

                if (substr($price, -1) == '.')
                {
                    $price = substr($price, 0, -1);
                }
                break;
            case 2: // 不四舍五入，保留1位
                $price = substr(number_format($price, 2, '.', ''), 0, -1);
                break;
            case 3: // 直接取整
                $price = intval($price);
                break;
            case 4: // 四舍五入，保留 1 位
                $price = number_format($price, 1, '.', '');
                break;
            case 5: // 先四舍五入，不保留小数
                $price = round($price);
                break;
        }
    }
    else
    {
        $price = number_format($price, 2, '.', '');
    }

    return sprintf($GLOBALS['_CFG']['currency_format'], $price);
}

/**
 *  清除指定后缀的模板缓存或编译文件
 *
 * @access  public
 * @param  bool       $is_cache  是否清除缓存还是清出编译文件
 * @param  string     $ext       需要删除的文件名，不包含后缀
 *
 * @return int        返回清除的文件个数
 */
function clear_tpl_files($is_cache = true, $ext = '')
{
    $dirs = array();
	

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)
    {
        $tmp_dir = DATA_DIR ;
    }
    else
    {
        $tmp_dir = 'temp';
    }
    if ($is_cache)
    {
		$cache_dir = ROOT_PATH . $tmp_dir . '/caches/';
        $dirs[] = ROOT_PATH . $tmp_dir . '/query_caches/';
        $dirs[] = ROOT_PATH . $tmp_dir . '/static_caches/';
        for($i = 0; $i < 16; $i++)
        {
            $hash_dir = $cache_dir . dechex($i);
            $dirs[] = $hash_dir . '/';
        }
    }
    else
    {
		$dirs[] = ROOT_PATH . $tmp_dir . '/compiled/';
        $dirs[] = ROOT_PATH . $tmp_dir . '/compiled/admin/';
    }

    $str_len = strlen($ext);
    $count   = 0;

    foreach ($dirs AS $dir)
    {
        $folder = @opendir($dir);

        if ($folder === false)
        {
            continue;
        }

        while ($file = readdir($folder))
        {
            if ($file == '.' || $file == '..' || $file == 'index.htm' || $file == 'index.html')
            {
                continue;
            }
            if (is_file($dir . $file))
            {
                /* 如果有文件名则判断是否匹配 */
                $pos = ($is_cache) ? strrpos($file, '_') : strrpos($file, '.');

                if ($str_len > 0 && $pos !== false)
                {
                    $ext_str = substr($file, 0, $pos);

                    if ($ext_str == $ext)
                    {
                        if (@unlink($dir . $file))
                        {
                            $count++;
                        }
                    }
                }
                else
                {
                    if (@unlink($dir . $file))
                    {
                        $count++;
                    }
                }
            }
        }
        closedir($folder);
    }
    return $count;
}

/**
 * 清除模版编译文件
 *
 * @access  public
 * @param   mix     $ext    模版文件名， 不包含后缀
 * @return  void
 */
function clear_compiled_files($ext = '')
{
    return clear_tpl_files(false, $ext);
}

/**
 * 清除缓存文件
 *
 * @access  public
 * @param   mix     $ext    模版文件名， 不包含后缀
 * @return  void
 */
function clear_cache_files($ext = '')
{
    return clear_tpl_files(true, $ext);
}

/**
 * 清除模版编译和缓存文件
 *
 * @access  public
 * @param   mix     $ext    模版文件名后缀
 * @return  void
 */
function clear_all_files($ext = '')
{
    return clear_tpl_files(false, $ext) + clear_tpl_files(true,  $ext);
}

/**
 * 页面上调用的js文件
 *
 * @access  public
 * @param   string      $files
 * @return  void
 */
function smarty_insert_scripts($args)
{
    static $scripts = array();

    $arr = explode(',', str_replace(' ','',$args['files']));

    $str = '';
    foreach ($arr AS $val)
    {
        if (in_array($val, $scripts) == false)
        {
            $scripts[] = $val;
            if ($val{0} == '.')
            {
                $str .= '<script type="text/javascript" src="' . $val . '"></script>';
            }
            else
            {
                $str .= '<script type="text/javascript" src="js/' . $val . '"></script>';
            }
        }
    }

    return $str;
}

/**
 * 创建分页的列表
 *
 * @access  public
 * @param   integer $count
 * @return  string
 */
function smarty_create_pages($params)
{
    extract($params);

    $str = '';
    $len = 10;

    if (empty($page))
    {
        $page = 1;
    }

    if (!empty($count))
    {
        $step = 1;
        $str .= "<option value='1'>1</option>";

        for ($i = 2; $i < $count; $i += $step)
        {
            $step = ($i >= $page + $len - 1 || $i <= $page - $len + 1) ? $len : 1;
            $str .= "<option value='$i'";
            $str .= $page == $i ? " selected='true'" : '';
            $str .= ">$i</option>";
        }

        if ($count > 1)
        {
            $str .= "<option value='$count'";
            $str .= $page == $count ? " selected='true'" : '';
            $str .= ">$count</option>";
        }
    }

    return $str;
}

/**
 * 格式化重量：小于1千克用克表示，否则用千克表示
 * @param   float   $weight     重量
 * @return  string  格式化后的重量
 */
function formated_weight($weight)
{
    $weight = round(floatval($weight), 3);
    if ($weight > 0)
    {
        if ($weight < 1)
        {
            /* 小于1千克，用克表示 */
            return intval($weight * 1000) . '克';
        }
        else
        {
            /* 大于1千克，用千克表示 */
            return $weight . '千克';
        }
    }
    else
    {
        return 0;
    }
}

/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   float   $user_money     可用余额变动
 * @param   float   $frozen_money   冻结余额变动
 * @param   int     $rank_points    等级积分变动
 * @param   int     $pay_points     消费积分变动
 * @param   string  $change_desc    变动说明
 * @param   int     $change_type    变动类型：参见常量文件
 * @return  void
 */
function log_account_change($user_id, $user_money = 0, $frozen_money = 0, $rank_points = 0, $pay_points = 0, $change_desc = '', $change_type = 99, $dist_money = 0)
{
    /* 插入帐户变动记录 */
    $account_log = array(
        'user_id'       => $user_id,
        'user_money'    => $user_money,
        'frozen_money'  => $frozen_money,
        'rank_points'   => $rank_points,
        'pay_points'    => $pay_points,
        'dist_money'    => $dist_money,
        'change_time'   => gmtime(),
        'change_desc'   => $change_desc,
        'change_type'   => $change_type
    );
    $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('account_log'), $account_log, 'INSERT');

    /* 更新用户信息 */
    $sql = "UPDATE " . $GLOBALS['aos']->table('users') .
            " SET user_money = user_money + ('$user_money')," .
            " frozen_money = frozen_money + ('$frozen_money')," .
            " rank_points = rank_points + ('$rank_points')," .
            " dist_money = dist_money + ('$dist_money')," .
            " pay_points = pay_points + ('$pay_points')" .
            " WHERE user_id = '$user_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
}


/**
 * 重新获得商品图片与商品相册的地址
 */
function get_image_path($goods_id, $image='')
{
	$http = (!empty($image) ? substr($image, 0, 4) : "");
	if ($http != "http") {
		$image = $GLOBALS["_CFG"]["site_domain"] . $image;
	}
    $url = empty($image) ? $GLOBALS['_CFG']['no_picture'] : $image;
    return $url;
}


/**
 * 取得商品优惠价格列表
 *
 * @param   string  $goods_id    商品编号
 *
 * @return  优惠价格列表
 */
function get_tuan_price_list($goods_id)
{
    $tuan_price = array();
    $temp_index   = '0';

    $sql = "SELECT `tuan_number` , `tuan_price`".
           " FROM " .$GLOBALS['aos']->table('tuan_price'). "".
           " WHERE `goods_id` = '" . $goods_id . "'".
           " ORDER BY `tuan_number`";

    $res = $GLOBALS['db']->getAll($sql);

    foreach ($res as $k => $v)
    {
        $tuan_price[$temp_index]                 = array();
        $tuan_price[$temp_index]['number']       = $v['tuan_number'];
        $tuan_price[$temp_index]['price']        = $v['tuan_price'];
        $tuan_price[$temp_index]['format_price'] = price_format($v['tuan_price']);
        $temp_index ++;
    }
    return $tuan_price;
}

/**
 * 取得商品最终使用价格
 *
 * @param   string  $goods_id      商品编号
 * @param   string  $goods_num     购买数量
 * @param   boolean $is_sku_price 是否加入规格价格
 * @param   mix     $sku          规格ID的数组或者逗号分隔的字符串
 *
 * @return  商品最终购买价格
 */
function get_final_price($goods_id, $goods_num = '1', $is_sku_price = false, $sku = 0, $rec_type = 0)
{
	$final_price   = '0'; //商品最终购买价格
	$tuan_price  = '0'; //商品优惠价格
    $miao_price  = '0'; //商品优惠价格
    $lottery_price  = '0'; //商品优惠价格
	$user_price    = '0'; //商品会员价格
	
	/* 取得商品信息 */
	$sql = "SELECT shop_price ".
		" FROM " .$GLOBALS['aos']->table('goods').
		" WHERE goods_id = '" . $goods_id . "'" .
		" AND is_delete = 0";
	$shop_price = $GLOBALS['db']->getOne($sql);
	

		
	if($rec_type == 0)
	{
		//取得商品优惠价格列表
		$price_list   = get_tuan_price_list($goods_id);
		if (!empty($price_list))
		{
			foreach ($price_list as $value)
			{
				if ($goods_num >= $value['number'])
				{
					$tuan_price = $value['price'];
				}
			}
		}
		if (!empty($tuan_price))
		{
			$final_price = $tuan_price;
		}
		else
		{
			$final_price = $shop_price;
		}	
	}
	elseif($rec_type == 1)
	{
		//取得商品优惠价格列表
        $tuan_price = array_column(get_tuan_price_list($goods_id),'price');
		$final_price = max($tuan_price);
	}
    elseif($rec_type == 2)
    {
        //取得商品优惠价格列表
        $miao_price = get_seck_price($goods_id);
        $final_price = $miao_price;
    }
    elseif($rec_type == 3)
    {
        //取得商品优惠价格列表
        $lottery_price = get_lottery_price($goods_id);
        $final_price = $lottery_price;
        $is_sku_price=false;
    }
    //如果需要加入规格价格
    if ($is_sku_price)
    {
        if ($sku)
        {
            $sku_price   = sku_price($sku);
            $final_price += $sku_price;
        }
    }

    //返回商品最终购买价格
    return $final_price;
}


/**
 *
 * 是否存在规格
 *
 * @access      public
 * @param       array       $goods_attr_id_array        一维数组
 *
 * @return      string
 */
function is_sku($attr_id )
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('goods_attr'). " 
            WHERE attr_id = $attr_id";
    $row = $GLOBALS['db']->GetOne($sql);
    if($row)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function is_attr($goods_id )
{
    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('goods_attr'). " 
            WHERE goods_id = $goods_id";
    $row = $GLOBALS['db']->GetOne($sql);
    if($row)
    {
        return 1;
    }
    else
    {
        return 0;
    }
}


/**
 * 调用array_combine函数
 *
 * @param   array  $keys
 * @param   array  $values
 *
 * @return  $combined
 */
if (!function_exists('array_combine')) {
    function array_combine($keys, $values)
    {
        if (!is_array($keys)) {
            user_error('array_combine() expects parameter 1 to be array, ' .
                gettype($keys) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_array($values)) {
            user_error('array_combine() expects parameter 2 to be array, ' .
                gettype($values) . ' given', E_USER_WARNING);
            return;
        }

        $key_count = count($keys);
        $value_count = count($values);
        if ($key_count !== $value_count) {
            user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
            return false;
        }

        if ($key_count === 0 || $value_count === 0) {
            user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
            return false;
        }

        $keys    = array_values($keys);
        $values  = array_values($values);

        $combined = array();
        for ($i = 0; $i < $key_count; $i++) {
            $combined[$keys[$i]] = $values[$i];
        }

        return $combined;
    }
}


function get_table_date($table = "", $where = 1, $date = array(), $sqlType = 0)
{
	$date = implode(",", $date);

	if (!empty($date)) {
		if ($sqlType != 1) {
			$where .= " LIMIT 1";
		}

		$sql = "SELECT " . $date . " FROM " . $GLOBALS["aos"]->table($table) . " WHERE " . $where;

		if ($sqlType == 1) {
			return $GLOBALS["db"]->getAll($sql);
		}
		else if ($sqlType == 2) {
			return $GLOBALS["db"]->getOne($sql);
		}
		else {
			return $GLOBALS["db"]->getRow($sql);
		}
	}
}
/*获取地区类型*/
function get_region_type($region_id)
{
    $sql = "SELECT region_type FROM ".$GLOBALS['aos']->table('region')." WHERE region_id = $region_id";
    return $GLOBALS['db']->getOne($sql);
}
/*获取团购人数*/
function get_tuan_number($goods_id)
{
    $sql = "SELECT tuan_number FROM " .$GLOBALS['aos']->table('tuan_price'). " WHERE goods_id = $goods_id ORDER BY tuan_number";
    return $GLOBALS['db']->getCol($sql);
}
/*获取参团的人*/
function get_tuan_mem($tuan_id)
{
    $sql = "SELECT u.user_id,u.nickname,u.user_id,o.pay_time FROM ".$GLOBALS['aos']->table('order_info')." as o left join ".$GLOBALS['aos']->table('users')." as u on o.user_id=u.user_id WHERE extension_id = ".$tuan_id." AND pay_status = 2 order by pay_time ";
    $tuan_mem = $GLOBALS['db']->getAll($sql);
    foreach ($tuan_mem AS $idx => $row)
    {   if($row['user_id']){

            $tuan_mem[$idx]['headimgurl']  = getAvatar($row['user_id']);
        }
    }
    return $tuan_mem;
}
/*获取参团人数*/
function get_tuan_mem_num($tuan_id)
{
    $sql = "SELECT COUNT(*) from ".$GLOBALS['aos']->table('order_info'). " WHERE extension_code = 'tuan' AND extension_id = ".$tuan_id." AND pay_status = 2 AND order_status = 1 order by order_id ";
    return $GLOBALS['db']->getOne($sql);
}

/*进行中的团*/
function assign_tuan_ing($goods_id, $user_id, $num)
{
    $now_time = gmtime();
    $sql="SELECT oi.pay_time,oi.extension_id,oi.tuan_num,oi.bonus_id,og.goods_id,g.goods_name,u.nickname,oi.user_id,u.city,og.goods_id from ".$GLOBALS['aos']->table("order_info")." as oi left join "
       .$GLOBALS['aos']->table("order_goods")." as og on og.order_id=oi.order_id left join "
       .$GLOBALS['aos']->table("goods")." as g on og.goods_id=g.goods_id left join "
       .$GLOBALS['aos']->table('users')." as u on oi.user_id=u.user_id WHERE oi.order_status = 1 AND oi.tuan_first=1 AND oi.extension_code='tuan' AND og.goods_id='$goods_id' AND oi.tuan_status = 1 AND oi.user_id != $user_id ORDER BY oi.order_id DESC LIMIT ".$num;
    $result=$GLOBALS['db']->getAll($sql);
    foreach ($result AS $idx => $row)
    {
        $arr[$idx]['tuan_id']  = $row['extension_id'];
        $arr[$idx]['nickname']  = $row['nickname'];
        
        $arr[$idx]['headimgurl']  = getAvatar($row['user_id']);
        $arr[$idx]['city']  = $row['city'];
        $arr[$idx]['countdown']  = local_date($GLOBALS['_CFG']['time_format'],($row['pay_time']+$GLOBALS['_CFG']['tuan_time']*3600));
        $arr[$idx]['difference']  = $row['tuan_num']-get_tuan_mem_num($row['extension_id']);
        if((($row['pay_time']+$GLOBALS['_CFG']['tuan_time']*3600) < $now_time) || !empty($row['bonus_id']))
        {
            unset($arr[$idx]);
        }
    }
    return $arr;
}

/*获取门店名称*/
function get_store_name($store_id)
{
    $sql = "SELECT store_name FROM ".$GLOBALS['aos']->table('store')." WHERE store_id = $store_id";
    return $GLOBALS['db']->getOne($sql);
}
//团失败退款,$price退差价
function refunds($order,$differ_price="",$admim_ref=""){
    require_once(ROOT_PATH .'source/library/order.php');
    //include_once(ROOT_PATH .'source/library/payment.php');
    global $wechat,$aos,$admin_wechat;
    if(empty($wechat)){
        $wechat=$admin_wechat;
    }
    if($order[pay_status]!=2){
        return false;
    }

    $pay_log = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['aos']->table('pay_log') . " WHERE order_id = ". $order['order_id']);

    $payment = payment_info($order['pay_id']);
    //微信
    if($payment['pay_code']=='wxpay'){
        if($order['money_paid']=='0.00'){
            return false;
        }
        include_once(ROOT_PATH .'source/library/' . $payment['pay_code'] . '/' . $payment['pay_code'] . '.class.php');

        if(!empty($differ_price)){
            //差价
            $arr=array();
            $arr['money_paid']  = $order['money_paid']-$differ_price;
            $arr['order_amount']= 0;

        }else{
            //团购失败退款呢
            $arr=array();
            $arr['tuan_status']  = 4;
            $arr['money_paid']  = 0;
            $arr['order_amount']= $order['money_paid'] + $order['order_amount'];
        }
        $paymentinfo = unserialize_config($payment['pay_config']);
        $order['appId'] = $paymentinfo['appId'];
        $order['partnerId'] = $paymentinfo['partnerId'];
        $order['partnerKey'] = $paymentinfo['partnerKey'];

        $order['order_sns'] = $order['order_sn'].'-'.$pay_log;

        $pay_obj    = new $payment['pay_code'];
        if(!empty($differ_price)){
            $code = $pay_obj->refund($order,$differ_price);
        }else{
            $code = $pay_obj->refund($order);
        }
        
        //print_r($code);
        if($code['return_code'] == 'FAIL'){
           return false;
           //return 'wei_false';
        }
        elseif($code['return_code'] == 'SUCCESS'){
            
            
            //前台退款
            if(empty($admim_ref)){
               update_order($order['order_id'], $arr);
               if(!empty($differ_price)){

                    order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '团购成功，退回微信(差价)', '');

                }else{

                    order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '团购失败，退回微信', '');
                    
                } 
            }elseif($admim_ref=="refund"){
                //后台
                update_order($order['order_id'], $arr);
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '后台退款，退回微信', '');
            }elseif($admim_ref=="lottery"){
                //后台
                $arr=array();
                $arr['is_luck']  = 3;
                $arr['money_paid']  = 0;
                $arr['order_amount']= $order['money_paid'] + $order['order_amount'];
                update_order($order['order_id'], $arr);
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '未中奖，退回微信', '');
            }
            if($admim_ref!="refund"){
                //发送模板消息
                $refund_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
                $wx_url=$aos->url()."index.php?c=user&a=order_detail&order_id=".$order['order_id'];

                if(!empty($differ_price)){
                    $refund_price="¥".$differ_price;
                }else{
                    $refund_price="¥".$order[money_paid];
                }
                $openid=getOpenid($order['user_id']);
                $message=getMessage(6);
                $wx_title = "退款成功通知";
                $wx_desc = $message[title]."\r\n退款商品：".$order[goods_name]."\r\n退款金额：".$refund_price."\r\n退款时间：".$refund_time."\r\n".$message[note];
                //$wx_pic = $aos_url;
                $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
            }
            
            
            if($admim_ref=="refund"){
                return 'wei_true';
            }else{
                return true;
            }
             
            //return 'wei_true';
            
        }
    //支付宝    
    }elseif($payment['pay_code']=="alipay"){
        
        return alipay_refund($order,$differ_price,$admim_ref);
        //return 'ali_true';
    //余额支付
    }elseif($payment['pay_code']=="balance"){
        
        return balance_refund($order,$differ_price,$admim_ref);
    }
    

}

function alipay_refund($order,$differ_price,$admim_ref){
    require_once(ROOT_PATH .'source/library/order.php');
    //前台退款
        if(empty($admim_ref)){
           
           if(empty($differ_price)){
                $arr=array();
                $arr['tuan_status']  = 3;
                update_order($order['order_id'], $arr);
                order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '团购失败，支付宝未退款', '');
                return true;
                
            }else{
               order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '团购成功，支付宝未退差价', ''); 

            } 
        }elseif($admim_ref=="refund"){
            //后台
            order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '后台退款，支付宝未退款', '');
            return 'ali_true';
        }elseif($admim_ref=="lottery"){
            //后台

            order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '未中奖，支付宝未退款', '');
        }
}
function balance_refund($order,$differ_price,$admim_ref){
    require_once(ROOT_PATH .'source/library/order.php');
    if($order['surplus']=='0.00'){
            return false;
        }
        $user_id = $order['user_id'];
        //前台退款
        if(empty($admim_ref)){
           //退款
            if(!empty($differ_price)){

                //退差价
                $user_money=$differ_price;
                $note=sprintf('支付退差价 %s', $order['order_sn']);
                $tuan_note='团购成功，退回余额(差价)';
                $arr=array();
                $arr['surplus']  = $order['surplus']-$differ_price;
                $arr['order_amount']= 0;
                
            }else{

                
                $user_money=$order['surplus'];
                $note='团购失败，退回余额';
                $tuan_note='团购失败，退回余额';
                $arr=array();
                $arr['tuan_status']  = 4;
                $arr['surplus']  = 0;
                $arr['order_amount']= $order['surplus'] + $order['order_amount'];
                
            } 

            
        }elseif($admim_ref=="refund"){
            //退款
            if(!empty($differ_price)){

                //退差价
                $user_money=$differ_price;
                $note=sprintf('后台退款 %s', $order['order_sn']);
                $tuan_note='后台退款，退回余额';
                $arr=array();
                $arr['surplus']  = $order['surplus']-$differ_price;
                $arr['order_amount']= 0;
            }else{
               $user_money=$order['surplus'];
               $note='后台退款，退回余额';
               $tuan_note='后台退款，退回余额';
               $arr=array();
               $arr['tuan_status']  = 4;
               $arr['surplus']  = 0;
               $arr['order_amount']= $order['surplus'] + $order['order_amount']; 
            }

        }elseif($admim_ref=="lottery"){
            //后台
            $user_money=$order['surplus'];
            $note='未中奖，退回余额';
            $tuan_note='未中奖，退回余额';
            $arr=array();
            $arr['is_luck']  = 3;
            $arr['surplus']  = 0;
            $arr['order_amount']= $order['surplus'] + $order['order_amount'];
              
        }
        log_account_change($user_id, $user_money, 0, 0, 0, $note);
        update_order($order['order_id'], $arr);
        order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], $tuan_note, '');
        return true;

}
//秒杀/抽奖成团
function cheng_tuan($extension_id,$api=0){
    require_once(ROOT_PATH .'source/library/order.php');
    global $wechat,$aos,$admin_wechat;
    if(empty($wechat)){
        $wechat=$admin_wechat;
    }
    $aaa = 0; 
    //团
    $sql="select o.extension_code,o.pay_id,o.user_id,o.order_id,o.order_status,o.shipping_status,o.pay_status,o.order_sn,o.surplus,o.money_paid,o.order_amount,o.tuan_num,o.act_id,o.bonus_id,o.integral,g.goods_id,g.goods_name from ".$GLOBALS['aos']->table('order_info')." as o left join ".$GLOBALS['aos']->table('order_goods')." as g on o.order_id = g.order_id where o.extension_id = ".$extension_id." and o.order_status in (0,1) and o.tuan_status in (0,1)";
    $miao=$GLOBALS['db']->getAll($sql);
    $bet_price='';
    //成团
    $extension_code=$miao[0]['extension_code'];
    if($extension_code=='tuan'){
        
        $sql="select tuan_number,tuan_price from ".$GLOBALS['aos']->table('tuan_price')." where goods_id = ".$miao[0]['goods_id']." order by tuan_number asc";
        $tuan_price = $GLOBALS['db']->getAll($sql);
        $new_tuan =$old_tuan = array();
        //比较阶梯团差价
        $goods_tuan_num=get_tuan_number($miao[0]['goods_id']);
        $sql="select count(order_id) from ".$GLOBALS['aos']->table('order_info')." where extension_id = ".$extension_id." and extension_code = 'tuan' and pay_status = 2 and order_status = 1";
        $tuan_num = $GLOBALS['db']->getOne($sql);
        $max_tuan=max($goods_tuan_num);
        foreach($tuan_price as $key=>$vo){
            $k=$key-1;
            if(min($goods_tuan_num) == $vo['tuan_number']){
                $old_tuan['tuan_number']=$vo['tuan_number'];
                $old_tuan['tuan_price']=$vo['tuan_price'];
            }
            if($tuan_num < $vo['tuan_number']){
                $new_tuan['tuan_number']=$tuan_price[$k]['tuan_number'];
                $new_tuan['tuan_price']=$tuan_price[$k]['tuan_price'];
                break;
            }
            if($tuan_num == $vo['tuan_number']){
                $new_tuan['tuan_number']=$vo['tuan_number'];
                $new_tuan['tuan_price']=$vo['tuan_price'];
                break;
            }
            if($tuan_num > $vo['tuan_number'] && $vo['tuan_number'] ==$max_tuan){
                $new_tuan['tuan_number']=$vo['tuan_number'];
                $new_tuan['tuan_price']=$vo['tuan_price'];
                break;
            }
            
        }
        $bet_price=$old_tuan['tuan_price']-$new_tuan['tuan_price'];
    }
    foreach($miao as $vo){
        if($vo['pay_status']!=2){
            return_user_surplus_integral_bonus($vo);
            $arr=array();
            $arr['tuan_status']  = 4;
            $arr['order_status']  = 3;
            update_order($vo['order_id'], $arr);
            if($extension_code=='miao'){
                order_action($vo['order_sn'], $arr['order_status'], $vo['shipping_status'], $vo['pay_status'], '秒杀成团未支付订单设无效', '');
            }elseif($extension_code=='lottery'){
                order_action($vo['order_sn'], $arr['order_status'], $vo['shipping_status'], $vo['pay_status'], '抽奖成团未支付订单设无效', '');
            }elseif($extension_code=='tuan'){
                order_action($vo['order_sn'], $arr['order_status'], $vo['shipping_status'], $vo['pay_status'], '阶梯成团未支付订单设无效', '');
            }
            
        }else{
            $arr=array();
            $arr['tuan_status']  = 2;
            $arr['suc_tuan_time']  = gmtime();
            if($api==1){
                $wx_url=substr($aos->url(), 0, -4);
                
            }else{
                $wx_url=$aos->url();
            }
            
            $wx_url.="index.php?c=share&tuan_id=".$extension_id;
            if($extension_code=='miao'){
                order_action($vo['order_sn'], $vo['order_status'], $vo['shipping_status'], $vo['pay_status'], '秒杀自动成团', '');
            }elseif($extension_code=='lottery'){
                order_action($vo['order_sn'], $vo['order_status'], $vo['shipping_status'], $vo['pay_status'], '抽奖自动成团', '');
            }elseif($extension_code=='tuan'){
                //退差价

                if($bet_price>0){
                    $r= refunds($vo,$bet_price);
                }
                $arr['tuan_num']  = $new_tuan['tuan_number'];
                $arr['suc_tuan_time']  = gmtime();
                order_action($vo['order_sn'], $vo['order_status'], $vo['shipping_status'], $vo['pay_status'], '自动成团', '');
                
            }
            update_order($vo['order_id'], $arr);  
            /* 如果使用库存，且成团时减库存，则减少库存 */
            if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == 2 && $extension_code != 'lottery'){
                change_order_goods_storage($vo['order_id'], true, 2);
            }  
            $aaa = 1;    
            //发送模板消息
            $openid=getOpenid($vo['user_id']);
            

            $message=getMessage(3);
            $wx_title = "组团成功通知";
            $wx_desc = $message[title]."\r\n订单编号".$vo[order_sn]."\r\n团购商品：".$vo['goods_name']."\r\n".$message[note];
            $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
        }
    }
    if($extension_code!='lottery'){
        if($aaa == 1){
            $sql="select openid from ".$GLOBALS['aos']->table('wxmanage')." where store_id = 0";
            $res=$GLOBALS['db']->getAll($sql);
            if(!empty($res)){
              foreach($res as $vo){
                    $openid=$vo['openid'];
                    if($api==1){
                        $wx_url=substr($aos->url(), 0, -4);
                        
                    }else{
                        $wx_url=$aos->url();
                    }
                    
                    //$wx_url.="index.php?c=share&tuan_id=".$extension_id;
                    $wx_title = "发货通知";
                    $wx_desc = "有一个团购已成团\r\n成团商品：".$miao[0]['goods_name']."\r\n请及时处理";
                    $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                }  
            }
            
        }
    }
    
    return true; 

}
//团长模板消息
/*function tuan_offered($order){
    require_once(ROOT_PATH .'source/library/order.php');
    include_once(ROOT_PATH .'source/class/wechat.class.php');
    $wxconfig = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_config') . " WHERE `id` = 1" );
    $wechat = new wechat($wxconfig);
    $aos = new AOS($db_name, $prefix);
    //发送模板消息
    
    $openid=getOpenid($order['user_id']);
    $message=getMessage(11);
    $url=$aos->url()."index.php?c=user&a=order_detail&order_id=".$order['order_id'];
    
    $template=array(  
        'touser'=>"$openid",  
        'template_id'=>"$message[code]",  
        'url'=>"$url",  
        'topcolor'=>"#7B68EE",  
        'data'=>array(  
            'first'=>array('value'=>"$message[first]",'color'=>"#FF0000"),
            'keyword1'=>array('value'=>"$order[goods_name]",'color'=>"#FF0000"),
            'keyword2'=>array('value'=>"$order[goods_price]",'color'=>"#FF0000"),
            'keyword3'=>array('value'=>"$order[tuan_time]",'color'=>"#FF0000"),
            'remark'=>array('value'=>"$message[remark]",'color'=>'#FF0000')  
        )  
    );
    $wechat->sendtemplate($template);
    
    return true; 

}*/

/*获取订单支付id*/
function get_pay_log_id($order_id)
{
    if (!empty($order_id))
    {
        $pay_log_id = $GLOBALS['db']->getOne("SELECT log_id FROM " . $GLOBALS['aos']->table('pay_log') . " WHERE order_id='" . $order_id . "'");
        return $pay_log_id;
    }
    else
    {
        return "";
    }
}

/*取得自提点列表*/
function get_store_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('store');
    $store_list = $GLOBALS['db']->getAll($sql);
    foreach($store_list as $idx=>$value)
    {
        $store_list[$idx]['province_name'] = get_region_name($value['province']);
        $store_list[$idx]['city_name']     = get_region_name($value['city']);
        $store_list[$idx]['district_name'] = get_region_name($value['district']);
    }
    return $store_list;
}
/*取得自提点*/
function get_store($store_id)
{
    if($store_id){
        $sql = "SELECT * FROM " . $GLOBALS['aos']->table('store') .
            " WHERE store_id = '$store_id'";
    }
    else
    {
        $sql = "SELECT * FROM " . $GLOBALS['aos']->table('store') .
            " ORDER BY store_id LIMIT 1";
    }
    return $GLOBALS['db']->getRow($sql);
}

/*获取模板消息*/
function get_wxmessage($id)
{
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('wxmessage') .
            " WHERE id = '$id'";
    return $GLOBALS['db']->getRow($sql);
}

/* 获得门店列表 */
function store_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('store') .
            "WHERE enabled = 1 ORDER BY store_id";
    $result = $GLOBALS['db']->getAll($sql);
    
    foreach ($result AS $idx => $row)
    {
        $store[$idx]['store_id']           = $row['store_id'];
        $store[$idx]['store_name']         = $row['store_name'];
        $store[$idx]['store_mobile']        = $row['store_mobile'];
        $store[$idx]['store_address']       = $row['store_address'];
    $lbs = new lbs;
        $store[$idx]['direction'] = $lbs->direction($_SESSION['coordinate'],$row['store_coordinate']);
        $store[$idx]['distance'] = distance($store[$idx]['direction']);
    }
    $store = array_sort($store,'direction');
  return $store;
}

/*获得商品ID获取秒杀价格*/
function get_seck_price($goods_id)
{
    $sql='SELECT seck_price FROM ' . $GLOBALS['aos']->table('seckill')." WHERE goods_id = $goods_id and seckill_id = '".$_SESSION['act_id']."' ";
    $result = $GLOBALS['db']->getOne($sql);
    return $result;
}
/*获得商品ID获取抽奖价格*/
function get_lottery_price($goods_id)
{
    $sql='SELECT lottery_price FROM ' . $GLOBALS['aos']->table('lottery')." WHERE goods_id = $goods_id and lottery_id = '".$_SESSION['act_id']."'";
    $result = $GLOBALS['db']->getOne($sql);
    return $result;
}


//获取openid
function getOpenid($user_id=''){
    if(empty($user_id)){

        $user_id=$_SESSION['user_id'];
    }
    $sql="select openid from ".$GLOBALS['aos']->table('users')." where user_id = $user_id";
    return $GLOBALS['db']->getOne($sql);
}

//获取用户图像
function getAvatar($user_id)
{
    global $wechat;
    $avatar_file = ROOT_PATH . 'uploads/avatar/avatar_'.$user_id.'.jpg';

    if(is_readable($avatar_file) == false) { 
        $sql='SELECT headimgurl FROM ' . $GLOBALS['aos']->table('users')." WHERE user_id = $user_id ";
        $headimgurl = $GLOBALS['db']->getOne($sql);
        $headimgurl = $wechat->http_get($headimgurl);
        @file_put_contents($avatar_file,$headimgurl);
    }
    return 'uploads/avatar/avatar_'.$user_id.'.jpg';
}

//获取商品佣金
function goods_comm($goods_id)
{
    $sql='SELECT is_dist,comm_shop_price,comm_tuan_price FROM ' . $GLOBALS['aos']->table('goods')." WHERE goods_id = $goods_id ";
    return $GLOBALS['db']->getRow($sql);
}


//获取模板信息
function getMessage($message_id=''){

    if(empty($message_id)){

        return false;
    }
    $sql="select title,note from ".$GLOBALS['aos']->table('wx_msg')." where id = $message_id";

    $message=$GLOBALS['db']->getRow($sql);

    return $message;

}

/**
 * 获得商品的规格
 *
 * @access  public
 * @param   integer $goods_id
 * @return  array
 */
function get_sku_list($goods_id,$attr='0')
{
    /* 获得商品的规格 */
    if($attr==1){

        $sql = "SELECT * FROM " . $GLOBALS['aos']->table('goods_attr') .
            "WHERE attr_id in ($goods_id) ORDER BY product_sn";
        $res = $GLOBALS['db']->getAll($sql);
    
    }else{
        $sql = "SELECT * FROM " . $GLOBALS['aos']->table('goods_attr') .
            "WHERE goods_id = '$goods_id' ORDER BY product_sn";
        $res = $GLOBALS['db']->getAll($sql);
    }
    
    return $res;
}


function get_sku_info($goods_id, $attr_id=0)
{
    $where = "goods_id = '$goods_id'";
    if($attr_id)
    {
        $where .= " AND attr_id = '$attr_id'";
    }

    /* 获得商品的规格 */
    $sql = "SELECT * FROM " . $GLOBALS['aos']->table('goods_attr') .
            "WHERE $where";
    $res = $GLOBALS['db']->getRow($sql);
    return $res;
}

function get_sku_name($attr_id)
{
    /* 获得商品的规格 */
    $sql = "SELECT attr_value FROM " . $GLOBALS['aos']->table('goods_attr') .
            "WHERE attr_id = '$attr_id'";
    $res = $GLOBALS['db']->getOne($sql);
    return $res;
}
function get_sku_num($goods_id)
{
    $sql = "SELECT product_number FROM " . $GLOBALS['aos']->table('goods_attr') .
            "WHERE goods_id = '$goods_id' ORDER BY product_number DESC";
    $res = $GLOBALS['db']->getOne($sql);
    return $res;
}
/*生成分享水印图*/
function get_share_img($goods_id)
{
    $path = ROOT_PATH.'uploads/share/share_'.$goods_id.'.jpg';
    if (is_readable($path) == false)
    {
        $sql = 'SELECT goods_img FROM ' . $GLOBALS['aos']->table('goods') ." WHERE goods_id = ".$goods_id;
        $goods_img = $GLOBALS['db']->GetOne($sql);

        $goods_img = ROOT_PATH . $goods_img;
        $titbg = ROOT_PATH . 'uploads/images/share_logo.png';
        $is_very = file_get_contents($goods_img);
        $is_tit = file_get_contents($titbg);
        if(strlen($is_very) < 1)
        {
          return false;  
        }
        if(strlen($is_tit) < 1)
        {
          return false;  
        }
        $img = imagecreatefromstring($is_very);
        $titimg = imagecreatefromstring($is_tit);
        $tit_width  = imagesx($titimg);
        $tit_height = imagesy($titimg);
        imagecopyresampled($img, $titimg, 0, 320, 0, 0, 400, 80, $tit_width, $tit_height);
        $share_img = imagejpeg($img,$path);
        //imagedestroy($share_img);
        return 'uploads/share/share_'.$goods_id.'.jpg';
    }
    else
    {
        return 'uploads/share/share_'.$goods_id.'.jpg';
    }
}
/*获取用户父级用户*/
function get_parent_user($user_id)
{
    $sql = "SELECT parent_id FROM " . $GLOBALS['aos']->table('users') .
            "WHERE user_id = '$user_id'";
    $res = $GLOBALS['db']->getOne($sql);
    return $res;
}
function get_express($shipping_code,$invoice_no)
{
    $host = "http://aliapi.kuaidi.com";
    $path = "/kuaidiinfo";
    $method = "GET";
    $appcode = $GLOBALS['_CFG']['express_key'];
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "nu=$invoice_no&com=$shipping_code&muti=0&order=desc";
    $bodys = "";
    $url = $host . $path . "?" . $querys;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    //var_dump(curl_exec($curl));
    $results = strstr(curl_exec($curl),'{');
    return json_decode($results, true);
}

 /*发送短消息*/
function sendsms($phones,$msg,$template)
{
    $accessKeyId = $GLOBALS['_CFG']['accessKeyId'];
    $accessKeySecret = $GLOBALS['_CFG']['accessKeySecret'];
    $params = array ();
    $params["PhoneNumbers"] = $phones;
    $params["SignName"] = $GLOBALS['_CFG']['sms_sign'];
    $params["TemplateCode"] = $template;
    $params['TemplateParam'] = Array (
        "msg" => $msg
    );

    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }
    $helper = new sms();
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ))
    );
    return object_array($content);
}

//PHP stdClass Object转array  
function object_array($array) {  
    if(is_object($array)) {  
        $array = (array)$array;  
     } if(is_array($array)) {  
         foreach($array as $key=>$value) {  
             $array[$key] = object_array($value);  
             }  
     }  
     return $array;  
}

function xia_dan_api($order_id)
{

    global $aos,$db;
    $sql="select o.*,g.goods_name,sum(g.goods_number) as count,g.goods_price,g.goods_name,g.goods_sn,g.goods_id from ".$aos->table('order_info')." as o left join ".$aos->table('order_goods')." as g on o.order_id = g.order_id where o.order_id = $order_id";
    $row=$db->getRow($sql);
    $time=local_date('Y-m-d',gmtime());
    //body
    $body=array(
        'otaOrderId'=>"2017112951554956",
        'vendorOrderId'=>"SK1711290018",
        'otaProductName'=>"$row[goods_name]",
        'price'=>"$row[goods_price]",
        'count'=>"$row[count]",
        'contactName'=>"$row[consignee]",
        'contactMobile'=>"$row[mobile]",
        'useDate'=>"$time"
    );
    
    //$url="http://demo.ptuan.com.cn/pt/index.php?c=auto";
    $str = api_http($body ,'NoticeOrderConsumed'); //传递参数
    var_dump($str);exit;
    if($str['header']['resultCode']=='0000'){
        $vendorOrderId=$str[body][vendorOrderId];
        $sql="update ".$aos->table('order_info')." set vendorOrderId = '$vendorOrderId' where order_id = $order_id";
        $db->query($sql);
    }

}

function api_http($body,$type){
    $x_body=array(
        'body'=>$body
    );
    $xml_body=arrayToXml($x_body);
    $data=base64_encode($xml_body);
    $time=local_date('Y-m-d H:i:s',gmtime());
    $sign=md5('lwp'.$type.$time.$data.'2.0dd38f48136fc472eb84486fa265627d8');

    $header1=array(
        'accountId'=>'lwp',
        'version'=>'2.0',
        'serviceName'=>"$type",
        'requestTime'=>"$time",
        'sign'=>"$sign"
    );
    $data=array(
        'request'=>array(
            'header'=>$header1,
            'body'=>$body
        )
    );

    $xmlData=arrayToXml($data);

    $url="http://liuwa.s1.ptuan.com.cn/api/lwp.php";


    $header[] = "Content-type: text/xml";

    //print_r($header);die;

    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $xmlData);
    $response = curl_exec($ch);
    var_dump($response);
    if(curl_errno($ch)){
        printcurl_error($ch);
    }
    curl_close($ch);
    //先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组。
    return $response = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

}
function arrayToXml($arr){ 
    foreach ($arr as $key=>$val){ 
        if(is_array($val)){ 
        $xml.="<".$key.">".arrayToXml($val)."</".$key.">"; 
        }else{ 
        $xml.="<".$key.">".$val."</".$key.">"; 
        } 
    } 
    return $xml; 
}

?>