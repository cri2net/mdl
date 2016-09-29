<?php
    $plat_list = CKS::getPlatList();
    $districts = CKS::getDistricts();
?>
<h1 class="big-title">Сплата послуг ЦКС</h1>

<form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/cks/">
    <div style="display: inline-block; float: none; width: 100%;">
        <div class="field-group">
            <label>
                Оберіть район м. Київ <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <select class="txt" required="required" id="district">
                    <option value="">-- виберіть --</option>
                    <?php
                        $i = 0;
                        foreach ($districts as $item) {
                            ?>
                            <option value="<?= ++$i; ?>"><?= htmlspecialchars($item['name']); ?></option>
                            <?php
                        }
                    ?>
                </select>
            </label>
        </div>
        <div class="field-group">
            <label>
                Оберіть підрозділ <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <select class="txt" required="required" id="firme" name="firme">
                    <option id="firme_empty" value="">-- виберіть --</option>
                    <?php
                        $i = 0;
                        
                        foreach ($districts as $item) {
                            
                            $i++;

                            foreach ($item['firme_list'] as $firme_item) {
                                ?>
                                <option data-number="<?= $i; ?>" value="<?= $firme_item['id']; ?>"><?= htmlspecialchars($firme_item['name']); ?></option>
                                <?php
                            }
                        }
                    ?>
                </select>
            </label>
        </div>
    </div>  

    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first align-center counters-th" colspan="4">
                    Оберіть послуги до сплати
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bank-name title">
                <td class="first">
                    <div class="check-box-line">
                        <span id="check_all_services" class="niceCheck check-group-rule"><input onchange="checkAllServices($('#check_all_services-elem'));" id="check_all_services-elem" type="checkbox"></span>
                        <label onclick="$('#check_all_services').click();">
                            Послуга
                        </label>
                    </div>
                </td>
                <td style="white-space:nowrap;">До сплати, грн</td>
            </tr>

            <?php
                $row_counter = 0;

                foreach ($plat_list as $item) {
                    $row_counter++;
                    ?>
                    <tr class="item-row <?= ($row_counter % 2 == 0) ? 'even' : 'odd'; ?>">
                        <td class="first">
                            <div class="check-box-line">
                                <span class="niceCheck check-group" id="bill_item_<?= $item['id']; ?>">
                                    <input onchange="selectService('bill_checkbox_<?= $item['id']; ?>', 'inp_<?= $item['id']; ?>');" id="bill_checkbox_<?= $item['id']; ?>" value="inp_<?= $item['id']; ?>" name="items[]" type="checkbox">
                                </span>
                                <label onclick="$('#bill_item_<?= $item['id']; ?>').click();">
                                    <span><?= htmlspecialchars($item['name']); ?></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <input disabled="disabled" readonly="readonly" class="bill-summ-input txt num-short green bold form-txt-input" name="inp_<?= $item['id']; ?>_sum" size="20" value="<?= str_replace('.', ',', sprintf('%.2f', $item['sum'])); ?>" onblur="bill_input_blur(this);" onfocus="bill_input_focus(this);" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $item['id']; ?>" type="text">
                        </td>
                    </tr>
                    <?php
                }
            ?>
            <tr class="total-summ-tr">
                <td class="first align-right">Усього, грн:</td>
                <td class="total-sum" id="total_debt">0,00</td>
            </tr>
            <tr>
                <td class="align-center" colspan="4">
                    <button disabled="disabled" class="btn green bold big" id="pay_button">Сплатити</button>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<script>
    $(document).ready(function(){

        $("#district").change(function() {
            var val = $("#district").val();
            $('#firme').val('');
            $("#firme option").css('display', 'none');
            $("#firme_empty").css('display', '');

            $('#firme option').each(function(i) {
                var number = $(this).data('number');
                if (number == val) {
                    $(this).css('display', '');
                }
            });
        });

        $(".niceCheck").click(function() {
            changeCheck($(this), 'check-group');
        });

        $("#district").trigger('change');
    });
</script>
