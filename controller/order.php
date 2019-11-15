<?php namespace FormLister;

use Shkf\Cart;

/**
 * Class Order
 *
 * @license GNU General Public License (GPL), http://www.gnu.org/copyleft/gpl.html
 * @author 64j
 * @package ShkF
 */
class Order extends Form
{
    public $cart = null;

    public function __construct(\DocumentParser $modx, array $cfg = array())
    {
        $this->cart = Cart::getInstance();
        if (isset($_REQUEST[$this->cart->getConfig('prefix') . '-action']) && $_REQUEST[$this->cart->getConfig('prefix') . '-action'] == 'recount') {
            $cfg['disableSubmit'] = 1;
        }
        parent::__construct($modx, $cfg);
    }

    public function render()
    {
        if (!$this->cart->getCount()) {
            return;
        }

        $this->modx->jscripts['shkfOrder_jscripts'] = '
        <script>shkf.orderFormId = \'' . $this->getCFGDef('formid', '') . '\';</script>
        <script src="assets/modules/shkf/js/shkf.order.js"></script>';

        // Доставка
        $deliveryList = '';
        $deliveryData = is_array($this->getCFGDef('shkfDeliveryData')) ? $this->getCFGDef('shkfDeliveryData') : json_decode($this->getCFGDef('shkfDeliveryData', '{}'), true);
        $deliveryField = $this->getCFGDef('shkfDeliveryField', 'delivery');
        $deliveryValue = $this->getField($deliveryField);

        foreach ($deliveryData as $k => $v) {
            if (!empty($v['disabled'])) {
                continue;
            }
            $deliveryList .= $this->parseChunk($this->getCFGDef('shkfDeliveryTpl', '@CODE:[+id+]:[+value+]'), array_merge($v, [
                'id' => $k,
                'selected' => $deliveryValue == $k ? ' selected' : '',
                'checked' => $deliveryValue == $k ? ' checked' : '',
                'active' => $deliveryValue == $k ? ' active' : ''
            ]));

            if ($deliveryValue == $k) {
                if (!empty($this->cart->out['cart'])) {
                    $this->cart->setSession('sum_added', $v['price']);
                    $this->cart->out['cart']['cart.' . $deliveryField . '.price'] = $v['price'];
                    $this->cart->out['cart']['cart.' . $deliveryField . '.title'] = isset($v['title']) ? $v['title'] : '';
                    $this->cart->out['cart']['cart.' . $deliveryField . '.value'] = isset($v['value']) ? $v['value'] : '';
                    $this->cart->out['cart']['cart.sum.total'] = $this->cart->sum + $v['price'];
                    $this->cart->out['cart']['cart.sum.total.format'] = $this->cart->number_format($this->cart->out['cart']['cart.sum.total'], $this->cart->getConfig('price_decimals'), $this->cart->getConfig('price_thousands_sep'));
                }

                $this->setField($deliveryField, isset($v['value']) ? $v['value'] : (isset($v['title']) ? $v['title'] : $k));
            }
        }

        $this->setPlaceholder($deliveryField . '.list', $deliveryList);

        // Оплата
        $paymentList = '';
        $paymentData = is_array($this->getCFGDef('shkfPaymentData')) ? $this->getCFGDef('shkfPaymentData') : json_decode($this->getCFGDef('shkfPaymentData', '{}'), true);
        $paymentField = $this->getCFGDef('shkfPaymentField', 'payment');
        $paymentValue = $this->getField($paymentField);

        foreach ($paymentData as $k => $v) {
            if (!empty($v['disabled'])) {
                continue;
            }
            $paymentList .= $this->parseChunk($this->getCFGDef('shkfPaymentTpl', '@CODE:[+id+]:[+value+]'), array_merge($v, [
                'id' => $k,
                'selected' => $paymentValue == $k ? ' selected' : '',
                'checked' => $paymentValue == $k ? ' checked' : '',
                'active' => $paymentValue == $k ? ' active' : ''
            ]));

            if ($paymentValue == $k) {
                if (!empty($this->cart->out['cart'])) {
                    $this->cart->out['cart']['cart.' . $paymentField . '.title'] = isset($v['title']) ? $v['title'] : '';
                    $this->cart->out['cart']['cart.' . $paymentField . '.value'] = isset($v['value']) ? $v['value'] : '';
                }

                $this->setField($paymentField, isset($v['value']) ? $v['value'] : (isset($v['title']) ? $v['title'] : $k));
            }
        }

        $this->setPlaceholder($paymentField . '.list', $paymentList);

        // Плейсхолдеры в форме заказа
        if (!empty($this->cart->out['cart'])) {
            foreach ($this->cart->out['cart'] as $k => $v) {
                $this->setPlaceholder($k, $v);
            }
        }

        // Товары
        $orderData = '';

        if (!empty($this->cart->out['cart'])) {
            if (!empty($this->cart->out['items'])) {
                foreach ($this->cart->out['items'] as $item) {
                    $item['iteration'] += 1;
                    $orderData .= $this->parseChunk($this->getCFGDef('shkfOrderDataRowTpl', '@CODE:[+id+]:[+title+]'), $item);
                }
            }

            $orderData = $this->parseChunk($this->getCFGDef('shkfOrderDataTpl', '@CODE:[+id+]:[+title+]'), array_merge($this->cart->out['cart'], [
                'cart.wrap' => $orderData
            ]));
        }

        $this->setPlaceholder('orderData', $orderData);

        $rules = $this->getValidationRules();
        if (!empty($rules)) {
            foreach ($rules as $k => $rule) {
                if (!empty($rule['fields'])) {
                    foreach ($rule['fields'] as $key => $val) {
                        if ($key == $deliveryField) {
                            if (isset($val['params']) && in_array($deliveryValue, $val['params']) && !empty($this->getField($deliveryField)) && empty($this->getField($k))) {
                                $this->addError($k, 'required', $val['message']);
                            }
                        }
                        if ($key == $paymentField) {
                            if (isset($val['params']) && in_array($paymentField, $val['params']) && !empty($this->getField($paymentField)) && empty($this->getField($k))) {
                                $this->addError($k, 'required', $val['message']);
                            }
                        }
                    }
                }
            }
        }

        return parent::render();
    }

    public function process()
    {
        // Заказ
        $data = [];
        $fields = $this->getFormData('fields');
        $deliveryField = $this->getCFGDef('shkfDeliveryField', 'delivery');
        $paymentField = $this->getCFGDef('shkfPaymentField', 'payment');

        $data['status'] = 1;
        $data['items'] = $this->json_encode($this->cart->out['items']);
        $data['cart'] = $this->json_encode($this->cart->out['cart']);

        if (isset($fields[$deliveryField])) {
            $data['delivery'] = $fields[$deliveryField];
            unset($fields[$deliveryField]);
        }

        if (isset($fields[$paymentField])) {
            $data['payment'] = $fields[$paymentField];
            unset($fields[$paymentField]);
        }

        unset($fields['formid']);

        $data['user'] = '';
        $data['customer'] = $this->json_encode($fields);
        $data['hash'] = md5($data['cart'] . $data['customer'] . time());

        $orderID = $this->modx->db->insert($this->modx->db->escape($data), '[+prefix+]shkf_orders');

        $this->setPlaceholder('orderID', $orderID);

        // очищаем корзину
        $this->cart->destroy();

        return parent::process();
    }

    public function json_encode(
        $data = [],
        $options = JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE,
        $depth = 512
    ) {
        return json_encode($data, $options, $depth);
    }
}
