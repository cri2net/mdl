<?php
    $_service = $services[0];

    for ($i=0; $i < count($services); $i++) {
        $services[$i]['data'] = @json_decode($services[$i]['data']);
    }
?>
<div class="real-full-width-block">
    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first" colspan="5">Деталі платежу № <?= $payment['id']; ?></th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row even">
                <td colspan="1" class="first">Тип платежу</td>
                <td colspan="4" class="">Послуги ЦКС</td>
            </tr>
            <tr class="item-row odd">
                <td colspan="1" class="first">Статус</td>
                <td colspan="4" class="">
                    <?php
                        switch ($payment['status']) {
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
                        }
                    ?>
                </td>
            </tr>
            <?php
                if ($payment['status'] == 'error') {
                    $error_desc = ShoppingCart::getErrorDescription($payment['processing'], $payment['trancode']);
                    
                    if (!empty($error_desc)) {
                        ?>
                        <tr class="item-row odd">
                            <td class="first">Код, опис помилки</td>
                            <td colspan="4"><?= $payment['trancode']; ?>, <?= $error_desc; ?></td>
                        </tr>
                        <?php
                    }
                }
            ?>
            <tr class="item-row even">
                <td colspan="1" class="first">Дата та час</td>
                <td colspan="4" class="">
                    <?php
                        $time = ($payment['go_to_payment_time']) ? $payment['go_to_payment_time'] : $payment['timestamp'];
                    ?>
                    <span class="date-day"><?= getUkraineDate('j m Y', $time); ?></span>
                    <span class="date-time"><?= getUkraineDate('H:i:s', $time); ?></span>
                </td>
            </tr>
            <tr class="item-row odd">
                <td colspan="1" class="first">Сума</td>
                <td colspan="4" class="">
                    <?php
                        $summ = explode('.', number_format($payment['summ_plat'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
            <tr class="item-row even">
                <td colspan="1" class="first">Комісія</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($payment['summ_komis'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
            <tr class="item-row odd">
                <td colspan="1" class="first">Усього</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($payment['summ_total'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="5" class="first">Інформація про платіж</th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row odd">
                <td class="first" colspan="1">Одержувач платежу</td>
                <td colspan="4"><?= $services[0]['data']->dst_name; ?></td>
            </tr>
            <tr class="item-row even">
                <td class="first" colspan="1">Призначення платежу</td>
                <td colspan="4"><?= $services[0]['data']->dest; ?></td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Адреса відділення</td>
                <td colspan="4"><?= $services[0]['data']->firme_item->name; ?></td>
            </tr>
            <tr class="item-row even">
                <td class="first" colspan="1">Сума платежу</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($payment['summ_plat'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="5" class="first">Інформація про платника</th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row even">
                <td class="first" colspan="1">ПІБ</td>
                <td colspan="4"><?= htmlspecialchars($services[0]['data']->r1); ?></td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Місце проживання</td>
                <td colspan="4"><?= htmlspecialchars($services[0]['data']->r2); ?></td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="5" class="first">Послуги</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $i = 0;

                foreach ($services as $_service) {

                    $summ = explode('.', number_format($_service['sum'], 2));
                    ?>
                    <tr class="item-row <?= ($i % 2 == 0) ? 'even' : 'odd'; ?>">
                        <td class="first" colspan="4"><?= htmlspecialchars($_service['data']->plat_item->name); ?></td>
                        <td>
                            <span class="item-summ">
                                <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                            </span>
                            грн
                        </td>
                    </tr>
                    <?php
                    $i++;
                }
            ?>
        </tbody>
    </table>
</div>
<div class="clear"></div>
<br>
<br>
<?php
    if ($payment['status'] == 'success') {
        ?>
        <a class="btn green big" href="<?= BASE_URL; ?>/static/pdf/payment/<?= $payment['id']; ?>/GIOC-Invoice-<?= $payment['id']; ?>.pdf">&darr; Завантажити квитанцію</a>
        <?php
    }
?>
