<div class="bill-details--outer">
    <table class="bill-details">
        <thead>
            <tr>
                <th class="bill-details__head" colspan="5">Деталі платежу № <?= $payment['id']; ?></th>
            </tr>
        </thead>
        <tbody class="bill-details__body bill-details__body--outer">
            <tr class="bill-details__row">
                <td colspan="1" class="first"><strong>Тип платежу</strong></td>
                <td colspan="4">Комунальні послуги</td>
            </tr>
            <tr class="bill-details__row">
                <td colspan="1" class="first"><strong>Статус</strong></td>
                <td colspan="4"><?= $payment_statuses[$payment['status']]; ?></td>
            </tr>

            <tr class="bill-details__row">
                <td colspan="1" class="first"><strong>Дата та час</strong></td>
                <td colspan="4">
                    <?php
                        $time = ($payment['go_to_payment_time']) ? $payment['go_to_payment_time'] : $payment['timestamp'];
                    ?>
                    <span class="date-day"><?= getUkraineDate('j m Y', $time); ?></span>
                    <span class="date-time"><?= getUkraineDate('H:i:s', $time); ?></span>
                </td>
            </tr>
            <tr class="bill-details__row">
                <td colspan="1" class="first"><strong>Сума</strong></td>
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
            <tr class="bill-details__row">
                <td colspan="1" class="first"><strong>Комісія</strong></td>
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
            <tr class="bill-details__row">
                <td colspan="1" class="first"><strong>Усього</strong></td>
                <td colspan="4">
                    <span  class=" green">
                        <?php
                            $summ = explode('.', number_format($payment['summ_total'], 2));
                        ?>
                        <span class="item-summ">
                            <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                        </span>
                        грн
                    </span>
                </td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th class="bill-details__head" colspan="5">Склад платежу</th>
            </tr>
        </thead>
        <tbody class="bill-details__body">
            <?php
                $counter = 0;
                $services_with_counters = [];

                for ($i=0; $i < count($services); $i++) {
                    $services[$i]['counter_data'] = (array)(@json_decode($services[$i]['counter_data']));
                    $services[$i]['data'] = json_decode($services[$i]['data']);
                    
                    if (!empty($services[$i]['counter_data'])) {
                        $services_with_counters[] = $services[$i];
                    }
                }
                
                foreach ($services as $item) {
                    $counter++;

                    $from_date = DateTime::createFromFormat('Y-m-d', $item['data']->dbegin);
                    $to_date = DateTime::createFromFormat('Y-m-d', $item['data']->dend);
                    ?>

                        <tr class="bill-details__row">
                            <th class="bill-details__cell">Назва послуги та підприємства</th>
                            <td class="bill-details__cell bill-details__cell--right">
                                <strong class="green"><?= $item['data']->name_plat; ?></strong>
                                <br>
                                <?= $item['data']->firm_name; ?>
                            </td>
                        </tr>

                        <tr class="bill-details__row">
                            <th>Особовий рахунок</th>
                            <td><?= $item['data']->abcount; ?></td>
                        </tr>

                        <tr class="bill-details__row">
                            <th>Сума, грн</th>
                            <td>
                                <?php
                                    $summ = explode('.', number_format($item['sum'], 2));
                                ?>
                                <span class="item-summ">
                                    <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                </span>
                            </td>
                        </tr>

                        <tr class="bill-details__row">
                            <th>Період</th>
                            <td>
                                <span class="date-day"><?= getUkraineDate('j m Y', $from_date->getTimestamp()); ?></span> —
                                <span class="date-day"><?= getUkraineDate('j m Y', $to_date->getTimestamp()); ?></span>
                            </td>
                        </tr>
                    <?php
                }
            ?>
        </tbody>
    <?php
        if (!empty($services_with_counters)) {
            ?>
            <thead>
                <tr>
                    <th class="first" colspan="5">Показання лічильників</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bank-name title">
                    <th class="first">Послуга</th>
                    <th>№ ліч.</th>
                    <th>Поп. пок., м&sup3;/КвтЧ</th>
                    <th>Пот. пок., м&sup3;/КвтЧ</th>
                    <th>Різниця, м&sup3;/КвтЧ</th>
                </tr>
            </tbody>
            <tbody>
                <?php
                    $counter = 0;
                    
                    foreach ($services_with_counters as $item) {

                        $counter_number = 0;
                        foreach ($item['counter_data'] as $counter_item) {
                            $counter++;
                            $counter_number++;

                            if (empty($counter_item->abcounter)) {
                                $counter_item->abcounter = $counter_number;
                            }

                            $no_border = (($counter == count($firm['counter'])) && ($counter < count($debtData['firm'])));
                            ?>
                            <tr class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                                <td class="first">
                                    <strong class="green"><?= $item['data']->name_plat; ?></strong>
                                    <br>
                                    <?= $item['data']->firm_name; ?>
                                </td>
                                <td><span class="item"><?= htmlspecialchars($counter_item->abcounter); ?></span></td>
                                <td>
                                    <?php
                                        $summ = explode('.', number_format($counter_item->old_value, 2));
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $summ = explode('.', number_format($counter_item->new_value, 2));
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $summ = explode('.', number_format($counter_item->pcount, 2));
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                ?>
            </tbody>
            <?php
        }
    ?>
    </table>
</div>
<div class="clear"></div>
<br>
<br>
<?php
    if ($payment['status'] == 'success') {
        ?>
        <a class="btn green big button button__form button__form--card button__form--bill" href="<?= BASE_URL; ?>/static/pdf/payment/<?= $payment['id']; ?>/MDL-Invoice-<?= $payment['id']; ?>.pdf">&darr; Завантажити квитанцію</a>
        <?php
    } elseif ($payment['status'] != 'new') {
        ?>
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/object-item/repay/">
            <input type="hidden" name="payment_id" value="<?= $payment['id']; ?>" />
            <button class="btn green big">Спробувати сплатити ще раз</button>
        </form>
        <?php
    }
?>
