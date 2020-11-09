<div class="box-comment">
  <a class="box-close" href="javascript:void(0)" onClick="comment()" title="关闭"></a>
  <h3>发表评价</h3>
  <dl>
      <dt>评分</dt>
      <dd>
          <i></i>
          <i></i>
          <i></i>
          <i></i>
          <i></i>
      </dd>
  </dl>
  <p><textarea name="content" id="comment" placeholder="在此输入商品评价"></textarea></p>
  <p><input class="btn-send" type="submit" value="提交评论"></p>
  <input name="id" id="id" type="hidden" value="">
  <input name="oid" id="oid" type="hidden" value="">
</div>
<script type="text/javascript">
$(function () {
  $(".box-comment dd i").click(
      function(){
          var num = $(this).index()+1;
          var len = $(this).index();
          var thats = $(this).parent(".box-comment dd").find("i");
          if($(thats).eq(len).attr("class")=="on"){
              if($(thats).eq(num).attr("class")=="on"){
                  $(thats).removeClass();
                  for (var i=0 ; i<num; i++) {
                      $(thats).eq(i).addClass("on");
                  }
              }else{
                  $(thats).removeClass();
                  for (var k=0 ; k<len; k++) {
                      $(thats).eq(k).addClass("on");
                  }
              }
          }else{
              $(thats).removeClass();
              for (var j=0 ; j<num; j++) {
                  $(thats).eq(j).addClass("on");
              }
          }
      }
  );
  $(".btn-send").click(function(event) {
     var comment = $.trim($("#comment").val());
     var rank = $('.box-comment .on').length;
     var id = $("#id").val();
     var oid = $("#oid").val();

     if (comment.length>0) {
      $.ajax({
        url: 'index.php?c=comment&a=create',
        type: 'POST',
        dataType: 'json',
        data: {comment:comment, rank: rank,id_value: id,order_id: oid},
        success: function (res) {
          if(res.isError == 1){
            layer.open({
              content: res.message,
              btn: ['嗯']
            });
          }else{
            location.href='index.php?c=user&a=order_list&default=6';
          }
        }
      })
     }
  });
});
</script>