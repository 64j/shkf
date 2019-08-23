<div class="tab-pane" id="tpSHKF">
    <script>tpSHKF = new WebFXTabPane(document.getElementById('tpSHKF'), true);</script>
    <div class="tab-page" id="tabOrders">
        <h2 class="tab"><?= $_lang['orders'] ?></h2>
        <script>tpSHKF.addTabPage(document.getElementById('tabOrders'));</script>
        <div class="container container-body">
            <div class="row">
                <div class="table-responsive">
                    <?= $data ?>
                </div>
            </div>
        </div>
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
        <h2 class="tab" onmousedown="document.location.href='<?= $mod_page ?>&action=config';"><?= $_lang['configuration'] ?></h2>
        <script>tpSHKF.addTabPage(document.getElementById('tabConfig'));</script>
    </div>
</div>

<script>tpSHKF.setSelectedIndex(0);</script>