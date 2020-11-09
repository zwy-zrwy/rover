<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
use think\facade\Env;
    function get_children($data,$pid='pid',$keyword='sub',$fields=[])
    {
        $tree=[];
        foreach($data as $key=>$val)
        {
            if($val['pid'] == $pid)
            {
                $tree[$key]['id'] = $val['id'];
                $tree[$key]['name'] = $val['name'];
                if(!empty($fields))
                {
                    foreach($fields as $k=>$v)
                    {
                        $tree[$key][$v] = $val[$v];
                    }
                }
                $tree[$key][$keyword] = get_children($data,$val['id'],'sub',$fields);
            }
        }
        return $tree;
    }

    function upload($img='pic'){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($img);
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( 'uploads');
        if($info){
            return $info->getSaveName();
        }else{
            return $file->getError();
        }
    }

    function uploads($img='pics'){

    // 获取表单上传文件
        $files = request()->file($img);
        foreach($files as $file){
            // 移动到框架应用根目录/uploads/ 目录下
            $info = $file->move( 'uploads');
            if($info){
                $pics [] =  $info->getSaveName();
            }else{
                // 上传失败获取错误信息
                $pics = $file->getError();
            }
        }
        return $pics;
    }

    function is_http($url)
    {
        $preg = "/^http(s)?:\\/\\/.+/";
        if(preg_match($preg,$url))return true;
        return false;
    }

    function unlinkA($pic)
    {
        @unlink(Env::get('root_path').'public/uploads/'.$pic);
    }

    //执行函数
    function getSubIds($id = 0){
        //取出所有分类
        $data = db('category')->field('id,pid')->select();
        $res = get_all_child($data,$id);
        $res[] = $id;
        return $res;
    }
    //递归数据
    function get_all_child($array,$id){
        $arr = array();
        foreach($array as $v){
            if($v['pid'] == $id){
                $arr[] = $v['id'];
                $arr = array_merge($arr,get_all_child($array,$v['id']));
            }
        }
        return $arr;
    }

    function getGoodsPic($goods_id)
    {
        return '/uploads/'.db('goods')->where('id',$goods_id)->value('pic');
    }

    function getOrderGoods($oid)
    {
        $goods = db('order_goods')->where('order_id',$oid)->select();
        foreach($goods as $key=>$val)
        {
            $goods[$key]['pic'] = getGoodsPic($val['goods_id']);
            $goods[$key]['goods_sku'] = getSkuName($val['goods_sku']);
        }
        return $goods;
    }
    function getSkuName($id)
    {
        $sku = db('sku')->where('id',$id)->value('attr_id');
        return db('sttr')->where('id',$sku)->value('name');
    }

    function orderInfo($id)
    {
        $order = db('order_info')->where('id',$id)->find();
        $order['goods'] = getOrderGoods($order['id']);
        return $order;
    }

    function getWxconfig()
    {
        return db('wxconfig')->find(1);
    }

    function ask($key)
    {
        $host = "https://jisuiqa.market.alicloudapi.com";
        $path = "/iqa/query";
        $method = "GET";
        $appcode = "7e81a3b0fc0c4c80b17c39083f12f089";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "question=".$key;
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $res =  curl_exec($curl);
        return json_decode($res);
    }

    function curl_get($url)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    function curl_post($url,$post_data)
    {
        //初始化
        $curl = curl_init();

        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    function getAccessToken()
    {
        return db('wxconfig')->where('id',1)->value('access_token');
    }

    function getWebUrl()
    {
        return request()->scheme().'://'.request()->host();
    }

    function uploadMaterial($type,$pic_path)
    {
        $access_token = getAccessToken();
        $material_url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$type;
        $pic_get_path = new CurlFile($pic_path);
        $pic_data = array('media'=>$pic_get_path);
        $json = curl_post($material_url,$pic_data);
        $res = json_decode($json,1);
        return $res['media_id'];
    }
    //网页认证AccessToken
    function authAccessToken($appid,$appsecret,$code)
    {
        $url ='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
        $json = curl_get($url);
        $arr = json_decode($json,1);
        return $arr;
    }
    //网页认证拉取用户信息
    function authUser($token,$openid)
    {
        $url= 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';
        $json = curl_get($url);
        $arr = json_decode($json,1);
        return $arr;
    }

    function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        } else {
            return false;
        }
    }
    function GouldAddress($locations)
    {
        $locat = convert($locations);
        //将转换好的高德坐标通过高德地图逆地理编码转码成详细地址
        $url = 'https://restapi.amap.com/v3/geocode/regeo?parameters&key=58d764e7a0b57893e079a67414dc9d09&location='.$locat;
        $json = curl_get($url);
        $arr = json_decode($json,1);
        $address = $arr['regeocode']['addressComponent']['building']['name'];
        if(empty($address))
        {
            $address = $arr['regeocode']['formatted_address'];
        }
        return $address;
    }
//转换高德坐标
    function convert($locations)
    {
        $url = 'https://restapi.amap.com/v3/assistant/coordinate/convert?parameters&key=58d764e7a0b57893e079a67414dc9d09&coordsys=gps&locations='.$locations;
        $json = curl_get($url);
        $arr = json_decode($json,1);
        return $arr['locations'];
    }

    function getTittle($id)
    {
        return db('goods')->where('id',$id)->value('name');
    }
    function getFansOpenids()
    {
        $access_token = getAccessToken();
        $fans_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token;
        $data = curl_get($fans_url);
        $fans = json_decode($data,1);
//        echo '<pre>';
//        print_r($fans);die;
        return $fans['data']['openid'];
    }
    function sendMsg($url,$arr)
    {
        $post = '{"touser":"'.$arr['openid'].'","msgtype":"news","news":{"articles": [{"title":"'.$arr['title'].'","description":"'.$arr['desc'].'","url":"'.$arr['url'].'","picurl":"'.$arr['pic'].'"}]}}';
        $res = curl_post($url,$post);
        return json_decode($res,1);
    }

    function CartGoods($uid)
    {
        $data = db('cart')->where('user_id',$uid)->select();
        foreach($data as $key=>$val)
        {
            $pic = db('goods')->where('id',$data[$key]['goods_id'])->value('pic');
            if(!is_http($pic))
            {
                $data[$key]['pic'] = getWebUrl().'/uploads/'.str_replace('\\','/',$pic);
            }
        }
        $data = array_column($data,NULL,'id');
        return $data;
    }

    function encryptDecrypt($key, $string, $decrypt){
        if($decrypt){
            $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "12");
            return $decrypted;
        }else{
            $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
            return $encrypted;
        }
    }
