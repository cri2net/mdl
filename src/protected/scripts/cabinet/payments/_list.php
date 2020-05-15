<?php
    if (empty($payments)) {
        ?>
        <div class="big-error-message">Немає платежів для відображення</div>
        <?php
        return;
    }
?>
<label class="checkbox green list__checkbox" id="payments_only_success"><input type="checkbox" class="check-toggle" checked ><span>Тільки успішні платежі</span></label>

<div class="real-full-width-block table-responsive border-top">
    <div class="full-width-table datailbill-table">
        <div>
            <?php
                $counter = 0;
                
                foreach ($payments as $item) {
                    $counter++;
                    $time = ($item['go_to_payment_time']) ? $item['go_to_payment_time'] : $item['timestamp'];
                    ?>
                    <div style="<?= ($item['status'] == 'success') ? '' : 'display: none;'; ?>" class="detail-bill detail-bill--outer item-payment-status item-payment-status-<?= $item['status']; ?>">
                        <div class="list__bill">
                            <p class="list__bill-header">Номер</p>
                            <p class="first border-bottom">
                                <a href="<?= BASE_URL; ?>/cabinet/payments/details/<?= $item['id']; ?>/" class="list__bill-link"><?= $item['id']; ?></a>
                            </p>
                        </div>
                        <div class="list__bill">
                            <p class="list__bill-header">Дата створення</p>
                            <p class="border-bottom" >
                                <span class="date-day"><?= getUkraineDate('j m Y', $time); ?></span><br>
                                <span class="date-time"><?= date('H:i:s', $time); ?></span>
                            </p>
                        </div>
                        <div class="list__bill">
                            <p class="list__bill-header">Тип</p>
                            <p>
                                <?php
                                    switch ($item['type']) {
                                        case 'komdebt':
                                            echo 'Комунальні послуги';
                                            break;
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="list__bill list__bill--sum">
                            <p class="list__bill-header">Сума, грн</p>
                            <p>
                                <?php
                                    $summ = explode('.', number_format($item['summ_total'], 2));
                                ?>

                                <span class="item-summ">
                                    <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                </span>
                            </p>
                        </div>
                        <div class="list__bill">
                            <p class="list__bill-header">Статус</p>
                            <p>
                                <?= $payment_statuses[$item['status']]; ?>
                            </p>
                        </div>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $(".niceCheck").click(function() {
        changeCheck($(this), 'check-group');
    });
    $("#payments_only_success input").change(function() {
        var checked = ($("#payments_only_success input").is(':checked'));
        if (checked) {
            $('.item-payment-status').not('.item-payment-status-success').css('display', 'none');
        } else {
            $('.item-payment-status').css('display', '');
        }
    });
});
</script>
