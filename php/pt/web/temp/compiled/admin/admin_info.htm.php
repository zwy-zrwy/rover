<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
      <h2>管理员</h2>
      <div class="tab_navs">
        <ul>
          <li class="cur"><a href="javascript:void(0);"><?php if ($this->_var['form_act'] == 'insert'): ?>添加<?php else: ?>修改管理<?php endif; ?></a></li>
        </ul>
      </div>
    </div>
    <div class="main_bd">
      <form name="theForm" method="post" enctype="multipart/form-data" onsubmit="return validate();">
      <table class="table">
        <tr>
          <td>用户名</td>
          <td><input type="text" name="user_name" maxlength="20" value="<?php echo htmlspecialchars($this->_var['user']['user_name']); ?>" size="34"/></td>
        </tr>
       <?php if ($this->_var['action'] == "add"): ?>
        <tr>
          <td>密码</td>
          <td><input type="password" name="password" maxlength="32" size="34" /></td>
        </tr>
        <tr>
          <td>确认密码</td>
          <td><input type="password" name="pwd_confirm" maxlength="32" size="34" /></td>
        </tr>
        <?php endif; ?>
        <?php if ($this->_var['action'] != "add"): ?>
        <tr>
          <td>旧密码</td>
          <td><input type="password" name="old_password" size="34" /></td>
        </tr>
        <tr>
          <td>新密码</td>
          <td><input type="password" name="new_password" maxlength="32" size="34" /></td>
        </tr>
        <tr>
          <td>确认密码</td>
          <td><input type="password" name="pwd_confirm" value="" size="34" /></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" value="确定" class="btn" />
            <input type="hidden" name="op" value="<?php echo $this->_var['form_act']; ?>" />
            <input type="hidden" name="token" value="<?php echo $this->_var['token']; ?>" />
            <input type="hidden" name="id" value="<?php echo $this->_var['user']['user_id']; ?>" /></td>
        </tr>
      </table>
      </form>
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.htm'); ?>