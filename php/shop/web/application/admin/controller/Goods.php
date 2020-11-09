<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 18:02
 */

namespace app\admin\controller;
class Goods extends Base
{
    public function index()
    {
        $data = db('goods')->alias('g')->field('g.*,c.name cname,b.name bname')->leftJoin('category c','g.cid = c.id')->leftJoin('brand b','g.brand_id = b.id')->paginate(10)->each(function($item, $key){
            if(!is_http($item['pic']))
            {
                $item['pic'] = '/uploads/'.$item['pic'];
            }
            return $item;
        });
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            if($_FILES['pic']['tmp_name'])
            {
                $post['pic'] = upload();
            }
            if($post['is_attr'])
            {
                foreach($post['prices'] as $key=>$val){
                    $sku[$key]['price'] = $val;
                    $sku[$key]['num'] = $post['nums'][$key];
                    $sku[$key]['attr_id'] = $key;
                }
            }
            unset($post['prices']);
            unset($post['nums']);
            $gid = db('goods')->insertGetId($post);
            if($gid)
            {
                if($_FILES['pics']['tmp_name'][0])
                {
                    $pics = uploads();
                    foreach($pics as $key=>$val)
                    {
                        $arr[$key]['pic'] = $val;
                        $arr[$key]['gid'] = $gid;
                    }
                    $res = db('goods_photo')->insertAll($arr);
                }
                if($post['is_attr'])
                {
                    foreach($sku as $key=>$val)
                    {
                        $sku[$key]['gid'] = $gid;
                    }
                    db('sku')->insertAll($sku);
                }
                $this->success('添加成功','index');
            }
            else
            {
                $this->error('添加失败');
            }
        }
        else
        {
            $cat = db('category')->where('status',1)->select();
            $cat = get_children($cat);
            $brand = db('brand')->where('status',1)->select();
            $sttr = db('sttr')->select();
            $this->assign(['cat'=>$cat,'brand'=>$brand,'sttr'=>$sttr]);
            return $this->fetch();
        }
    }
    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            if($_FILES['pic']['tmp_name'])
            {
                $post['pic'] = upload();
            }
            if($post['is_attr'])
            {
                foreach($post['prices'] as $key=>$val){
                    $sku[$key]['price'] = $val;
                    $sku[$key]['num'] = $post['nums'][$key];
                }
            }
            unset($post['prices']);
            unset($post['nums']);
            $result = db('goods')->update($post);
            if($result)
            {
                if($_FILES['pics']['tmp_name'][0])
                {
                    $pics = uploads();
                    foreach($pics as $key=>$val)
                    {
                        $arr[$key]['pic'] = $val;
                        $arr[$key]['gid'] = $post['id'];
                    }

                    $res = db('goods_photo')->insertAll($arr);
                }
                if($post['is_attr'])
                {
                    foreach($sku as $key=>$val)
                    {
                        $sku[$key]['id'] = $key;
                        $sku[$key]['gid'] = $post['id'];
                    }
                    foreach($sku as $key=>$val)
                    {
                        db('sku')->update($sku[$key]);
                    }
                }
                $this->success('修改成功','admin/goods/index');
            }
            else
            {
                $this->error('修改失败');
            }
        }
        else
        {
            $id = input('id');
            $info = db('goods')->find($id);
            $pics = db('goods_photo')->where('gid',$id)->select();
            $cat = db('category')->where('status',1)->select();
            $cat = get_children($cat);
            $brand = db('brand')->where('status',1)->select();
            $sku = db('sku')->field('s.*,st.name')->alias('s')->leftJoin('sttr st','st.id = s.attr_id')->where('s.gid',$id)->select();
            $this->assign(['cat'=>$cat,'info'=>$info,'pics'=>$pics,'brand'=>$brand,'sku'=>$sku]);
            return $this->fetch();
        }
    }

    public function del()
    {
        $id = input('id');
        $pic = db('goods')->where('id',$id)->value('pic');
        $pics = db('goods_photo')->where('gid',$id)->select();
        $res1 = db('goods')->delete($id);
        $res2 = db('goods_photo')->where('gid',$id)->delete();
        unlinkA($pic);
        foreach($pics as $val)
        {
            unlinkA($val['pic']);
        }
        if($res1 && $res2)
        {
            $this->success('删除成功','admin/goods/index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}