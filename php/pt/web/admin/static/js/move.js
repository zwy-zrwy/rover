$(function() {
  //左移 
  $(".J_leftMove").click(function(){
    var $this = $(this);
    var curLi = $this.parents("li");
    var prevLi = $this.parents("li").prev();
    if(prevLi.length == 0){
	  layer.msg('第一行,想移啥？');
      return;
    }else{
      prevLi.before(curLi);
    }
  });
  //右移
  $(".J_rightMove").click(function(){
    var $this = $(this);
    var curLi = $this.parents("li");
    var nextLi = $this.parents("li").next();
    if(nextLi.length == 0){
	  layer.msg('最后一行,想移啥？');
      return;
    }else{
      nextLi.after(curLi);
    }
  });

  /*SKU图片*/
  $(".skuimg").click(function(){
    var id = $(this).children('input').attr("id").substring(3);
    var src=$("#show_"+id).attr("src");
    if(src)
    {
      $(this).children("img").show();
    }
    else
    {
      $(this).children("img").hide();
    }
  });






});
