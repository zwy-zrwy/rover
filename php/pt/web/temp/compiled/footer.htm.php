<footer>
    <ul class="footer fixed">
	    <li class="home"><a href="index.php"><i class="iconfont">&#xe61d;</i>首页</a></li>
		<li class="rank"><a href="index.php?c=rank"><i class="iconfont">&#xe61a;</i>热榜</a></li>
		<li class="category"><a href="index.php?c=category"><i class="iconfont">&#xe605;</i>分类</a></li>
		<li class="user"><a href="index.php?c=user"><i class="iconfont">&#xe633;</i>我的</a></li>
	</ul>
</footer>
<script>
  window.onload = function() {
    $('#loading').css('display','none'); 
    $('#container').css('display','');
    setInterval("order_time_ajax()",5000);
    setInterval("changeAuto()",5000);
  }
</script>
<?php if ($this->_var['stats_code']): ?><?php echo $this->_var['stats_code']; ?><?php endif; ?>