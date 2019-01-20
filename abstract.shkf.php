<?php namespace ShkF;

/**
 * Class ShkF
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author 64j
 * @package ShkF
 */
abstract class ShkF
{
    protected $modx;

    protected $DL;

    protected $DL_config;

    protected $request;

    protected $config;

    protected $session;

    public $carts;

    protected $params = [];

    public $docs;

    public $sum;

    public $sumTotal;

    public $count;

    public $countItems;

    public $cartId;

    public $out;

    /**
     * ShkF constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->modx = evolutionCMS();
        $this->request = [];
        $this->sum = 0;
        $this->sumTotal = 0;
        $this->count = 0;
        $this->countItems = 0;
        $this->out = !is_null($this->out) ? $this->out : null;

        $this->getConfig('', [
            'prefix' => 'shkf',
            'tvPrice' => 'price',
            'price_thousands_sep' => '&nbsp;',
            'price_decimals' => 0,
            'prepareTpl' => '',
            'prepareWrap' => ''
        ]);

        $this->getSession();

        $this->params = array_merge($_REQUEST, $this->setParams($params));
        if (!empty($this->params)) {
            foreach ($this->params as $k => $v) {
                if (substr($k, 0, strlen($this->config['prefix'] . '-')) == $this->config['prefix'] . '-') {
                    unset($this->params[$k]);
                    $k = str_replace($this->config['prefix'] . '-', '', $k);
                    $this->request[$k] = $v;
                    if (is_string($v)) {
                        $this->request[$k] = $this->modx->removeSanitizeSeed($v);
                    }
                } else {
                    $this->params[$k] = $this->modx->removeSanitizeSeed($v);
                }
            }
        }
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
    }

    // Prevent cloning of the instance
    protected function __clone()
    {
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->out;
    }

    /**
     * @return array | string
     */
    public function toJson()
    {
        header('content-type: application/json');

        return $this->json_encode($this->out);
    }

    /**
     * @param $number
     * @param int $decimals
     * @param string $thousands_sep
     * @return int|string
     */
    protected function number_format($number, $decimals = 0, $thousands_sep = '')
    {
        $number = str_replace(',', '.', $number);
        $number = !empty($number) ? number_format($number, $decimals, '.', $thousands_sep) : 0;

        return $number;
    }

    /**
     * @param $number
     * @param int $decimals
     * @return float
     */
    protected function float($number, $decimals = 0)
    {
        $number = str_replace(',', '.', $number);
        $number = preg_replace('/[^.0-9]/', '', $number);
        return floatval($number);
    }

    /**
     * @param array $data
     * @param int $options
     * @param int $depth
     * @return string
     */
    protected function json_encode(
        $data = [],
        $options = JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE,
        $depth = 512
    ) {
        return json_encode($data, $options, $depth);
    }

    /**
     * @param array $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return array
     */
    protected function json_decode($json = [], $assoc = true, $depth = 512, $options = JSON_OBJECT_AS_ARRAY)
    {
        return json_decode($json, $assoc, $depth, $options);
    }

    /**
     * @param string $tpl
     * @param array $ph
     * @return string
     */
    protected function parseTpl($tpl = '', $ph = [])
    {
        $out = $this->modx->getTpl($tpl);
        foreach ($ph as $k => $v) {
            $key = 'data-' . $this->config['prefix'] . '-' . str_replace('.', '-', $k);
            $out = preg_replace('/(' . $key . '(=["\'].+?["\']|))+?([^-])/u', $key . '="' . $v . '"$3', $out);
        }
        $out = $this->modx->parseText($out, $ph);

        return $out;
    }

    /**
     * @param string $name
     * @param array $data
     * @return array|mixed|string
     */
    protected function prepare($name = 'prepareTpl', $data = [])
    {
        if (!empty($name)) {
            $params = [
                'data' => $data,
                'modx' => $this->modx,
                '_Shkf' => $this
            ];

            if ((is_object($name)) || is_callable($name)) {
                $data = call_user_func_array($name, $params);
            } else {
                $data = $this->modx->runSnippet($name, $params);
            }
        }

        return $data;
    }

    protected function setParams($params = [])
    {
        if (!empty($params)) {
            $this->params = $params;
        }

        return $this->params;
    }

    /**
     * @param string $key
     * @param string $default
     * @return array | string
     */
    public function getRequest($key = '', $default = '')
    {
        if ($key == '') {
            $out = $this->request;
        } elseif ($key != '') {
            if (isset($this->request[$key])) {
                $out = $this->request[$key];
            } else {
                $out = $default;
            }
        } else {
            $out = $this->request = [];
        }

        return $out;
    }

    /**
     * @param string $key
     * @param string $default
     * @return array
     */
    public function getSession($key = '', $default = '')
    {
        if ($key == '') {
            if ($this->modx->getLoginUserID('web')) {
                $this->session = &$_SESSION['webUsrConfigSet'][$this->config['prefix'] . '-session'];
            } else {
                $this->session = &$_SESSION[$this->config['prefix'] . '-session'];
            }
            $out = $this->session;
        } elseif ($key != '') {
            if (isset($this->session[$key])) {
                $out = $this->session[$key];
            } else {
                $out = $default;
            }
        } else {
            $out = $this->session;
        }

        return $out;
    }

    /**
     * @param string $key
     * @param string $default
     * @return string| array
     */
    public function getConfig($key = '', $default = '')
    {
        if ($key == '') {
            $this->config = empty($default) ? [] : $default;
            if (!empty($this->modx->snippetCache['shkfCartProps'])) {
                $config = $this->json_decode($this->modx->snippetCache['shkfCartProps']);
                $this->config = array_merge($this->config, $config);
            }
            $out = $this->config;
        } elseif ($key != '') {
            if (isset($this->config[$key])) {
                $out = $this->config[$key];
            } else {
                $out = $default;
            }
        } else {
            $out = $this->config;
        }

        return $out;
    }

    /**
     * @param string $ctx
     * @param string $key
     * @param string $default
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    protected function getDLConfig($ctx = 'shkf', $key = '', $default = '', $prefix = '', $suffix = '')
    {
        $out = '';
        if (\is_array($key)) {
            $this->DL_config[$ctx] = $key;
            if (\is_array($default)) {
                $this->DL_config[$ctx] = array_merge($this->DL_config[$ctx], $default);
            }
            $this->DL_config[$ctx] = array_diff($this->DL_config[$ctx], $this->getConfig());
            if (!empty($this->DL_config[$ctx]['tvList'])) {
                $this->DL_config[$ctx]['tvList'] .= ', ' . $this->config['tvPrice'];
                $this->DL_config[$ctx]['tvList'] = array_map('trim', explode(',', $this->DL_config[$ctx]['tvList']));
                $this->DL_config[$ctx]['tvList'] = array_unique($this->DL_config[$ctx]['tvList']);
                $this->DL_config[$ctx]['tvList'] = trim(implode(',', $this->DL_config[$ctx]['tvList']), ',');
            } else {
                $this->DL_config[$ctx]['tvList'] = $this->config['tvPrice'];
            }
            $out = $this->DL_config[$ctx];
        } elseif ($key != '') {
            if (isset($this->DL_config[$ctx][$key])) {
                if ($this->DL_config[$ctx][$key] != '') {
                    $out = $this->DL_config[$ctx][$key];
                } else {
                    $out = $default;
                }
                if ($out != '') {
                    $out = $prefix . $out . $suffix;
                }
            }
        } else {
            $out = $this->DL_config[$ctx];
        }

        return $out;
    }

    /**
     * custom array keys to string
     *
     * @param $data
     * @param array $parents
     * @param array $delimiter
     * @return array
     */
    protected function array_keys_to_string(
        $data,
        $parents = array(),
        $delimiter = array(
            '',
            '.',
            ''
        )
    ) {
        $result = array();
        foreach ($data as $key => $value) {
            $group = $parents;
            array_push($group, $key);
            if (is_array($value)) {
                $result = $this->array_keys_to_string($value, $group, $delimiter);
                continue;
            }
            if (!empty($parents)) {
                if (!empty($value)) {
                    $result[$delimiter[0] . implode($delimiter[1], $group) . $delimiter[2]] = $value;
                }
                continue;
            }
        }

        return $result;
    }
}
