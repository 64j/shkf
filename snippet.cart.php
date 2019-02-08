<?php
/**
 * Cart snippet
 * @author 64j
 * @package ShkF
 */
if (!defined('MODX_BASE_PATH')) {
    die('Unauthorized access.');
}

require_once MODX_BASE_PATH . 'assets/modules/shkf/abstract.shkf.php';
require_once MODX_BASE_PATH . 'assets/modules/shkf/controller/cart.php';

$params = !empty($params) ? $params : [];
$params['id'] = !empty($params['id']) ? $params['id'] : uniqid('cart_');
$params['async'] = isset($params['async']) ? $params['async'] : false;
$params['dataType'] = !empty($params['dataType']) ? $params['dataType'] : 'html';
$params['ownerTPL'] = !empty($params['ownerTPL']) ? $params['ownerTPL'] : '@CODE:<div id="[+cart.id+]">[+cart.count+]</div>';
$params['noneTPL'] = !empty($params['noneTPL']) ? $params['noneTPL'] : $params['ownerTPL'];
$params['tplParams'] = !empty($params['tplParams']) ? $params['tplParams'] : '@CODE:<div>[+params+]</div>';
$params['tplParam'] = !empty($params['tplParam']) ? $params['tplParam'] : '@CODE:[+name+]:[+values+]<br>';
$params['paramSeparator'] = !empty($params['paramSeparator']) ? $params['paramSeparator'] : ', ';

$modx->jscripts[$params['prefix'] . '_jscripts'] = '
<script src="assets/modules/shkf/js/shkf.js?v=128"></script>
<script>var shkf = new shkf({prefix: \'' . $params['prefix'] . '\'});</script>';

$config = json_decode($modx->snippetCache['shkfCartProps'], true);
$DL_config = array_diff($params, $config);

$_ = ['[*', '*]', '[(', ')]', '{{', '}}', '[[', ']]', '[+', '+]'];
$__ = ['\[\*', '\*\]', '\[\(', '\)\]', '\{\{', '\}\}', '\[\[', '\]\]', '\[\+', '\+\]'];
foreach ($DL_config as $k => $v) {
    if (in_array($k, ['ownerTPL', 'tpl', 'noneTPL', 'tplParams', 'tplParam'])) {
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

$shkf = \ShkF\Cart::getInstance($params);

if (empty($params['async'])) {
    return $shkf->run()
        ->toHtml();
} else {
    $tpl = empty($shkf->getSession('items')) ? $params['noneTPL'] : $params['ownerTPL'];
    return $modx->parseText($modx->getTpl($tpl), ['cart.id' => $params['id']]);
}
