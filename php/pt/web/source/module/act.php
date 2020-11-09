<?php
if (!defined('IN_AOS'))
{
  die('Hacking attempt');
}

$aid = intval($_GET['aid']);

if (!empty($action) && $action == 'ajax')
{
	$arr = doAward($aid);
	die(json_encode($arr));
}

$act = $db->getRow ( "SELECT * FROM " . $aos->table('wx_act') . " WHERE `aid` = $aid and isopen=1" );
if(!$act) exit("活动已经结束");
$prize = (array)$db->getAll ( "SELECT * FROM " . $aos->table('wx_prize') . " where aid=$aid and isopen=1" );
if(!$prize) exit("活动未设置奖项");

$sql = "SELECT " . $aos->table('wx_win') . ".*," . $aos->table('users') . ".nickname FROM " . $aos->table('wx_win') . " 
		left join " . $aos->table('users') . " on " . $aos->table('wx_win') . ".uid=" . $aos->table('users') . ".user_id 
		where code!='' and aid=$aid order by lid desc";
$award = $db->getAll ($sql);

$uid = intval($_SESSION['user_id']);
$awardNum = intval(getAwardNum($aid));

$smarty->assign('uid',           $uid);
$smarty->assign('aid',           $aid);
$smarty->assign('act',           $act);
$smarty->assign('prize',           $prize);
$smarty->assign('award',           $award);
$smarty->assign('awardNum',           $awardNum);


if($act['tpl'] == 1)
{
  $smarty->display('act_zjd.htm');
}
elseif($act['tpl'] == 2)
{
  $smarty->display('act_ggk.htm');
}
elseif($act['tpl'] == 3)
{
  $smarty->display('act_dzp.htm');	
}




//统计剩余抽奖次数
function getAwardNum($aid){
	$act = checkAward($aid);
	if(!$act) return 0;
	$uid = $_SESSION['user_id'];
	if($act['type'] == 1){
		$ymd = date('Y-m-d');
		$sql = "SELECT count(1) FROM " . $GLOBALS['aos']->table('wx_win') . " WHERE `uid` = '$uid' and aid = '$aid' and createymd='$ymd'";
	}else{
		$sql = "SELECT count(1) FROM " . $GLOBALS['aos']->table('wx_win') . " WHERE `uid` = '$uid' and aid = '$aid'";
	}
	$useNum = $GLOBALS['db']->getOne ( $sql );
	$num = $act['num']>$useNum ? $act['num']-$useNum : 0;
	return $num;
}
//抽奖
function doAward($aid){
	$act = checkAward($aid);
	if(!$act) return array('num'=>0,'msg'=>2,'prize'=>"活动不存在！");;
	$awardNum = getAwardNum($aid);
	if($awardNum<=0){
		return array('num'=>0,'msg'=>2,'prize'=>"您的抽奖机会已经用完！");
	}
	//$awardNum = $awardNum-1;
	$time = time();
	$ymd = date('Y-m-d',$time);
	$res = randAward($aid);
	$class_name = '';$code = '';$msg = 0;
	$uid = $_SESSION['user_id'];
	$arr = array(2,3,4,6,7,8,11,12);
	$r = $arr[array_rand($arr)];
	if($res){
		$class_name = $res['awardname'];
		$code = $res['code'];
		$msg = 1;
		switch($res['title']){
			case "一等奖":
					$r = 1;
				break;
			case "二等奖":
					$r = 5;
				break;
			case "三等奖":
					$r = 9;
				break;
		}
	}
	$GLOBALS['db']->query("INSERT INTO ".$GLOBALS['aos']->table('wx_win')." (uid,aid,class_name,createymd,createtime,code,issend) 
	value ($uid,$aid,'$class_name','$ymd','$time','$code',0)");
	$class_name = $class_name ? $class_name : "非常遗憾没有中奖！";
	
	return array('num'=>$awardNum,'msg'=>$msg,'prize'=>$class_name,'prize_code'=>$code,'r'=>$r);
}
function randAward($aid){
	//if(intval(rand(1,5)) != 1) return false;
	$actList = $GLOBALS['db']->getAll ( "SELECT title,lid,randnum,awardname,num FROM ".$GLOBALS['aos']->table('wx_prize')." where aid=$aid and isopen=1 and num>num2 order by num desc" );
	if($actList){
		foreach($actList as $v){
			if(intval(rand(1,10000)) <= $v['randnum']*100){
				$v['code'] = uniqid();
				$GLOBALS['db']->query("update " . $GLOBALS['aos']->table('wx_prize') . " set num2=num2+1 where lid={$v['lid']}");
				return $v;
			}
		}
	}
	return false;
}
function checkAward($aid){
	$act = $GLOBALS['db']->getRow ( "SELECT * FROM " . $GLOBALS['aos']->table('wx_act') . " where aid=$aid" );
	if($act['isopen'] == 0) return false;
	return $act;
}
?>