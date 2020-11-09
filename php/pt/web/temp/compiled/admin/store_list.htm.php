<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
	    <h2>门店管理</h2>
  		<div class="tab_navs">
  		  <ul>
  			  <li class="cur"><a href="javascript:void(0);">门店管理</a></li>
          <li><a href="index.php?act=store&amp;op=store_add">添加门店</a></li>
  			</ul>
  		</div>
	  </div>
  	<div class="main_bd">
      <table class="table">
        <thead>
        <tr class="thead">
          <th>门店名称</th>
          <th>手机号码</th>
          <th>详细地址</th>
          <th>门店描述</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php $_from = $this->_var['store_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'store');if (count($_from)):
    foreach ($_from AS $this->_var['store']):
?>
        <tr>
          <td><?php echo htmlspecialchars($this->_var['store']['store_name']); ?></td>
          <td><?php echo $this->_var['store']['store_mobile']; ?></td>
          <td><?php echo $this->_var['store']['store_address']; ?></td>
          <td><?php echo nl2br($this->_var['store']['store_desc']); ?></td>
          <td align="center">
            <a href="index.php?act=store&amp;op=wxmanage&amp;id=<?php echo $this->_var['store']['store_id']; ?>">绑定核销员</a>
            <a href="index.php?act=store&amp;op=store_edit&amp;id=<?php echo $this->_var['store']['store_id']; ?>">修改</a>
            <a href="index.php?act=store&op=remove&id=<?php echo $this->_var['store']['store_id']; ?>" onclick="{if(confirm('您确实要删除该门店吗？')){return true;}return false;}">删除</a>
          </td>
        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
      </table>
	  <?php echo $this->_var['pager']; ?>
  	</div>
  </div>
</div>
<?php echo $this->fetch('footer.htm'); ?>