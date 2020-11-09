<?php
namespace app\index\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        $data = db('category')->where('status',1)->select();
        $cate = get_children($data);
        $this->assign('cate',$cate);

        $goods = db('goods')->select();
        $list = db('goods')->alias('g')->field('g.*,ca.name cname')->leftJoin('category c','c.id =g.cid')->leftJoin('category ca','c.pid = ca.id')->where('g.status',1)->select();
        $floor = db('category')->where('status',1)->where('pid',0)->select();
        foreach($floor as $key=>$val)
        {
            $floor[$key]['sub'] = db('category')->where('status',1)->where('pid',$val['id'])->select();
            $floor[$key]['goods'] = db('goods')->where('cid','in',\getSubIds($val['id']))->select();
            foreach($floor[$key]['goods'] as $k=>$v)
            {
                if( !is_http($floor[$key]['goods'][$k]['pic']))
                {
                    $floor[$key]['goods'][$k]['pic'] = '/uploads/'.$floor[$key]['goods'][$k]['pic'];
                }
            }
        }
//         echo '<pre>';
//        print_r($floor);die;
        $this->assign('floor',$floor);
        return $this->fetch();
    }
    public function login()
    {
        session('user_id',1);
        session('username','user');
        $this->success('登录成功','index');
    }
}
