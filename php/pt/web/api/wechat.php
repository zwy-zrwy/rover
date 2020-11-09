<?php
define('IN_AOS', true);
$is_wap = true;
require('../source/aoshop.php');
$aos_url = substr($aos->url(), 0, -4);
if(isset($_GET['echostr'])) {
    $wechat->valid();
}else{
	require_once(ROOT_PATH.'source/class/wxapi.class.php');
	$wxapi = new wxapi();
	$type = $wechat->getRev()->getRevType();
	$wxid = $wechat->getRev()->getRevFrom();
	$data = $wechat->getRevData();
	$reMsg = '';
	switch($type) {
		case 'text':
			$content = $wechat->getRev()->getRevContent();
			break;
		case 'event':
			$event = $wechat->getRev()->getRevEvent();
			$content =  json_encode($event);
			break;
		case 'image':
			$content = json_encode($wechat->getRev()->getRevPic());
			$reMsg = "图片很美！";
			break;
		case 'location':
			$content = json_encode($wechat->getRev()->getRevGeo());
			$reMsg = "您所在的位置很安全！";
			break;
		default:
	    $helpmsg = $wxconfig['helpmsg'];
		  $helpmsg = str_replace("<br/>","\r\n",$helpmsg);
			$reMsg = $helpmsg;
	}

	if($reMsg){
		echo $wechat->text($reMsg)->reply();exit;
	}

	//用户关注自动回复
	if($event['event'] == "subscribe") {
		$wxapi->subscribe($wxid,$event[key]);
		//var_dump($event[key]);exit;
		if($wxconfig['followtype'] == 1)
		{
			echo $wechat->text($wxconfig['followmsg'])->reply();
		}
		elseif($wxconfig['followtype'] == 2)
		{
			$reMsg = $wxapi->getNewsKey($wxconfig['followkey']);
			if($reMsg){
				$k = 0;
				foreach($reMsg as $v){
					$newsData[$k]['Title'] = $v['title'];
					$newsData[$k]['Description'] = strip_tags($v['description']);
					$newsData[$k]['PicUrl'] = $aos_url.$v['pic_url'];
					if($v['url_type'])
					{
						$newsData[$k]['Url'] = $aos_url.$v['url'];
					}
					else
					{
						$newsData[$k]['Url'] = $v['link'];
					}
					$k++;
				}
				echo $wechat->news($newsData)->reply();
			}
		}
		//echo $wechat->text($event[key])->reply();
		exit;
	}
	if($event['event'] == "SCAN") {
		$wxapi->assist($wxid,$event[key],2);
		//echo $wechat->text('欢迎扫码')->reply();
		exit;
	}
	//用户取消关注
	if($event['event'] == "unsubscribe") {
		$wxapi->unsubscribe($wxid);
		exit;
	}
	//微信菜单图文回复
	if($event['event'] == "CLICK"){
		$content = $event['key'];
	        $reMsg = $wxapi->getNewsKey($content);
	        if($reMsg){
				$k = 0;
				foreach($reMsg as $v){
					$newsData[$k]['Title'] = $v['title'];
					$newsData[$k]['Description'] = strip_tags($v['description']);
					$newsData[$k]['PicUrl'] = $aos_url.$v['pic_url'];
					if($v['url_type'])
					{
						$newsData[$k]['Url'] = $aos_url.$v['url'];
					}
					else
					{
						$newsData[$k]['Url'] = $v['link'];
					}
					$k++;
				}
				echo $wechat->news($newsData)->reply();exit;
			}

		exit;
	}
	
	//处理用户的输入
	if($content){

		if($content == '客服' || $content == 'kefu'){
	    	echo $wechat->kefu()->reply();exit;
	    }
	}
	$reMsg = $wxapi->getNewsKey($content);
	if($reMsg){
		$k = 0;
		foreach($reMsg as $v){
			$newsData[$k]['Title'] = $v['title'];
			$newsData[$k]['Description'] = strip_tags($v['description']);
			$newsData[$k]['PicUrl'] = $aos_url.$v['pic_url'];
			if($v['url_type'])
			{
				$newsData[$k]['Url'] = $aos_url.$v['url'];
			}
			else
			{
				$newsData[$k]['Url'] = $v['link'];
			}
			$k++;
		}
		echo $wechat->news($newsData)->reply();exit;
	}else{
		if($content){
			echo $wechat->kefu()->reply();exit;
		}
	}


}

?>