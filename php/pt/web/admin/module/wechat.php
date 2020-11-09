<?php
define('IN_AOS', true);


switch ($operation){
	case "wx_config":
		admin_priv('wxconfig');
		if($_POST){
			$token = getstr($_POST ['token']);
			$appid = getstr($_POST ['appid']);
			$appsecret = getstr($_POST ['appsecret']);
			$followmsg = getstr($_POST ['followmsg']);
			$followtype = getstr($_POST ['followtype']);
			$followkey = getstr($_POST ['followkey']);
			$ret = $db->query ( "UPDATE " . $aos->table('wx_config') . " SET `token`='$token',`appid`='$appid',`appsecret`='$appsecret',`followmsg`='$followmsg',`followtype`='$followtype',`followkey`='$followkey' WHERE `id`=1;");
			$link [] = array ('href' => 'index.php?act=wechat&op=wx_config','text' => '微信设置');
			if ($ret) {
				sys_msg ( '设置成功', 0, $link );
			} else {
				sys_msg ( '设置失败，请重试', 0, $link );
			}
		}else{
			$ymd = date('Y-m-d');
			$ret = $db->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_config') . " WHERE `id` = 1" );
			$smarty->assign ('ret', $ret);
			$smarty->assign('shop_url', $aos->url());
			$smarty->display ( 'wechat/wx_config.html' );
		}
	break;
	case "wx_menu"://菜单页面
	admin_priv('wxmenu');
		if($_POST){
			
		}else{
			$ret = $db->getAll ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_menu') . " order by `sort_order` asc" );
			$menu = $pmenu = array();
			if($ret){
				foreach ($ret as $k=>$v){
					$k=$k+1;
					if($v['pid'] == 0){
						$pmenu[$v[id]] = $v;
					}else{
						$pmenu[$v[pid]][pids][] = $v;
					}
				}
			}
			$smarty->assign ( 'menu', $menu );
			$smarty->assign ( 'pmenu', $pmenu );
			$smarty->display ( 'wechat/wx_menu.html' );
		}
	break;

	case "delmenu":
	admin_priv('wxmenu');
		$id = intval($_GET['id']);
		$ret = $db->getRow ( "SELECT pid FROM " . $GLOBALS['aos']->table('wx_menu') . " WHERE `id` = $id" );
		if($ret['pid'] == 0){
			$db->query("DELETE FROM " . $GLOBALS['aos']->table('wx_menu') . " WHERE `pid` = {$id};");
		}
		$db->query("DELETE FROM " . $GLOBALS['aos']->table('wx_menu') . " WHERE `id` = $id;");
		$link [] = array ('href' => 'index.php?act=wechat&op=wx_menu','text' => '自定义菜单');
		update_menu();
		sys_msg ( '删除成功', 0, $link );
	break;
	case "addmenu"://添加菜单
	admin_priv('wxmenu');
    //保存信息
    if($_POST['sub']=="保存"){
    	$sql = "SELECT * FROM " . $aos->table('wx_menu') ;
    	$res = $db->query($sql);
      while ($row = $db->fetchRow($res))
      {
        $wei_menu_list[$row['id']] = array('sign' => 'delete', 'id' => $row['id']); 
      }
      if(count($_POST['menu_list'])>3){
      	sys_msg ( '一级菜单不能超过3个！');
      }
      // 循环现有的，根据原有的做相应处理
      if(isset($_POST['menu_list']))
      {
      	//主菜单数据
        foreach ($_POST['menu_list'] AS $key => $vo)
        {
        	$wei_name=$_POST['wei_name'][$key];
        	$keys=$key+1;
        	$wei_menu_list[$wei_name][wei_name] = trim($_POST['wei_name'][$key]);
          $wei_menu_list[$wei_name][wei_type] = intval($_POST['wei_type'][$key]);
          $wei_menu_list[$wei_name][wei_value] = trim($_POST['wei_value'][$key]);
          $wei_menu_list[$wei_name][wei_stort] = intval($_POST['wei_stort'][$key]);
          //判断主菜单是否显示
          if(!is_array($_POST['wei_enabled'])){
          	$wei_menu_list[$wei_name][enabled] = 0;
          }elseif(in_array($keys,$_POST['wei_enabled'])){
          	$wei_menu_list[$wei_name][enabled] = 1;
          }else{
          	$wei_menu_list[$wei_name][enabled] = 0;
          }
          if (isset($wei_menu_list[$vo]))
          {
            $wei_menu_list[$wei_name][sign] = 'update';
            $wei_menu_list[$wei_name][id] = $vo;
            $wei_menu_list[$vo] = array();
          }else{
          	$wei_menu_list[$wei_name][sign] = 'insert';
          }
          if(!empty($_POST['menu_'.$keys.'_list'])){
          	if(count($_POST['menu_'.$keys.'_list'])>5){
	        	sys_msg ( '二级菜单不能超过5个！');
	        }
          //二级菜单
          foreach($_POST['menu_'.$keys.'_list'] as $k=>$v)
          {
            $wei_a_name=trim($_POST['wei_'.$keys.'_name'][$k]);
            if (!empty($wei_a_name))
      			{
            	$wei_menu_list[$wei_name]['zi'][$k][wei_name] = trim($_POST['wei_'.$keys.'_name'][$k]);
              $wei_menu_list[$wei_name]['zi'][$k][wei_type] = intval($_POST['wei_'.$keys.'_type'][$k]);
              $wei_menu_list[$wei_name]['zi'][$k][wei_value] = trim($_POST['wei_'.$keys.'_value'][$k]);
              $wei_menu_list[$wei_name]['zi'][$k][wei_stort] = intval($_POST['wei_'.$keys.'_stort'][$k]);
              //判断子类是否显示
              if(!is_array($_POST['wei_'.$keys.'_enabled'])){
              	$wei_menu_list[$wei_name]['zi'][$k][enabled] = 0;
              }
              elseif(in_array($k,$_POST['wei_'.$keys.'_enabled']))
              {
              	$wei_menu_list[$wei_name]['zi'][$k][enabled] = 1;
              }else{
              	$wei_menu_list[$wei_name]['zi'][$k][enabled] = 0;
              }
              if(isset($wei_menu_list[$v]))
              {
              	$wei_menu_list[$wei_name]['zi'][$k][sign] = 'update';
              	$wei_menu_list[$wei_name]['zi'][$k][id] = $v;
              	$wei_menu_list[$v] = array();
              }else{
              	$wei_menu_list[$wei_name]['zi'][$k][sign] = 'insert';
              }
          	}
          }
        }    
      }
    }
    /* 插入、更新、删除数据 */ 
    foreach ($wei_menu_list as $attr_value => $info)
    {
      $pid='';
      if ($info['sign'] == 'insert')
      {
      	$sql = "INSERT INTO " .$aos->table('wx_menu'). " (name, type,sort_order,value,enabled)"." VALUES ('$info[wei_name]', '$info[wei_type]','$info[wei_stort]', '$info[wei_value]', '$info[enabled]')";
        $db->query($sql);
        $pid = $db->insert_id();
      }
      elseif ($info['sign'] == 'update')
      {
      	$sql = "UPDATE " .$aos->table('wx_menu'). " SET name = '$info[wei_name]',type = '$info[wei_type]',sort_order = '$info[wei_stort]',enabled = '$info[enabled]',value='$info[wei_value]' WHERE id = '$info[id]' LIMIT 1";
      	$db->query($sql);
      	$pid=$info['id'];
      }
      else
      {
        $sql = "DELETE FROM " .$aos->table('wx_menu'). " WHERE id = '$info[id]' and pid != 0 LIMIT 1";
        $db->query($sql);
      }
      if(!empty($info['zi'])){
        foreach($info['zi'] as $key=>$vo){
        	if ($vo['sign'] == 'insert')
          {
          	$sql = "INSERT INTO " .$aos->table('wx_menu'). " (name, type,sort_order,value,pid,enabled)".
          	 "VALUES ('$vo[wei_name]', '$vo[wei_type]','$vo[wei_stort]', '$vo[wei_value]','$pid','$vo[enabled]')";
			
          }
          elseif ($vo['sign'] == 'update')
          {
          	$sql = "UPDATE " .$aos->table('wx_menu'). " SET name = '$vo[wei_name]',type = '$vo[wei_type]',sort_order = '$vo[wei_stort]',pid='$pid',enabled='$vo[enabled]',value='$vo[wei_value]' WHERE id = '$vo[id]' LIMIT 1";
          }
          $db->query($sql);
        }
  	  }
    }   
	  $link [] = array ('href' => 'index.php?act=wechat&op=wx_menu','text' => '微信菜单');
		sys_msg ( '操作成功', 0, $link );
	}elseif($_POST['sub']=="生成自定义菜单")
	{
		//生成微信菜单
		$a=update_menu();
		$link [] = array ('href' => 'index.php?act=wechat&op=wx_menu','text' => '微信菜单');
		if ($a) {
			sys_msg ('菜单生成添加成功', 0, $link);
		} else {
			sys_msg ('菜单生成失败，请重试', 0, $link);
		}
	}
	break;

	case "wx_msg":
		admin_priv('wx_msg');
		$wxmsg = wxmsg();
	    $smarty->assign('wxmsg', $wxmsg); 
		$smarty->display ('wechat/wx_msg.html');
	break;

	case "wx_msg_post":
	  admin_priv('wx_msg');
	  if(isset($_POST['id']))
	  {
	    $ids= count($_POST['id']);
	  }
	  for($i=0; $i<$ids; $i++)
	  {
	    $sql = "UPDATE " . $aos->table('wx_msg') . " SET title = '".$_POST[title][$i]."',note = '".$_POST[note][$i]."' WHERE id = ".$_POST[id][$i];
	    $db->query($sql);
	  }
	  if($db)
	  {
	    $links[] = array('text' => '返回模板消息', 'href' => 'index.php?act=wechat&op=wx_msg');
	    sys_msg('修改成功', 0, $links);
	  }
	break;

	case "wx_corn":
		admin_priv('wx_corn');
		$id = intval($_GET['id']);
		if($id > 0 && intval($_REQUEST['tag']) == 1)
		{
			$link [] = array ('href' => 'index.php?act=wechat&op=wx_corn_list','text' => '推送列表');
			$num = $db->query("DELETE FROM ".$aos->table('wx_corn')." where id = '".$id."'");
			if($num > 0)
			{
				sys_msg('删除成功！',0,$link);	 
			}
		}
		if($_POST){
			$artid = $_POST['artid'];
			$type = $_POST['msgtype']==1 ? "text" : "news";
			$sendtime = strtotime($_POST['sendtime']);
			if($sendtime<time()){
				$sendtime = time();
				//sys_msg ( '推送时间必须大于当前时间', 0, $link );
			}
			$createtime = time();
			if($artid == '')
			{
				sys_msg('推送的文章编号或自定义内容不能为空！',0,$link); 
			}
			if(preg_match('/[^\d,]+/', $_POST['artid'])){
				sys_msg ( '推送的文章不存在格式错误', 0, $link );
			}
			$artInfo = $db->getAll("select article_id,title,content,description,spic,bpic from ".$GLOBALS['aos']->table('article')." where article_id in($artid)");
			if(!$artInfo){
				sys_msg ( '推送的文章不存在', 0, $link );
			}
			/*$content = array(
					'touser'=>'',
					'msgtype'=>'news',
					'news'=>array('articles'=>$artInfo)
			);
			
			$content = serialize($content);*/
			if($id){
				$sql = "update " . $GLOBALS['aos']->table('wx_corn') . " set sendtime='{$sendtime}',`content`='{$artid}' where id=$id";
			}else{
				$sql = "insert into " . $GLOBALS['aos']->table('wx_corn') . " (`uid`,`content`,`createtime`,`sendtime`,`issend`,`sendtype`)
			value (0,'{$artid}','{$createtime}','{$sendtime}','0','1')";
			}
			$GLOBALS['db']->query($sql);
			$link [] = array ('href' => 'index.php?act=wechat&op=wx_corn_list','text' => '推送列表');
			sys_msg ( "auto_do", 0, $link, false );
		}else{
			if(!empty($id)){
				$ret = $db->getRow("select * from " . $GLOBALS['aos']->table('wx_corn') . " where id=$id");
				/*$content = unserialize($ret['content']);
				if($content['news']['articles']){
					foreach($content['news']['articles'] as $v){
						$artid .= $v['article_id'].",";
					}
					$smarty->assign('artid', rtrim($artid,","));
				}
				if($content['text']['content']){
					$smarty->assign('artid', $content['text']['content']);
				}*/
				$ret['sendtime'] = $ret['sendtime'] ? $ret['sendtime'] :time()+3600;
				$smarty->assign('msgtype', $content['msgtype']);
				$smarty->assign('sendymd', date('Y-m-d H:i:s',$ret['sendtime']));
				$smarty->assign('corn', $ret);
			}
			
			$smarty->display ( 'wechat/wx_corn.html' );
		}
	  
	break;

	case "wx_corn_list":
		admin_priv('wx_corn');
		$ret = $db->getAll("select * from " . $GLOBALS['aos']->table('wx_corn') . " where sendtype=1 and issend in (0,1)");
		if($ret){
			
			foreach ($ret as $key => $value) {
				$ret[$key]['sendymd'] = date("Y-m-d H:i:s",$value['sendtime']);
				$artInfo = $db->getAll("select article_id,title,content,description,spic,bpic from ".$GLOBALS['aos']->table('article')." where article_id in($value[content])");
				foreach ($artInfo as $k=>$v){
				
					$ret[$key]['title'] .= "<a href='index.php?act=article&op=edit&id=".$v[article_id]."'>".$v[title]."</a><br />";
				
				}
			}
			
		}
		$smarty->assign('corn', $ret);
	  $smarty->display ('wechat/wx_corn_list.html');
	break;
	case "wx_user":
		admin_priv('wx_corn');
		if($_POST){
			$user_array = (is_array($_REQUEST['user'])) ? implode(',',$_REQUEST['user']) : $_REQUEST['user'];
			$sql = "update " . $GLOBALS['aos']->table('wx_config') . " set ceshi_user='{$user_array}' where id=1";
			$GLOBALS['db']->query($sql);
			$link [] = array ('href' => 'index.php?act=wechat&op=wx_user','text' => '测试会员');
			sys_msg ( "设置成功", 0, $link, false );
		}else{
			$sql = "select ceshi_user from " . $GLOBALS['aos']->table('wx_config') . "  where id=1";
			$user_array=$db->getOne($sql);
			if(!empty($user_array)){
				$sql = "SELECT user_id, nickname FROM " . $aos->table('users') .
            " WHERE  user_id in ($user_array)";
    			$row = $db->getAll($sql);
			}
			$smarty->assign('user_array',$row);
			$smarty->display ('wechat/wx_by_user.html');
		}
	  
	break;
	case "do_send":
		admin_priv('wx_corn');
		$t = time();
		$id = intval($_GET['id']);
		$rs = $db->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_corn') . " WHERE `issend` = 0 and `id`={$id} order by sendtime desc" );
		if($rs){
			global $admin_wechat;
				$artInfo = $db->getAll("select article_id,title,content,description,spic,bpic,link from ".$GLOBALS['aos']->table('article')." where article_id in($rs[content])");
				$msg = array();
				//$content = unserialize($rs['content']);
				$msg['msgtype'] = 'news';
				$url= $aos->url();
				foreach($artInfo as $k=>$v){
					$msg['news']['articles'][$k]['title'] = $v['title'];
					$msg['news']['articles'][$k]['description'] = $v['description'];
					if($v['link'] == 'http://' || $v['link'] == 'https://')
					{
					    $msg['news']['articles'][$k]['url'] = $url."index.php?c=news&id=".$v['article_id'];
					}
					else
					{
						$msg['news']['articles'][$k]['url'] = $v['link'];
					}
					if($k == 0)
					{
						$msg['news']['articles'][$k]['picurl'] = $url.'uploads/article/'.$v['bpic'];
					}
					else
					{
						$msg['news']['articles'][$k]['picurl'] = $url.'uploads/article/'.$v['spic'];
					}
					
				}
				//判断测试用户/全部用户
				if($_REQUEST['send_type']=='1'){
					
					$sql="select openid from ".$aos->table('users')." where  openid != '' ";
					$user=$db->getAll($sql);
					
				}else{

					$sql="SELECT ceshi_user from ".$aos->table('wx_config')." where id= 1";
					$ceshi_user=$db->getOne($sql);
					if(!empty($ceshi_user)){
						$sql="select openid from ".$aos->table('users')." where user_id in ($ceshi_user) and  openid != ''";
						$user=$db->getAll($sql);
					}
				}
				if($user){
					foreach($user as $u){

						$msg['touser'] = $u['openid'];
						$ret = $admin_wechat->sendMsg($msg);
						
					}
					if($_REQUEST['send_type']=='1'){
						$db->query("UPDATE " . $GLOBALS['aos']->table('wx_corn') . " SET issend=2 where id ={$rs['id']}");
					}
					
				}
				if($_GET['ajax'] == 1){
					$result = array('error'=>0,'content'=>'');
					echo json_encode($result);
				}
			
		}
	break;
	case "wx_act": //活动列表
	  	admin_priv('wx_act');
		$act = $db->getAll ( "SELECT * FROM  " . $aos->table('wx_act'));
		$smarty->assign ( 'act_list', $act );
	    $smarty->display ('wechat/wx_act.html');
	break;
	case "wx_act_edit": //编辑活动
		admin_priv('wx_act');
		$aid = intval($_REQUEST['aid']);
		if($_POST){
			$title = getstr($_POST ['title']);
			$content = getstr($_POST ['content']);
			$isopen = intval($_POST ['isopen']);
			$type = intval($_POST ['type']);
			$num = intval($_POST ['num']);
			$overymd = getstr($_POST ['overymd']);
			if($aid > 0){
				$ret = $db->query ( 
					"UPDATE  " . $aos->table('wx_act') . "  SET 
					`title`='$title',
					`content`='$content',
					`isopen`='$isopen',
					`type`='$type',
					`overymd`='$overymd',
					`num`='$num'
					 WHERE `aid`=$aid;" );
			}
			$link [] = array ('href' => 'index.php?act=wechat&op=wx_act','text' => '活动管理');
			sys_msg ( '处理成功', 0, $link );
		}elseif($aid > 0){
			$act = $db->getRow ( "SELECT * FROM  " . $aos->table('wx_act') . "  where aid=$aid" );
			$smarty->assign ( 'act', $act );
			$smarty->display ('wechat/wx_act_edit.html');
			return;
		}
	    
	break;
	case "wx_prize": //活动奖项
	  	admin_priv('wx_act');	
		$aid = intval($_GET['aid']) ? intval($_GET['aid']) : 1;
		if($_POST){
			$lid = $_REQUEST['label_id'];
			foreach($lid as $key=>$vo){
				if(!empty($vo)){
					$sql = "update ". $aos->table('wx_prize') ."  set title='".getstr($_POST ['title'][$key])."',randnum='".round($_POST ['randnum'][$key],2)."',num='".intval($_POST ['num'][$key])."',awardname='".getstr($_POST ['awardname'][$key])."' where lid=$vo";
					$db->query($sql);
				}elseif(!empty($_POST ['title'][$key])){
					$sql = "insert into ". $aos->table('wx_prize') ."  (title,randnum,num,aid,awardname) 
				value ('".$_POST ['title'][$key]."','".round($_POST ['randnum'][$key],2)."','".intval($_POST ['num'][$key])."','".$aid."','".getstr($_POST ['awardname'][$key])."')";
					$db->query($sql);
				}
			}
			$link [] = array ('href' => 'index.php?act=wechat&op=wx_prize&aid='.$aid,'text' => '奖项管理');
			sys_msg ( '处理成功', 0, $link );
		}else{
			$sql="select * from ".$aos->table('wx_prize')." where aid = '$aid'";
			$label_list=$db->getAll($sql);
			$smarty->assign('label_list',$label_list);
			$smarty->assign('aid',$aid);
			$smarty->display ('wechat/wx_prize.html');
		}
	    
	break;
	case "wx_win":
		admin_priv('wx_win');
		$lid = intval($_GET['lid']);
		$tag = $_GET['tag'];
		/*if($lid > 0 && $tag == 'send'){
			$ret = $db->query("update " . $ecs->table('weixin_win') . " set issend=1 where lid=$lid");
			$link [] = array ('href' => 'weixin_egg.php?act=log','text' => '获奖管理');
			sys_msg ( '处理成功', 0, $link );
		}
		else if($lid > 0 && $tag == 'delete')
		{	
			$ret = $db->query("DELETE FROM ".$ecs->table('weixin_win')." where lid = '".$lid."'");
			$link [] = array ('href' => 'weixin_egg.php?act=log','text' => '获奖管理');
			sys_msg ( '处理成功', 0, $link );
		} */
		$sql = "SELECT w.*,u.nickname FROM " . $aos->table('wx_win') . " as w 
		left join " . $aos->table('users') . " as u on w.uid=u.user_id 
		where code!='' order by lid desc";
		$log = $db->getAll ($sql);
		
		$qcode_list = wx_qcode_list(); 
		$smarty->assign('win',   $qcode_list['qcode_list']);
		$pager = get_page($qcode_list['filter']);
    	$smarty->assign('pager',   $pager);
		
		$smarty->display ('wechat/wx_win.html');
		
	  
	break;
	case "toggle_is_open":
		admin_priv('wx_act');
		$lid       = intval($_POST['id']);
    	$isopen        = intval($_POST['val']);
    	$sql="update ".$aos->table('wx_prize')." set isopen = '$isopen' where lid = '$lid'";
    	if ($db->query($sql))
    	{
        	clear_cache_files();
        	make_json_result($isopen);
    	}
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