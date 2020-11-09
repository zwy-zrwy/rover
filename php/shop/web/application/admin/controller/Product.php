<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 18:02
 */

namespace app\admin\controller;
class Product extends Base
{
    public function index()
    {
       return $this->fetch();
    }
    public function ajax()
    {
            $param = input();
            $count = db('goods')->count();
            $data = db('goods')->alias('g')->field('g.*,c.name cname')->leftJoin('category c','g.cid=c.id')->page($param['page'],$param['limit'])->select();
            foreach($data as $key=>$val)
            {
                if(!is_http($data[$key]['pic']))
                {
                    $data[$key]['pic'] = getWebUrl().'/uploads/'.$data[$key]['pic'];
                }
            }
            $res = ['code'=>0,'msg'=>'ok','count'=>$count,'data'=>$data];
//            echo '<pre>';
//            print_r($res);die;
            $this->view->engine->layout(false);
            return json($res);
    }

    public function status()
    {
        $param = input();
        switch ($param['status'])
        {
            case 'false':$param['status'] = 0;break;
            default:$param['status'] =1;
        }
        $result = db('goods')->update($param);
        if($result)
        {
            $arr = ['code'=>0,'msg'=>'修改成功'];
        }
        else
        {
            $arr = ['code'=>1,'msg'=>'修改失败'];
        }
        return json($arr);
    }

    public function del()
    {
        $id = input('id');
        $result = db('goods')->delete($id);
        if($result)
        {
            $arr = ['code'=>0,'msg'=>'删除成功！'];
        }
        else
        {
            $arr = ['code'=>1,'msg'=>'删除失败'];
        }
        return json($arr);
    }

    public function delSelected()
    {
        if(request()->isPost())
        {
            $post = input();
            $ids = explode(',',$post['ids']);
            foreach($ids as $key=>$val)
            {
                db('goods')->delete($val);
            }
            $arr = ['code'=>0,'msg'=>'ok'];
            return json($arr);
        }
    }

    public function edit()
    {
        $param = input();
        $result = db('goods')->update($param);
        if($result)
        {
            $arr = ['code'=>0,'msg'=>'修改成功'];
        }
        else
        {
            $arr = ['code'=>1,'msg'=>'修改失败'];
        }
        return json($arr);
    }
}