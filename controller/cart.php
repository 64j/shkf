<?php namespace ShkF;

/**
 * Class Cart
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author 64j
 * @package ShkF
 */
class Cart extends ShkF
{
    private static $instance = null;

    protected $default_fields = [
        // cart
        'count' => '',
        'key' => '',
        // prepare
        'iteration' => '',
        'title' => '',
        'url' => '',
        // document
        'id' => '',
        'type' => '',
        'contentType' => '',
        'pagetitle' => '',
        'longtitle' => '',
        'description' => '',
        'alias' => '',
        'link_attributes' => '',
        'published' => '',
        'pub_date' => '',
        'unpub_date' => '',
        'parent' => '',
        'isfolder' => '',
        'introtext' => '',
        'content' => '',
        'richtext' => '',
        'template' => '',
        'menuindex' => '',
        'searchable' => '',
        'cacheable' => '',
        'createdon' => '',
        'createdby' => '',
        'editedon' => '',
        'editedby' => '',
        'deleted' => '',
        'deletedon' => '',
        'deletedby' => '',
        'publishedon' => '',
        'publishedby' => '',
        'menutitle' => '',
        'donthit' => '',
        'privateweb' => '',
        'privatemgr' => '',
        'content_dispo' => '',
        'hidemenu' => '',
        'alias_visible' => ''
    ];

    protected $isAjax;

    /**
     * @param array $params
     * @return Cart|null
     */
    public static function getInstance($params = [])
    {
        if (self::$instance != null) {
            self::$instance->setParams($params);
            return self::$instance;
        }

        self::$instance = new self($params);

        return self::$instance;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * @return mixed
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->out['items'];
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        $out = $this->out['cart'];
        unset($out['cart.id']);

        return $out;
    }

    /**
     * @return $this
     */
    public function run()
    {
        $this->setStartConfigs();
        $this->setActions();
        $this->setItems();

        return $this;
    }

    /**
     * определим конфиги для корзины и для DL по умолчанию
     */
    protected function setStartConfigs()
    {
        $DL_config = [
            'async' => 1,
            'dataType' => 'json',
            'id' => 'shkf_cart',
            'tvPrefix' => 'tv',
            'urlScheme' => '',
            'prepare' => '',
            'tvList' => '',
            'selectFields' => 'c.id, c.parent, c.pagetitle, c.longtitle, c.alias, c.isfolder, c.introtext, c.template',
            'ownerTPL' => '@CODE:<div id="[+cart.id+]">[+cart.count+]</div>',
            'noneTPL' => '@CODE:<div id="[+cart.id+]">[+cart.count+]</div>',
            'tpl' => '@CODE:<a href="[+url+]">[+pagetitle+]</a>',
            'tplParams' => '@CODE:<div>as[+params+]</div>',
            'tplParam' => '@CODE:[+param+]<br>',
        ];

        if (!empty($this->params['carts'])) {
            $this->isAjax = true;
            $this->params['carts'] = $this->json_decode($this->params['carts']);
            foreach ($this->params['carts'] as $cartId => $cfg) {
                $this->out['carts'][$cartId] = [];
                $this->getDLConfig($cartId, array_merge($DL_config, $cfg));
            }
        } else {
            $this->isAjax = false;
            $this->cartId = $this->params['id'];
            $this->getDLConfig($this->cartId, array_merge($DL_config, $this->params));
            $this->out['carts'][$this->cartId] = [];
        }
    }

    /**
     * обработка экшен
     */
    protected function setActions()
    {
        if (!empty($this->request['key'])) {
            switch ($this->getRequest('action', '')) {
                case 'add':
                    $this->add();
                    break;
                case 'del':
                    $this->del();
                    break;
                case 'minus':
                case 'plus':
                case 'count':
                case 'recount':
                    $this->recount();
                    break;
            }
        } else {
            if ($this->getRequest('action', '') == 'empty') {
                $this->destroy();
            }
        }
    }

    /**
     * add item to cart
     */
    public function add()
    {
        $key = $this->getKey($this->request['key']);
        $this->session['items'][$key] = $this->setCount($key);
    }

    /**
     * @param $key
     * @return string
     */
    protected function getKey($key)
    {
        $key = trim($key);
        $params = $this->getItemParams($key);
        if (!isset($this->session['items'][$key])) {
            $key .= '#' . md5($this->json_encode($params));
        }
        $this->session['params'][$key] = $params;

        return $key;
    }

    /**
     * @param string $key
     * @return array
     */
    protected function getItemParams($key = '')
    {
        $params = [];
        if (!empty($this->request) && isset($this->request['params'])) {
            foreach ($this->request['params'] as $name => $values) {
                if (!empty($values)) {
                    $params[$name] = [];
                    if (\is_array($values)) {
                        foreach ($values as $k => $v) {
                            $params[$name][] = $this->setItemParam($v);
                        }
                    } else {
                        $params[$name][] = $this->setItemParam($values);
                    }
                }
                array_multisort($params);
            }
        } else {
            $params = !empty($this->session['params'][$key]) ? $this->session['params'][$key] : [];
        }

        return $params;
    }

    /**
     * @param $data
     * @return array
     */
    protected function setItemParam($data)
    {
        $param = [];
        list($price, $key, $value) = explode(':', $data . '::');
        if (!$price && !$key && !$value) {

        } else {
            $param = [
                'key' => $key,
                'value' => !empty($value) ? $value : $key
            ];
            if (!empty($price)) {
                $param['calc'] = preg_replace('/[^\*\/÷\+-]/', '', $price[0]);
                $price = $this->float($price);
                $param['price'] = $price;
            }
        }

        return $param;
    }

    /**
     * @param $key
     * @param bool $set
     * @return int
     */
    protected function setCount($key, $set = false)
    {
        if (empty($set)) {
            $count = isset($this->session['items'][$key]) ? $this->session['items'][$key] : 0;
            if (!empty($this->request['count'])) {
                $count += $this->request['count'];
            } else {
                $count++;
            }
        } else {
            if (!empty($this->request['count'])) {
                $count = $this->request['count'];
            } else {
                $count = 1;
            }
        }

        if (empty($count)) {
            $count = 1;
        }

        $count = str_replace(',', '.', $count);

        return $count;
    }

    /**
     * delete item from cart
     */
    public function del()
    {
        unset($this->session['items'][$this->request['key']]);
        unset($this->session['params'][$this->request['key']]);
    }

    /**
     * recount item
     */
    public function recount()
    {
        $key = $this->getKey($this->request['key']);
        $this->session['items'][$key] = $this->setCount($key, true);
    }

    /**
     * empty cart
     */
    public function destroy()
    {
        $this->session = [];
        $this->docs = [];
        $this->out = [];
    }

    /**
     * находим товары в корзине
     */
    protected function setItems()
    {
        $ids = implode(',', $this->_getDocs());
        if ($ids) {
            foreach ($this->out['carts'] as $cartId => $cart) {
                $this->sum = 0;
                $this->sumTotal = 0;
                $this->count = 0;
                $this->countItems = 0;
                $this->cartId = $cartId;

                $this->modx->runSnippet('DocLister', array_merge($this->DL_config[$cartId], [
                    'parents' => '',
                    'idType' => 'documents',
                    'documents' => $ids,
                    'sortType' => 'doclist',
                    'saveDLObject' => '_SHKF',
                ]));
                $DL = $this->modx->getPlaceholder('_SHKF');
                $this->docs = $DL->docsCollection()
                    ->toArray();

                $tvPrice = $this->getDLConfig($cartId, 'tvPrefix', '', '', '.' . $this->config['tvPrice']);
                if (!isset($this->default_fields[$tvPrice])) {
                    $this->default_fields[$tvPrice] = $tvPrice;
                }

                $i = 0;
                foreach ($this->session['items'] as $k => $count) {
                    $id = explode('#', $k)[0];
                    $item = $this->docs[$id];

                    $params = $this->parseParams($this->session['params'][$k]);
                    $item = array_merge($item, $params);
                    $this->default_fields = array_merge($this->default_fields, $params);

                    $item = $this->_render($cartId, $item, [
                        'key' => $k,
                        'count' => $count,
                        'iteration' => $i++,
                        $tvPrice => $this->setCalcParams($k, $id, $tvPrice)
                    ], $DL);

                    $item = $this->prepare($this->getConfig('prepareTpl', ''), $item);

                    $priceTotal = $item[$tvPrice] * $item['count'];

                    $placeholders = [
                        $tvPrice . '.format' => $this->number_format($item[$tvPrice], $this->config['price_decimals'],
                            $this->config['price_thousands_sep']),
                        $tvPrice . '.total' => $priceTotal,
                        $tvPrice . '.total.format' => $this->number_format($priceTotal, $this->config['price_decimals'],
                            $this->config['price_thousands_sep']),
                        $this->config['prefix'] . '.params' => $params[$this->config['prefix'] . '.params']
                    ];

                    $item = array_merge($item, $placeholders);
                    $this->default_fields = array_merge($this->default_fields, $placeholders);

                    $this->sum += $placeholders[$tvPrice . '.total'];
                    $this->count++;
                    $this->countItems += $item['count'];

                    $this->out['items'][$k] = array_intersect_key($item, $this->default_fields);
                    $this->out['carts'][$cartId]['items'][$k] = array_diff_key($item, $this->out['items'][$k]);
                }

                $this->sumTotal = $this->sum;

                $this->out['cart'] = [
                    'cart.id' => $cartId,
                    'cart.count' => $this->float($this->count),
                    'cart.count.items' => $this->float($this->countItems),
                    'cart.sum' => $this->float($this->sum),
                    'cart.sum.format' => $this->number_format($this->sum, $this->config['price_decimals'],
                        $this->config['price_thousands_sep']),
                    'cart.sum.total' => $this->float($this->sumTotal),
                    'cart.sum.total.format' => $this->number_format($this->sumTotal, $this->config['price_decimals'],
                        $this->config['price_thousands_sep'])
                ];

                if ($this->getDLConfig($cartId, 'async')) {
                    if ($this->getDLConfig($cartId, 'dataType') == 'html') {
                        $this->out['carts'][$cartId]['html'] = $this->renderTemplates();
                    } elseif ($this->getDLConfig($cartId, 'dataType') == 'info') {
                        unset($this->out['carts'][$cartId]['items']);
                    }
                }

                unset($this->out['cart']['cart.id']);

                if ($this->isAjax) {
                    unset($this->out['carts'][$cartId]['cart']);
                    if ($this->getDLConfig($cartId, 'dataType') == 'html') {
                        unset($this->out['carts'][$cartId]['items']);
                    }
                }
            }
        } else {
            $this->out['cart'] = [
                'cart.count' => 0,
                'cart.count.items' => 0,
                'cart.sum' => 0,
                'cart.sum.format' => 0,
                'cart.sum.total' => 0,
                'cart.sum.total.format' => 0
            ];
        }

        $this->out = $this->prepare($this->getConfig('prepareWrap', ''), $this->out);
    }

    /**
     * @return array
     */
    protected function _getDocs()
    {
        $this->docs = [];
        if (!empty($this->session['items'])) {
            foreach ($this->session['items'] as $k => $v) {
                $id = explode('#', $k)[0];
                $this->docs[$id] = $id;
            }
        }

        return $this->docs;
    }

    /**
     * @param string $ctx
     * @param array $item
     * @param array $plh
     * @param null $DL
     * @return array
     */
    protected function _render($ctx = 'shkf', $item = [], $plh = [], $DL = null)
    {
        $item = array_merge($item, $plh);
        $item['title'] = ($item['menutitle'] == '' ? $item['pagetitle'] : $item['menutitle']);

        if ($item['type'] == 'reference') {
            $item['url'] = is_numeric($item['content']) ? $this->modx->makeUrl($item['content'], '', '',
                $this->getDLConfig($ctx, 'urlScheme')) : $item['content'];
        } else {
            $item['url'] = $this->modx->makeUrl((int)$item['id'], '', '', $this->getDLConfig($ctx, 'urlScheme'));
        }
        $extPrepare = $DL->getExtender('prepare');
        if (!empty($extPrepare)) {
            $item = $extPrepare->init($DL, array(
                'data' => $item,
                'nameParam' => 'prepare'
            ));
        }

        return $item;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function parseParams($params = [])
    {
        $out = [];
        $key = $this->config['prefix'] . '.params';

        $_params = [];
        $_params['params'] = '';
        foreach ($params as $name => $values) {
            $out[$key . '.' . $name] = '';
            $param = '';
            foreach ($values as $k => $v) {
                $out[$key . '.' . $name] .= '||' . $v['value'];
                $param .= '||' . $v['value'];
            }
            $out[$key . '.' . $name] = ltrim($out[$key . '.' . $name], '||');
            $_params['params'] .= $this->parseTpl($this->DL_config[$this->cartId]['tplParam'], [
                'param' => ltrim($param, '||')
            ]);
        }
        $out[$key] = !empty($_params['params']) ? $this->parseTpl($this->DL_config[$this->cartId]['tplParams'],
            $_params) : '';
        $out = array_merge($out, $this->array_keys_to_string([
            $key => $params
        ]));

        return $out;
    }

    /**
     * @param $key
     * @param $id
     * @param string $tvPrice
     * @return float|mixed
     */
    protected function setCalcParams($key, $id, $tvPrice = 'tv.price')
    {
        $price = $this->docs[$id][$tvPrice];
        foreach ($this->session['params'][$key] as $name => $values) {
            if (!empty($values)) {
                foreach ($values as $k => $v) {
                    if (!empty($v['price'])) {
                        $v['price'] = $this->float($v['price']);
                        if (empty($v['calc'])) {
                            $price = $v['price'];
                        } else {
                            $price = eval('return ' . $price . $v['calc'] . $v['price'] . ';');
                        }
                    }
                }
            }
        }

        return $price;
    }

    /**
     * @return string
     */
    protected function renderTemplates()
    {
        $cart = $this->out['cart'];
        $cart['cart.id'] = $this->cartId;
        $cart['cart.wrap'] = '';
        if (!empty($this->count)) {
            $items = empty($this->out['carts'][$this->cartId]['items']) ? $this->out['items'] : array_merge_recursive($this->out['items'],
                $this->out['carts'][$this->cartId]['items']);
            foreach ($items as $k => $v) {
                $cart['cart.wrap'] .= $this->parseTpl($this->DL_config[$this->cartId]['tpl'], $v);
            }
            $out = $this->parseTpl($this->DL_config[$this->cartId]['ownerTPL'], $cart);
        } else {
            $out = $this->parseTpl($this->DL_config[$this->cartId]['noneTPL'], $cart);
        }
        $out = $this->modx->cleanUpMODXTags($out);
        $out = str_ireplace('sanitized_by_modx<s cript', '<script', $out);

        return $out;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->renderTemplates();
    }

}
