<?php
/*会员中心*/
if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

include_once(ROOT_PATH . '/source/class/image.class.php');
$image = new cls_image($_CFG['bgcolor']);
include_once(ROOT_PATH .'source/library/user.php');
$user_id = $_SESSION['user_id'];
assign_template();
$smarty->assign('data_dir',   DATA_DIR);   // 数据目录
//用户中心欢迎页
if ($action == 'index')
{
    include_once(ROOT_PATH .'source/library/order.php');
    $info = get_user_default($user_id);
    $sql="select rank_id,rank_name,max_points from ".$aos->table('user_rank')." where min_points <= '$info[rank_points]' and max_points>='$info[rank_points]'";
    $rank=$db->getRow($sql);
    $rank['cha_point']=$rank['max_points']-$info['rank_points']+1;
    $rank['next_rank']=$rank['rank_id']+1;
    $smarty->assign('info',        $info);
    $smarty->assign('rank',$rank);
    $cur_date = gmtime();
    $sql = "SELECT count(bonus_sn) ".
           " FROM " .$GLOBALS['aos']->table('user_bonus')." AS u ,".
           $GLOBALS['aos']->table('bonus_type'). " AS b".
           " WHERE u.bonus_type_id = b.type_id AND u.user_id = '$user_id' AND u.order_id = 0 AND $cur_date < b.use_end_date ";
    $num['bonus']=$GLOBALS['db']->getOne($sql);


    //用户订单统计
    //待付款
    $num['await_pay'] = get_order_num($user_id,'await_pay');
    //待成团
    $num['await_tuan'] = get_order_num($user_id,'await_tuan');
    //待核销
    $num['await_veri'] = get_order_num($user_id,'await_veri');
    //待发货
    $num['await_ship'] = get_order_num($user_id,'await_ship');
    //待收货
    $num['await_receipt'] = get_order_num($user_id,'await_receipt');
    //待评价
    $num['await_comment'] = get_order_num($user_id,'await_comment');
    $smarty->assign('num', $num);
    $smarty->display('user_index.htm');
}

/* 个人资料页面 */
elseif ($action == 'profile')
{   
    $user_info = get_profile($user_id);
    $smarty->assign('profile', $user_info);
    $smarty->display('user_profile.htm');
}
elseif ($action == 'rank')
{
    $rank = get_user_rank();

    $points = get_user_points($user_id);
    $sql="select rank_id from ".$aos->table('user_rank')." where min_points <= '$points[rank_points]' and max_points>='$points[rank_points]'";
    $rank_id=$db->getOne($sql);

    $smarty->assign('rank', $rank);
    $smarty->assign('rank_id', $rank_id);
    $smarty->assign('points', $points['rank_points']);
    $smarty->display('user_rank.htm');
}
elseif ($action == 'logout')
{   
    
    unset($_SESSION);
    //更新资料
    aos_header("Location: index.php\n");
}

/* 个人资料页面 */
elseif ($action == 'renew')
{   
    if($_GET['code']){
        $json = $wechat->getOauthAccessToken();
        $info = $wechat->getOauthUserinfo($json['access_token'],$json['openid']);
    }
    else
    {
      $url = $wechat->getOauthRedirect($cur_url,'wxbase','snsapi_base');
      header("Location:$url");exit;
    }
    
    //更新资料
    if(!empty($info)){
        if($info['headimgurl'])
        {
          //保存图像到本地
          $avatar    = $wechat->http_get($info[headimgurl]);
          $path   = ROOT_PATH . 'uploads/avatar/avatar_'.$user_id.'.jpg';
          @file_put_contents($path,$avatar);
        }
       $sql = "UPDATE " .$GLOBALS['aos']->table('users'). " SET nickname = '" .$info['nickname']."',sex = '" .$info['sex']."',headimgurl = '" .$info['headimgurl']."',country = '" .$info['country']."',province = '" .$info['province']."',city = '" .$info['city']."' WHERE user_id = '" . $_SESSION['user_id'] . "'";
        $GLOBALS['db']->query($sql);
    }
    aos_header("Location: index.php?c=user&a=profile\n");
}
elseif ($action == 'realname_ajax')
{
    $result=array();
    $result['err'] = 0;
    $realname = $_REQUEST['realname'] ? $_REQUEST['realname'] : '';
    if(empty($realname)){
        $result['err'] = 1;
    }else{
        
        $sql = "UPDATE " . $GLOBALS['aos']->table('users') ." SET realname = '$realname' WHERE user_id = '$user_id' ";
        $res=$GLOBALS['db']->query($sql);
        if ($res)
        {
            $result['realname'] = $realname;
        }
    }
    die(json_encode($result));
}
elseif ($action == 'sex_ajax')
{
    $result=array();
    $result['err'] = 0;
    $sex = $_REQUEST['sex'] ? intval($_REQUEST['sex']) : '';
    if(empty($sex)){
        $result['err'] = 1;
    }else{
        $sql = "UPDATE " . $GLOBALS['aos']->table('users') ." SET sex = '$sex' WHERE user_id = '$user_id' ";
        $res=$GLOBALS['db']->query($sql);
        if ($res)
        {
            if($sex==1){
                $sex='男';
            }elseif($sex==2){
                $sex='女';
            }
            $result['sex'] = $sex;
        }
    }
    die(json_encode($result));
}
elseif ($action == 'mobile_ajax')
{
    $result=array();
    $result['err'] = 0;
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    if(empty($mobile)){
        $result['err'] = 1;
    }else{
        if($code == $_SESSION['validate_code']){
            $sql = "UPDATE " . $GLOBALS['aos']->table('users') ." SET mobile = '$mobile' WHERE user_id = '$user_id' ";
            $res=$GLOBALS['db']->query($sql);
            unset($_SESSION['validate_code']);
            if ($res)
            {
                $result['mobile'] = $mobile;
            }
        }else{
            $result['err'] = 1;
            $result['message'] = "验证码错误";
        }
    }
    die(json_encode($result));
}

/* 查看订单列表 */
elseif ($action == 'order_list')
{
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 'all';
    $default = $_REQUEST['default'] ? $_REQUEST['default'] : '0';
	$smarty->assign('status',  $status);
    $smarty->assign('default',  $default);
    $smarty->display('user_order_list.htm');
}
elseif ($action == 'order_list_ajax')
{
	include_once(ROOT_PATH . 'source/library/order.php');
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 'all';
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
	$limit = "limit $last,$amount";//每次加载的个数
	$orderslist = get_user_orders($user_id, $status, $limit);
    $res['count'] = $orderslist['count'];
	foreach($orderslist['info'] as $val){
		$GLOBALS['smarty']->assign('item',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/orders_list.htm');
	}
	die(json_encode($res));
}

/* 查看订单详情 */
elseif ($action == 'order_detail')
{
    include_once(ROOT_PATH . 'source/library/payment.php');
    include_once(ROOT_PATH . 'source/library/order.php');
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);
    if ($order === false)
    {
        $err->show('返回首页', './');
        exit;
    }
     /* 设置能否修改使用余额数 */
    if ($order['order_amount'] > 0)
    {
        if ($order['order_status'] == 0 || $order['order_status'] == 1)
        {
            $user = user_info($order['user_id']);
            if ($user['user_money'] + $user['credit_line'] > 0)
            {
                $smarty->assign('allow_edit_surplus', 1);
                $smarty->assign('max_surplus', sprintf('（您的帐户余额：%s）', $user['user_money']));
            }
        }
    }
    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == 0 && $order['shipping_status'] == 0)
    {
        $payment_list = payment_list(false, 0, true);
        /* 过滤掉当前支付方式和余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_id'] == $order['pay_id'] || $payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        $smarty->assign('payment_list', $payment_list);
    }
    if ($order['order_amount'] > 0)
    {
        $payment = payment_info($order['pay_id']);
        
        if($payment['pay_code'] == 'alipay'){
            $pay_url = "index.php?c=alipay&out_trade_no=".$order['log_id']."&total_fee=".$order['order_amount'];
        }
        if($payment['pay_code'] == 'wxpay'){
            $pay_url = "index.php?c=wxpay&out_trade_no=".$order['log_id'];
        }
    }
    $smarty->assign('pay_url',$pay_url);
    //付款金额
    $payment = payment_info($order['pay_id']);
    if($payment['pay_code'] == 'balance'){
        $order['money_paid']=$order['surplus'];
    }
    /* 订单 支付 配送 状态语言项 */
    $order['order_statu'] = $order['order_status'];
    $order['pay_statu'] = $order['pay_status'];
    $order['shipping_statu'] = $order['shipping_status'];
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];
    $is_show=1;
    if(!empty($order['store_id'])){
        //按核销时间
        if(!empty($order['veri_time'])){
            $betwen_time=$order['veri_time']+24*60*60*$_CFG['refund_time'];
            if($betwen_time<gmtime()){
                $is_show=0;
            }
        }
    }else{
        //按发货时间
        if(!empty($order['shipping_date_time'])){
            $betwen_time=$order['shipping_date_time']+24*60*60*$_CFG['refund_time'];
            if($betwen_time<gmtime()){
                $is_show=0;
            }
        }
    }
    
    $smarty->assign('is_show',$is_show);
    $shipping_name = $order['shipping_name'];
    $shipping_code = $db->getOne("SELECT shipping_code ".
           " FROM " . $aos->table('shipping').
           " WHERE shipping_name = '".$shipping_name."'");
    $invoice_no = $order['invoice_no'];
    if($invoice_no)
    {
        $delivery_info = get_express($shipping_code,$invoice_no);
        $smarty->assign('delivery_info', $delivery_info);
    }
    $smarty->assign('order',      $order);
    $smarty->display('user_order_detail.htm');
}

/* 取消订单 */
elseif ($action == 'cancel_order')
{
    include_once(ROOT_PATH . 'source/library/order.php');
    $result=array();
    
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    if (cancel_order($order_id, $user_id))
    {
        $result['err'] = 0;
        $result['order_id'] = $order_id;
    }
    else
    {
        $result['err'] = 1;
        $result['msg'] = '删除失败';
    }
    die(json_encode($result));
}
/* 退货退款 */
elseif ($action == 'refund')
{
  $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
  $refund_type=isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
  $smarty->assign('order_id',$order_id);
  //获取当前退款状态
  $sql="select goods_img,o.consignee,o.address,o.mobile,o.refund_money_1,o.refund_money_2,o.status_back,o.status_refund,o.imgs,o.back_pay,o.back_type,o.area,g.goods_id,g.goods_name,g.goods_attr,g.back_goods_number,g.back_goods_price from ".$GLOBALS['aos']->table('back_order')." as o left join ".$aos->table('back_goods')." as g on o.back_id=g.back_id left join ".$aos->table('goods')." as gs on g.goods_id=gs.goods_id where o.order_id = $order_id";
  $back_order=$db->getRow($sql);
  //如果已申请
  if($back_order){
    include_once(ROOT_PATH . 'source/library/order.php');
    //退款单状态
    if($back_order['status_back']==4){
        $back_order['status_back']    = "审核通过";
    }else{
        $back_order['status_back']    = $_LANG['bos'][$back_order['status_back']];
    }
    
    $back_order['status_refund']      = $_LANG['bps'][$back_order['status_refund']];

    $area = explode(',',$back_order['area']);
    
    $area['province'] = get_region_name($area['0']);
    $area['city'] = get_region_name($area['1']);
    $area['district'] = get_region_name($area['2']);
    $back_order['area']  = $area['province'].$area['city'].$area['district'];

    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);
    $smarty->assign('order',      $order);
    $smarty->assign('back_order',$back_order);
    $smarty->assign('page_title',"退货单详情");
    $smarty->display('user_refund_detail.htm');
    exit;

  }
  if(!empty($refund_type))
  {
    $sql="select p.pay_code,o.mobile,o.shipping_fee,o.money_paid,o.surplus from ".$GLOBALS['aos']->table('order_info')." as o left join ".$GLOBALS['aos']->table('payment')." as p on o.pay_id = p.pay_id where o.user_id = '$user_id' and o.pay_status = 2 and o.order_id =  '$order_id' and (o.tuan_status = 2 or o.extension_code = '') and o.extension_code not in ('lottery','assist')";
    $res=$GLOBALS['db']->getRow($sql);
    if(!$res){
        show_message("不能进行操作", "返回订单详情", "index.php?c=user&a=order_detail&order_id=$order_id", '');
    }
    if($res['pay_code']=='balance'){

        $res['money']=$res['surplus'];
        
    }else{

        $res['money']=price_format($res['money_paid']-$res['shipping_fee']);

    }
    $smarty->assign('order',$res);
    $smarty->assign('type',$refund_type);
    
  }
  $smarty->display('user_refund.htm');  
}
elseif ($action == 'do_refund')
{
    $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;
    
    if(! $order_id)
    {
        show_message('对不起，您进行了错误操作！');
        exit();
    }
    

    $back_reason = ! empty($_POST['back_reason']) ? trim($_POST['back_reason']) : "";
    $mobile = ! empty($_POST['back_mobile']) ? trim($_POST['back_mobile']) : "";
    $back_pay = intval($_POST['back_pay']);
    $back_type = intval($_POST['back_type']);
    $reason_type = intval($_POST['reason_type']);
    if(empty($mobile)){

        show_message('请填写联系电话！');
        exit();
    }
    if(empty($back_reason)){

        show_message('请填写退款说明！');
        exit();
    }
    if(!in_array($back_type,array(1,4))){

        show_message('对不起，您进行了错误操作！');
        exit();
    }
    
    $sql="select p.pay_code,o.area,o.shipping_time,o.money_paid,o.shipping_fee,o.surplus,o.consignee,o.address,o.shipping_id,o.shipping_name,o.store_id,o.veri_time from ".$GLOBALS['aos']->table('order_info')." as o left join ".$GLOBALS['aos']->table('payment')." as p on o.pay_id = p.pay_id where o.user_id = '$user_id' and o.pay_status = 2 and o.order_id =  '$order_id' and o.order_status in (1,5) and (o.tuan_status = 2 or o.extension_code = '') and o.extension_code not in ('lottery','assist')";
    $res=$GLOBALS['db']->getRow($sql);
    if(!$res){
        show_message("不能进行操作", "返回订单详情", "index.php?c=user&a=order_detail&order_id=$order_id", '');
    }
    if(!empty($order['store_id'])){
        //按核销时间
        if(!empty($order['veri_time'])){
            $betwen_time=$order['veri_time']+24*60*60*$_CFG['refund_time'];
            if($betwen_time<gmtime()){
                show_message("已超退货时间", "返回订单详情", "index.php?c=user&a=order_detail&order_id=$order_id", '');
            }
        }
    }else{
        //按发货时间
        if(!empty($order['shipping_date_time'])){
            $betwen_time=$order['shipping_date_time']+24*60*60*$_CFG['refund_time'];
            if($betwen_time<gmtime()){
                show_message("已超退货时间", "返回订单详情", "index.php?c=user&a=order_detail&order_id=$order_id", '');
            }
        }
    }
    if($res['pay_code']=='balance'){

        $money=$res['surplus'];
    }else{
        $money=$res['money_paid']-$res['shipping_fee'];
    }  
    $add_time = gmtime();
    $sql = "select order_id from " . $aos->table('back_order') . " where order_id='$order_id' and user_id = $user_id";
    $back_order = $db->getOne($sql);
    if($back_order)
    {
        show_message('请勿重复申请');
        exit();
    }
    
    $sql = "select * from " . $aos->table('order_info') . " as o where order_id='$order_id' and pay_status=2 and order_status in (1,5) and (o.tuan_status = 2 or o.extension_code='') and extension_code != 'assist' and user_id = $user_id";
    $order_info = $db->getRow($sql);
    if(empty($order_info))
    {
        show_message('对不起，此订单不能操作！');
        exit();
    }
    if($_FILES['file']){
        $file=$_FILES['file'];
        foreach($file['tmp_name'] as $k=>$name){
            $pic_info['tmp_name']=$name;
            $pic_info['size']=$file['size'][$k];
            $pic_info['type']=$file['type'][$k];
            $pic_info['name']=$file['name'][$k];
            $refund_img.=$image->upload_image($pic_info,'refund_img').',';
        }
        $refund_img=substr($refund_img,0,-1);
    }
    $order_sn = $order_info['order_sn'];
    $sql_og = "SELECT * FROM " . $GLOBALS['aos']->table('order_goods') . " WHERE order_id = " . $order_id;
    $goods_info = $GLOBALS['db']->getRow($sql_og);
    //插入退货订单
    $sql = "insert into " . $GLOBALS['aos']->table('back_order') . "(order_sn, order_id,consignee,area,address, goods_id,  user_id, shipping_fee, " . " add_time ,mobile, goods_name, imgs, back_pay ,back_type,back_reason, supplier_id,refund_money_1,status_back,reason_type) " . " values('$order_sn', '$order_id', '$res[consignee]','$res[area]','$res[address]', '$goods_info[goods_id]',  '$user_id', '$order_info[shipping_fee]', '$add_time','$mobile',   '$goods_info[goods_name]', '$refund_img', '$back_pay','$back_type', '$back_reason', '$order_info[store_id]','$money',5,'$reason_type')";
    $db->query($sql);

    // 插入退换货商品 80_back_goods
    $back_id = $db->insert_id();
    $have_tuikuan = 0; // 是否有退货
    $price_refund_all = ($goods_info['goods_price'] * $goods_info['goods_number']);

    //退货产品
    $sql = "INSERT INTO " . $GLOBALS['aos']->table('back_goods') . "(back_id, goods_id, goods_name,  product_id, goods_attr,  back_type, " . "send_number,back_goods_number, back_goods_price,goods_sn ) " . " values('$back_id', '".$goods_info['goods_id']."', '".$goods_info['goods_name']."',  '".$goods_info['attr_id']."', '".$goods_info['goods_attr']."',  '$back_type', '".$goods_info['goods_number']."','".$goods_info['goods_number']."', '".$goods_info['goods_price']."', '".$goods_info['goods_sn']."'  ) ";
    $res=$db->query($sql);
    //订单状态改为取消
    $sql = "update " . $aos->table('order_info') . " set order_status= '4' where order_id='$order_id' ";
        $db->query($sql);
    if($res){
        show_message("提交成功", "返回订单详情", "index.php?c=user&a=order_detail&order_id=$order_id", '');
    }
}
//退款订单列表
elseif ($action == 'refund_list')
{
    $smarty->display('user_refund_list.htm');
}

/* 收货地址列表界面*/
elseif ($action == 'address_list')
{
    /* 获得用户所有的收货人信息 */
    $consignee_list = get_address_list($_SESSION['user_id']);
	
	foreach($consignee_list as $idx=>$value)
    {
        $area = explode(',',$value['area']);
    
        $area['province'] = get_region_name($area['0']);
        $area['city'] = get_region_name($area['1']);
        $area['district'] = get_region_name($area['2']);
        $consignee_list[$idx]['area']  = $area['province'].$area['city'].$area['district'];
    }
    $smarty->assign('consignee_list', $consignee_list);

    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$aos->table('users'). " WHERE user_id='$user_id'");

    //赋值于模板
    $smarty->assign('address',          $address_id);
    $smarty->display('user_address_list.htm');
}
/* 收货地址界面*/
elseif ($action == 'address')
{
    $address_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
	$consignee = get_address($address_id);
	
	$area = explode(',',$consignee['area']);
	$consignee['province_name'] = get_region_name($area['0']);
	$consignee['city_name'] = get_region_name($area['1']);
	$consignee['district_name'] = get_region_name($area['2']);

    $smarty->assign('consignee', $consignee);
    $smarty->display('user_address.htm');
}

/* 添加/编辑收货地址的处理 */
elseif ($action == 'act_edit_address')
{
    $address = array(
        'user_id'    => $user_id,
        'address_id' => intval($_POST['address_id']),
        'area'    => isset($_POST['area'])   ? compile_str(trim($_POST['area']))    : '',
		'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        );

    if (update_address($address))
    {
		aos_header("Location: index.php?c=user&a=address_list\n");
        exit;
    }
}
/* 添加收藏商品(ajax) */
elseif ($action == 'collect')
{
    $result = array('error' => 0, 'message' => '');
    $goods_id = $_GET['id'];

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0 || empty($goods_id))
    {
        $result['error'] = 2;
        $result['message'] = '由于您还没有登录，因此您还不能使用该功能。';
        die(json_encode($result));
    }
    else
    {
        /* 检查是否已经存在于用户的收藏夹 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['aos']->table('collect') .
            " WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";
        if ($GLOBALS['db']->GetOne($sql) > 0)
        {
            $result['error'] = 1;
            $result['message'] = '取消收藏成功';
            $db->query("DELETE FROM " .$aos->table('collect'). " WHERE goods_id='$goods_id' AND user_id ='$_SESSION[user_id]'" );
            die(json_encode($result));
        }
        else
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['aos']->table('collect'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

            if ($GLOBALS['db']->query($sql) === false)
            {
                $result['error'] = 2;
                $result['message'] = $GLOBALS['db']->errorMsg();
                die(json_encode($result));
            }
            else
            {
                $result['error'] = 0;
                $result['message'] = '收藏成功';
                die(json_encode($result));
            }
        }
    }
}
/* 显示收藏商品列表 */
elseif ($action == 'collection_list')
{
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$aos->table('collect'). " WHERE user_id='$user_id' ORDER BY add_time DESC");
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));
    $smarty->assign('user_id',  $user_id);
    $smarty->display('user_collection_list.htm');
}

/* 删除收藏的商品 */
elseif ($action == 'del_collection')
{
    $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;
    if ($collection_id > 0)
    {
        $db->query('DELETE FROM ' .$aos->table('collect'). " WHERE rec_id='$collection_id' AND user_id ='$user_id'" );
    }
    aos_header("Location: index.php?c=user&a=collection_list\n");
    exit;
}

/* 显示评论列表 */
elseif ($action == 'comment_list')
{
    $sql= "SELECT COUNT(*) from ".$GLOBALS['aos']->table('comment')." where user_id='".$_SESSION[user_id]."'";
    $count = $GLOBALS['db']->getOne($sql);
    $smarty->assign('count',$count);
    $smarty->display('user_comment_list.htm');
}
/* 显示评论列表 */
elseif ($action == 'comment_list_ajax')
{
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $comment_list = get_comment_list($limit);
    foreach($comment_list['list'] as $val){
        $GLOBALS['smarty']->assign('comment',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/user_comments_list.htm');
    }
    $res['count']=$comment_list['count'];
    die(json_encode($res));
}
/* 删除评论 */
elseif ($action == 'del_comment')
{
    $result = array('error' => 0, 'message' => '');
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0)
    {
        $sql = "DELETE FROM " .$aos->table('comment'). " WHERE comment_id = '$id' AND user_id = '$user_id'";
        $db->query($sql);
        if($db)
        {
            $result['error'] = 1;
            $result['message'] = 'ok';
            $result['comment_id'] = $id;
            die(json_encode($result));
        }
        else
        {
            $result['error'] = 0;
            $result['message'] = 'no';
            die(json_encode($result));
        }
    }
}

/* 确认收货 */
elseif ($action == 'affirm_received')
{
    $result=array();
    $result['err'] = 0;

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    $time=gmtime();
    if (affirm_received($order_id, $user_id))
    {
        /*$sql="select o.money_paid,g.goods_id,g.goods_name from ".$aos->table('order_info')." as o left join ".$aos->table('order_goods')." as g on o.order_id = g.order_id where o.order_id = $order_id";
        $res=$db->getRow($sql);
        //按订单送优惠劵
        $sql="select type_id,min_amount,type_money,use_start_date,use_end_date from ".$aos->table('bonus_type')." where  send_type = 2 and send_start_date < $time and send_end_date > $time";
        $bonus_list=$db->getAll($sql);
        if(!empty($bonus_list)){
            foreach($bonus_list as $vo){
                if($res['money_paid']>=$vo['min_amount']){
                    $sql="insert into ".$aos->table('user_bonus')." (bonus_type_id,user_id) values ('$vo[type_id]','$user_id')";
                    $db->query($sql);
                    //发送消息
                    $openid=getOpenid($user_id);
                    $use_time=local_date("m月d日", $vo['use_start_date']).'-'.local_date("m月d日", $vo['use_end_date']);
                    $wx_title = "获得优惠劵通知";
                    $wx_desc = "恭喜您购买的".$res['goods_name']."获得优惠劵\r\n优惠劵金额：".$vo['type_money']."元\r\n有效期：".$use_time;
                    
                    $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
                }
                
            }
        }
        //按产品送优惠劵
        $sql="select b.type_id,b.type_money,b.use_start_date,b.use_end_date from ".$aos->table('goods')." as g left join ".$aos->table('bonus_type')." as b on g.bonus_type_id=b.type_id where  g.goods_id =$res[goods_id]  and b.send_type = 1 and b.send_start_date < $time and b.send_end_date > $time";
        $bonus_type=$db->getRow($sql);
        if(!empty($bonus_type)){
            $id=$bonus_type['type_id'];
            $sql="insert into ".$aos->table('user_bonus')." (bonus_type_id,user_id) values ('$id','$user_id')";
            $db->query($sql);
            //发送消息
            $openid=getOpenid($user_id);
            $use_time=local_date("m月d日", $bonus_type['use_start_date']).'-'.local_date("m月d日", $bonus_type['use_end_date']);
            $wx_title = "获得优惠劵通知";
            $wx_desc = "恭喜您购买的".$res['goods_name']."获得优惠劵\r\n优惠劵金额：".$bonus_type['type_money']."元\r\n有效期：".$use_time;
            
            $wechat->sendWxMsg($openid,$wx_title,$wx_desc,$wx_url);
               
        }*/
        //增加积分 
        $result['order_id']=$order_id;
    }
    else
    {
        $result['err']=1;
    }
    die(json_encode($result));
}

/* 会员退款申请界面 */
elseif ($action == 'account_raply')
{
    $smarty->display('user_account.htm');
}

/* 会员预付款界面 */
elseif ($action == 'account_deposit')
{

    $surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($surplus_id);

    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order',   $account);
    $smarty->display('user_account.htm');
}

/* 会员账目明细界面 */
elseif ($action == 'account_detail')
{

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'user_money';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$aos->table('account_log').
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $aos->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time']   = local_date('Y-m-d H:i:s', $row['change_time']);
        //$row['user_money'] = price_format(abs($row['user_money']), false);
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_account.htm');
}

/* 会员充值和提现申请记录 */
elseif ($action == 'account_log')
{

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$aos->table('user_account').
           " WHERE user_id = '$user_id'" .
           " AND process_type " . db_create_in(array(0, 1));
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = get_account_log($user_id, $pager['size'], $pager['start']);

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_account.htm');
}

/* 对会员余额申请的处理 */
elseif ($action == 'act_account')
{
    include_once(ROOT_PATH . 'source/library/order.php');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount <= 0)
    {
        show_message('请在“金额”栏输入大于0的数字');
    }

    /* 变量初始化 */
    $surplus = array(
            'user_id'      => $user_id,
            'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
            'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '',
            'amount'       => $amount,
    );

    /* 退款申请的处理 */
    if ($surplus['process_type'] == 1)
    {
        /* 判断是否有足够的余额的进行退款的操作 */
        $sur_amount = get_user_surplus($user_id);
        if ($amount > $sur_amount)
        {
            $content = '您要申请提现的金额超过了您现有的余额，此操作将不可进行！';
            show_message($content, '返回上一页', '', 'info');
        }

        //插入会员账目明细
        $amount = '-'.$amount;
        $surplus['payment'] = '';
        $surplus['rec_id']  = insert_user_account($surplus, $amount);

        /* 如果成功提交 */
        if ($surplus['rec_id'] > 0)
        {
            $content = '您的提现申请已成功提交，请等待管理员的审核！';
            show_message($content, '返回帐户明细列表', 'index.php?c=user&a=account_log', 'info');
        }
        else
        {
            $content = '此次操作失败，请返回重试！';
            show_message($content, '返回上一页', '', 'info');
        }
    }
    /* 如果是会员预付款，跳转到下一步，进行线上支付的操作 */
    else
    {
        if ($surplus['payment_id'] <= 0)
        {
            show_message('请选择支付方式');
        }

        include_once(ROOT_PATH .'source/library/payment.php');

        //获取支付方式名称
        $payment_info = array();
        $payment_info = payment_info($surplus['payment_id']);

        $surplus['payment_name'] = $payment_info['pay_name'];

        if ($surplus['rec_id'] > 0)
        {
            //检查金额是否有改变，如果有改变，不可以修改
            if(!check_account_money($surplus['rec_id'],$user_id,$amount)){
                show_message('修改的金额与充值金额不相等，不能进行修改');
            }
            //更新会员账目明细
            $surplus['rec_id'] = update_user_account($surplus);
        }
        else
        {
            //插入会员账目明细
            $surplus['rec_id'] = insert_user_account($surplus, $amount);
        }

        $order = array();
        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'], $amount, $type=1, 0);

        if ($amount > 0)
        {
            if($surplus['payment_id'] == 2){
              $pay_url = "index.php?c=wxpay&out_trade_no=".$order['log_id'];
            }
            if($surplus['payment_id'] == 3){
              $pay_url = "index.php?c=alipay&out_trade_no=".$order['log_id'];
            }
            header("Location:".$pay_url."\n");
        }
    }
}

/* 删除会员余额 */
elseif ($action == 'cancel')
{

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        aos_header("Location: index.php?c=user&a=account_log\n");
        exit;
    }

    if(del_user_account($id, $user_id))
    {
        $result['error'] = 1;
        $result['message'] = 'ok';
        $result['rec_id'] = $id;
        die(json_encode($result));
    }
    else
    {
        $result['error'] = 0;
        $result['message'] = 'no';
        die(json_encode($result));
    }
}

/* 会员积分 */
elseif ($action == 'integral')
{
	$points = get_user_points($user_id);
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : '';
	$smarty->assign('status',  $status);
	$smarty->assign('points', $points['pay_points']);
	$smarty->assign('points_log', get_points_log($user_id,$status));
	$smarty->display('user_integral.htm');
}

/* 会员积分获取说明 */
elseif ($action == 'integral_guide')
{
	$smarty->display('user_integral_guide.htm');
}

/* 会员通过帐目明细列表进行再付款的操作 */
elseif ($action == 'pay')
{
    include_once(ROOT_PATH . 'source/library/payment.php');
    include_once(ROOT_PATH . 'source/library/order.php');

    //变量初始化
    $surplus_id = isset($_GET['id'])  ? intval($_GET['id'])  : 0;
    $payment_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

    if ($surplus_id == 0)
    {
        aos_header("Location: index.php?c=user&a=account_log\n");
        exit;
    }
    //获取需要支付的log_id
    $log_id = get_paylog_id($surplus_id, $pay_type = 1);
    if($payment_id == 2){
      $pay_url = "index.php?c=wxpay&out_trade_no=".$log_id;
    }
    if($payment_id == 3){
      $pay_url = "index.php?c=alipay&out_trade_no=".$log_id;
    }
    header("Location:".$pay_url."\n");
}


/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_surplus')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        aos_header("Location: ./index.php\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        aos_header("Location: ./index.php\n");
        exit;
    }

    /* 检查余额 */
    $surplus = floatval($_POST['surplus']);
    if ($surplus <= 0)
    {
        $err->add('您输入的数字不正确！');
        $err->show('订单详情', 'index.php?c=user&a=order_detail&order_id=' . $order_id);
    }

    include_once(ROOT_PATH . 'source/library/order.php');

    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款，检查应付款金额是否大于0 */
    if ($order['pay_status'] != 0 || $order['order_amount'] <= 0)
    {
        $err->add('该订单不需要付款！');
        $err->show('订单详情', 'index.php?c=user&a=order_detail&order_id=' . $order_id);
    }

    /* 计算应付款金额（减去支付费用） */
    //$order['order_amount'] -= $order['pay_fee'];

    /* 余额是否超过了应付款金额，改为应付款金额 */
    if ($surplus > $order['order_amount'])
    {
        $surplus = $order['order_amount'];
    }

    /* 取得用户信息 */
    $user = user_info($_SESSION['user_id']);

    /* 用户帐户余额是否足够 */
    if ($surplus > $user['user_money'] + $user['credit_line'])
    {
        $err->add('您的帐户余额不足！');
        $err->show('订单详情', 'index.php?c=user&a=order_detail&order_id=' . $order_id);
    }

    /* 修改订单，重新计算支付费用 */
    $order['surplus'] += $surplus;
    $order['order_amount'] -= $surplus;
    if ($order['order_amount'] > 0)
    {
        $cod_fee = 0;
        if ($order['shipping_id'] > 0)
        {
            $regions  = array($order['country'], $order['province'], $order['city'], $order['district']);
            $shipping = shipping_area_info($order['shipping_id'], $regions);
        }
    }

    /* 如果全部支付，设为已确认、已付款 */
    if ($order['order_amount'] == 0)
    {
        if ($order['order_status'] == 0)
        {
            $order['order_status'] = 1;
            $order['confirm_time'] = gmtime();
        }
        $order['pay_status'] = 2;
        $order['pay_time'] = gmtime();
    }
    $order = addslashes_deep($order);
    update_order($order_id, $order);

    /* 更新用户余额 */
    $change_desc = sprintf('追加使用余额支付订单：%s', $order['order_sn']);
    log_account_change($user['user_id'], (-1) * $surplus, 0, 0, 0, $change_desc);

    // 更新pay_log表
    if ($order['order_amount']>0) {
        $GLOBALS['db']->query("UPDATE ".$GLOBALS['aos']->table('pay_log')." SET order_amount='".$order['order_amount']."' WHERE order_id='".$order_id."' AND order_type=0 and is_paid=0");
    }elseif ($order['order_amount'] == 0) {
        $GLOBALS['db']->query("UPDATE ".$GLOBALS['aos']->table('pay_log')." SET order_amount='".$order['order_amount']."',is_paid=1 WHERE order_id='".$order_id."' AND order_type=0 and is_paid=0");
    }

    /* 跳转 */
    aos_header('Location: index.php?c=user&a=order_detail&order_id=' . $order_id . "\n");
    exit;
}

/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_payment')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 检查支付方式 */
    $pay_id = intval($_POST['pay_id']);
    if ($pay_id <= 0)
    {
        aos_header("Location: ./\n");
        exit;
    }

    include_once(ROOT_PATH . 'source/library/order.php');
    $payment_info = payment_info($pay_id);
    if (empty($payment_info))
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        aos_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款和未发货 以及订单金额是否为0 和支付id是否为改变*/
    if ($order['pay_status'] != 0 || $order['shipping_status'] != 0 || $order['goods_amount'] <= 0 || $order['pay_id'] == $pay_id)
    {
        aos_header("Location: index.php?c=user&a=order_detail&order_id=$order_id\n");
        exit;
    }

    $order_amount = $order['order_amount'];

    $sql = "UPDATE " . $aos->table('order_info') .
           " SET pay_id='$pay_id', pay_name='$payment_info[pay_name]', order_amount='$order_amount'".
           " WHERE order_id = '$order_id'";
    $db->query($sql);

    /* 跳转 */
    aos_header("Location: index.php?c=user&a=order_detail&order_id=$order_id\n");
    exit;
}

/* 保存订单详情收货地址 */
elseif ($action == 'save_order_address')
{
    
    $address = array(
        'consignee' => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'     => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'address'   => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'zipcode'   => isset($_POST['zipcode'])   ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        'tel'       => isset($_POST['tel'])       ? compile_str(trim($_POST['tel']))        : '',
        'mobile'    => isset($_POST['mobile'])    ? compile_str(trim($_POST['mobile']))     : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'best_time' => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'order_id'  => isset($_POST['order_id'])  ? intval($_POST['order_id']) : 0
        );
    if (save_order_address($address, $user_id))
    {
        $order_sn = $db->getOne("SELECT order_sn FROM ".$aos->table('order_info')." WHERE order_id='{$address['order_id']}'");
        if ($order_sn) {
            $sql = "UPDATE " . $aos->table('order_info') .
           " SET lastmodify=".gmtime()."  WHERE order_id = ".$address['order_id'];
            $db->query($sql);
            include_once(ROOT_PATH . 'source/class/matrix.php');
            $matrix = new matrix;
            $matrix->createOrder($order_sn);
        }
        aos_header('Location: index.php?c=user&a=order_detail&order_id=' .$address['order_id']. "\n");
        exit;
    }
    else
    {
        $err->show('我的订单列表', 'index.php?c=user&a=order_list');
    }
}

/* 我的优惠券列表 */
elseif ($action == 'bonus')
{
    $status = $_REQUEST['status'] ? $_REQUEST['status'] : 'not_use';
	$smarty->assign('status',  $status);
    $smarty->display('user_bonus.htm');
}
elseif ($action == 'bonus_ajax')
{

	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 'not_use';
	$last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
	$limit = "limit $last,$amount";//每次加载的个数
	$bonuslist = get_user_bouns_list($user_id, $status, $limit);
	foreach($bonuslist['arr'] as $val){
		$GLOBALS['smarty']->assign('item',$val);
		$res['info'][]  = $GLOBALS['smarty']->fetch('inc/bonus_list.htm');
	}
    $res['count']=$bonuslist['count'];
	die(json_encode($res));
}

/* 添加一个优惠券 */
elseif ($action == 'add_bonus')
{

    $bouns_sn = isset($_POST['bonus_sn']) ? intval($_POST['bonus_sn']) : '';

    if (add_bonus($user_id, $bouns_sn))
    {
        $res = array(
            'isError' => 0,
            'message' => '添加成功！'
        );
    }
    else
    {
        $res = array(
            'isError' => 1,
            'message' => '添加失败！'
        );
    }
    die(json_encode($res));
}

/* 我的拼团列表 */
elseif ($action == 'tuan_list')
{
	$status = $_REQUEST['status'] ? $_REQUEST['status'] : 'all';
	$smarty->assign('status',  $status);
	
    $smarty->display('user_tuan_list.htm');
}
elseif ($action == 'tuan_list_ajax')
{
    include_once(ROOT_PATH . 'source/library/order.php');
    $status = $_REQUEST['status'] ? $_REQUEST['status'] : 0;
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $orderslist = get_user_tuan($user_id, $status, $limit);
    
    $res['count'] = $orderslist['count'];
    //print_r($orderslist);
    foreach($orderslist['tuans'] as $val){
        $GLOBALS['smarty']->assign('lists',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/users_tuan_list.htm');
    }
    die(json_encode($res));
}
//立即成团
elseif ($action == 'capa_tuan')
{   
    include_once(ROOT_PATH . 'source/library/order.php');
    $result=array();
    $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : 0;

    $result['error']=0;
    $sql="SELECT extension_id from ".$aos->table('order_info')." where order_id = '$order_id' and tuan_first = 1 and user_id = '$user_id' and tuan_status = 1 and extension_code = 'tuan' ";
    $res=$db->getOne($sql);
    
    if ($res)
    {
        $sql="select count(o.order_id) from ".$GLOBALS['aos']->table('order_info')." as o  where  o.pay_status = 2 and o.extension_code = 'tuan' and o.tuan_status = 1 and o.order_status = 1 and o.extension_id=".$res;
        $count= $GLOBALS['db']->getOne($sql);
        
        $order_goods = order_goods($order_id);
        $goods_tuan_number=get_tuan_number($order_goods['goods_id']);
        $goods_tuan_num = min($goods_tuan_number);
        if($count>=$goods_tuan_num){
            //更改团人数及状态
            cheng_tuan($res);
            
            $result['error']=0;
            $result['order_id']=$order_id;
            $result['message']="拼团成功";
            
        }else{
            $result['error']=1;
            $result['message']="未达到成团人数";
            
        }
    }
    else
    {
        $result['error']=1;
        $result['message']="不能进行操作";
        
    }
    die(json_encode($result));
    
}

/* 我的抽奖列表 */
elseif ($action == 'lottery_list')
{
    $status = $_REQUEST['status'] ? $_REQUEST['status'] : 'all';
    $smarty->assign('status',  $status);
    $smarty->display('user_lottery_list.htm');
}
elseif ($action == 'lottery_list_ajax')
{
    include_once(ROOT_PATH . 'source/library/order.php');
    $status = $_REQUEST['status'] ? $_REQUEST['status'] : 0;
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $orderslist = get_user_lottery($user_id, $status, $limit);
    
    $res['count'] = $orderslist['count'];
    //print_r($orderslist);
    foreach($orderslist['tuans'] as $val){
        $GLOBALS['smarty']->assign('lists',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/users_lottery_list.htm');
    }
    die(json_encode($res));
}

/* 我的助力列表 */
elseif ($action == 'assist')
{
    $address_list = get_address_list($_SESSION['user_id']);
    foreach($address_list as $idx=>$value)
    {
      $area = explode(',',$value['area']);
  
      $area['province'] = get_region_name($area['0']);
      $area['city'] = get_region_name($area['1']);
      $area['district'] = get_region_name($area['2']);
      $address_list[$idx]['area']  = $area['province'].$area['city'].$area['district'];
    }
    $smarty->assign('address_list',  $address_list);
    $smarty->display('user_assist.htm');
}
elseif ($action == 'assist_ajax')
{
    include_once(ROOT_PATH . 'source/library/order.php');
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $page = !empty($_POST['page'])?intval($_POST['page']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $assistlist = get_user_assist($user_id, $limit);
    
    $res['count'] = $assistlist['count'];
    //print_r($orderslist);
    foreach($assistlist['assist'] as $val){
        $GLOBALS['smarty']->assign('page',$page);
        $GLOBALS['smarty']->assign('lists',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/users_assist_list.htm');
    }
    die(json_encode($res));
}
/* 清除商品浏览历史 */
elseif ($action == 'clear_history')
{
    setcookie('AOS[history]',   '', 1);
}
/* 物流信息 */
elseif ($action == 'delivery_info'){
    $_GET['order_sn'] = trim($_GET['order_sn']);
    $order_sn = empty($_GET['order_sn']) ? '' : addslashes($_GET['order_sn']);

    if (empty($order_sn))
    {
        show_message('无效订单号', '', 'index.php?c=user&a=order_list');
    }

    $sql = "SELECT shipping_name, invoice_no ".
           " FROM " . $aos->table('order_info').
           " WHERE order_sn = '$order_sn' and user_id = ".$_SESSION['user_id']." LIMIT 1";
    $row = $db->getRow($sql);
	$shipping_name = $row['shipping_name'];
    $shipping_code = $db->getOne("SELECT shipping_code ".
           " FROM " . $aos->table('shipping').
           " WHERE shipping_name = '".$shipping_name."'");
	$invoice_no = $row['invoice_no'];
    if($invoice_no)
    {
        $delivery_info = get_express($shipping_code,$invoice_no);
        $smarty->assign('delivery_info', $delivery_info);
    }
    $smarty->display('delivery_info.htm');
}

?>