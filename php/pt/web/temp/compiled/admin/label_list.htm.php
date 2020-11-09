<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
	    <h2>商品标签</h2>
  	</div>
  	<div class="main_bd">
      <table class="table">
        <thead>
        <tr>
          <th>ID</th>
          <th>名称</th>
          <th>简介</th>
          <th>启用</th>
        </tr>
        </thead>
        <tbody>
        <form name="theForm" action="index.php?act=label&op=post" method="post">
        <?php $_from = $this->_var['label_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'label');if (count($_from)):
    foreach ($_from AS $this->_var['label']):
?>
        <tr>
          <td><?php echo $this->_var['label']['label_id']; ?><input type="hidden" name="label_id[]" value="<?php echo $this->_var['label']['label_id']; ?>"/></td>
          <td><input name="label_name[]" type="text" value="<?php echo $this->_var['label']['label_name']; ?>" /></td>
          <td><input name="label_desc[]" type="text" value="<?php echo $this->_var['label']['label_desc']; ?>" style="width:500px;" /></td>
          <td class="binding" width="50">
            <span><i class="fa <?php if ($this->_var['label']['enabled']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_enabled', <?php echo $this->_var['label']['label_id']; ?>)"></i></span>
          </td>
        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        <tr>
          <td colspan="4">新增一个</td>
        </tr>
        <tr>
          <td><input type="hidden" name="label_id[]" value=""/></td>
          <td><input name="label_name[]" type="text" value="" /></td>
          <td colspan="2"><input name="label_desc[]" type="text" value="" style="width:500px;"/></td>
        </tr>
        <tr class="tfoot">
          <td colspan="4"><input name="submit" type="submit" value="修改" class="btn" /></td>
        </tr>
        </form>
        </tbody>
      </table>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.htm'); ?>