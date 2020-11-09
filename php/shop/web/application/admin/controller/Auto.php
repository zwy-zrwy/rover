<?php
namespace app\admin\controller;
class Auto
{
	public function index()
	{
		set_time_limit(0);
//		$result = $this->caiji();
//		$pages = ceil($result['data']['total_num'] /50);
//		for($i = 2;$i<=$pages;$i++)
//		{
//			sleep(10);
//			$this->caiji($i);
//		}
//        $this->caiji();
	}
	public function caiji($page = 1)
	{
		$url = 'http://api.dataoke.com/index.php?r=Port/index&type=total&appkey=5f619d74fc&v=2&page='.$page;
		$json = file_get_contents($url);
		$arr = json_decode($json,1);
		foreach($arr['result'] as $key=>$val)
		{
		    if(is_http($val['Pic'])) {

                $insert[$key]['pic'] = $val['Pic'];
                $insert[$key]['name'] = $val['D_title'];
                $insert[$key]['content'] = $val['Introduce'];
                $insert[$key]['price'] = $val['Price'];
                $insert[$key]['number'] = $val['Quan_surplus'];

                $insert[$key]['brand_id'] = rand(1, 3);

                $rand = [2, 3, 4, 12, 13, 14];
                $k = array_rand($rand, 1);
                $insert[$key]['cid'] = $rand[$k];

                $insert[$key]['status'] = 1;

                $insert[$key]['is_attr'] = 0;
            }
		}
		db('goods')->insertAll($insert);
		return $arr;
	}
}