<?php
/**
 * Created by PhpStorm.
 * @author 64j
 */
define('MODX_API_MODE', true);

include_once("../../../index.php");

$modx->db->connect();

if (empty($modx->config)) {
    $modx->getSettings();
}

if (empty($modx->snippetCache['shkfCartProps']) || (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'))) {
    $modx->sendErrorPage();
}

require_once MODX_BASE_PATH . 'assets/modules/shkf/abstract.shkf.php';
require_once MODX_BASE_PATH . 'assets/modules/shkf/controller/cart.php';

echo \ShkF\Cart::getInstance()
    ->run()
    ->toJson();
