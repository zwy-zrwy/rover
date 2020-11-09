<?php

class wxapi
{
	//用户关注
	function subscribe($wxid,$key=''){

      	
		$user_id = $GLOBALS['db']->getOne("SELECT user_id FROM ".$GLOBALS['aos']->table('users')." WHERE openid = '$wxid'");
		if($user_id)
		{
			$GLOBALS['db']->query('UPDATE ' . $GLOBALS['aos']->table('users') . " SET subscribe = 1 WHERE user_id = '$user_id'");
		}else{
			//关注注册
			global $wechat;
			$aos=$GLOBALS['aos'];
			$db=$GLOBALS['db'];
			$user_info = $wechat->getUserInfo($wxid);
			//@file_put_contents(ROOT_PATH."data/pangbin.txt", $user_info['nickname']);
			if(!empty($user_info[headimgurl])){
				$user_info['nickname'] = replaceSpecialChar($user_info[nickname]);
				//保存图像到本地
	            $avatar    = $wechat->http_get($user_info[headimgurl]);
	            $path   = ROOT_PATH . 'uploads/avatar/avatar_'.$user_id.'.jpg';
	            @file_put_contents($path,$avatar);
			}
			

            $time = gmtime();
	        if(!empty($user_info[unionid])){
	            $sql="select user_id from ".$aos->table('users')." where unionid = '$user_info[unionid]'";
	            $id = $db->getOne($sql);
	            if($id){
	                $sql = "UPDATE ".$aos->table('users')." set openid = '$user_info[openid]' where user_id = '$id'";
	                $res=$db->query($sql);
	            }else{
	                $sql = "INSERT INTO " . $aos->table('users') . " (openid, nickname, sex, headimgurl, country, province, city, reg_time, subscribe,unionid) VALUES ('$user_info[openid]', '$user_info[nickname]', '$user_info[sex]', '$user_info[headimgurl]', '$user_info[country]', '$user_info[province]', '$user_info[city]', '$time', '$user_info[subscribe]','$user_info[unionid]')";
	                $res=$db->query($sql);
	                $id = $db->insert_id();
	            }
	        }else{
	            $sql = "INSERT INTO " . $aos->table('users') . " (openid, nickname, sex, headimgurl, country, province, city, reg_time, subscribe) VALUES ('$user_info[openid]', '$user_info[nickname]', '$user_info[sex]', '$user_info[headimgurl]', '$user_info[country]', '$user_info[province]', '$user_info[city]', '$time', '$user_info[subscribe]')";
	            $res=$db->query($sql);
	            $id = $db->insert_id();
	        }
	        $this->assist($wxid,$key,$type=1,$id);
	        if($res){
	        	$sql="select type_id,type_money,use_start_date,use_end_date from ".$aos->table('bonus_type')." where send_type = 4 and send_start_date < $time and send_end_date > $time";
		        $bonus_list=$db->getAll($sql);
		        if(!empty($bonus_list)){
		            foreach($bonus_list as $vo){
		                $sql="insert into ".$aos->table('user_bonus')." (bonus_type_id,user_id) values ('$vo[type_id]','$id')";
		                $db->query($sql);
		                $openid=getOpenid($id);
	                    $use_time=local_date("m月d日", $vo['use_start_date']).'-'.local_date("m月d日", $vo['use_end_date']);
	                    $wx_title = "获得优惠劵通知";
	                    $wx_desc = "恭喜您获得优惠劵\r\n优惠劵金额：".$vo['type_money']."元\r\n有效期：".$use_time;
	                    
	                    $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
		            }
		        }
	        }
	        
			
		}
	}
	//取消关注
	function unsubscribe($wxid){
		$GLOBALS['db']->query("update ".$GLOBALS['aos']->table('users')." set subscribe = 0, subscribe_time = 0 where openid='$wxid'");
		return true;
	}
	//助力
	function assist($wxid,$key,$type=1,$user_id=0){
		global $wechat,$aos,$db;
		if(!empty($key)){
			if($type==1){
				$a=explode('_', $key);
				$order_sn=$a[1];
			}elseif($type==2){
				$user_id = $GLOBALS['db']->getOne("SELECT user_id FROM ".$GLOBALS['aos']->table('users')." WHERE openid = '$wxid'");
				$order_sn=$key;
			}
			
			$sql="select o.order_id,o.user_id,g.goods_name,o.tuan_status,o.tuan_num,o.assist_num,o.act_id from ".$GLOBALS['aos']->table('order_info')." as o left join ".$aos->table('order_goods')." as g on o.order_id = g.order_id where o.order_sn = '$order_sn' order by o.order_id desc";
			$res=$GLOBALS['db']->getRow($sql);
			if($res){
				$openid=getOpenid($user_id);
				
				$wx_url=substr($GLOBALS['aos']->url(),0,-4)."index.php?c=user&a=assist";
				
				if($type==1){
					if($res['tuan_status']==1){
						//检查库存
						$now_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
				        $sql="select assist_sales,assist_number from ".$aos->table('assist')." where assist_id = ".$res['act_id'];
				        $row=$db->getRow($sql);
				        if($row['assist_sales']>=$row['assist_number']){

				        	$db->query('UPDATE ' . $aos->table('order_info') . " SET tuan_status = 4 WHERE order_sn = $order_sn");
				        	$openid=getOpenid($res['user_id']);
						    $message=getMessage(13);
				            $wx_title = "助力活动失败通知";
				            $wx_desc = $message[title]."\r\n任务名称：助力享免单\r\n失败原因：库存不足\r\n助力商品：".$row[goods_name]."\r\n失败时间：".$now_time."\r\n".$message['note'];
				            //$wx_pic = $aos_url;
				            $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
				            order_action($row['order_sn'], 1, 0, 2, '助力活动失败', '');
						    
						}else{

							$GLOBALS['db']->query('UPDATE ' . $GLOBALS['aos']->table('order_info') . " SET assist_num = assist_num+1 WHERE order_sn = '$order_sn'");
						
					       //好友助力消息
					        $message=getMessage(15);
					        $wx_title="助力成功通知";
					        $wx_desc = $message['title']."\r\n任务名称：助力享免单\r\n助力商品：".$res[goods_name]."\r\n助力时间：".$now_time."\r\n".$message['note'];
					        //$wx_pic = $aos_url;
					        $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);


					        //团长消息
					        $message=getMessage(14);
					        $openid=getOpenid($res['user_id']);
					        $nickname=$db->getOne("select nickname from ".$aos->table('users')." where user_id = $user_id");
					        $wx_title="好友助力通知";
					        $wx_desc = "您的好友".$nickname."帮您助力了\r\n任务名称：助力享免单\r\n助力商品：".$res[goods_name]."\r\n助力时间：".$now_time."\r\n".$message['note'];
					        //$wx_pic = $aos_url;
					        $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
					        order_action($order_sn, 1, 0, 2, $nickname."助力", '关注');



					        if($res['tuan_num']<=($res['assist_num']+1)){
					        	//人数达到助力成功
					        	$sql = "UPDATE ". $GLOBALS['aos']->table('order_info') ." SET tuan_status = '2' WHERE order_id='".$res['order_id']."'";
				                $GLOBALS['db']->query($sql);
				                $message=getMessage(12);
				                $wx_title="助力活动成功通知";
				                $wx_desc = $message['title']."\r\n任务名称：助力享免单\r\n助力商品：".$res[goods_name]."\r\n成功时间：".$now_time."\r\n".$message['note'];
				                //$wx_pic = $aos_url;
				                $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
				                order_action($order_sn, 1, 0, 2, "助力活动成功", '');
				                require_once(ROOT_PATH . 'source/library/order.php');
				                change_order_goods_storage($res['order_id'], true, 2);
					        }
				        }
					}elseif($res['tuan_status']==2){
						$now_time=local_date($GLOBALS['_CFG']['time_format'], gmtime());
				       //好友助力消息
				        $message=getMessage(17);
				        $wx_title="助力失败通知";
				        $wx_desc = $message['title']."\r\n助力商品：".$res[goods_name]."\r\n助力时间：".$now_time."\r\n".$message['note'];
				        //$wx_pic = $aos_url;
				        $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
					}
					
			    }elseif($type==2){
			    	//以关注无法助力
			    	$message=getMessage(16);
			    	$wx_title="助力失败通知";
			    	$wx_desc = $message['title']."\r\n任务名称：助力享免单\r\n通知类型：获得0元秒杀权利\r\n".$message['note'];
			        //$wx_pic = $aos_url;
			        $aaa = $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
			    }
			}
		}
	}

	//多图文文章
	function getNewsKey($key){
		$key = $this->getstr($key);
		$where = "keywords like '%{$key}%' and is_open = 1";
		$sql = "select article_id,title,spic,bpic,link,description from ". $GLOBALS['aos']->table('article') ." where $where order by article_id desc limit 8";
		$result = $GLOBALS['db']->getAll($sql);
		foreach ($result AS $idx => $row)
		{
		  	$news[$idx]['title']  = $row['title'];
		  	$news[$idx]['description'] = $row['description'];
		  	if($idx == 0)
		  	{
		  		$news[$idx]['pic_url']= 'uploads/article/'.$row['bpic'];
		  	}
		  	else
		  	{
		  		$news[$idx]['pic_url']  = 'uploads/article/'.$row['spic'];
		  	}
	        if($row['link'] == 'http://' || $row['link'] == 'https://')
	        {
	            $news[$idx]['url_type'] = 1;
	        }
	        $news[$idx]['link']    = $row['link'];
	        $news[$idx]['url'] = 'index.php?c=news&id='.$row['article_id'];
		}
		return $news;
	}

	function getstr($str){
		return htmlspecialchars($str,ENT_QUOTES);
	}
}

?>