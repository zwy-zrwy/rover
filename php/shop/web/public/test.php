<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/23
 * Time: 17:51
 */
//创建对象
$redis = new redis();
//连接redis服务器
$redis->connect('127.0.0.1',6379);
//选择数据路
$redis->select(0);
//添加数据
$name = $redis->get('name');
echo $name;
?>