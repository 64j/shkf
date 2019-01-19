<?php
/**
 * Cart snippet
 * @author 64j
 * @package ShkF
 */
if (!defined('MODX_BASE_PATH')) {
    die('Unauthorized access.');
}

$params = !empty($params) ? $params : [];
$params['id'] = !empty($params['id']) ? $params['id'] : uniqid('cart_');
$params['async'] = isset($params['async']) ? $params['async'] : 1;
$params['dataType'] = !empty($params['dataType']) ? $params['dataType'] : 'json';
$params['noneTPL'] = !empty($params['noneTPL']) ? $params['noneTPL'] : '@CODE:<div id="' . $params['id'] . '">[+cart.count+]</div>';
$params['ownerTPL'] = !empty($params['ownerTPL']) ? $params['ownerTPL'] : '@CODE:<div id="' . $params['id'] . '">[+cart.count+]</div>';

$modx->jscripts[$params['prefix'] . '_jscripts'] = '
<script src="assets/modules/shkf/js/shkf.js?v=128"></script>
<script>var shkf = new shkf({prefix: \'' . $params['prefix'] . '\'});</script>';

$config = json_decode($modx->snippetCache['shkfCartProps'], true);
$DL_config = array_diff($params, $config);

$_ = ['[*', '*]', '[(', ')]', '{{', '}}', '[[', ']]', '[+', '+]'];
$__ = ['\[\*', '\*\]', '\[\(', '\)\]', '\{\{', '\}\}', '\[\[', '\]\]', '\[\+', '\+\]'];
foreach ($DL_config as $k => $v) {
    if (in_array($k, ['ownerTPL', 'tpl', 'noneTPL'])) {
        $v = $modx->getTpl($v);
        $v = str_replace($_, $__, $v);
    }
    $v = preg_replace('|\s+|u', ' ', $v);
    //$v = str_replace('> <', '><', $v);
    $DL_config[$k] = $v;
}

$DL_config = json_encode($DL_config, JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
$modx->regClientHTMLBlock('<script id="options_' . $params['prefix'] . '_' . $params['id'] . '">
shkf.init(' . $DL_config . ');
</script>');

if (empty($params['async'])) {
    include_once 'abstract.shkf.php';
    include_once 'controller/cart.php';
    $shkf = \ShkF\Cart::getInstance($params)
        ->run();
    return $shkf->toHtml();
} else {
    return $params['dataType'] == 'info' ? $modx->parseText($modx->getTpl($params['ownerTPL']),
        $params) : '<div id="' . $params['id'] . '">[+cart.count+]</div>';
}
