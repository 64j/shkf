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

//if (empty($modx->snippetCache['shkfCartProps']) || (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'))) {
//    $modx->sendErrorPage();
//}

header('content-type: application/json');

include_once 'abstract.shkf.php';
include_once 'controller/cart.php';

echo (new \ShkF\Cart())->run()
    ->toJson();
