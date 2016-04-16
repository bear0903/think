<?php /* Smarty version 2.6.11, created on 2016-03-03 16:43:03
         compiled from block_info.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'block_info.html', 6, false),)), $this); ?>

<div class="ui-widget">
    <div class="ui-state-highlight ui-corner-all">
        <p style="padding: 5px;margin:0px;">
            <span style="float:left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>
            <?php echo ((is_array($_tmp=@$this->_tpl_vars['msg_txt'])) ? $this->_run_mod_handler('default', true, $_tmp, 'No Data Found.') : smarty_modifier_default($_tmp, 'No Data Found.')); ?>

        </p>
    </div>
</div>