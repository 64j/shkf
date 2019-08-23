<?php
defined('IN_MANAGER_MODE') or die();

global $_style, $_lang, $modx_manager_charset, $lastInstallTime, $modx_lang_attribute;

require_once MODX_MANAGER_PATH . 'includes/header.inc.php';

require_once MODX_BASE_PATH . 'assets/modules/shkf/controller/module.php';
$module = new ShkF\Module();
$module->version = '1.0';
$module->mod_page = 'index.php?a=112&id=' . $_GET['id'];
$module->action = !empty($_GET['action']) ? $_GET['action'] : (!empty($_POST['action']) ? $_POST['action'] : '');

echo $module->render();

require_once MODX_MANAGER_PATH . 'includes/footer.inc.php';
