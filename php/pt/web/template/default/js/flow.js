/* $Id : shopping_flow.js 4865 2007-01-31 14:04:10Z paulgao $ */

var selectedShipping = null;
var selectedPayment  = null;

/* *
 * 改变配送方式
 */
function selectShipping(shipping_id)
{
  ShippingHide(shipping_id);
  var address_id = $("input[name='address_id']").val();
  $.ajax({
        type: 'GET',
        data: {shipping_id},
        url: 'index.php?c=flow&a=select_shipping',
        dataType: 'json',
        success: function(result){
          if(result.error == 1)
          {
            layer.open({content: '您的购物车中没有商品！',skin: 'msg',time: 2});
          }
          else if(result.error == 2)
          {
            layer.open({content: '您选择的优惠券并不存在。',skin: 'msg',time: 2});
          }
          if(result.area == 1 && address_id > 0)
          {
            layer.open({content: '您的地区暂不支持配送',skin: 'msg',time: 2});
            $('.no-area').css('display','block');
          }
          else
          {
            $('.no-area').css('display','none');
          }
          orderSelectedResponse(result);
        }
    });
}

/*切换自提点和收货地址*/
function ShippingHide(shipping_id)
{
  var address_info = document.getElementById("address-info");
	var store_info = document.getElementById("store-info");
  if(shipping_id == 1)
  {
    address_info.style.display = "none";
    store_info.style.display = "block";
  }
  else if(shipping_id == 2)
  {
	  address_info.style.display = "block";
    store_info.style.display = "none";
  }
}

/**
 *
 */


/* *
 * 改变支付方式
 */
function selectPayment(payment_id)
{
    $.ajax({
        type: 'GET',
        data: {payment_id},
        url: 'index.php?c=flow&a=select_payment',
        dataType: 'json',
        success: function(result){
          if(result.error == 1)
          {
            layer.open({content: '您的购物车中没有商品！',skin: 'msg',time: 2});
          }
          //else if(result.error == 2)
          //{
            //layer.open({content: '您选择的优惠券并不存在。',skin: 'msg',time: 2});
          //}
          //orderSelectedResponse(result);
        }
    });
}

/* *
 * 回调函数
 */
function orderSelectedResponse(result)
{
  if (result.error)
  {
    layer.open({content: result.error,skin: 'msg',time: 2});
    location.href = './';
  }

  try
  {
    var layer = document.getElementById("J_total");

    layer.innerHTML = (typeof result == "object") ? result.content : result;
    if (result.payment != undefined)
    {
      var surplusObj = document.forms['theForm'].elements['surplus'];
      if (surplusObj != undefined)
      {
        surplusObj.disabled = result.pay_code == 'balance';
      }
    }
  }
  catch (ex) { }
}

/* *
 * 改变优惠券
 */
function changeBonus(val)
{
    
  var bonus = val;
  $.ajax({
      type: 'GET',
      data: {bonus},
      url: 'index.php?c=flow&a=change_bonus',
      dataType: 'json',
      success: function(obj){
        if (obj.error)
        {
          layer.open({content: obj.error,skin: 'msg',time: 2});
        }
        else
        {
          if(obj.formated)
          {
            $("#J_coupon").html('- '+obj.formated+'元');
          }
          else
          {
            $("#J_coupon").html('不使用优惠券');
          }
          $('#coupon_'+val).siblings('dl').removeClass('on');
          $('#coupon_'+val).addClass('on');
          $(".coupon-list").slideUp();
          $(".box-hide").hide();

          
          orderSelectedResponse(obj.content);
        }
      }
  });
}


function openCoupon()
{
  $(".box-hide").show();
  $(".coupon-list").slideDown();
}
function closeCoupon()
{
  $(".coupon-list").slideUp();
  $(".box-hide").hide();
}

/* *
 * 检查提交的订单表单
 */
function checkOrderForm(frm)
{
  var paymentSelected = false;
  var shippingSelected = false;

  // 检查是否选择了支付配送方式
  for (i = 0; i < frm.elements.length; i ++ )
  {
    if (frm.elements[i].name == 'shipping' && frm.elements[i].checked)
    {
      shippingSelected = true;
    }

    if (frm.elements[i].name == 'payment' && frm.elements[i].checked)
    {
      paymentSelected = true;
    }
  }
  
  if (!shippingSelected)
  {
    layer.open({content: '请选择配送方式',skin: 'msg',time: 2});
    
    return false;
  }

  if ( ! paymentSelected)
  {
    layer.open({content: '请选择支付方式',skin: 'msg',time: 2});
    return false;
  }

  var shipping_id = $("input[name='shipping']:checked").val();
  var address_id = $("input[name='address_id']").val();
  var goods_num = $("input[name='goods_num']").val();

  if(goods_num < 1)
  {
    layer.open({content: '您的购物车中没有商品！',skin: 'msg',time: 2});
    return false;
  }

  if(shipping_id==1){
    if($("input[name='consignee']").val()==""){
      layer.open({content: '请填写姓名',skin: 'msg',time: 2});
      return false;
    }
    if($("input[name='mobile']").val()==""){
      layer.open({content: '请填写手机号码',skin: 'msg',time: 2});
      return false;
    }
  }
  if(shipping_id==2){
    if(address_id<1){
      layer.open({content: '请选择收货地址',skin: 'msg',time: 2});
      return false;
    }
  }
  

  frm.action = frm.action + '&a=done';
  return true;
}
