//取消申请记录
function drop_cancel(rec_id)
{
  layer.open({
    content: '您确定要删除此条记录吗？',
    btn: ['确认', '取消'],
    shadeClose: false,
    yes: function(index){
      var id = rec_id;
      $.ajax({
        type: 'GET',
        data: {id},
        url: 'index.php?c=user&a=cancel',
        dataType: 'json',
        success: function(result){
         if (result.error > 0)
          {
            $('#item_'+result.rec_id).remove();
          }
          else
          {
            layer.open({content: result.message,skin: 'msg',time: 2});
          }     
        }
      });
	    layer.close(index);
    }
  });
}
//确认收货
function affirm_received(order_id,page=0)
{
  layer.open({
    content: '你确认已经收到货物了吗？',
    btn: ['确认', '取消'],
    shadeClose: false,
    yes: function(index){
      $.ajax({
        type: 'POST',
        url: 'index.php?c=user&a=affirm_received&order_id=' + order_id,
        dataType: 'json',
        success: function(data){
          if(data.err==0){
            if(page==1)
            {
              $("#j_receive_"+data.order_id).remove();//订单详情
            }
            else
            {
              $("#item_"+data.order_id).remove();//订单列表         
            }
            layer.close(index);  
          }
        }   
      })
    }
  });
}
//取消订单
function cancel_order(order_id,page=0)
{
  layer.open({
    content: '确认要取消订单吗？',
    btn: ['确认', '取消'],
    shadeClose: false,
    yes: function(index){
      $.ajax({
        type: 'POST',
        url: 'index.php?c=user&a=cancel_order&order_id=' + order_id,
        dataType: 'json',
        success: function(data){
          if(data.err==0){

            if(page==1)
            {
              location.href = 'index.php?c=user&a=order_list';//订单详情
            }
            else
            {

              $("#item_"+data.order_id).remove();//订单列表
              layer.open({content:'删除成功',skin: 'msg',time: 2});         
            }
          }
          else
          {
            layer.open({content: result.msg,skin: 'msg',time: 2});
          }
        }   
      })
    }
  });
}
//核销
function verification(order_id)
{
  var vericode = '<img src="api/qrcode.php?c=verification&id='+order_id+'">';
  $(".box-hide").fadeToggle();
  $(".box-veri").fadeToggle();
  $(".veri-code").html(vericode);
}
//评论
function comment(goods_id,order_id){
  $(".box-hide").fadeToggle();
  $(".box-comment").fadeToggle();
  $("#id").val(goods_id)
  $("#oid").val(order_id);
}

