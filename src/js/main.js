function is_int(input) {
    return typeof(input)=='number'&&parseInt(input)==input;
};

function htmlspecialchars (string, reverse) {
    var specialChars = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        }, x;

    if (typeof(reverse) != "undefined") {
        reverse = [];
        for (x in specialChars) {
            reverse.push(x);
        }
        reverse.reverse();

        for (x = 0; x < reverse.length; x++) {
            string = string.replace(new RegExp(specialChars[reverse[x]], "g"), reverse[x]);
        }

        return string;
    }
    
    for (x in specialChars) {
        string = string.replace(new RegExp(x, "g"), specialChars[x]);
    }

    return string;
};

function removeSpaces(string) {
    if((string === undefined) || (string.length == 0)) {
        return '';
    }
    return string.replace(/ +/g, "").replace(/ +/g, "");
};

function toFloat(val) {
    return Math.round(val * 100) / 100;
};

var add_ok_message_seconds = 0;
function add_ok_message_timeout() {
    add_ok_message_seconds++;
    if (add_ok_message_seconds == 4) {
        add_ok_message_seconds = 0;
    }
    var message = 'Идёт добавление объекта';
    for (var i = 0; i < add_ok_message_seconds; i++) {
        message += '.';
    }
    $('#add_ok_message_h4').html(message);
    setTimeout(function(){add_ok_message_timeout();}, 600);
};

function addNewHouse() {
    $('#add_house_button').attr('disabled', 'disabled');
    var data = {};
    data.obj = 'Flat';
    data.ac = 'addFlat';
    data.params = {};
    data.params.flat_id = $('#flat').val();
    
    $.ajax({
        dataType: 'json',
        data: data,
        type: 'POST',
        url : '/ajax/json/_engine',
        success : function(res, textStatus){
            if (res.success == true) {
                $('#overlay_dom_new .bgr').append('<div class="add_ok_message"><h4 id="add_ok_message_h4">Идёт добавление объекта</h4></div>');
                setTimeout(function(){add_ok_message_timeout();}, 600);
                top.location.href = top.location.href;
            } else if (res.success == false) {
                $('#add_house_button').removeAttr('disabled');
                $('#error_house').removeAttr('style');
                $('#error_msg').html(res.record.msg);
            }
        }
    });
};

function deleteHouse(flat_id) {
    var data = {};
    data.obj = 'Flat';
    data.ac = 'removeUserFlat';
    data.params = {};
    data.params.flat_id = flat_id;
    
    $.ajax({
        dataType: 'json',
        data: data,
        type: 'POST',
        url : '/ajax/json/_engine',
    });
    
    $('#bbox_house_' + flat_id).remove();
    var count = parseInt($('#house_count').html()) - 1;
    $('#house_count').html(count.toString());
    
    return false;
};

function recalc2() {
    var total = 0;
    $('input:text').each(function(i){
        var name = $(this).attr('name');
        if (!$(this).attr('disabled') && name.indexOf('sum') != -1) {
            if ($(this).val() == '') {
                $(this).val('0,00');
            }
            var val = $(this).val().replace(',', '.');
            
            if (isNaN(val)){
                $(this).val('0,00');
                val = '0.00';
            }
            
            total += parseFloat(val);
        }
    });
    total = toFloat(total);
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

function recalc() {
    var total = 0;
    $('input:text').each(function(i){
        var name = $(this).attr('name');
        if (!$(this).attr('disabled') && name.indexOf('sum') != -1){
            if ($(this).val() == '') {
                $(this).val('0,00');
            }
            var val = $(this).val().replace(',', '.');
            
            if (isNaN(val)){
                $(this).val('0,00');
                val = '0.00';
            }
            total += parseFloat(val);
        }
    });
    total = toFloat(total);
    if (total <= 0) {
        $('#recalc_button').attr('disabled', 'disabled');
        $('#pay_button').attr('disabled', 'disabled');
    } else {
        $('#recalc_button').removeAttr('disabled');
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

function checkAllServices(checkbox) {
    var totalFlag = 0;
    var total = 0;
    if (checkbox.checked == true) {
        totalFlag = 1;
        $('input:checkbox').each(function(i){
            if (i > 0){
                $(this).attr('checked', 'checked');
            }
        });
        $('input:text').each(function(i){
            $(this).removeAttr('disabled');
        });
        $('#recalc_button').removeAttr('disabled');
        $('#pay_button').removeAttr('disabled');
    } else {
        $('input:checkbox').each(function(i){
            if (i > 0) {
                $(this).removeAttr('checked');
            }
        });
        $('input:text').each(function(i){
            $(this).attr('disabled', 'disabled');
        });
        $('#recalc_button').attr('disabled', 'disabled');
        $('#pay_button').attr('disabled', 'disabled');
    }
    
    if (totalFlag == 0) {
        total = '0,00';
    } else if (totalFlag == 1) {
        $('input:text').each(function(i){
            var val = $(this).val().replace(',', '.');
            total += parseFloat(val);
        });
        total = toFloat(total);
    }
    var strTotal = new String(total);
    strTotal = strTotal.replace('.', ',');
    
    $('#total_debt').html(strTotal);
};

function selectService(chechbox, inputId) {
    var total = $('#total_debt').html().replace(',', '.');
    total = parseFloat(total);
    var currVal = $('#'+inputId).val().replace(',', '.');
    var totalDebt = 0;
    
    if (chechbox.checked == true){
        totalDebt = toFloat(total) + toFloat(currVal);
        $('#'+inputId).removeAttr('disabled');
        $('#recalc_button').removeAttr('disabled');
        $('#pay_button').removeAttr('disabled');
    } else {
        totalDebt = toFloat(total) - toFloat(currVal);
        $('#'+inputId).attr('disabled', 'disabled');
        if (toFloat(totalDebt) <= 0) {
            $('#recalc_button').attr('disabled', 'disabled');
            $('#pay_button').attr('disabled', 'disabled');
        }
    }
    totalDebt = toFloat(totalDebt);
    
    if (is_int(totalDebt)) {
        totalDebt = totalDebt + ',00';
    }
    var strTotal = new String(totalDebt);
    strTotal = strTotal.replace('.', ',');
    

    $('#total_debt').html(strTotal);
};

function getShoppingCartTotal(total, persentSum, cctype) {
    var fTotal = parseFloat(total.replace(',', '.'));
    var fPersent = parseFloat(persentSum.replace(',', '.'));
    var total = fTotal + fPersent;
    total = toFloat(total);
    fPersent = toFloat(fPersent);
    
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
    var PersentStr = new String(fPersent);
    PersentStr = PersentStr.replace('.', ',')
    var indx = PersentStr.lastIndexOf(',');
    if (indx != -1){
        var sub = PersentStr.substring(indx+1, PersentStr.length);
        if (sub.length < 2){
            PersentStr = PersentStr + '0';
        }
    } else {
        PersentStr = PersentStr + ',00';
    }
    $('#totalBillSum').html(totalStr + ' грн');
    $('#comission_sum').html(PersentStr + ' грн');
    $('#cctype').val(cctype);
};

function checkForInt(evt) {
    var charCode = ( evt.which != null ) ? evt.which : event.keyCode
    // charCodes < 32 include tab, delete, arrow keys, etc
    return (charCode < 32 || (charCode >= 48 && charCode <= 57))
};

function recount_counter_summ(key, old_value, tarif, counter_no) {
    var old_value = old_value.split(',').join('.');
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
                var other_old_summ = $('#old_' + $(elem).attr('id')).html();
                other_old_summ = other_old_summ.split(',').join('.');
                other_old_summ = parseFloat(other_old_summ);
                if (!isNaN(other_summ) && !isNaN(other_old_summ) && (other_summ >= other_old_summ)) {
                    add_cost += (other_summ - other_old_summ) * tarif;
                }
            }
        });
    }
    
    if (isNaN(tarif) || isNaN(new_value)) {
        $('#newval_counter_' + key + '_' + counter_no).html('новое&nbsp;значение');
        add_cost = add_cost.toFixed(2);
        add_cost += '';
        add_cost = add_cost.split('.').join(',');
        $('#inp_'+key).val(add_cost);
        return;
    }

    if (old_value > new_value) {
        var add_val = '1';
        var old_value_str = old_value + '';
        for (var i = 0; i < old_value_str.length; i++) {
            add_val += '0';
        }
        new_value += parseInt(add_val, 10);
    }
    var summ = (new_value - old_value) * tarif;
    summ += add_cost;
    summ = summ.toFixed(2);
    summ += '';
    summ = summ.split('.').join(',');
    $('#inp_'+key).val(summ);
    recalc2();
    $('#newval_counter_'+key + '_' + counter_no).html(new_value+'&nbsp;м<sup>3</sup>');
};

function close_all_header_submenu(submenu_id)
{
    for (var i=0; i < have_main_submenu_item.length; i++) {
        if (have_main_submenu_item[i] != submenu_id) {
            $('#header_submenu_'+have_main_submenu_item[i]).css('display', '');
            $('header_down_'+have_main_submenu_item[i]).css('display', '');
            $('#header_submenu_'+have_main_submenu_item[i]).stopTime('header_submenu_'+have_main_submenu_item[i]);
        }
    }
}

function show_header_submenu(submenu_id)
{
    close_all_header_submenu(submenu_id);
    
    var submenu = $('#header_submenu_'+submenu_id);
    var down = $('#header_down_'+submenu_id);
    var visible = $(submenu).is(':visible');
    var item = $('#header_top_item_'+submenu_id);

    $(submenu).fadeIn(200);
    $(down).css('display', 'block');
    $(submenu).stopTime('header_submenu_'+submenu_id);
    $('.open-submenu').removeClass('open-submenu');
    $(item).addClass('open-submenu');
    
    $(submenu).oneTime(700, 'header_submenu_'+submenu_id, function(){
        $(submenu).fadeOut(200);
        $(down).css('display', '');
    });
    
    $(submenu).mouseout(function(){
        $(submenu).everyTime(700, 'header_submenu_'+submenu_id, function(){
            $(submenu).fadeOut(200);
            $(down).css('display', '');
        });
    });
    
    $(submenu).mouseover(function(){
        $(submenu).stopTime('header_submenu_'+submenu_id);
    });
    
    $(item).mouseout(function(){
        $(submenu).stopTime('header_submenu_'+submenu_id);
        $(submenu).everyTime(700, 'header_submenu_'+submenu_id, function(){
            $(submenu).fadeOut(200);
            $(down).css('display', '');
            $(item).removeClass('open-submenu');
        });
    });
    
    $(item).mouseover(function(){
        $(submenu).stopTime('header_submenu_'+submenu_id);
    });
};

var current_slide = 0;
function next_slide_rotate_index() {
    var next = current_slide + 1;
    if (next >= slide_count) {
        next = 0;
    }
    for (var i = 0; i < slide_count; i++) {
        $('#slide_'+i).css('display', 'none');
        $('#bullet_'+i).removeClass('active');
    };
    $('#slide_'+next).css('display', 'block');

    $('#bullet_'+next).addClass('active');
    current_slide++;
    if (current_slide >= slide_count) {
        current_slide = 0;
    }
};

function prev_slide_rotate_index() {
    var prev = current_slide - 1;
    if (prev < 0) {
        prev = slide_count - 1;
    }
    for (var i = 0; i < slide_count; i++) {
        $('#slide_'+i).css('display', 'none');
        $('#bullet_'+i).removeClass('active');
    };
    $('#slide_'+prev).css('display', 'block');

    $('#bullet_'+prev).addClass('active');
    current_slide--;
    if (current_slide < 0) {
        current_slide = slide_count - 1;
    }
    $('.bullets').stopTime('slider_bullets');
    $('.bullets').everyTime(4000, 'slider_bullets', function() {
        next_slide_rotate_index();
    });
};

function jump_to_slide(next) {
    if (current_slide == next) {
        return;
    }
    for (var i = 0; i < slide_count; i++) {
        $('#slide_'+i).css('display', 'none');
        $('#bullet_'+i).removeClass('active');
    };
    $('#slide_'+next).css('display', 'block');

    $('#bullet_'+next).addClass('active');
    current_slide++;
    if (current_slide >= slide_count) {
        current_slide = 0;
    }

    $('.bullets').stopTime('slider_bullets');
    $('.bullets').everyTime(4000, 'slider_bullets', function() {
        next_slide_rotate_index();
    });
};

function registration_show_password() {
    var element = $('#reg-password');
    var replica = $('#reg-password-replica');
    if ($(element).is(':visible')) {
        var val = $(element).val();
        $(element).css('display', 'none');
        $(replica).val(val).css('display', 'block');
    } else {
        var val = $(replica).val();
        $(replica).css('display', 'none');
        $(element).val(val).css('display', 'block');
    }
};

function registration_form_submit() {
    // меняем пароль на правильное место
    var element = $('#reg-password');
    var replica = $('#reg-password-replica');
    
    if ($(replica).is(':visible')) {
        var val = $(replica).val();
        $(element).val(val);
    }

    return true;
};

function registration_ckeck_empty_fileld_password(element) {
    var val = removeSpaces($(element).val());
    if (val.length == 0) {
        $(element).parent().parent().parent().find('.error-text').css('display', 'block');
    } else {
        $(element).parent().parent().parent().find('.error-text').css('display', 'none');
    }
};

function registration_ckeck_empty_fileld(element) {
    var val = removeSpaces($(element).val());
    if (val.length == 0 || (val == '+___(__)___-__-__')) {
        $(element).parent().parent().find('.error-text').css('display', 'block');
    } else {
        $(element).parent().parent().find('.error-text').css('display', 'none');
    }
};


$(document).ready(function(){
    (function(){
        $('.spoiler-title').click(function(){
            var par = $(this).parent();
            var btn = $(par).find('.spoiler-title');
            if ($(par).hasClass('open')) {
                $(par).removeClass('open').find('.spoiler-text').slideUp(400);
            } else {
                $(par).addClass('open').find('.spoiler-text').slideDown(400);
            }
        });
        $('.spoiler-close').click(function(){
            $(this).parent().parent().find('.spoiler-title').click();
        });
    })();
});

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
        input.attr("checked", true)
    } else {
        $(element).removeClass("checked");
        input.attr("checked", false)
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
            $('.' + group_class).addClass('checked').find("input").attr("checked", true);
        } else {
            $('.' + group_class).removeClass('checked').find("input").attr("checked", false);
        }
    }
};

function open_feedback_msg(chief_id)
{
    $('body').addClass('popup-open').append('<div id="global-owerlay"><div id="popup-box" class="popup-box"><div class="popup-content"></div></div></div>');
    $('#popup-box .popup-content').html(chief_empty_form);
    $('#chief_id').val(chief_id);
};

function close_feedback_msg()
{
    $('#global-owerlay').remove();
    $('body').removeClass('popup-open');
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

function show_more_news(loader_icon_id)
{
    var img = $('#' + loader_icon_id);
    var elem = img.parent();
    if ($(elem).hasClass('rotation')) {
        return;
    }

    $(elem).addClass('rotation');
    image_rotation($(elem).find('img'), 'rotation');

    var data = {
        action: 'load_more',
        news_on_page: news_on_page,
        news_current_page: news_current_page
    };

    $.ajax({
        dataType: 'json',
        data: data,
        type: 'POST',
        url : '/ajax/json/news',
        success : function(response){
            $(elem).removeClass('rotation');
            if (response.status) {
                $('<div class="news-list-slidedown" style="display:none;">' + response.html + '</div>').insertBefore('#mews-insert-before');
                news_current_page++;
                if (news_pages_cont == news_current_page) {
                    $('#btn-more-block').fadeOut(400).remove();
                }
                $('.news-list-slidedown').slideDown(400).removeClass('news-list-slidedown');
                var pages = $('.ruler a.current + a:last').addClass('current');
            }
        }
    });
};

function image_rotation(elem, need_class)
{
    if ($(elem).parent().hasClass(need_class)) {
        $(elem).rotate({
            angle: 0,
            animateTo: 360,
            duration: 1500,
            callback: function(){ image_rotation(elem, need_class); }
        });
    }
};
