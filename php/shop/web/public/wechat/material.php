<?php
    $access_token = '27_vmdlrWSQlw7MfghACsheAWqelhgQlFF4C2RCjMHkgvRlbZ2F-TWSZY1r0PH5mtHG8YchBXnH-Y7bwLn9VsdUWqmRgPRnOcKRMBioycRdrv85TQm4y8JdCLFiRWfTJFXTkrQsdDJRQei_Q6LfMYJaABAQCN';
//    $type = 'video';
//    //新增临时素材
//    $material_url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$type;
//    //本地文件地址
//    $pic_path = dirname(__FILE__).'/E135_material.mp4';
//
//    $pic_get_path = new CurlFile($pic_path);
//    $pic_data = array('media'=>$pic_get_path);
//    $res = curl_post($material_url,$pic_data);
//    echo $res;
    //素材ID
    $image = 'zcPJ9kmSQFdb9GEBGVA8cHF2LK8MBBPRQPLlovTpRR7Eu7ZTe6OpnkdrooP79qSw';
    $voice = 'zzpm68MPk-H8CtzlVWSWXZtVVLa1plR7f-PATk_w4Kzv3i9B126vC-KWRyHxZOdV';
    $video = 'T_DyDsPXxpCUemHAt6MyWX-P5BWeDJ4LVzV3yzNdHk-Mnfd1rVcU2sY88TWPxzY3';
//    $get_material_url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$video;
//    $result = curl_get($get_material_url);
//    echo $result;
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
        //curl_setopt($curl, CURLOPT_SAFE_UPLOAD, TRUE);
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
?>