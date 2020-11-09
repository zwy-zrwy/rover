<?php

/*团详情*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
include_once(ROOT_PATH . 'source/library/order.php');

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

$tuan_id = isset($_REQUEST['tuan_id'])  ? trim($_REQUEST['tuan_id']) : 0;

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */


        
    $smarty->assign('tuan_id',           $tuan_id);
    $smarty->assign('id',           $tuan_id);
    
    $smarty->assign('cfg',          $_CFG);


    /* 获得商品的信息 */
    $tuan = get_tuan_info($tuan_id);
    $type = $tuan[extension_code];
    $act_id=$tuan[act_id];

    $smarty->assign('type',              $type);
    $smarty->assign('act_id',           $act_id);
    if ($tuan === false)
    {
        /* 如果没有找到任何记录则跳回到首页 */
        aos_header("Location: ./\n");
        exit;
    }
    else
    {
        
        $smarty->assign('tuan',              $tuan);

        assign_template();




        if($type == 'lottery')
        {
          $lott_attr = get_lott_attr($act_id);
          if(!empty($lott_attr)){
            $smarty->assign('goods_sku', get_sku_list($lott_attr,1));
          }
        }
        else
        {
            $smarty->assign('goods_sku', get_sku_list($tuan['goods_id']));
        }
        


        $tuan_price_list = get_tuan_price_list($tuan['goods_id']);
        $tuan_price = array_column($tuan_price_list,'price');

        $tuan_price_num = COUNT($tuan_price_list);
        
        $smarty->assign('tuan_price_list',$tuan_price_list);
        $smarty->assign('tuan_price_num',$tuan_price_num);

        if($type == 'miao')
        {
            $tuan_price = get_miao_price($act_id);
        }
        elseif($type == 'lottery')
        {
            $tuan_price = get_lott_price($act_id);
        }
        else
        {
            $tuan_price = max($tuan_price);
        }


        $smarty->assign('tuan_price',$tuan_price);

        $smarty->assign('tuan_price_formated',price_format($tuan_price));

        $tuan_mem = get_tuan_mem($tuan_id);//参团的人
		$smarty->assign('tuan_mem',              $tuan_mem);


        $tuan_mem_num = count($tuan_mem);//参团人数

        $difference = $tuan['tuan_num'] - $tuan_mem_num; //还差
        $smarty->assign('tuan_mem_num',              $tuan_mem_num);
        $smarty->assign('difference',              $difference);
        /*循环空白图像*/
        $d_num_arr=array();
        for($i=0;$i<$difference;$i++){
            $d_num_arr[]=$i;
        }
        $smarty->assign('d_num_arr', $d_num_arr);
        if($tuan['tuan_status']==1){
            if($tuan['extension_code']=='lottery')
            {
                $sql= "SELECT lottery_tuan_num from ".$GLOBALS['aos']->table('lottery')." where lottery_id=$act_id";
                $lottery_tuan_num = $GLOBALS['db']->getOne($sql);
                if($tuan_mem_num >= $lottery_tuan_num)
                {
                    cheng_tuan($tuan_id);
                    echo "<script>location.replace(location.href);</script>";
                    exit;

                }
            }
            if($tuan['extension_code']=='miao')
            {
                $sql= "SELECT seck_tuan_num from ".$GLOBALS['aos']->table('seckill')." where seckill_id=$act_id";
                $seck_tuan_num = $GLOBALS['db']->getOne($sql);
                if($tuan_mem_num >= $seck_tuan_num)
                {
                    cheng_tuan($tuan_id);
                    echo "<script>location.replace(location.href);</script>";
                    exit;

                }
            }

    		//print_r($difference);

            /*阶梯团更新团购人数*/
            if($tuan['extension_code']=='tuan'){
                $tuan_num = get_tuan_number($tuan['goods_id']);
                $max_tuan_num = max($tuan_num);
                
                if($tuan_mem_num >= $max_tuan_num){
                    cheng_tuan($tuan_id);
                }
                if($tuan_mem_num >= $tuan['tuan_num'])
                {

                    /*$new_tuan_num = array_filter($tuan_num, function($tuan_num) use($tuan_mem_num){return $tuan_mem_num < $tuan_num;});
                    sort($new_tuan_num);
                    $new_tuan_num = $new_tuan_num[0];*/
                    foreach($tuan_num as $key=>$vo){

                        $numb="$vo";

                        if($tuan_mem_num < $numb){
                            $new_tuan['tuan_number']=$numb;
                            break;
                        }
                        /*if($tuan_mem_num == $vo['tuan_number']){
                            $new_tuan['tuan_number']=$vo['tuan_number'];
                            break;
                        }
                        if($tuan_mem_num > $vo['tuan_number'] && $max_tuan_num==$vo['tuan_number']){
                            $new_tuan['tuan_number']=$vo['tuan_number'];
                            break;
                        }*/
                        
                    }
                    if($new_tuan['tuan_number']){
                      $db->query('UPDATE ' . $aos->table('order_info')." SET tuan_num = ".$new_tuan['tuan_number']." WHERE extension_id = '$tuan_id'");
                      echo "<script>location.replace(location.href);</script>";
                    exit; 
                    }
                        
                    
                }
                
            }
        }

        //用户是否参团
        $sql="SELECT COUNT(*) from ".$aos->table('order_info')." WHERE extension_id = ".$tuan_id." AND pay_status = 2 AND user_id = ".$_SESSION['user_id'];
        $in_tuan = $db->getOne($sql);
        $smarty->assign('in_tuan',              $in_tuan);
    }

if($type =='miao')
{
    $act_name = '秒杀';
}
elseif($type =='lottery')
{
    $act_name = '抽奖';
}
else
{
    $act_name = '拼团';
}

  
$smarty->assign('rand_goods',              rand_goods($tuan['goods_id']));

//$tuantime = date("Y-m-d H:i:s",($tuan['time']+$_CFG['tuan_time']*3600));
$tuantime =local_date($GLOBALS['_CFG']['time_format'], ($tuan['time']+$_CFG['tuan_time']*3600));

$suc_tuan_time =local_date($GLOBALS['_CFG']['time_format'], $tuan['suc_tuan_time']);

$smarty->assign('tuantime',              $tuantime);
$smarty->assign('suc_tuan_time',              $suc_tuan_time);
$aos_url = substr($aos->url(), 0, -1);
$req_url = $_SERVER['REQUEST_URI'];
$smarty->assign('aos_url',  $aos_url);
$smarty->assign('req_url',  $req_url);
$smarty->assign('now_time',  gmtime()); 

$share['title'] = '【还差'.$difference.'人】我参加了“'.$act_name.'“'.$tuan['goods_name'];
$share['desc'] = $GLOBALS['_CFG']['share_desc'];
$imgUrl = get_share_img($tuan['goods_id']);
$share['imgUrl'] = $aos->url().$imgUrl;
$share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$smarty->assign('share',       $share);


$smarty->display('share.htm');

function get_tuan_info($tuan_id)
{
    $sql = "SELECT order_id, pay_time, tuan_status, tuan_num,extension_code,act_id,suc_tuan_time FROM " . $GLOBALS['aos']->table('order_info') .
                " WHERE extension_id = $tuan_id and tuan_first = 1";
    $tuan = $GLOBALS['db']->getRow($sql);
    if ($tuan)
    {
		$order_goods = order_goods($tuan['order_id']);
		
		
		$tuan['goods_id'] = $order_goods['goods_id'];
		$tuan['goods_name'] = $order_goods['goods_name'];
		$tuan['goods_img'] = get_goods_img($order_goods['goods_id']);
		$tuan['goods_price'] = price_format($order_goods['goods_price']);
		$tuan['time'] = $tuan['pay_time'];
    }

    return $tuan;
}
function get_miao_price($act_id)
{
    $sql = "SELECT seck_price FROM " . $GLOBALS['aos']->table('seckill') .
                " WHERE seckill_id = $act_id";
    return $GLOBALS['db']->getOne($sql);
}
function get_lott_price($act_id)
{
    $sql = "SELECT lottery_price FROM " . $GLOBALS['aos']->table('lottery') .
                " WHERE lottery_id = $act_id";
    return $GLOBALS['db']->getOne($sql);
}
function get_lott_attr($act_id)
{
    $sql = "SELECT goods_attr FROM " . $GLOBALS['aos']->table('lottery') .
                " WHERE lottery_id = $act_id";
    return $GLOBALS['db']->getOne($sql);
}
?>