<?php
define('IN_AOS', true);

$result = array();

if ($operation == 'img_remove')
{
	$type = $_REQUEST['type'];
	$id = intval($_REQUEST['id']);
	$sql="select * from ".$aos->table('goods_'.$type)." where ".$type."_id = $id";
	$res=$db->getRow($sql);
	@unlink('../'.$res[$type.'_img']);
	$sql="delete from ".$aos->table('goods_'.$type)." where ".$type."_id = $id";
	$res=$db->query($sql);
	if($res){
		$result['error']=0;
	}else{
		$result['error']=1;
	}
	$result['type']=$type;
	$result['id']=$id;
	echo json_encode($result);
}


?>