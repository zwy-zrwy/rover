<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
	    <h2>商品分类</h2>
    	<div class="tab_navs">
    	  <ul>
    		  <li class="cur"><a href="javascript:void(0);">分类管理</a></li>
    			<li><a href="index.php?act=category&amp;op=category_add">添加分类</a></li>
    		</ul>
    	</div>
	  </div>
    <div class="highlight_box">
      <p class="desc">删除分类前请先删除分类商品子分类</p>
    </div>
  	<div class="main_bd">
      <table class="table">
        <thead>
        <tr class="thead">
          <th width="60">ID</th>
      	  <th width="100">排序</th>
          <th>分类名称</th>
          <th>是否显示</th>
          <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php $_from = $this->_var['cat_info']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'cat');if (count($_from)):
    foreach ($_from AS $this->_var['cat']):
?>
        <tr class="<?php echo $this->_var['cat']['level']; ?>" id="remove_<?php echo $this->_var['cat']['cat_id']; ?>">
        <td><?php echo $this->_var['cat']['cat_id']; ?></td>
      	<td>
      	    <span class="edit" onclick="edit(this, 'edit_sort_order', <?php echo $this->_var['cat']['cat_id']; ?> ,'category')" class="editable"><?php echo $this->_var['cat']['sort_order']; ?></span>
      	</td>
      	<td class="w50pre name">
      	  <?php if ($this->_var['cat']['level'] > 0): ?><img src="static/images/tv-item1.gif"/><?php endif; ?>
            <span class="editable"><?php echo $this->_var['cat']['cat_name']; ?></span>
          </td>
          <td class="binding">
                      
                          <span><i class="fa <?php if ($this->_var['cat']['is_show'] == '1'): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_is_show', <?php echo $this->_var['cat']['cat_id']; ?>)"></i></span>
                      
                      </td>



          
          <td>
            <a href="index.php?act=category&amp;op=edit&amp;cat_id=<?php echo $this->_var['cat']['cat_id']; ?>">修改</a>
            <a href="javascript:;" onclick="remove(<?php echo $this->_var['cat']['cat_id']; ?>,  'remove')" title="<?php echo $this->_var['lang']['remove']; ?>">删除</a>
            
          </td>
        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
      </table>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.htm'); ?>