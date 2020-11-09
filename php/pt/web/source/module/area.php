<?php
/*地区切换*/
define('IN_AOS', true);
define('INIT_NO_USERS', true);
define('INIT_NO_SMARTY', true);
header('Content-type: text/html; charset=' . AO_CHARSET);
$provs = get_area(1,0);
$citys = get_area(1);
$dists = get_area(2);
$area_data = 'var provs_data = '.json_encode($provs).',citys_data = '.json_encode($citys).',dists_data = '.json_encode($dists).';';

$myfile = fopen("data/areadata.js", "w") or die("Unable to open file!");
fwrite($myfile, $area_data);
fclose($myfile);

function get_area($type = 0, $level = 1)
{
  $sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['aos']->table('region') ." WHERE region_type = '$type'";	
	$res = $GLOBALS['db']->getAll($sql);
    if($level == 0)
	{
		return $res;
	}
	else
	{
		foreach ($res AS $row)
    {
			$arr[$row['region_id']] = get_area_tree($row['region_id']);
			$arr = array_filter($arr);
		}
    return $arr;
	}				
}
function get_area_tree($parent_id = 0)
{
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['aos']->table('region') . " WHERE parent_id = '$parent_id'";
	if ($GLOBALS['db']->getOne($sql))
	{
		$sql = 'SELECT region_id, region_name FROM ' . $GLOBALS['aos']->table('region') ."WHERE parent_id = '$parent_id'";
    $res = $GLOBALS['db']->getAll($sql);
	}
   return $res;
}
?>