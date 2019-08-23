<?php namespace ShkF;

class Module
{
    public $version;
    public $mod_page;
    public $action;
    protected $evo;
    protected $config;
    protected $basePath;
    protected $orderStatus;
    protected $lang;

    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->evo = evolutionCMS();
        $this->getConfig();
        $this->basePath = MODX_BASE_PATH . 'assets/modules/shkf/';

        $manager_language = $this->evo->getConfig('manager_language');
        if (file_exists($this->basePath . 'lang/' . $manager_language . '.php')) {
            $this->lang = require_once $this->basePath . 'lang/' . $manager_language . '.php';
        } else {
            $this->lang = require_once $this->basePath . 'lang/english.php';
        }

        $this->orderStatus = [
            1 => ['title' => $this->lang['status.1'], 'color' => $this->config['orders_status_color_1']],
            2 => ['title' => $this->lang['status.2'], 'color' => $this->config['orders_status_color_2']],
            3 => ['title' => $this->lang['status.3'], 'color' => $this->config['orders_status_color_3']],
            4 => ['title' => $this->lang['status.4'], 'color' => $this->config['orders_status_color_4']],
            5 => ['title' => $this->lang['status.5'], 'color' => $this->config['orders_status_color_5']],
            6 => ['title' => $this->lang['status.6'], 'color' => $this->config['orders_status_color_6']],
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if ($this->evo->db->getRecordCount($this->evo->db->query('SHOW TABLES FROM ' . $this->evo->db->config['dbase'] . ' LIKE "' . $this->evo->db->config['table_prefix'] . 'shkf_config' . '"')) > 0 && empty($this->config)) {
            $sql = $this->evo->db->select('*', $this->evo->getFullTableName('shkf_config'));
            while ($config = $this->evo->db->getRow($sql)) {
                $this->config[$config['setting']] = $config['value'];
            }
        }

        return $this->config;
    }

    /**
     * install
     */
    public function install()
    {
        $sql = [];

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_config 
        (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `setting` varchar(255),
        `value` text,
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_orders 
        (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `hash` text,
        `create_ad` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `update_ad` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `status` int(11) DEFAULT NULL,
        `items` text,
        `cart` text,
        `delivery` text,
        `payment` text,
        `user` int(11) DEFAULT NULL,
        `customer` text,
        `note` text,
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_delivery 
        (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255),
        `title` text,
        `price` int(11),
        `rank` int(11),
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_payment 
        (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255),
        `title` text,
        `rank` int(11),
        PRIMARY KEY (`id`)
        );';

        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "version", "' . $this->version . '");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_tpl", "@FILE:orders.tpl");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_ownerTPL", "@FILE:orders.ownerTPL");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_status_color_1", "#C5CAFE");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_status_color_2", "#B1F2FC");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_status_color_3", "#F3FDB0");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_status_color_4", "#BEFAB4");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_status_color_5", "#FFAEAE");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_status_color_6", "#FFE1A4");';
        $sql[] = 'INSERT INTO ' . $this->evo->getFullTableName('shkf_config') . ' VALUES (NULL, "orders_results", "30");';

        foreach ($sql as $v) {
            $this->evo->db->query($v);
        }

        $this->evo->sendRedirect($this->mod_page);
    }

    /**
     * uninstall
     */
    public function uninstall()
    {
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_config';
        $sql[] = 'DROP TABLE IF EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_orders';
        $sql[] = 'DROP TABLE IF EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_delivery';
        $sql[] = 'DROP TABLE IF EXISTS ' . $this->evo->db->config['table_prefix'] . 'shkf_payment';

        foreach ($sql as $v) {
            $this->evo->db->query($v);
        }

        $this->evo->sendRedirect($this->mod_page);
    }

    /**
     * @param $__tpl
     * @param array $__data
     * @return false|string
     */
    public function view($__tpl, $__data = [])
    {
        $__tpl = trim($__tpl, '/');
        $__tpl = $this->basePath . 'view/' . $__tpl . '.php';
        $mod_page = $this->mod_page;
        $_lang = $this->lang;
        if (file_exists($__tpl)) {
            extract($__data);
            ob_start();
            require($__tpl);
            $__out = ob_get_contents();
            ob_end_clean();
        } else {
            $__out = 'Error: Could not load template ' . $__tpl . '!<br>';
        }

        return $__out;
    }

    protected function _lang()
    {
        $result = [];
        foreach ($this->lang as $k => $v) {
            $result['lang.' . $k] = $v;
        }

        return $result;
    }

    /**
     *
     */
    public function saveConfig()
    {
        $config = $_REQUEST['config'];
        $data = [
            'version' => $this->version,
            'orders_tpl' => !empty($config['orders_tpl']) ? $config['orders_tpl'] : '@FILE:orders.tpl',
            'orders_ownerTPL' => !empty($config['orders_ownerTPL']) ? $config['orders_ownerTPL'] : '@FILE:orders.ownerTPL',
            'orders_status_color_1' => !empty($config['orders_status_color_1']) ? $config['orders_status_color_1'] : '#C5CAFE',
            'orders_status_color_2' => !empty($config['orders_status_color_2']) ? $config['orders_status_color_2'] : '#B1F2FC',
            'orders_status_color_3' => !empty($config['orders_status_color_3']) ? $config['orders_status_color_3'] : '#F3FDB0',
            'orders_status_color_4' => !empty($config['orders_status_color_4']) ? $config['orders_status_color_4'] : '#BEFAB4',
            'orders_status_color_5' => !empty($config['orders_status_color_5']) ? $config['orders_status_color_5'] : '#FFAEAE',
            'orders_status_color_6' => !empty($config['orders_status_color_6']) ? $config['orders_status_color_6'] : '#FFE1A4',
            'orders_results' => !empty($config['orders_results']) ? (int)$config['orders_results'] : 30,
        ];

        $this->evo->db->delete('[+prefix+]shkf_config');

        $data = $this->evo->db->escape($data);

        foreach ($data as $k => $v) {
            $this->evo->db->insert([
                'setting' => $k,
                'value' => $v
            ], $this->evo->getFullTableName('shkf_config'));
        }

        $this->evo->sendRedirect($this->mod_page . '&action=config');
    }

    /**
     *
     */
    public function ordersDelete()
    {
        if (!empty($_REQUEST['orderID'])) {
            $this->evo->db->delete('[+prefix+]shkf_orders', 'id=' . (int)$_REQUEST['orderID']);
        }

        $this->evo->sendRedirect($this->mod_page . '&action=orders');
    }

    /**
     *
     */
    public function ordersChangeStatus()
    {
        if (!empty($_REQUEST['orderID']) && !empty($_REQUEST['status'])) {
            $this->evo->db->update([
                'status' => (int)$_REQUEST['status']
            ], '[+prefix+]shkf_orders', 'id=' . (int)$_REQUEST['orderID']);
        }

        $this->evo->sendRedirect($this->mod_page . '&action=orders');
    }

    /**
     *
     */
    public function paymentDelete()
    {
        if (!empty($_REQUEST['paymentID'])) {
            $this->evo->db->delete('[+prefix+]shkf_payment', 'id=' . (int)$_REQUEST['paymentID']);
        }

        $this->evo->sendRedirect($this->mod_page . '&action=payment');
    }

    /**
     *
     */
    public function paymentAdd()
    {
        if (!empty($_REQUEST['new_title'])) {
            $this->evo->db->insert([
                'name' => !empty($_REQUEST['new_name']) ? (string)$_REQUEST['new_name'] : '',
                'title' => (string)$_REQUEST['new_title'],
                'rank' => !empty($_REQUEST['new_rank']) ? (int)$_REQUEST['new_rank'] : 0
            ], '[+prefix+]shkf_payment');
        }

        $this->evo->sendRedirect($this->mod_page . '&action=payment');
    }

    /**
     * @return mixed
     */
    public function paymentData()
    {
        $mod_page = $this->mod_page;
        $_lang = $this->_lang();

        return $this->evo->runSnippet('DocLister', array(
            'debug' => 0,
            'controller' => 'onetable',
            'table' => 'shkf_payment',
            'display' => $this->config['orders_results'],
            'idType' => 'documents',
            'ignoreEmpty' => 1,
            'addWhereList' => '',
            'orderBy' => 'rank ASC',
            'paginate' => 'pages',
            'reversePagination' => 0,
            'TplWrapPaginate' => '@CODE:<ul class="pagination">[+wrap+]</ul>',
            'TplPage' => '@CODE:<li class="page-item"><a href="[+link+]" class="page-link">[+num+]</a></li>',
            'TplCurrentPage' => '@CODE:<li class="page-item active"><span class="page-link">[+num+]</span></li>',
            'TplDotsPage' => '@CODE:<li class="page-item disabled"><span class="page-link"> ... </span></li>',
            'TplPrevP' => '@CODE:<li class="page-item page-prev"><a href="[+link+]" class="page-link">← Назад</a></li>',
            'TplNextP' => '@CODE:<li class="page-item page-next"><a href="[+link+]" class="page-link">Вперёд →</a></li>',
            'makePaginateUrl' => function () {
                return '/manager/?a=' . $_GET['a'] . '&id=' . $_GET['id'];
            },
            'templatePath' => 'assets/modules/shkf/view/',
            'templateExtension' => 'tpl',
            'tpl' => '@FILE:payment.tpl',
            'ownerTPL' => '@FILE:payment.ownerTPL',
            'prepare' => function ($data) use ($mod_page, $_lang) {
                $data['mod_page'] = $mod_page;
                $data = array_merge($data, $_lang);

                return $data;
            },
            'prepareWrap' => function ($data) use ($mod_page, $_lang) {
                $plh = $data['placeholders'];
                $plh['mod_page'] = $mod_page;
                $plh = array_merge($plh, $_lang);

                return $plh;
            }
        ));
    }

    /**
     *
     */
    public function deliveryDelete()
    {
        if (!empty($_REQUEST['deliveryID'])) {
            $this->evo->db->delete('[+prefix+]shkf_delivery', 'id=' . (int)$_REQUEST['deliveryID']);
        }

        $this->evo->sendRedirect($this->mod_page . '&action=delivery');
    }

    /**
     * @return string
     */
    public function deliveryData()
    {
        $mod_page = $this->mod_page;
        $_lang = $this->_lang();

        return $this->evo->runSnippet('DocLister', array(
            'debug' => 0,
            'controller' => 'onetable',
            'table' => 'shkf_delivery',
            'display' => $this->config['orders_results'],
            'idType' => 'documents',
            'ignoreEmpty' => 1,
            'addWhereList' => '',
            'orderBy' => 'rank ASC',
            'paginate' => 'pages',
            'reversePagination' => 0,
            'TplWrapPaginate' => '@CODE:<ul class="pagination">[+wrap+]</ul>',
            'TplPage' => '@CODE:<li class="page-item"><a href="[+link+]" class="page-link">[+num+]</a></li>',
            'TplCurrentPage' => '@CODE:<li class="page-item active"><span class="page-link">[+num+]</span></li>',
            'TplDotsPage' => '@CODE:<li class="page-item disabled"><span class="page-link"> ... </span></li>',
            'TplPrevP' => '@CODE:<li class="page-item page-prev"><a href="[+link+]" class="page-link">← Назад</a></li>',
            'TplNextP' => '@CODE:<li class="page-item page-next"><a href="[+link+]" class="page-link">Вперёд →</a></li>',
            'makePaginateUrl' => function () {
                return '/manager/?a=' . $_GET['a'] . '&id=' . $_GET['id'];
            },
            'templatePath' => 'assets/modules/shkf/view/',
            'templateExtension' => 'tpl',
            'tpl' => '@FILE:delivery.tpl',
            'ownerTPL' => '@FILE:delivery.ownerTPL',
            'prepare' => function ($data) use ($mod_page, $_lang) {
                $data['mod_page'] = $mod_page;
                $data = array_merge($data, $_lang);

                return $data;
            },
            'prepareWrap' => function ($data) use ($mod_page, $_lang) {
                $plh = $data['placeholders'];
                $plh['mod_page'] = $mod_page;
                $plh = array_merge($plh, $_lang);

                return $plh;
            }
        ));
    }

    /**
     *
     */
    public function deliveryAdd()
    {
        if (!empty($_REQUEST['new_title'])) {
            $this->evo->db->insert([
                'name' => !empty($_REQUEST['new_name']) ? (string)$_REQUEST['new_name'] : '',
                'title' => (string)$_REQUEST['new_title'],
                'price' => !empty($_REQUEST['new_price']) ? (int)$_REQUEST['new_price'] : 0,
                'rank' => !empty($_REQUEST['new_rank']) ? (int)$_REQUEST['new_rank'] : 0
            ], '[+prefix+]shkf_delivery');
        }

        $this->evo->sendRedirect($this->mod_page . '&action=delivery');
    }

    /**
     * @return string
     */
    public function ordersData()
    {
        $mod_page = $this->mod_page;
        $orderStatus = $this->orderStatus;
        $_lang = $this->_lang();

        return $this->evo->runSnippet('DocLister', array(
            'debug' => 0,
            'controller' => 'onetable',
            'table' => 'shkf_orders',
            'display' => $this->config['orders_results'],
            'idType' => 'documents',
            'ignoreEmpty' => 1,
            'addWhereList' => '',
            'orderBy' => 'id DESC',
            'paginate' => 'pages',
            'reversePagination' => 0,
            'TplWrapPaginate' => '@CODE:<ul class="pagination">[+wrap+]</ul>',
            'TplPage' => '@CODE:<li class="page-item"><a href="[+link+]" class="page-link">[+num+]</a></li>',
            'TplCurrentPage' => '@CODE:<li class="page-item active"><span class="page-link">[+num+]</span></li>',
            'TplDotsPage' => '@CODE:<li class="page-item disabled"><span class="page-link"> ... </span></li>',
            'TplPrevP' => '@CODE:<li class="page-item page-prev"><a href="[+link+]" class="page-link">← Назад</a></li>',
            'TplNextP' => '@CODE:<li class="page-item page-next"><a href="[+link+]" class="page-link">Вперёд →</a></li>',
            'makePaginateUrl' => function () {
                return '/manager/?a=' . $_GET['a'] . '&id=' . $_GET['id'];
            },
            'templatePath' => 'assets/modules/shkf/view/',
            'templateExtension' => 'tpl',
            'tpl' => $this->config['orders_tpl'],
            'ownerTPL' => $this->config['orders_ownerTPL'],
            'prepare' => function ($data) use ($orderStatus, $mod_page, $_lang) {
                $data['mod_page'] = $mod_page;
                $data['items'] = json_decode($data['items'], true);
                $data['cart'] = json_decode($data['cart'], true);
                $data['customer'] = json_decode($data['customer'], true);

                // Состав заказа
                foreach ($data['items'] as $k => $v) {
                    $data['items'][$k] = '<div>' . $v['title'] . ' <br>' . $v['tv.price.format'] . ' x ' . $v['count'] . ' = ' . $v['tv.price.total.format'] . '</div>';
                }
                $data['items'] = implode($data['items']);
                $data['sum.total'] = $data['cart']['cart.sum.total.format'];

                // Покупатель
                foreach ($data['customer'] as $k => $v) {
                    $data['customer.' . $k] = $v;
                }

                // Статус
                $data['color'] = '';
                $data['status.select'] = '<select onchange="document.location.href=\'' . $data['mod_page'] . '&action=orders/status&orderID=' . $data['id'] . '&status=\' + this.value">';
                foreach ($orderStatus as $k => $v) {
                    if ($k == $data['status']) {
                        $data['color'] = $v['color'];
                        $selected = ' selected';
                    } else {
                        $selected = '';
                    }
                    $data['status.select'] .= '<option value="' . $k . '"' . $selected . '>' . $v['title'] . '</option>';
                }
                $data['status.select'] .= '</select>';

                return $data;
            },
            'prepareWrap' => function ($data) use ($mod_page, $_lang) {
                $plh = $data['placeholders'];
                $plh['mod_page'] = $mod_page;
                $plh = array_merge($plh, $_lang);

                return $plh;
            }
        ));
    }

    /**
     * @return string
     */
    public function render()
    {
        $out = '';

        if (!empty($this->config['version'])) {
            $out .= $this->view('header');
        }

        switch ($this->action) {
            case 'install':
                $this->install();
                break;

            case 'uninstall':
                $this->uninstall();
                break;

            case 'delivery':
                $out .= $this->view('delivery', [
                    'data' => $this->deliveryData(),
                    'pagination' => $this->evo->getPlaceholder('pages')
                ]);
                break;

            case 'delivery/add':
                $this->deliveryAdd();
                break;

            case 'delivery/delete':
                $this->deliveryDelete();
                break;

            case 'payment':
                $out .= $this->view('payment', [
                    'data' => $this->paymentData(),
                    'pagination' => $this->evo->getPlaceholder('pages')
                ]);
                break;

            case 'payment/add':
                $this->paymentAdd();
                break;

            case 'payment/delete':
                $this->paymentDelete();
                break;

            case 'config':
                $out .= $this->view('config', array_merge([
                    'mgr_dir' => MGR_DIR
                ], $this->config));
                break;

            case 'config/save':
                $this->saveConfig();
                break;

            case 'orders/delete':
                $this->ordersDelete();
                break;

            case 'orders/status':
                $this->ordersChangeStatus();
                break;

            case 'orders':
            default:
                if (empty($this->config['version'])) {
                    $out .= $this->view('install');
                } else {
                    $out .= $this->view('orders', [
                        'data' => $this->ordersData(),
                        'pagination' => $this->evo->getPlaceholder('pages')
                    ]);
                }
                break;
        }

        return $out;
    }
}
