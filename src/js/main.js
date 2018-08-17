function is_int(input)
{
    return typeof(input)=='number'&&parseInt(input)==input;
};

function removeSpaces(string)
{
    if((string === undefined) || (string.length == 0)) {
        return '';
    }
    return string.replace(/ +/g, "").replace(/\t+/g, "");
};

function toFloat(val)
{
    return Math.round(val * 100) / 100;
};

function recalc2()
{
    var total = 0;
    $('input:text').each(function(i){
        var name = $(this).attr('name');
        if (!$(this).attr('disabled') && name.indexOf('sum') != -1) {
            if ($(this).val() == '') {
                $(this).val('0,00');
            }
            var val = parseFloat($(this).val().replace(',', '.'));
            
            if (isNaN(val)){
                $(this).val('0,00');
            } else if (val > 0) {
                total += val;
            }
        }
    });

    fetchTotalSumm(total);
};

function fetchTotalSumm(total)
{
    total = toFloat(total);
    if (total <= 0) {
        $('#pay_button').attr('disabled', 'disabled');
    } else {
        $('#pay_button').removeAttr('disabled');
    }
    if (is_int(total)) {
        total = total+',00';
    }
    var str = new String(total);
    totalStr = str.replace('.', ',');
    var indx = totalStr.lastIndexOf(',');
    var sub = totalStr.substring(indx+1, totalStr.length);
    if (sub.length < 2) {
        totalStr = totalStr + '0';
    }
    $('#total_debt').html(totalStr);
};

function recalc()
{
    var total = 0;
    $('input:text').each(function(i){
        var name = $(this).attr('name');
        if (!$(this).attr('disabled') && name.indexOf('sum') != -1){
            var val = parseFloat($(this).val().replace(',', '.'));

            if (!isNaN(val) && (val > 0)) {
                total += val;
            } else if (!isNaN(val) && (val == 0)) {
                var obl_pay = parseFloat($(this).data('obl-pay'));
                if (!isNaN(obl_pay)) {
                    total += obl_pay;
                }
            }
        }
    });
    
    fetchTotalSumm(total);
};

function bill_input_blur(el)
{
    var val = parseFloat($(el).val().replace(',', '.'));

    if (isNaN(val) || (val == 0)) {
        $(el).val('0,00');
        
        var obl_pay = $(el).data('obl-pay');
        if (!isNaN(parseFloat(obl_pay))) {
            $(el).val(obl_pay.toString().split('.').join(','));
            recalc();
        }
    }
};

function bill_input_focus(el)
{
    if ($(el).val() == '0,00') {
        $(el).val('');
    }
};

function checkAllServices(checkbox)
{
    var totalFlag = 0;
    var total = 0;
    if ($(checkbox).is(':checked')) {
        totalFlag = 1;
        $('input:checkbox').each(function(i){
            if (i > 0){
                $(this).attr('checked', 'checked');
            }
        });
        $('input.bill-summ-input').each(function(i){
            $(this).removeAttr('disabled');
        });
        $('#pay_button').removeAttr('disabled');
    } else {
        $('input:checkbox').each(function(i){
            if (i > 0) {
                $(this).removeAttr('checked');
            }
        });
        $('input.bill-summ-input').each(function(i){
            $(this).attr('disabled', 'disabled');
        });
        $('#pay_button').attr('disabled', 'disabled');
    }
    
    billPageUpdateTotalSumm();
};

function billPageUpdateTotalSumm()
{
    var total = 0;
    $('input.bill-summ-input').each(function(i){
        if (!$(this).is(':disabled')) {
            var val = $(this).val().replace(',', '.');
            val = parseFloat(val);
            if (!isNaN(val) && (val > 0)) {
                total += val;
            }
        }
    });
    total = toFloat(total);

    if (total <= 0) {
        $('#pay_button').attr('disabled', 'disabled');
    } else {
        $('#pay_button').removeAttr('disabled');
    }

    if (is_int(total)) {
        total += ',00';
    }

    var strTotal = new String(total);
    strTotal = strTotal.replace('.', ',');
    
    $('#total_debt').html(strTotal);
};

function selectService(checkboxId, inputId)
{
    var checkbox = $('#'+checkboxId);
    var currVal = $('#'+inputId).val().replace(',', '.');

    if ($(checkbox).is(':checked')) {
        $('#'+inputId).removeAttr('disabled');
    } else {
        $('#'+inputId).attr('disabled', 'disabled');
    }

    billPageUpdateTotalSumm();
};

function getShoppingCartTotal(total, percentSum, cctype)
{
    console.log(total);
    console.log(percentSum);
    console.log(cctype);
    var fTotal = parseFloat(total.replace(',', '.'));
    var fPercent = parseFloat(percentSum.replace(',', '.'));
    var total = fTotal + fPercent;
    total = toFloat(total);
    fPercent = toFloat(fPercent);
    
    var totalStr = new String(total);
    totalStr = totalStr.replace('.', ',')
    var indx = totalStr.lastIndexOf(',');

    if (indx != -1) {
        var sub = totalStr.substring(indx+1, totalStr.length);
        if (sub.length < 2) {
            totalStr = totalStr + '0';
        }
    } else {
        totalStr = totalStr + ',00';
    }

    var PercentStr = new String(fPercent);
    PercentStr = PercentStr.replace('.', ',')
    var indx = PercentStr.lastIndexOf(',');

    if (indx != -1){
        var sub = PercentStr.substring(indx+1, PercentStr.length);
        if (sub.length < 2){
            PercentStr = PercentStr + '0';
        }
    } else {
        PercentStr = PercentStr + ',00';
    }

    if (!$('.paybill-ps-item-' + cctype).is(':visible')) {
        $('.paybill-ps-item').slideUp(300);
        $('.paybill-ps-item-' + cctype).slideDown(400);
    }
    if (!$('.paybill-ps-item-hide-' + cctype).is(':visible')) {
        $('.paybill-ps-item-hide').slideUp(300);
        $('.paybill-ps-item-hide-' + cctype).slideDown(400);
    }

    $('#totalBillSum').html(totalStr + ' грн');
    // $('#comission_sum').html(PercentStr + ' грн');

    if (percentSum > 0) {
        $('#comission_sum').html('<span style="font-size: 14px;" id="comission_sum">2%, але не менше 5грн у розрізі постачальника послуг</span>');
    } else {
        $('#comission_sum').html('0,00 грн');
    }

    $('#cctype').val(cctype);
};

function checkForInt(evt)
{
    var charCode = (evt.which != null) ? evt.which : event.keyCode
    // charCodes < 32 include tab, delete, arrow keys, etc
    return (charCode < 32 || (charCode >= 48 && charCode <= 57))
};

function checkForDouble(input)
{
    // даёт возможность вставить одну точку или одну запятую и одну цифру дробной части
    var val = $(input).val();
    var goodval = val.replace(/[^\d,.]*/g, '')
          .replace(/([,.])[,.]+/g, '$1')
          .replace(/^([^\d]*(\d+([.,]\d{0,1})?)).*$/g, '$1');

    if (goodval != val) {
        $(input).val(goodval).change();
    }
    $(input).change();
};

function recount_counter_summ(key, tarif, counter_no) {
    var summ;
    var obl_pay = $('#inp_'+key).data('obl-pay');
    var old_value = $('#old_inp_'+ key +'_new_count_' + counter_no).val();
    old_value = old_value.split(',').join('.');
    old_value = parseFloat(old_value);

    var new_value = $('#inp_'+ key +'_new_count_' + counter_no).val();
    new_value = new_value.split(',').join('.');
    new_value = parseFloat(new_value);
    var other_counters = $('.inp_'+ key +'_new_count');
    var add_cost = 0;
    
    if (other_counters.length > 1) {
        $(other_counters).each(function(i, elem) {
            if ($(elem).attr('id') != 'inp_'+ key +'_new_count_' + counter_no) {
                var other_summ = $(elem).val();
                other_summ = other_summ.split(',').join('.');
                other_summ = parseFloat(other_summ);
                var other_old_summ = $('#old_' + $(elem).attr('id')).val();
                other_old_summ = other_old_summ.split(',').join('.');
                other_old_summ = parseFloat(other_old_summ);
                
                if (!isNaN(other_summ) && !isNaN(other_old_summ) && (other_summ >= other_old_summ)) {
                    add_cost += (other_summ - other_old_summ) * tarif;
                }
            }
        });
    }
    
    if (isNaN(tarif) || isNaN(old_value)) {
        $('#oldval_counter_' + key + '_' + counter_no).html('0');
    } else {
        $('#oldval_counter_'+key + '_' + counter_no).html(old_value);
        $('#inp_'+ key +'_new_count_' + counter_no).attr('min', old_value);
    }

    if (isNaN(tarif) || isNaN(new_value)) {
        $('#newval_counter_' + key + '_' + counter_no).html('поточне&nbsp;значення');
        add_cost = add_cost.toFixed(2);
        add_cost += '';
        add_cost = add_cost.split('.').join(',');
        $('#inp_'+key).val(add_cost).blur();
        return;
    }

    if (old_value > new_value) {
        // насколько я понял, в реальной жизни не может быть ситуации, когда значения счётчика перескочили с девяточек на нолики.
        // так что новое значение не должно быть меньше старого
        new_value = old_value;
    }
    summ = (new_value - old_value) * tarif;
    summ += add_cost;
    var call_input_blur = (!isNaN(parseFloat(obl_pay)) && (summ == 0));
    summ = summ.toFixed(2);
    summ += '';
    summ = summ.split('.').join(',');

    $('#inp_'+key).val(summ);
    recalc2();
    $('#newval_counter_'+key + '_' + counter_no).html(new_value);

    if (call_input_blur) {
        $('#inp_'+key).blur();
    }
};

function registration_show_password()
{
    var element = $('#reg-password');
    var replica = $('#reg-password-replica');
    var eyeIcon = $('.eye');

    if ($(element).is(':visible')) {
        var val = $(element).val();
        $(element).css('display', 'none');
        $(replica).val(val).css('display', 'block');
        eyeIcon.addClass('stroked');
    } else {
        var val = $(replica).val();
        $(replica).css('display', 'none');
        $(element).val(val).css('display', 'block');
        eyeIcon.removeClass('stroked');
    }
};

function registration_form_submit()
{
    // меняем пароль на правильное место
    var element = $('#reg-password');
    var replica = $('#reg-password-replica');
    
    if ($(replica).is(':visible')) {
        var val = $(replica).val();
        $(element).val(val);
    }

    return true;
};

function registration_ckeck_empty_fileld_password(element)
{
    var val = removeSpaces($(element).val());
    if (val.length == 0) {
        $(element).parent().parent().parent().find('.error-text').css('display', 'block');
    } else {
        $(element).parent().parent().parent().find('.error-text').css('display', 'none');
    }
};

function registration_ckeck_empty_fileld(element)
{
    var val = removeSpaces($(element).val());
    if (val.length == 0 || (val == '+___(__)___-__-__')) {
        $(element).parent().parent().find('.error-text').css('display', 'block');
    } else {
        $(element).parent().parent().find('.error-text').css('display', 'none');
    }
};

function changeCheck(element, group_class)
{
    var element = element,
        input = element.find("input").eq(0);
    
    if ($(element).hasClass('radio')) {
       
        $(element).removeClass("checked");
        input.attr("checked", false);

        var radio_name = $(input).attr('name');
        var elems = $('input[name=' + radio_name +']:checked');
        if (elems.length) {
            $(elems[0]).attr("checked", false).parent().removeClass('checked');
        }

        $(element).addClass("checked");
        input.attr("checked", true);

        $(input).change();

        return;
    }

    if (!input.attr("checked")) {
        $(element).addClass("checked");
        $(input).attr("checked", true).change();
    } else {
        $(element).removeClass("checked");
        $(input).attr("checked", false).change();
    }

    if ($(element).hasClass(group_class)) {
        var elems = $('.'+ group_class +'.checked');
        if (elems.length) {
            $('.'+ group_class +'-rule').addClass('checked').find("input").attr("checked", true);
        } else {
            $('.'+ group_class +'-rule').removeClass('checked').find("input").attr("checked", false);
        }
    } else if ($(element).hasClass(group_class +'-rule')) {
        if ($(element).hasClass('checked')) {
            $('.' + group_class).addClass('checked').find("input").attr("checked", true).change();
        } else {
            $('.' + group_class).removeClass('checked').find("input").attr("checked", false).change();
        }
    }
};

function check_delete_profile()
{
    return ($('#confirm_delete_profile input').is(':checked'));
};

function wait_ok_message_timeout(message, elem, counter, interval)
{
    var original_message = message;
    counter++;
    if (counter == 4) {
        counter = 0;
    }
    for (var i = 0; i < counter; i++) {
        message += '.';
    }
    $(elem).html(message);
    setTimeout(function(){wait_ok_message_timeout(original_message, elem, counter, interval);}, interval);
};

function isNumberKey(evt)
{
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }

    return true;
};

function isNumberKeyPlusDot(evt)
{
    var e = window.event || evt; // for trans-browser compatibility
    var charCode = e.which || e.keyCode;
    if ((charCode > 45 && charCode < 58) || charCode == 8) {
        return true;
    }
    return false;
};

function send_activation_code(element)
{
    $(element).fadeOut(400);
    setTimeout(function(){ $('#verify-email_send').fadeIn(400); }, 400);
    
    $.ajax({
        dataType: 'json',
        data: {},
        type: 'POST',
        url : BASE_URL + '/ajax/json/send_activation_code'
    });
};

function add_new_counters(key, abcounter, tarif)
{
    // максимум пять счётчиков
    if (new_counter_no['k' + key] >= 5) {
        return;
    }
    
    new_counter_no['k' + key]++;
    var new_counter_numnber = new_counter_no['k' + key];


    var html = 
        '<div class="row row-counter item-counter-'+ key +'" id="item-counter-'+ key +'-' + new_counter_numnber + '">'
+ '            <div class="col-md-12">'
+ '                <div class="counter-field">'
+ '                    <label>поточні</label>'
+ '                    <input class="inp_' + key + '_new_count" type="text" id="inp_' + key + '_new_count_' + new_counter_numnber + '" name="inp_' + key + '_new_count[]" maxlength="10" value="" onkeyup="checkForDouble(this);" onchange="recount_counter_summ(\'' + key + '\', ' + tarif + ', \'' + new_counter_numnber + '\');">'
+ '                </div>'
+ '                <div class="counter-field">'
+ '                    <label>минулі</label>'
+ '                    <input name="inp_' + key + '_old_count[]" type="text" maxlength="10" onkeyup="checkForDouble(this);" onchange="recount_counter_summ(\'' + key + '\', ' + tarif + ', \'' + new_counter_numnber + '\');" id="old_inp_' + key + '_new_count_' + new_counter_numnber + '" value="">'
+ '                </div>'
+ '                <div class="counter-field">'
+ '                    <label>№ лічильника</label>'
+ '                    <input type="text" id="num_inp_' + key + '_new_count_' + new_counter_numnber + '" name="inp_' + key + '_abcounter[]" value="' + abcounter + '">'
+ '                    <a data-id="' + abcounter + '" class="delete counter-delete" onclick="$(\'#item-counter-' + key + '-' + new_counter_numnber + '\').remove(); new_counter_no[\'k' + key + '\']--;">&times;</a>'
+ '                </div>'
+ '            </div>'
+ '            <input type="hidden" name="inp_' + key + '_count_number[]" value="' + new_counter_numnber + '">'
+ '        </div>';
/*    
        '<tr id="item-counter-'+ key + '-' + new_counter_numnber +'" data-number="'+ key +'" class="item-counter item-counter-'+ key +'" style="display: table-row;">' +
            '<td colspan="6">' +
                '<div class="row">' +
                    '<div class="col-md-4">' +
                        '<div class="counter-field">' +
                            '<input class="inp_'+ key +'_new_count" type="text" id="inp_'+ key +'_new_count_'+ new_counter_numnber +'" name="inp_'+ key +'_new_count[]" maxlength="10" onkeyup="checkForDouble(this);" onchange="recount_counter_summ(\''+ key +'\', '+ tarif +', \''+ new_counter_numnber +'\');">' +
                            '<span class="edit"></span>' +
                        '</div>' +
                        '<div class="counter-label">поточні</div>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<div class="counter-field">' +
                            '<input name="inp_'+ key +'_old_count[]" type="text" maxlength="10" onkeyup="checkForDouble(this);" onchange="(\''+ key +'\', '+ tarif +', \''+ new_counter_numnber +'\');" id="old_inp_'+ key +'_new_count_'+ new_counter_numnber +'">' +
                            '<span class="edit"></span>' +
                        '</div>' +
                        '<div class="counter-label">минулі</div>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<div class="counter-field">' +
                            '<input type="text" id="num_inp_'+ key +'_new_count_'+ new_counter_numnber +'" name="inp_'+ key +'_abcounter[]" value="'+ abcounter +'">' + 
                            '<a data-id="'+ abcounter +'" class="delete counter-delete" onclick="$(\'#item-counter-'+ key + '-' + new_counter_numnber + '\').remove(); new_counter_no[\'k' + key + '\']--;">&times;</a>' +
                            // '<a data-id="'+ abcounter +'" class="delete counter-delete" data-toggle="modal" data-target="#modalCounterConfirm">&times;</a>' +
                        '</div>' +
                        '<div class="counter-label">№ лічильника</div>' +
                    '</div>' +
                '</div>' +
                '<input type="hidden" name="inp_'+ key +'_count_number[]" value="'+ new_counter_numnber +'">' +
            '</td>' +
        '</tr>';
*/        

    $(html).insertBefore('.counter-container-' + key + ' .row-add-counter');
};

function tas_frame_load()
{
    $('#tas_frame_error').css('display', 'none');
    $('#tas_frame').css('display', '');
    clearTimeout(tas_timeout_id);
};

$(document).ready(function(){

    // украинизация jquery.ui.datepicker
    $.datepicker.regional['ua'] = {
        closeText: 'Закрити',
        prevText: '&#x3c;Попр',
        nextText: 'Наст&#x3e;',
        currentText: 'Сьогодні',
        monthNames: ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'],
        monthNamesShort: ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'],
        dayNames: ['неділя', 'понеділок', 'вівторок', 'середа', 'четвер', 'п’ятниця', 'субота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Нд', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        isRTL: false
    };
    $.datepicker.setDefaults($.datepicker.regional['ua']);
});


(function ($) {
    "use strict";

    $.fn.depdropLocales['ua'] = {
        loadingText: 'завантаження ...',
        placeholder: 'Обрати ...',
        emptyMsg: 'Дані не знайдено'
    };
})(window.jQuery);
