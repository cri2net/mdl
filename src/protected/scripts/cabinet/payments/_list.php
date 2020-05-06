<?php
    if (empty($payments)) {
        ?>
        <div class="big-warning-message">Немає платежів для відображення</div>
        <?php
        return;
    }
?>
<label class="checkbox green" id="payments_only_success"><input name="service[<?= $key ?>]" type="checkbox" class="check-toggle" checked ><span>Тільки успішні платежі</span></label>

<div class="real-full-width-block table-responsive border-top">
    <table class="full-width-table datailbill-table">
        <thead>
            <tr>
                <th class="first">Номер</th>
                <th>Дата створення</th>
                <th>Тип</th>
                <th class="td-sum">Сума, грн</th>
                <th class="td-last">Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $counter = 0;
                
                foreach ($payments as $item) {
                    $counter++;
                    $time = ($item['go_to_payment_time']) ? $item['go_to_payment_time'] : $item['timestamp'];
                    ?>
                    <tr style="<?= ($item['status'] == 'success') ? '' : 'display: none;'; ?>" class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?> item-payment-status item-payment-status-<?= $item['status']; ?>">
                        <td class="first border-bottom">
                            <a href="<?= BASE_URL; ?>/cabinet/payments/details/<?= $item['id']; ?>/"><?= $item['id']; ?></a>
                        </td>
                        <td class="border-bottom" >
                            <span class="date-day"><?= getUkraineDate('j m Y', $time); ?></span><br>
                            <span class="date-time"><?= date('H:i:s', $time); ?></span>
                        </td>
                        <td class="border-bottom" >
                            <?php
                                switch ($item['type']) {
                                    case 'komdebt':
                                        echo 'Комунальні послуги';
                                        break;
                                }
                            ?>
                        </td>
                        <td class="border-bottom">
                            <?php
                                $summ = explode('.', number_format($item['summ_total'], 2));
                            ?>
                            <span class="item-summ">
                                <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                            </span>
                        </td>
                        <td class="border-bottom">
                            <?php
                                switch ($item['status']) {
                                    case 'new':
                                    case 'timeout':
                                        echo 'Новий (не оплачений)';
                                        break;

                                    case 'success':
                                        echo 'Успішний';
                                        break;

                                    case 'error':
                                        echo 'Помилка';
                                        break;

                                    case 'reverse':
                                        echo 'Cторнований';
                                        break;

                                    case 'pending':
                                        echo 'В обробцi';
                                        break;
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
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
