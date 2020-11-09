<div class="menu_box">
    <?php $_from = $this->_var['menus']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['menu']):
?>
    <dl>
        <dt><i class="icon_menu_<?php echo $this->_var['k']; ?>"></i><?php echo $this->_var['menu']['label']; ?></dt>
        <?php $_from = $this->_var['menu']['children']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'child');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['child']):
?>
        <dd<?php if ($this->_var['action'] == 'wechat' || $this->_var['action'] == 'app'): ?><?php if ($this->_var['child']['key'] == $this->_var['operation']): ?> class="cur"<?php endif; ?><?php else: ?><?php if ($this->_var['child']['action'] == $this->_var['action']): ?> class="cur"<?php endif; ?><?php endif; ?>><a href="index.php?act=<?php echo $this->_var['child']['action']; ?>&op=<?php echo $this->_var['child']['key']; ?>"><?php echo $this->_var['child']['label']; ?></a></dd>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </dl>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</div>