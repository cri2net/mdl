<div class="real-full-width-block">
    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th colspan="5" class="first">Реквізити</th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row even">
                <td class="first" colspan="1">Отримувач</td>
                <td colspan="4"><?= $_service['data']->dst_name; ?></td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">ЄДРПОУ (ЗКПО)</td>
                <td colspan="4"><?= $_service['data']->dst_okpo; ?></td>
            </tr>
            <tr class="item-row even">
                <td class="first" colspan="1">МФО</td>
                <td colspan="4"><?= $_service['data']->dst_mfo; ?></td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Розрахунковий рахунок</td>
                <td colspan="4"><?= $_service['data']->dst_rcount; ?></td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="5" class="first">Інформація про платіж</th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row even">
                <td class="first" colspan="1">Дата операції</td>
                <td colspan="4">
                    <span class="date-day"><?= getUkraineDate('j m Y', $_service['timestamp']); ?></span>
                    <span class="date-time"><?= getUkraineDate('H:i:s', $_service['timestamp']); ?></span>
                </td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Одержувач платежу</td>
                <td colspan="4"><?= $_service['data']->dst_name; ?></td>
            </tr>
            <tr class="item-row even">
                <td class="first" colspan="1">Призначення платежу</td>
                <td colspan="4"><?= $_service['data']->dest; ?></td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Сума платежу</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($_payment['summ_plat'], 2));
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
                <td colspan="4"><?= htmlspecialchars($_service['data']->r1); ?></td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Місце проживання</td>
                <td colspan="4"><?= htmlspecialchars($_service['data']->r2); ?></td>
            </tr>
        </tbody>
        <thead>
            <tr>
                <th colspan="5" class="first">Вартість платежу</th>
            </tr>
        </thead>
        <tbody>
            <tr class="item-row even">
                <td class="first" colspan="1">Збір за обробку платежу</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($_payment['summ_komis'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
            <tr class="item-row odd">
                <td class="first" colspan="1">Усього до сплати</td>
                <td colspan="4">
                    <?php
                        $summ = explode('.', number_format($_payment['summ_total'], 2));
                    ?>
                    <span class="item-summ">
                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                    </span>
                    грн
                </td>
            </tr>
            <tr>
                <td class="align-center" colspan="5">
                    <form action="<?= BASE_URL; ?>/post/cabinet/instant-payments/<?= $__route_result['values']['section']; ?>/" method="post">
                        <input type="hidden" name="get_last_step" value="1">
                        <div class="blue_button registration">
                            <button style="width:240px;" id="submitOrder" class="btn green bold">Перейти до сплати</button>
                        </div>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
</div>
