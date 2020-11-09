<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/14
 * Time: 15:58
 */

namespace app\index\controller;
use think\Controller;
class Goods extends Controller
{
    public function index()
    {
        $id = input('id');
        $info = db('goods')->find($id);
        $pics = db('goods_photo')->where('gid',$id)->select();
        $sku = db('sku')->field('s.*,st.name')->alias('s')->leftJoin('sttr st','st.id = s.attr_id')->where('s.gid',$id)->select();
        $this->assign(['info'=>$info,'pics'=>$pics,'sku'=>$sku]);
        return $this->fetch();
    }
}