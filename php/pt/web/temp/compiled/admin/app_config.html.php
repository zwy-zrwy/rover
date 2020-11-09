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
            <li class="cur"><a href="javascript:void(0);">管理</a></li>
        </ul>
      </div>
    </div>
    <div class="main_bd">
      <form name="theForm"  method="post" action="index.php?act=app&op=app_config">
        <table class="table">
        <tbody>
        <tr>
          <td>AppId:</td>
          <td><input type="text" name="appid" size="20" value="<?php echo $this->_var['ret']['appid']; ?>" datatype="s18-18"></td>
        </tr>
        <tr>
          <td>AppSecret:</td>
          <td><input type="text" name="appsecret" size="32" value="<?php echo $this->_var['ret']['appsecret']; ?>" datatype="s32-32"></td>
        </tr>
        <tr>
          <td>followkey:</td>
          <td><input type="text" name="followkey" size="32" value="<?php echo $this->_var['ret']['followkey']; ?>" datatype="s32-32"></td>
        </tr>
        <tr>
          <td colspan="2" align="center">
            <input type="submit" value="提交" class="btn"/>
          </td>
        </tr>
        </tbody>
        </table>
      </form>
    </div>
  </div>
</div>
<script>
  $(function(){
    $(".wechatForm").Validform();
  });
</script>    

<?php echo $this->fetch('footer.htm'); ?>