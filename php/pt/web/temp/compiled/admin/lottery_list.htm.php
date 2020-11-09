<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
	    <h2>限时抽奖</h2>
    	<div class="tab_navs">
    	  <ul>
    		  <li class="cur"><a href="javascript:void(0);">抽奖管理</a></li>
    			<li><a href="index.php?act=lottery&amp;op=lottery_add">添加抽奖</a></li>
    		</ul>
    	</div>
	  </div>
  	<div class="main_bd">
      <table class="table">
        <thead>
        <tr class="thead">
          <th width="80">ID</th>
          <th>商品名称</th>
          <th width="160">活动时间</th>
          <th width="100">状态</th>
          <th width="100">首页推荐</th>
          <th width="150">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php $_from = $this->_var['lottery_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'lottery');if (count($_from)):
    foreach ($_from AS $this->_var['lottery']):
?>
        <tr id="remove_<?php echo $this->_var['lottery']['lottery_id']; ?>">
          <td><?php echo $this->_var['lottery']['lottery_id']; ?></td>
          <td><?php echo $this->_var['lottery']['goods_name']; ?></td>
          <td><?php echo $this->_var['lottery']['lottery_start_time']; ?><br/><?php echo $this->_var['lottery']['lottery_end_time']; ?></td>
          <td><?php echo $this->_var['lottery']['status']; ?></td>
          <td class="binding" width="50">
            <span><i class="fa <?php if ($this->_var['lottery']['enabled']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_enabled', <?php echo $this->_var['lottery']['lottery_id']; ?>)"></i></span>
          </td>
          <td>
            <a href="index.php?act=lottery&amp;op=view&amp;id=<?php echo $this->_var['lottery']['lottery_id']; ?>">查看</a>
            <a href="index.php?act=lottery&amp;op=lottery_edit&amp;id=<?php echo $this->_var['lottery']['lottery_id']; ?>">修改</a>
            <a  href="javascript:" onclick="remove(<?php echo $this->_var['lottery']['lottery_id']; ?>, 'remove')" title="<?php echo $this->_var['lang']['remove']; ?>"title="<?php echo $this->_var['lang']['remove']; ?>">删除</a>
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