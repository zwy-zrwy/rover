<?php
/*抽奖页面*/
if (!defined('IN_AOS'))
{
  die('Hacking attempt');
}
include_once(ROOT_PATH .'source/library/user.php');
include_once(ROOT_PATH .'source/library/order.php');
if (!empty($action) && $action == 'ajax')
{
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 0;
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
  $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
  $page = !empty($_POST['page'])?intval($_POST['page']):'0';
	$limit = " limit $last,$amount";//每次加载的个数
  $goodslist = get_assist_goods($status, $limit);
	foreach($goodslist['info'] as $val){
		$GLOBALS['smarty']->assign('page',$page);
    $GLOBALS['smarty']->assign('goods',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/assist_list.htm');
	}
	$res['count']=$goodslist['count'];
	die(json_encode($res));
}
if ($action == 'index')
{
  //$cache_id = sprintf('%X', crc32('assist'));
	  assign_template();
    $address_list = get_address_list($_SESSION['user_id']);
    foreach($address_list as $idx=>$value)
    {
      $area = explode(',',$value['area']);
  
      $area['country'] = 1;
      $area['province'] = get_region_name($area['0']);
      $area['city'] = get_region_name($area['1']);
      $area['district'] = get_region_name($area['2']);
      $address_list[$idx]['area']  = $area['province'].$area['city'].$area['district'];
      $shipping_info = shipping_area_info($area);
      if (empty($shipping_info))
      {
          $address_list[$idx]['off'] = 1;
      }
    }
    $smarty->assign('address_list',  $address_list);
    $share['title'] = $GLOBALS['_CFG']['assist_title'];
    $share['desc'] = $GLOBALS['_CFG']['assist_desc'];
    $share['imgUrl'] = AOS_HTTP.$_SERVER['HTTP_HOST'].'/'.$GLOBALS['_CFG']['directory'].'/uploads/images/'.$GLOBALS['_CFG']['wap_logo'];
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
    $smarty->display('assist.htm', $cache_id);
}
elseif ($action == 'address')
{
    include_once(ROOT_PATH . 'source/library/user.php');
    $address_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
    $consignee = get_address($address_id);
    
    $area = explode(',',$consignee['area']);
    $consignee['province_name'] = get_region_name($area['0']);
    $consignee['city_name'] = get_region_name($area['1']);
    $consignee['district_name'] = get_region_name($area['2']);

    $smarty->assign('consignee', $consignee);
    $smarty->display('assist_address.htm', $cache_id);
}
elseif ($action == 'act_address')
{
    include_once(ROOT_PATH . 'source/library/user.php');
    $address = array(
        'user_id'    => $user_id,
        'address_id' => 0,
        'area'    => isset($_POST['area'])   ? compile_str(trim($_POST['area']))    : '',
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        );

    if (update_address($address))
    {
        aos_header("Location: index.php?c=assist");
        exit;
    }
}
elseif ($action == 'assist_img_ajax')
{
  $act_id = !empty($_POST['act_id'])?intval($_POST['act_id']):'0';
  $assist_info = get_assist_info($act_id);
  if($assist_info['assist_sales']>=$assist_info['assist_number']){
      $res = '库存不足';
      die(json_encode($res));
  }
  $sql="select order_sn,pay_time,tuan_num,assist_num,order_id,tuan_status from ".$aos->table('order_info')." where extension_code = 'assist' and user_id =".$_SESSION['user_id']." and act_id = $act_id";
  $old_order=$db->getRow($sql);
  $now_time     = gmtime();
  if($old_order){
    $order_sn=$old_order['order_sn'];
    
    $old_time     =$old_order['pay_time']+24*60*60;
    
    if($old_order[assist_num]>=$old_order[tuan_num] || $old_order[tuan_status]==2){
      $res = '该产品已助力成功';
      die(json_encode($res));
    }
  }
  
  if(!empty($assist_info['goods_id']))
  {
    if($now_time<$old_time){
      $assist_img_file = ROOT_PATH.'uploads/assist_img/assist_'.$act_id.'_'.$user_id.'.jpg';
      if (is_readable($assist_img_file) == false) {
        $res = 0;
      }
      else
      {
        $res = 1;
      }
    }else{
      $res = 0;
    }
    
    die(json_encode($res));
  }
}
elseif ($action == 'done')
{
  include_once('source/library/order.php');
  $sql = "SELECT * FROM " . $GLOBALS['aos']->table('user_address') ." WHERE user_id = '".$_SESSION['user_id']."' AND address_id = '".intval($_REQUEST[address_id])."'";
  $consignee = $db->getRow($sql);
  $area = explode(',',$consignee['area']);
  $area['country'] = 1;
  $area['province'] = get_region_name($area['0']);
  $area['city'] = get_region_name($area['1']);
  $area['district'] = get_region_name($area['2']);
  $shipping_info = shipping_area_info($area);
  if (empty($shipping_info))
  {
      $result['error']=1;
      $result['message']='该区域暂不支持配送';
      die(json_encode($result));
  }
  //$bonus_order = flow_order_info();
  $act_id =intval($_REQUEST['act_id']);
  $order = array(
    'shipping_id'     => intval(2),
    'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,
    'postscript'      => trim($_POST['postscript']),
    'user_id'         => $_SESSION['user_id'],
    'add_time'        => gmtime(),
    'lastmodify'      => gmtime(),
    'order_status'    => 1,
    'shipping_status' => 0,
    'pay_status'      => 0,
    'act_id'          =>$act_id
  );

  /* 检查积分余额是否合法 */
  $user_id = $_SESSION['user_id'];
  $sql="select order_sn,pay_time,tuan_num,assist_num from ".$aos->table('order_info')." where extension_code = 'assist' and user_id = '$user_id' and act_id = $act_id order by order_id desc";
  $old_order=$db->getRow($sql);
  $order_sn=$old_order['order_sn'];
  if ($old_order)
  {
    $now_time     = gmtime();
    $old_time     =$old_order['pay_time']+24*60*60;
    if(($now_time<$old_time || $old_order[assist_num]>=$old_order[tuan_num]) && !empty($old_order[assist_num])){
      exit;
    }
    $sql = "SELECT a.assist_tuan_num,g.goods_id, g.goods_name, g.goods_sn, g.shop_price, g.goods_number, g.tuan_img, " .
      "g.market_price, g.shop_price, g.is_shipping, " .
      "g.shop_price * goods_number AS subtotal " .
      "FROM " . $GLOBALS['aos']->table('assist') .
      " as a left join ".$aos->table('goods')." as g on a.goods_id = g.goods_id WHERE a.assist_id = '" .intval($_REQUEST['act_id']). "' " ;
    $goods = $GLOBALS['db']->getRow($sql);
    $pay_time     = gmtime();
    if(empty($consignee)){
      $result['error']=1;
      $result['message']='请填写收货人';
      die(json_encode($result));
    }
    $pay_time     = gmtime();
    $consigneet=$consignee['consignee'];
    $area=$consignee['area'];
    $address=$consignee['address'];
    $mobile=$consignee['mobile'];
    $sql = "UPDATE ". $aos->table('order_info') ." SET consignee = '".$consigneet."', area = '".$area."', address = '".$address."', mobile = '".$mobile."', pay_time = '".$pay_time."', tuan_status = '1' WHERE order_sn='$order_sn'";
    $db->query($sql);
  }else{
    $now_time=gmtime();
    /* 订单中的商品 */
    //$cart_goods = cart_goods($flow_type);
    $sql = "SELECT a.assist_tuan_num,g.goods_id, g.goods_name, g.goods_sn, g.shop_price, g.goods_number, g.tuan_img, " .
            "g.market_price, g.shop_price, g.is_shipping, " .
            "g.shop_price * goods_number AS subtotal " .
            "FROM " . $GLOBALS['aos']->table('assist') .
            " as a left join ".$aos->table('goods')." as g on a.goods_id = g.goods_id WHERE a.assist_id = '" .intval($_REQUEST['act_id']). "' " .
            "AND a.assist_start_time < '$now_time' and a.assist_end_time > '$now_time'";

    $goods = $GLOBALS['db']->getRow($sql);
    if (empty($goods))
    {
      $result['error']=1;
      $result['message']='活动已结束';
      die(json_encode($result));
    }

    /* 订单中的总额 */
    $total = order_fee($order, $cart_goods, $consignee);
    $order['bonus']        = $total['bonus'];
    $order['goods_amount'] = $total['goods_price'];
    $order['discount']     = $total['discount'];
    $order['surplus']      = $total['surplus'];


    // 优惠券和积分最多能支付的金额为商品总额
    if ($order['goods_amount'] <= 0)
    {
        $order['bonus_id'] = 0;
    }

    /* 配送方式 */
    if ($order['shipping_id'] > 0)
    {
      $shipping = shipping_info($order['shipping_id']);
      $order['shipping_name'] = addslashes($shipping['shipping_name']);
    }
    $order['shipping_fee'] = $total['shipping_fee'];

    /* 收货人信息 */
      
    if(empty($consignee)){
      $result['error']=1;
      $result['message']='请填写收货人';
      die(json_encode($result));
    }
    foreach ($consignee as $key => $value)
    {
      $order[$key] = addslashes($value);
    }
    


    $order['pay_fee'] = $total['pay_fee'];
    $order['cod_fee'] = $total['cod_fee'];
    $order['order_amount']  = number_format($total['amount'], 2, '.', '');

    /* 如果订单金额为0（使用余额或积分或优惠券支付），修改订单状态为已确认、已付款 */
    
    $order['order_status'] = 1;
    $order['confirm_time'] = gmtime();
    $order['pay_status']   = 2;
    $order['pay_time']     = gmtime();
    $order['order_amount'] = 0;
    

    /* 记录扩展信息 */
    
    $order['extension_code'] = 'assist';
    $order['extension_id'] = get_order_sn();

    /* 插入订单表 */
    $error_no = 0;
    do
    {
      $order['order_sn'] = get_order_sn(); //获取新订单号
      $GLOBALS['db']->autoExecute($GLOBALS['aos']->table('order_info'), $order, 'INSERT');
      $error_no = $GLOBALS['db']->errno();
      if ($error_no > 0 && $error_no != 1062)
      {
        die($GLOBALS['db']->errorMsg());
      }
    }
    while($error_no == 1062); //如果是订单号重复则重新提交数据
    $new_order_id = $db->insert_id();
    $order_id=$order['order_id'] = $new_order_id;

    /* 插入订单商品 */
    $sql = "INSERT INTO " . $aos->table('order_goods') . "( " .
                "order_id, goods_id, goods_name, goods_sn, goods_number, market_price, ".
                "goods_price) ".
            " values ('$new_order_id', '$goods[goods_id]', '$goods[goods_name]', '$goods[goods_sn]', 1, '$goods[market_price]', ".
                "'$goods[goods_price]')";
    $db->query($sql);
    $order_sn=$order['order_sn'];
    $tuan_status = 1;
    
    $sql = "UPDATE ". $aos->table('order_info') ." SET tuan_status = ".$tuan_status.", tuan_num = ".$goods[assist_tuan_num]." WHERE order_id=".$order['order_id'];
    $db->query($sql);


        
  }
  $wx_url=$aos->url();
  $wx_url.="index.php?c=user&a=assist";
  $openid=getOpenid($_SESSION['user_id']);
  //$message=getMessage(13);
  $wx_title = "助力活动通知";
  $end_time=local_date($GLOBALS['_CFG']['time_format'], (gmtime()+24*3600));
  $wx_desc = "快邀请好友一起助力\r\n任务名称：助力享免单\r\n助力商品：".$goods[goods_name]."\r\n结束时间：".$end_time."\r\n".$message['note'];
  //$wx_pic = $aos_url;
  $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
  $good_img = $goods['tuan_img'];
  $goods_name = $goods['goods_name'];
  $name1 = mb_substr($goods_name,0,13,'utf-8');;
  $name2 = mb_substr($goods_name,13,13,'utf-8');
  $goods_price = '商品价&#xFFE5;'.$goods['shop_price'];
  $assist_price = '&#xFFE5;0';
  $avatar = 'uploads/avatar/avatar_'.$user_id.'.jpg';
  $expire = 86400;
  $qrcode = $wechat->getAssistCode($order_sn,$expire);
  $adtext = '快来和我一起抢0元秒杀吧！';
  $codetext = '长按二维码查看';
  $nickname = $db->getOne('select nickname from '.$aos->table('users').' where user_id = ' . $user_id);

  $font_file = ROOT_PATH . 'data/font/simhei.ttf';//字体
  $fx_img    = ROOT_PATH . 'uploads/images/assist_bg.jpg';//背景图

  //背景图
  $is_very = file_get_contents($fx_img);
  if(strlen($is_very) < 1)
  {
  return false;  
  }
  $QR = imagecreatefromstring($is_very); 

  $wx_goods = imagecreatefromstring(file_get_contents($good_img));
  $wx_qrcode = imagecreatefromstring(file_get_contents($qrcode));
  $wx_avatar = imagecreatefromstring(file_get_contents($avatar)); 


  //字体颜色
  $white = imagecolorallocate($QR, 255, 255, 255);
  $red = imagecolorallocate($QR, 255, 0, 0);
  $black = imagecolorallocate($QR, 0, 0, 0);
  $gray = imagecolorallocate($QR, 128,138,135);

  $QR_width    = imagesx($QR);//背景图宽度 
  $QR_height   = imagesy($QR);//背景图高度 

  $wx_goods_width  = imagesx($wx_goods);
  $wx_goods_height = imagesy($wx_goods);

  $wx_qrcode_width  = imagesx($wx_qrcode);
  $wx_qrcode_height = imagesy($wx_qrcode);

  $wx_avatar_width  = imagesx($wx_avatar);
  $wx_avatar_height = imagesy($wx_avatar);

  //载入图片
  imagecopyresampled($QR, $wx_goods, 0, 0, 0, 0, 590, 369, $wx_goods_width, $wx_goods_height);
  imagecopyresampled($QR, $wx_avatar, 20, 390, 0, 0, 56, 56, $wx_avatar_width, $wx_avatar_height);
  imagecopyresampled($QR, $wx_qrcode, 380, 450, 0, 0, 200, 200, $wx_qrcode_width, $wx_qrcode_height);

  //载入字体
  imagefttext($QR , 18, 0, 86, 430, $black, $font_file, mb_convert_encoding($nickname, 'html-entities', 'UTF-8'));
  imagefttext($QR , 18, 0, 400, 430, $gray, $font_file, mb_convert_encoding($codetext, 'html-entities', 'UTF-8'));
  imagefttext($QR , 20, 0, 20, 490, $black, $font_file, mb_convert_encoding($name1, 'html-entities', 'UTF-8'));
  imagefttext($QR , 20, 0, 20, 526, $black, $font_file, mb_convert_encoding($name2, 'html-entities', 'UTF-8'));
  imagefttext($QR , 22, 0, 20, 580, $red, $font_file, mb_convert_encoding($adtext, 'html-entities', 'UTF-8'));
  imagefttext($QR , 24, 0, 20, 640, $red, $font_file, mb_convert_encoding($assist_price, 'html-entities', 'UTF-8'));
  imagefttext($QR , 20, 0, 90, 640, $gray, $font_file, mb_convert_encoding($goods_price, 'html-entities', 'UTF-8'));

  //输出图片 
  //header("Content-type: image/jpeg");
  //imagejpeg($QR);
  $path   = ROOT_PATH . 'uploads/assist_img/assist_'.$act_id.'_'.$user_id.'.jpg';

  $assist_img = imagejpeg($QR,$path);
  //销毁资源 
  imagedestroy($QR);

  $result['error']=0;
  $result['goods']=$goods;
  $result['order_id']=$order_id;
  $result['assist_img']='uploads/assist_img/assist_'.$act_id.'_'.$user_id.'.jpg';
  die(json_encode($result));
}

/*获得助力商品列表*/
function get_assist_goods($status, $limit = 1)
{
	$now_time = gmtime();
	$where = "$now_time < a.assist_end_time AND $now_time > a.assist_start_time";
  /* 获得商品列表 */
  $sql='SELECT count(*) FROM ' . $GLOBALS['aos']->table('assist') . ' as a ' .
    "WHERE $where ";
  $count=$GLOBALS['db']->getOne($sql);
  $sql="select a.*,g.goods_name,g.goods_img,g.shop_price from ". $GLOBALS['aos']->table('assist') ." as a ".
  "LEFT JOIN " .$GLOBALS['aos']->table('goods'). " AS g ON a.goods_id = g.goods_id WHERE $where $limit ";
  $result = $GLOBALS['db']->getAll($sql);
  $res=array();
	foreach ($result AS $idx => $row)
	{
		$goods[$idx]['assist_id']         = $row['assist_id'];
		$goods[$idx]['goods_id']           = $row['goods_id'];
		$goods[$idx]['goods_name']         = $row['goods_name'];
		$goods[$idx]['goods_img']    = get_image_path($row['goods_id'], $row['goods_img']);
		$goods[$idx]['assist_start_time'] = local_date('Y-m-d H:i:s', $row['assist_start_time']);
    $goods[$idx]['assist_end_time'] = local_date('Y-m-d H:i:s', $row['assist_end_time']);
    $goods[$idx]['shop_price']         = price_format($row['shop_price']);
    $goods[$idx]['assist_tuan_num']    = $row['assist_tuan_num'];
    if($now_time < $row['assist_start_time'])//未开始
    {
    	$goods[$idx]['status']  = 2;
    }
    else
    {
    	if($now_time < $row['assist_end_time'])
      {
      	$goods[$idx]['status']  = 1;
      }
      else
      {
      	if($goods[$idx]['assist_status'] == 1)
      	{
      		$goods[$idx]['status']  = 4;
      	}
      	else
      	{
      		$goods[$idx]['status']  = 3;
      	}
      }
    }
	}
	$res['info'] = array_sort($goods,'status','asc');
	$res['count'] = $count;
  return $res;
}

/*获得助力商品详情*/
function get_assist_info($act_id)
{
  $now_time=gmtime();
  $sql = "SELECT a.*,g.goods_id, g.goods_name, g.goods_sn, g.shop_price, g.goods_number, g.tuan_img, " .
          "g.market_price, g.shop_price, g.is_shipping, " .
          "g.shop_price * goods_number AS subtotal " .
          "FROM " . $GLOBALS['aos']->table('assist') .
          " as a left join ".$GLOBALS['aos']->table('goods')." as g on a.goods_id = g.goods_id WHERE a.assist_id = '" .$act_id. "' " .
          "AND a.assist_start_time < '$now_time' and a.assist_end_time > '$now_time'";
  $result = $GLOBALS['db']->getRow($sql);
  return $result;
}
?>