<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/2
 * Time: 16:08
 */
namespace app\miniprogram\controller;
class Category
{
    public function index()
    {
        $post = input();
        $id = $post['id'];
        $ids = getSubIds($id);
        $goods = db('goods')->where('cid','in',$ids)->page($post['page'],$post['size'])->select();
        foreach($goods as $key=>$val)
        {
            if(!is_http($goods[$key]['pic']))
            {
                $goods[$key]['pic'] = getWebUrl().'/uploads/'.str_replace('\\','/',$goods[$key]['pic']);
            }
        }

        $cate = db('category')->where('pid',$id)->select();
//        $cate = array_column($cate,NULL,'pid');
        if(empty($cate))
        {
            $pid = db('category')->where('id',$id)->value('pid');
            $cate = db('category')->where('pid',$pid)->select();
        }
        foreach($cate as $key=>$val)
        {
            if(!is_http($cate[$key]['pic']))
            {
                $cate[$key]['pic'] = getWebUrl().'/uploads/'.str_replace('\\','/',$cate[$key]['pic']);
            }
        }
        $arr = ['cate'=>$cate,'goods'=>$goods];
        return json($arr);
    }
}