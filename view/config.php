<script src="../<?= $mgr_dir ?>/media/script/spectrum/spectrum.evo.min.js"></script>
<link rel="stylesheet" href="../<?= $mgr_dir ?>/media/style/common/spectrum/spectrum.css">

<div id="actions">
    <div class="btn-group">
        <label for="btn_config_save" id="Button1" class="btn btn-success">
            <i class="fa fa-trash"></i><span><?= $_lang['save.configuration'] ?></span>
        </label>
        <a id="Button2" class="btn btn-danger" href="<?= $mod_page ?>&action=uninstall" onclick="return confirm('<?= $_lang['confirm.delete.module'] ?>')">
            <i class="fa fa-trash"></i><span><?= $_lang['delete.module'] ?></span>
        </a>
    </div>
</div>

<div class="tab-pane" id="tpSHKF">
    <script>tpSHKF = new WebFXTabPane(document.getElementById('tpSHKF'), true);</script>
    <div class="tab-page" id="tabOrders">
        <h2 class="tab" onmousedown="document.location.href='<?= $mod_page ?>';"><?= $_lang['orders'] ?></h2>
        <script>tpSHKF.addTabPage(document.getElementById('tabOrders'));</script>
    </div>
    <div class="tab-page" id="tabDelivery">
        <h2 class="tab" onmousedown="document.location.href='<?= $mod_page ?>&action=delivery';"><?= $_lang['delivery_methods'] ?></h2>
        <script>tpSHKF.addTabPage(document.getElementById('tabDelivery'));</script>
    </div>
    <div class="tab-page" id="tabPayment">
        <h2 class="tab" onmousedown="document.location.href='<?= $mod_page ?>&action=payment';"><?= $_lang['payment_methods'] ?></h2>
        <script>tpSHKF.addTabPage(document.getElementById('tabPayment'));</script>
    </div>
    <div class="tab-page" id="tabConfig">
        <h2 class="tab"><?= $_lang['configuration'] ?></h2>
        <script>tpSHKF.addTabPage(document.getElementById('tabConfig'));</script>
        <div class="container container-body">
            <form method="post" action="<?= $mod_page ?>&action=config/save">
                <div class="row form-row">
                    <h4><b><?= $_lang['settings.orders'] ?></b></h4>
                    <hr>
                    <label class="col-md-3 col-lg-2"><?= $_lang['orders_results'] ?></label>
                    <div class="col-md-9 col-lg-10">
                        <input type="number" name="config[orders_results]" id="orders_results" value="<?= $orders_results ?>">
                    </div>
                </div>
                <div class="row form-row">
                    <label class="col-md-3 col-lg-2"><?= $_lang['colors_order_status'] ?></label>
                    <div class="col-md-9 col-lg-10">
                        <table>
                            <tr>
                                <td><input type="text" name="config[orders_status_color_1]" value="<?= $orders_status_color_1 ?>" class="spectrum_color" hidden></td>
                                <td class="pl-1"><?= $_lang['status.1'] ?></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="config[orders_status_color_2]" value="<?= $orders_status_color_2 ?>" class="spectrum_color" hidden></td>
                                <td class="pl-1"><?= $_lang['status.2'] ?></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="config[orders_status_color_3]" value="<?= $orders_status_color_3 ?>" class="spectrum_color" hidden></td>
                                <td class="pl-1"><?= $_lang['status.3'] ?></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="config[orders_status_color_4]" value="<?= $orders_status_color_4 ?>" class="spectrum_color" hidden></td>
                                <td class="pl-1"><?= $_lang['status.4'] ?></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="config[orders_status_color_5]" value="<?= $orders_status_color_5 ?>" class="spectrum_color" hidden></td>
                                <td class="pl-1"><?= $_lang['status.5'] ?></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="config[orders_status_color_6]" value="<?= $orders_status_color_6 ?>" class="spectrum_color" hidden></td>
                                <td class="pl-1"><?= $_lang['status.6'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row form-row">
                    <label class="col-md-3 col-lg-2"><?= $_lang['template_wrap_data_orders'] ?></label>
                    <div class="col-md-9 col-lg-10">
                        <textarea name="config[orders_ownerTPL]" id="orders_ownerTPL" rows="3"><?= $orders_ownerTPL ?></textarea>
                    </div>
                </div>
                <div class="row form-row">
                    <label class="col-md-3 col-lg-2"><?= $_lang['template_tpl_data_orders'] ?></label>
                    <div class="col-md-9 col-lg-10">
                        <textarea name="config[orders_tpl]" id="order_tpl" rows="3"><?= $orders_tpl ?></textarea>
                    </div>
                </div>
                <div class="row form-row">
                    <h4><b><?= $_lang['settings.cart'] ?></b></h4>
                    <hr>
                </div>
                <button type="submit" id="btn_config_save" class="hidden" hidden></button>
            </form>
        </div>
    </div>
</div>

<script>
  tpSHKF.setSelectedIndex(3);

  jQuery('.spectrum_color').spectrum({
    preferredFormat: 'rgb',
    chooseText: '<?= $_lang['ok'] ?>',
    cancelText: '<?= $_lang['cancel'] ?>'
  });
</script>