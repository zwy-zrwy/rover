<?php
define('IN_AOS', true);


switch ($operation){
	case "app_config":
		admin_priv('appconfig');
		if($_POST){
			
			$appid = getstr($_POST ['appid']);
			$appsecret = getstr($_POST ['appsecret']);
			$followkey = getstr($_POST ['followkey']);
			$ret = $db->query ( "UPDATE " . $aos->table('app_config') . " SET `appid`='$appid',`appsecret`='$appsecret',`followkey`='$followkey' WHERE `id`=1;");
			$link [] = array ('href' => 'index.php?act=app&op=app_config','text' => '小程序设置');
			if ($ret) {
				sys_msg ( '设置成功', 0, $link );
			} else {
				sys_msg ( '设置失败，请重试', 0, $link );
			}
		}else{
			$ymd = date('Y-m-d');
			$ret = $db->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('app_config') . " WHERE `id` = 1" );
			$smarty->assign ('ret', $ret);
			$smarty->assign('shop_url', $aos->url());
			$smarty->display ( 'app/app_config.html' );
		}
	break;
	case "app_menu"://菜单页面
	admin_priv('appmenu');
		if($_POST){
		  if(isset($_POST['menu_id']))
		  {
		    $ids= count($_POST['menu_id']);
		  }
		  for($i=0; $i<$ids; $i++)
		  {
		    if(!empty($_POST[menu_id][$i])){
		      $sql = "UPDATE " . $aos->table('menu') . " SET menu_name = '".$_POST[menu_name][$i]."',menu_url = '".$_POST[menu_url][$i]."',sort_order = '".$_POST[sort_order][$i]."' WHERE menu_id = ".$_POST[menu_id][$i];
		      $db->query($sql);

		      $m_img = array('name' => $_FILES['menu_img']['name'][$i] , 'type' => $_FILES['menu_img']['type'][$i] , 'tmp_name' => $_FILES['menu_img']['tmp_name'][$i] , 'error' => $_FILES['menu_img']['error'][$i], 'size' => $_FILES['menu_img']['size'][$i]);


		      if (($m_img['tmp_name'] != '' && $m_img['tmp_name'] != 'none'))
		      {
		          if ($_POST[menu_id][$i] > 0)
		          {
		              /* 删除原来的图片文件 */
		              $sql = "SELECT menu_img FROM " . $aos->table('menu') .
		                      " WHERE menu_id = ".$_POST[menu_id][$i];
		              $row = $db->getRow($sql);
		              if ($row['menu_img'] != '' && is_file(ROOT_PATH . $row['menu_img']))
		              {
		                  @unlink(ROOT_PATH . $row['menu_img']);
		                  //oss_delete_file($row['goods_thumb']);
		              }
		          }
		          $menu_img   = $image->upload_image($m_img,'menu_img');
		          $sql = "UPDATE " . $aos->table('menu') . " SET menu_img = '".$menu_img."' WHERE menu_id = ".$_POST[menu_id][$i];
		          $db->query($sql);

		      }
		      
		    }elseif(!empty($_POST[menu_name][$i])){
		      $sql = "insert into " . $aos->table('menu') . " (menu_name,menu_url,sort_order,type)values('".$_POST[menu_name][$i]."','".$_POST[menu_url][$i]."','".$_POST[sort_order][$i]."',1)";
		      $db->query($sql);
		    }
		    
		  }
		  if($db)
		  {
		    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=app&op=app_menu');
		    sys_msg('修改成功', 0, $links);
		  }
		}else{
			$sql = "SELECT * FROM " . $GLOBALS['aos']->table('menu')." where type = 1";
    		$menu=$GLOBALS['db']->getAll($sql);
			
			$smarty->assign ( 'menu_list', $menu );
			$smarty->display ( 'app/app_menu.html' );
		}
	break;
	case 'toggle_enabled':
	{
	    $menu_id       = intval($_POST['id']);
	    $enabled        = intval($_POST['val']);
	    $sql = "UPDATE " . $aos->table('menu') . " SET enabled = '".$enabled."' WHERE menu_id = ".$menu_id;
	    $res=$db->query($sql);
	    if ($res)
	    {
	        clear_cache_files();
	        make_json_result($enabled);
	    }
	}
	break;
	case 'ad_enabled':
	{
	    $ad_id       = intval($_POST['id']);
	    $enabled        = intval($_POST['val']);
	    $sql = "UPDATE " . $aos->table('ad') . " SET enabled = '".$enabled."' WHERE ad_id = ".$ad_id;
		$res=$db->query($sql);
	    if ($res)
	    {
	        clear_cache_files();
	        make_json_result($enabled);
	    }
	}
	break;
	case "app_ad"://菜单页面
	admin_priv('appmenu');
	
		if($_POST){
		  
		  
		    $links[] = array('text' => '返回列表', 'href' => 'index.php?act=app&op=ads_list');
		    sys_msg('修改成功', 0, $links);
		  
		}else{
			$sql = "SELECT * FROM " . $aos->table('ad')." where position_id = 4";
    		$ads_list=$db->getAll($sql);
    		foreach($ads_list as $key=>$vo){
    			$ads_list[$key]['start_date']    = local_date($_CFG['date_format'], $vo['start_date']);
    			$ads_list[$key]['end_date']    = local_date($_CFG['date_format'], $vo['end_date']);
    		}
			$smarty->assign('ads_list',     $ads_list);
			$smarty->display ( 'app/ads_list.html' );
		}
	break;
	case "ad_add":
		$ad_link = empty($_GET['ad_link']) ? '' : trim($_GET['ad_link']);
	    $ad_name = empty($_GET['ad_name']) ? '' : trim($_GET['ad_name']);

	    $start_time = local_date('Y-m-d');
	    $end_time   = local_date('Y-m-d', gmtime() + 3600 * 24 * 30);  // 默认结束时间为1个月以后

	    $smarty->assign('ads',
	        array('ad_link' => $ad_link, 'ad_name' => $ad_name, 'start_time' => $start_time,
	            'end_time' => $end_time, 'enabled' => 1));

	    $smarty->assign('ur_here',       '添加广告');
	    $smarty->assign('action_link',   array('href' => 'index.php?act=app&op=app_ad', 'text' => '广告列表'));

	    $smarty->assign('form_act', 'ad_insert');
	    $smarty->assign('action',   'add');
	    $smarty->assign('cfg_lang', $_CFG['lang']);

	    
	    $smarty->display('app/ads_info.html');
	break;
	case "ad_edit":
		 /* 获取广告数据 */
	    $sql = "SELECT * FROM " .$aos->table('ad'). " WHERE ad_id='".intval($_REQUEST['id'])."'";
	    $ads_arr = $db->getRow($sql);

	    $ads_arr['ad_name'] = htmlspecialchars($ads_arr['ad_name']);
	    /* 格式化广告的有效日期 */
	    $ads_arr['start_time']  = local_date('Y-m-d', $ads_arr['start_time']);
	    $ads_arr['end_time']    = local_date('Y-m-d', $ads_arr['end_time']);


	    if (strpos($ads_arr['ad_code'], 'http://') === false && strpos($ads_arr['ad_code'], 'https://') === false)
	    {
	        $src = '../' . DATA_DIR . '/ads/'. $ads_arr['ad_code'];
	        $smarty->assign('img_src', $src);
	    }
	    else
	    {
	        $src = $ads_arr['ad_code'];
	        $smarty->assign('url_src', $src);
	    }

	    $smarty->assign('form_act',      'ad_update');
	    $smarty->assign('action',        'edit');
	    $smarty->assign('ads',           $ads_arr);

	    $smarty->display('app/ads_info.html');
	break;
	case "ad_insert":
		include_once(ROOT_PATH . 'source/class/image.class.php');
		$image = new cls_image($_CFG['bgcolor']);
		/* 初始化变量 */
	    $id      = !empty($_POST['id'])      ? intval($_POST['id'])    : 0;
	    $type    = !empty($_POST['type'])    ? intval($_POST['type'])  : 0;
	    $ad_name = !empty($_POST['ad_name']) ? trim($_POST['ad_name']) : '';
	    $ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';

	    /* 获得广告的开始时期与结束日期 */
	    $start_time = local_strtotime($_POST['start_time']);
	    $end_time   = local_strtotime($_POST['end_time']);

	    /* 查看广告名称是否有重复 */
	    $sql = "SELECT COUNT(*) FROM " .$aos->table('ad'). " WHERE ad_name = '$ad_name' and position_id = 4";
	    if ($db->getOne($sql) > 0)
	    {
	        $link[] = array('text' => "返回", 'href' => 'javascript:history.back(-1)');
	        sys_msg("广告名称重复", 0, $link);
	    }


	    if ((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name'] ) &&$_FILES['ad_img']['tmp_name'] != 'none'))
	    {
	        $ad_code = basename($image->upload_image($_FILES['ad_img'], 'ads_img'));
	    }
	    if (!empty($_POST['img_url']))
	    {
	        $ad_code = $_POST['img_url'];
	    }
	    if (((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] > 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] == 'none')) && empty($_POST['img_url']))
	    {
	        $link[] = array('text' => "返回", 'href' => 'javascript:history.back(-1)');
	        sys_msg('广告的图片不能为空!', 0, $link);
	    }

	    /* 插入数据 */
	    $sql = "INSERT INTO ".$aos->table('ad'). " (position_id,ad_name,ad_link,ad_code,start_time,end_time,enabled)
	    VALUES ('4',
	            '$ad_name',
	            '$ad_link',
	            '$ad_code',
	            '$start_time',
	            '$end_time',
	            '1')";

	    $db->query($sql);
	    /* 记录管理员操作 */
	    admin_log($_POST['ad_name'], 'add', 'ads');

	    clear_cache_files(); // 清除缓存文件

	    /* 提示信息 */
	    $link[0]['text'] = "返回广告列表";
	    $link[0]['href'] = 'index.php?act=app&op=app_ad';

	    $link[1]['text'] = "继续添加";
	    $link[1]['href'] = 'index.php?act=app&op=ad_add';
	    sys_msg("添加" . "&nbsp;" .$_POST['ad_name'] . "&nbsp;" . "成功",0, $link);
	break;
	case "ad_update":
		include_once(ROOT_PATH . 'source/class/image.class.php');
		$image = new cls_image($_CFG['bgcolor']);
		$id   = !empty($_POST['id'])   ? intval($_POST['id'])   : 0;
	    $ad_link = !empty($_POST['ad_link']) ? trim($_POST['ad_link']) : '';


	    /* 获得广告的开始时期与结束日期 */
	    $start_time = local_strtotime($_POST['start_time']);
	    $end_time   = local_strtotime($_POST['end_time']);


	    if ((isset($_FILES['ad_img']['error']) && $_FILES['ad_img']['error'] == 0) || (!isset($_FILES['ad_img']['error']) && isset($_FILES['ad_img']['tmp_name']) && $_FILES['ad_img']['tmp_name'] != 'none'))
	    {
	        $img_up_info = basename($image->upload_image($_FILES['ad_img'], 'ads_img'));
	        $ad_code = "ad_code = '".$img_up_info."'".',';
	    }
	    else
	    {
	        $ad_code = '';
	    }
	    if (!empty($_POST['img_url']))
	    {
	        $ad_code = "ad_code = '$_POST[img_url]', ";
	    }


	    $ad_code = str_replace('../uploads/ad_img/', '', $ad_code);
	    /* 更新信息 */
	    $sql = "UPDATE " .$aos->table('ad'). " SET ".
	            "ad_name     = '$_POST[ad_name]', ".
	            "ad_link     = '$ad_link', ".
	            $ad_code.
	            "start_time  = '$start_time', ".
	            "end_time    = '$end_time', ".
	            "enabled     = '$_POST[enabled]' ".
	            "WHERE ad_id = '$id'";
	    $db->query($sql);

	   /* 记录管理员操作 */
	   admin_log($_POST['ad_name'], 'edit', 'ads');

	   clear_cache_files(); // 清除模版缓存

	   /* 提示信息 */
	   $href[] = array('text' => "返回广告列表", 'href' => 'index.php?act=app&op=app_ad');
	   sys_msg("编辑" .' '.$_POST['ad_name'].' '. "成功", 0, $href);
	break;
	case "ad_remove":
	    check_authz_json('ads_manage');

	    $id = intval($_REQUEST['id']);
	    $exc   = new exchange($aos->table("ad"), $db, 'ad_id', 'ad_name');
	    $img = $exc->get_name($id, 'ad_code');

	    $exc->drop($id);

	    if ((strpos($img, 'http://') === false) && (strpos($img, 'https://') === false) && get_file_suffix($img, $allow_suffix))
	    {
	        $img_name = basename($img);
	        @unlink(ROOT_PATH. '/uploads/ads_img/'.$img_name);
	    }

	    admin_log('', 'remove', 'ads');

	    //$url = 'index.php?act=ads&op=query&' . str_replace('op=remove', '', $_SERVER['QUERY_STRING']);

	    make_json_result($id);
	break;
    case "app_create":
        $smarty->display ( 'app/app_create.html' );
	break;
}

function getstr($str){
	return htmlspecialchars($str,ENT_QUOTES);
}

function update_menu($id=1){
	global $admin_wechat;
	$ret = $GLOBALS['db']->getAll ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_menu') . " where pid=0 and enabled = 1 order by `sort_order` asc" );
	if($ret){
		foreach($ret as $k=>$v){
			$button[$k]['name'] = $v['name'];
			$ret2 = $GLOBALS['db']->getAll ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_menu') . " where pid={$v['id']} and enabled = 1 order by `sort_order` desc" );
			if($ret2){
				foreach($ret2 as $kk=>$vv){
					$button[$k]['sub_button'][$kk]['name'] = $vv['name'];
					if($vv['type'] == 1){
						$button[$k]['sub_button'][$kk]['key'] = $vv['value'];
						$button[$k]['sub_button'][$kk]['type'] = "click";
					}else{
						$vv['value'] = str_replace('{id}', $id, $vv['value']);
						$button[$k]['sub_button'][$kk]['url'] = $vv['value'];
						$button[$k]['sub_button'][$kk]['type'] = "view";
					}
				}
			}else{
				if($v['type'] == 1){
					$button[$k]['key'] = $v['value'];
					$button[$k]['type'] = "click";
				}else{
					$v['value'] = str_replace('{id}', $id, $v['value']);
					$button[$k]['url'] = $v['value'];
					$button[$k]['type'] = "view";
				}
			}
		}
	}
	$res = $admin_wechat->createMenu(array('button'=>$button));
	if($res === false){
		sys_msg ('更新菜单出错：'.$admin_wechat->errMsg, 1, $link);
	}else{
		return true;
	}
}

function qcode_list()
{
	$result = get_filter();
	if ($result === false)
	{
		$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		$filter['type'] = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
		$ex_where = " where " . $GLOBALS['aos']->table('wx_qcode') . ".type={$filter['type']}";
		if ($filter['keywords']){
			$key = "%".getstr($filter['keywords'])."%";
			$ex_where = " WHERE " . $GLOBALS['aos']->table('wx_qcode') . ".content like '{$key}' ";
		}	
		if($filter['type'] == 1){
			$tn = $GLOBALS['aos']->table('goods');
			$leftJoin = " left join {$tn} on " . $GLOBALS['aos']->table('wx_qcode') . ".content={$tn}.goods_id";
			$items = $GLOBALS['aos']->table('wx_qcode') . ".*,$tn.goods_name as title";
			if($key) $ex_where .= " or {$tn}.goods_name like '{$key}'";
		}elseif($filter['type'] == 2){
			$tn = $GLOBALS['aos']->table('article');
			$leftJoin = " left join {$tn} on " . $GLOBALS['aos']->table('wx_qcode') . ".content={$tn}.article_id";
			$items = $GLOBALS['aos']->table('wx_qcode') . ".*,$tn.title";
			if($key) $ex_where .= " or {$tn}.title like '{$key}'";
		}else{
			$leftJoin = "";
			$items = "*";
		}
		$sql = ("SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('wx_qcode') . $leftJoin . $ex_where);
		$filter['record_count'] = $GLOBALS['db']->getOne($sql);
		$filter = page_and_size($filter);
		$sql = "SELECT {$items} FROM " . $GLOBALS['aos']->table('wx_qcode') .$leftJoin. $ex_where . " ORDER BY id DESC ".
		" LIMIT " . (int)$filter['start'] . ',' . (int)$filter['page_size'];
		if($filter['type'] == 0)
		{
			$filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('wx_qcode'));
			$filter = page_and_size($filter);

			$sql = ("select w.*,IF(w.type=1, g.goods_name, a.title) as title FROM ".$GLOBALS['aos']->table('wx_qcode')  ." AS w left join ". $GLOBALS['aos']->table('goods') ." AS g ON w.content = g.goods_id LEFT JOIN ". $GLOBALS['aos']->table('article') ." AS a ON w.content = a.article_id") . " LIMIT " . (int)$filter['start'] . ',' . (int)$filter['page_size'];
		}
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else
	{
		$sql    = $result['sql'];
		$filter = $result['filter'];
	}
	$user_list = $GLOBALS['db']->getAll($sql);
	$arr = array('qcode_list' => $user_list, 'filter' => $filter,
			'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}
function wx_qcode_list(){
	$result = get_filter();
	$filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
	if($filter['keywords']){
		$where = " and " . $GLOBALS['aos']->table('wx_win') . ".code like '%{$filter['keywords']}%'";
	}
	$sql =  $GLOBALS['aos']->table('wx_win') . " as wi left join " . $GLOBALS['aos']->table('users') ." as u on wi.uid=u.user_id left join " . $GLOBALS['aos']->table('wx_act') . " as wa on wi.aid=wa.aid
		where code!='' {$where} order by lid desc";

	$filter['record_count'] = $GLOBALS['db']->getOne("SELECT COUNT(*) FROM " . $GLOBALS['aos']->table('wx_win')." where code!='' {$where}");
	$filter = page_and_size($filter);
	$filter['start'] = intval($filter['start']);
	$filter['page_size'] = intval($filter['page_size']);
	$user_list = $GLOBALS['db']->getAll("SELECT wi.*,u.nickname,wa.title,wa.overymd FROM".$sql." limit {$filter['start']},{$filter['page_size']}");
	$arr = array('qcode_list' => $user_list, 'filter' => $filter,
			'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}
function wxmsg()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['aos']->table('wx_msg');
    return $GLOBALS['db']->getAll($sql);
}


?>