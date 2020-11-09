<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/3
 * Time: 14:29
 */
namespace app\miniprogram\controller;
class Goods
{
    public function index()
    {
        $id = input('id');
        $good = db('goods')->find($id);
        if(!is_http($good['pic']))
        {
            $good['pic'] = getWebUrl().'/uploads/'.str_replace('\\','/',$good['pic']);
        }
        $arr = ['good'=>$good];
        return json($arr);
    }
}