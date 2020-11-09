<div class="search-view">
    <div class="search-top">
        <form action="index.php" class="search-form" oninput="changes()">
	        <div class="form" action="index.php">
	            <i class="search-icon"></i>
	            <input type="hidden" name="c" value="search">
	            <input type="text" name="key" id="key" value="<?php echo $this->_var['key']; ?>" class="key" placeholder="输入商品名称">
	            <i class="search-clear"></i>
	        </div>
	        <div id="cancel-view" class="cancel-view">取消</div>
	        <div id="submit-view" class="submit-view">搜索</div>
        </form>
    </div>
    <div id="key-list" class="key-list"></div>
    <!--div class="search-mod search-history">
        <h3>历史搜索<i></i></h3>
        <a href="#">猕猴桃</a>
        <a href="#">猕猴桃</a>
        <a href="#">猕猴桃</a>
    </div-->
    <div class="search-mod">
        <h3>热门搜索</h3>
        <?php $_from = $this->_var['searchkeywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'key');if (count($_from)):
    foreach ($_from AS $this->_var['key']):
?>
        <a href="index.php?c=search&key=<?php echo $this->_var['key']; ?>"><?php echo $this->_var['key']; ?></a>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
</div>
<script>
function changes(){
  var searchKey = $('#key').val();
  if(searchKey){
	$('.search-clear').show();
	$('#submit-view').show();
	$('#cancel-view').hide();
	$('.search-mod').hide();
	$('#key-list').show();
  }
  else
  {
	searchHide();
  }

    $.ajax({ 
        'url':'index.php?c=search&a=ajax_key', //服务器的地址 
        'data':{'key':searchKey}, //参数 
        'dataType':'json', //返回数据类型 
        'type':'POST', //请求类型 
        'success':function(data){
            var html= ""; 
            if(data.length) { 
                $.each(data, function(index,term) { 
                html +="<a href='index.php?c=search&key="+term.keywords+"'>"+term.keywords+"</a>";
                });
            }
            $("#key-list").html(html); 
        } 
    });

}
$(".submit-view").click(function() {
    $(".search-form").submit();
});
$('.search-clear').click(function(){
	searchHide();
});
$('.cancel-view').click(function(){
    $('.search-view').hide();
});

function searchHide(){
	$('.key').val('');
	$('.search-clear').hide();
	$('#submit-view').hide();
	$('#cancel-view').show();
	$('.search-mod').show();
	$('#key-list').hide();
}
</script>