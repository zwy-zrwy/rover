<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
	    <h2>助力活动</h2>
    	<div class="tab_navs">
    	  <ul>
    		  <li class="cur"><a href="javascript:void(0);">助力管理</a></li>
    			<li><a href="index.php?act=assist&amp;op=assist_add">添加助力</a></li>
    		</ul>
    	</div>
	  </div>
  	<div class="main_bd">
      <table class="table">
        <thead>
        <tr class="thead">
          <th width="50">ID</th>
          <th>商品名称</th>
          <th width="160">活动时间</th>
          <th width="80">已秒</th>
          <th width="140">状态</th>
          <th width="100">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php $_from = $this->_var['assist_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'assist');if (count($_from)):
    foreach ($_from AS $this->_var['assist']):
?>
        <tr id="remove_<?php echo $this->_var['assist']['assist_id']; ?>">
          <td><?php echo $this->_var['assist']['assist_id']; ?></td>
          <td><?php echo $this->_var['assist']['goods_name']; ?></td>
          <td><?php echo $this->_var['assist']['assist_start_time']; ?><br/><?php echo $this->_var['assist']['assist_end_time']; ?></td>
          <td><?php echo $this->_var['assist']['assist_sales']; ?></td>
          <td><?php echo $this->_var['assist']['status']; ?></td>
          <td>
            <a href="index.php?act=assist&amp;op=assist_edit&amp;id=<?php echo $this->_var['assist']['assist_id']; ?>">修改</a>
            <a  href="javascript:" onclick="remove(<?php echo $this->_var['assist']['assist_id']; ?>, 'remove')" title="<?php echo $this->_var['lang']['remove']; ?>">删除</a>
          </td>
        </tr>

        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
      </table>
      <div class="pagination">
        <?php echo $this->_var['pager']; ?>
      </div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.htm'); ?>