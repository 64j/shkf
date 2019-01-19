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
        'count',
        'key',
        // prepare
        'iteration',
        'title',
        'url',
        // document
        'id',
        'type',
        'contentType',
        'pagetitle',
        'longtitle',
        'description',
        'alias',
        'link_attributes',
        'published',
        'pub_date',
        'unpub_date',
        'parent',
        'isfolder',
        'introtext',
        'content',
        'richtext',
        'template',
        'menuindex',
        'searchable',
        'cacheable',
        'createdon',
        'createdby',
        'editedon',
        'editedby',
        'deleted',
        'deletedon',
        'deletedby',
        'publishedon',
        'publishedby',
        'menutitle',
        'donthit',
        'privateweb',
        'privatemgr',
        'content_dispo',
        'hidemenu',
        'alias_visible'
    ];

    protected $isAjax;

    /**
     * @param array $params
     * @return Cart|null
     */
    public static function getInstance($params = [])
    {
        if (self::$instance != null) {
            return self::$instance;
        }

        return new self($params);
    }

    /**
     * @return $this
     */
    public function run()
    {
        $this->out = [];

        $this->sum = 0;
        $this->sumTotal = 0;
        $this->count = 0;
        $this->countItems = 0;

        $this->setStartConfigs();
        $this->setActions();
        $this->setItems();

        $this->out['cart'] = [
            'cart.count' => $this->float($this->count),
            'cart.count.items' => $this->float($this->countItems),
            'cart.sum' => $this->float($this->sum),
            'cart.sum.format' => $this->number_format($this->sum, $this->config['price_decimals'],
                $this->config['price_thousands_sep']),
            'cart.sum.total' => $this->float($this->sumTotal),
            'cart.sum.total.format' => $this->number_format($this->sumTotal, $this->config['price_decimals'],
                $this->config['price_thousands_sep'])
        ];

        foreach ($this->out['carts'] as $cartId => $cart) {
            $this->cartId = $cartId;
            $this->out['carts'][$cartId] = [
                'cart' => array_merge($this->out['cart'], [
                    'cart.id' => $cartId
                ]),
                //'items' => empty($this->count) ? [] : $cart
                'items' => empty($this->count) ? [] : array_merge_recursive($cart, $this->out['items'])
            ];
            if ($this->getDLConfig($cartId, 'async')) {
                if ($this->getDLConfig($cartId, 'dataType') == 'html') {
                    $this->out['carts'][$cartId]['html'] = $this->renderTemplates($this->out['carts'][$cartId]);
                    $this->out['carts'][$cartId]['items'] = $cart;
                } elseif ($this->getDLConfig($cartId, 'dataType') == 'info') {
                    unset($this->out['carts'][$cartId]['items']);
                }
            }
            if ($this->isAjax) {
                unset($this->out['carts'][$cartId]['cart']);
                if ($this->getDLConfig($cartId, 'dataType') == 'html') {
                    unset($this->out['carts'][$cartId]['items']);
                }
            }
        }

        $this->out = $this->prepare($this->getConfig('prepareWrap', ''), $this->out);

        if (empty($this->isAjax)) {
            $this->out = $this->out['carts'][$this->cartId];
        }

        return $this;
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
     * @return array
     */
    protected function getItemParams()
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
     * @param $id
     * @return string
     */
    protected function getKey($id)
    {
        $id = trim($id);
        $params = $this->getItemParams();
        if (!isset($this->session['items'][$id])) {
            $id .= '#' . md5($this->json_encode($params));
        }
        $this->session['params'][$id] = $params;

        return $id;
    }

    /**
     * @return array
     */
    protected function getDocs()
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
     * @param null $extPrepare
     * @return array
     */
    protected function _render($ctx = 'shkf', $item = [], $plh = [], $extPrepare = null)
    {
        $item = array_merge($item, $plh);
        $item['title'] = ($item['menutitle'] == '' ? $item['pagetitle'] : $item['menutitle']);

        if ($item['type'] == 'reference') {
            $item['url'] = is_numeric($item['content']) ? $this->modx->makeUrl($item['content'], '', '',
                $this->getDLConfig($ctx, 'urlScheme')) : $item['content'];
        } else {
            $item['url'] = $this->modx->makeUrl((int)$item['id'], '', '', $this->getDLConfig($ctx, 'urlScheme'));
        }

        if (!empty($extPrepare)) {
            $item = $extPrepare->init($this->DL, array(
                'data' => $item,
                'nameParam' => 'prepare'
            ));
        }

        return $item;
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
            'noneTPL' => '@CODE:<div id="[+cart.id+]">[+cart.count+]</div>',
            'ownerTPL' => '@CODE:<div id="[+cart.id+]">[+cart.count+]</div>',
            'tpl' => '@CODE:<a href="[+url+]">[+pagetitle+]</a>'
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

        array_push($this->default_fields, $this->config['prefix'] . '.params');
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
     * находим товары в корзине
     */
    protected function setItems()
    {
        if ($this->docs = $this->getDocs()) {
            $ids = implode(',', $this->docs);
            $tvPrice = '';
            $this->default_fields = array_flip($this->default_fields);
            foreach ($this->out['carts'] as $cartId => $cart) {
                $this->modx->runSnippet('DocLister', array_merge($this->DL_config[$cartId], [
                    'parents' => '',
                    'idType' => 'documents',
                    'documents' => $ids,
                    'sortType' => 'doclist',
                    'saveDLObject' => 'DLAPI',
                ]));
                $this->DL = $this->modx->getPlaceholder('DLAPI');
                $this->docs = $this->DL->docsCollection()
                    ->toArray();

                $tvPrice = $this->getDLConfig($cartId, 'tvPrefix', '', '', '.' . $this->config['tvPrice']);
                $extPrepare = $this->DL->getExtender('prepare');
                if (!isset($this->default_fields[$tvPrice])) {
                    $this->default_fields[$tvPrice] = $tvPrice;
                }

                $i = 0;
                foreach ($this->session['items'] as $k => $count) {
                    $id = explode('#', $k)[0];
                    $item = $this->docs[$id];
                    $item = array_merge($item, $this->array_keys_to_string([
                        $this->config['prefix'] . '.params' => $this->session['params'][$k]
                    ]));
                    $item = $this->_render($cartId, $item, [
                        'key' => $k,
                        'count' => $count,
                        'iteration' => $i++,
                        $tvPrice => $this->setCalcParams($k, $id, $tvPrice),
                        $this->config['prefix'] . '.params' => $this->session['params'][$k]
                    ], $extPrepare);
                    $this->out['items'][$k] = array_intersect_key($item, $this->default_fields);
                    $this->out['carts'][$cartId][$k] = array_diff_key($item, $this->out['items'][$k]);
                }
            }

            foreach ($this->out['items'] as $k => $item) {
                $item = $this->prepare($this->getConfig('prepareTpl', ''), $item);
//                $item = array_merge($item, $this->array_keys_to_string([
//                    $this->config['prefix'] . '.params' => $item[$this->config['prefix'] . '.params']
//                ]));

                $priceTotal = $item[$tvPrice] * $item['count'];
                $item[$tvPrice . '.format'] = $this->number_format($item[$tvPrice], $this->config['price_decimals'],
                    $this->config['price_thousands_sep']);
                $item[$tvPrice . '.total'] = $priceTotal;
                $item[$tvPrice . '.total.format'] = $this->number_format($priceTotal, $this->config['price_decimals'],
                    $this->config['price_thousands_sep']);

                $this->sum += $item[$tvPrice . '.total'];
                $this->count++;
                $this->countItems += $item['count'];
                $this->out['items'][$k] = $item;
            }

            $this->sumTotal = $this->sum;
        }
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
    public function toHtml()
    {
        return $this->renderTemplates($this->out);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function renderTemplates($data = [])
    {
        if (!empty($this->count)) {
            if (!empty($data['items'])) {
                foreach ($data['items'] as $k => $v) {
                    $data['cart']['cart.wrap'] .= $this->parseTpl($this->DL_config[$this->cartId]['tpl'], $v);
                }
            }
            $data = $this->parseTpl($this->DL_config[$this->cartId]['ownerTPL'], $data['cart']);
        } else {
            $data = $this->parseTpl($this->DL_config[$this->cartId]['noneTPL'], $data['cart']);
        }
        $data = $this->modx->cleanUpMODXTags($data);
        $data = str_ireplace('sanitized_by_modx<s cript', '<script', $data);

        return $data;
    }

}
