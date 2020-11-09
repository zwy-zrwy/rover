<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/2
 * Time: 16:08
 */
namespace app\miniprogram\controller;
class Data
{
    public function index()
    {
        $post = input();
        $goods = db('goods')->where('status',1)->page($post['page'],$post['size'])->select();
        foreach($goods as $key=>$val)
        {
            if(!is_http($goods[$key]['pic']))
            {
                $goods[$key]['pic'] = getWebUrl().'/uploads/'.str_replace('\\','/',$val['pic']);
            }
        }
        $arr = ['goods'=>$goods];
        return json($arr);
    }
}