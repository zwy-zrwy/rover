<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
      <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
	    <h2>商品评论</h2>
  		<div class="tab_navs">
  		    <ul>
  			    <li <?php if ($this->_var['type'] == 1): ?>class="cur"<?php endif; ?>><a href="index.php?act=comment&op=comment_list">显示中</a></li>
  				<li <?php if ($this->_var['type'] == 0): ?>class="cur"<?php endif; ?>><a href="index.php?act=comment&op=comment_list&type=1">隐藏中</a></li>
  			</ul>
  		</div>
  	</div>
  	<div class="main_bd">
      <div class="top_s">
        <form action="javascript:searchComment()" name="searchForm">
          <label>输入评论内容</label><input type="text" name="keyword" />
          <input type="submit" class="btn" value="搜索" />
        </form>
      </div>
      <table class="table">
        <thead>
        <tr class="thead">
          <th width="60"><input onclick='selectAll(this, "checkboxes")' type="checkbox">ID</th>
          <th width="100">用户</th>
          <th>评论内容</th>
          <th width="100">评论时间</th>
          <th width="60">推荐</th>
          <th width="60">状态</th>
          <th width="60">操作</th>
        </tr>
        </thead>
        <form method="POST" action="index.php?act=comment" name="listForm" >
        <tbody>
        <?php $_from = $this->_var['comment_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'comment');if (count($_from)):
    foreach ($_from AS $this->_var['comment']):
?>
        <tr id="remove_<?php echo $this->_var['comment']['comment_id']; ?>">
          <td><input value="<?php echo $this->_var['comment']['comment_id']; ?>" name="checkboxes[]" type="checkbox"><?php echo $this->_var['comment']['comment_id']; ?></td>
          <td><?php echo $this->_var['comment']['nickname']; ?></td>
          <td>评论内容：<?php echo $this->_var['comment']['content']; ?><br/>评论商品：<?php echo $this->_var['comment']['title']; ?></td>
          <td><?php echo $this->_var['comment']['add_time']; ?></td>
          <td class="binding">
            <span><i class="fa <?php if ($this->_var['comment']['is_top'] == '1'): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_is_top', <?php echo $this->_var['comment']['comment_id']; ?>)"></i></span> 
          </td>
          <td class="binding">
            <span><i class="fa <?php if ($this->_var['comment']['status'] == '1'): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_status', <?php echo $this->_var['comment']['comment_id']; ?>)"></i></span> 
          </td>
          <td>
            <a href="javascript:" onclick="remove(<?php echo $this->_var['comment']['comment_id']; ?>, 'remove')">删除</a>
          </td>
        </tr>

          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
      </table>
      <div class="pagination">
        <div class="handler"><!--onsubmit="return confirm_bath()"-->
          
            <select name="sel_action">
              <option value="remove">删除评论</option>
              <option value="allow">允许显示</option>
              <option value="deny">禁止显示</option>
            </select>
            <input type="hidden" name="op" value="batch" />
            <input type="submit" name="drop" id="btnSubmit" value="提交" class="btn" />
          </form>
        </div>
        <?php echo $this->_var['pager']; ?>
      </div>
		</div>
	</div>
</div>
<?php echo $this->fetch('footer.htm'); ?>