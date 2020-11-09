<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/26
 * Time: 12:07
 */
namespace app\admin\controller;
class Wxmenu extends Base
{
    public function index()
    {
        $data = db('wxmenu')->where('status',1)->where('pid',0)->select();
        foreach($data as $key=>$val)
        {
            $data[$key]['sub'] = db('wxmenu')->where('status',1)->where('pid',$val['id'])->select();
        }
        $this->assign('data',$data);
        return $this->fetch();
    }

    public  function add()
    {
        if(request()->isPost())
        {
            $post = input();
            $res = db('wxmenu')->insert($post);
            if($res)
            {
                $this->success('添加成功','add');
            }
            else
            {
                $this->erro('添加失败');
            }
        }
        else
        {
            $data = db('wxmenu')->where('status',1)->where('pid',0)->select();
            $this->assign('data',$data);
            return $this->fetch();
        }
    }

    public function create()
    {
        $menu_arr = [];
        $list = db('wxmenu')->where('pid',0)->where('status',1)->select();
        foreach($list as $key=>$val)
        {
            $menu_arr[$key]['name'] = $val['name'];
            $menu_arr[$key]['type'] = $val['type'];
            if($val['type'] == 'click')
            {
                $menu_arr[$key]['key'] = $val['key'];
            }
            else
            {
                $menu_arr[$key]['url'] = $val['url'];
            }
            $sub_button = db('wxmenu')->where('pid',$val['id'])->where('status',1)->select();
            foreach($sub_button as $k=>$v)
            {
                $menu_arr[$key]['sub_button'][$k]['name'] = $v['name'];
                $menu_arr[$key]['sub_button'][$k]['type'] = $v['type'];
                if($v['type'] == 'click')
                {
                    $menu_arr[$key]['sub_button'][$k]['key'] = $v['key'];
                }
                else
                {
                    $menu_arr[$key]['sub_button'][$k]['url'] = $v['url'];
                }
            }
        }
        $menu['button'] =$menu_arr;
        $json = json_encode($menu,JSON_UNESCAPED_UNICODE);
        $access_token = getAccessToken();
        $menu_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $res = curl_post($menu_url,$json);
        $res = json_decode($res,1);
        if($res['errcode'] == 0)
        {
            $this->success('自定义菜单创建成功！');
        }
    }
}