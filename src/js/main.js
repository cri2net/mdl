function is_int(input)
{
    return typeof(input)=='number'&&parseInt(input)==input;
};

function htmlspecialchars (string, reverse)
{
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

function selectService(chechboxId, inputId)
{
    var chechbox = $('#'+chechboxId);
    var currVal = $('#'+inputId).val().replace(',', '.');

    if ($(chechbox).attr('checked')) {
        $('#'+inputId).removeAttr('disabled');
    } else {
        $('#'+inputId).attr('disabled', 'disabled');
    }

    billPageUpdateTotalSumm();
};

function getShoppingCartTotal(total, percentSum, cctype)
{
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
    $('#comission_sum').html(PercentStr + ' грн');
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

function close_all_header_submenu(submenu_id)
{
    for (var i=0; i < have_main_submenu_item.length; i++) {
        if (have_main_submenu_item[i] != submenu_id) {
            $('#header_submenu_'+have_main_submenu_item[i]).css('display', '');
            $('header_down_'+have_main_submenu_item[i]).css('display', '');
            $('#header_submenu_'+have_main_submenu_item[i]).stopTime('header_submenu_'+have_main_submenu_item[i]);
        }
    }
    // может быть избыточный код
    $('.open-submenu').removeClass('open-submenu');
    $('.a-element-box.open').removeClass('open');
};

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
    $('.a-element-box.open').removeClass('open');
    $(item).addClass('open-submenu').find('.a-element-box').addClass('open');
    
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
            $(item).removeClass('open-submenu').find('.a-element-box').removeClass('open');;
        });
    });
    
    $(item).mouseover(function(){
        $(submenu).stopTime('header_submenu_'+submenu_id);
    });
};

var current_slide = 0;
function next_slide_rotate_index()
{
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

function prev_slide_rotate_index()
{
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

function jump_to_slide(next)
{
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

function open_video_frame(element, youtube_id)
{
    current_youtube_id = 'ytplayer-' + youtube_id;
    $('body').addClass('popup-open').append('<div id="global-owerlay" onclick="close_feedback_msg();" class="youtube"><div id="popup-box" class="popup-box"><div class="popup-content"></div></div></div>');
    $('#popup-box .popup-content').html('<div id="'+ current_youtube_id +'"></div>');

    player = new YT.Player(current_youtube_id, {
        playerVars: { 'autoplay': 1, 'controls': 1, 'autohide':1, 'wmode':'opaque'},
        videoId: youtube_id,
        width: '460',
        height: '344',
    });
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
        url : BASE_URL + '/ajax/json/news',
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

function subscribe_by_email()
{
    var email = $('#subscribe_by_email').val();
    
    if (!email.length) {
        return;
    }

    $.ajax({
        dataType: 'json',
        data: {email: email},
        type: 'POST',
        url : BASE_URL + '/ajax/json/subscribe',
        success : function(response){
            if (response.status) {
                $('#subscribe_by_email').parent().html('<span class="thx">Дякуємо за підписку!</span>');
            } else {
                $('#subscribe_by_email').css('color', "#e00").css('cursor', "help").attr('title', response.text);
                setTimeout(function(){ $('#subscribe_by_email').css('color', "").css('cursor', ""); }, 1200);
            }
        }
    });
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

function searchSubmit()
{
    var s = $('search').val();
    return (s.length > 0);
};

function translite(str)
{
    var arr = {'а':'a', 'б':'b', 'в':'v', 'г':'g', 'д':'d', 'е':'e', 'ж':'g', 'з':'z', 'и':'i', 'й':'y', 'к':'k', 'л':'l', 'м':'m', 'н':'n', 'о':'o', 'п':'p', 'р':'r', 'с':'s', 'т':'t', 'у':'u', 'ф':'f', 'ы':'i', 'э':'e', 'А':'A', 'Б':'B', 'В':'V', 'Г':'G', 'Д':'D', 'Е':'E', 'Ж':'G', 'З':'Z', 'И':'I', 'Й':'Y', 'К':'K', 'Л':'L', 'М':'M', 'Н':'N', 'О':'O', 'П':'P', 'Р':'R', 'С':'S', 'Т':'T', 'У':'U', 'Ф':'F', 'Ы':'I', 'Э':'E', 'ё':'yo', 'х':'h', 'ц':'ts', 'ч':'ch', 'ш':'sh', 'щ':'shch', 'ъ':'', 'ь':'', 'ю':'yu', 'я':'ya', 'Ё':'Yo', 'Х':'H', 'Ц':'Ts', 'Ч':'Ch', 'Ш':'Sh', 'Щ':'Shch', 'Ъ':'', 'Ь':'', 'Ю':'Yu', 'Я':'Ya', 'ь':''};
    var replacer = function(a){return arr[a]||a};
    return str.replace(/[А-яёЁ]/g,replacer);
};

function strtolower(str)
{
    var arr = {'A':'a', 'B':'b', 'C':'c', 'D':'d', 'E':'e', 'F':'f', 'G':'g', 'H':'h', 'I':'i', 'J':'j', 'K':'k', 'L':'l', 'M':'m', 'N':'n', 'O':'o', 'P':'p', 'Q':'q', 'R':'r', 'S':'s', 'T':'t', 'U':'u', 'V':'v', 'W':'w', 'X':'x', 'Y':'y', 'Z':'z'};
    var replacer=function(a){return arr[a]||a};
    return str.replace(/[A-z]/g,replacer);
};

function strtoupper(str)
{
    var arr = {'a':'A', 'b':'B', 'c':'C', 'd':'D', 'e':'E', 'f':'F', 'g':'G', 'h':'H', 'i':'I', 'j':'J', 'k':'K', 'l':'L', 'm':'M', 'n':'N', 'o':'O', 'p':'P', 'q':'Q', 'r':'R', 's':'S', 't':'T', 'u':'U', 'v':'V', 'w':'W', 'x':'X', 'y':'Y', 'z':'Z'};
    var replacer=function(a){return arr[a]||a};
    return str.replace(/[A-z]/g,replacer);
};

function add_new_counters(key, abcounter, tarif)
{
    new_counter_no['k' + key]++;
    var new_counter_numnber = new_counter_no['k' + key];
    
    var html = '<div class="counter-data"><br> Показання лічильника №'+ new_counter_numnber +' : <br>' +
        '<div style="margin-top:5px; margin-bottom:5px;">' +
            '<label for="old_inp_'+ key +'_new_count_'+ new_counter_numnber +'" style="width:100px; display:inline-block;">Попередні:</label>' +
            '<input style="width: 60px;" value="0" id="old_inp_'+ key +'_new_count_'+ new_counter_numnber +'" name="inp_'+ key +'_old_count[]" type="text" maxlength="10" onkeyup="checkForDouble(this);" onchange="recount_counter_summ(\''+ key +'\', '+ tarif +', \''+ new_counter_numnber +'\');">' +
        '</div>' +
        '<div style="margin-bottom:5px;">' +
            '<label for="inp_'+ key +'_new_count_'+ new_counter_numnber +'" style="width:100px; display:inline-block;">Поточні:</label>' +
            '<input style="width: 60px;" class="inp_'+ key +'_new_count" type="text" id="inp_'+ key +'_new_count_'+ new_counter_numnber +'" name="inp_'+ key +'_new_count[]" maxlength="10" value="" onkeyup="checkForDouble(this);" onchange="recount_counter_summ(\''+ key +'\', '+ tarif +', \''+ new_counter_numnber +'\');">' +
        '</div>' +
        '<div style="margin-bottom:5px;">' +
            '<label for="cur_inp_'+ key +'_new_count_'+ new_counter_numnber +'" style="width:100px; display:inline-block;">поточні на дату сплати:</label>' +
            '<input style="width: 60px;" type="text" id="cur_inp_'+ key +'_new_count_'+ new_counter_numnber +'" name="inp_'+ key +'_cur_count[]" maxlength="10" value="" onkeyup="checkForDouble(this);">' +
        '</div>' +
        '<div style="margin-bottom:5px;">' +
            '<label for="num_inp_'+ key +'_new_count_'+ new_counter_numnber +'" style="width:100px; display:inline-block;">Заводський номер:</label>' +
            '<input style="width: 60px;" type="text" id="num_inp_'+ key +'_new_count_'+ new_counter_numnber +'" name="inp_'+ key +'_abcounter[]" value="'+ abcounter +'">' +
        '</div>' +

        '<input type="hidden" name="inp_'+ key +'_count_number[]" value="'+ new_counter_numnber +'">' +
        'До сплати: ( <div style="display:inline-block;" id="newval_counter_'+ key +'_'+ new_counter_numnber +'">поточне&nbsp;значення</div>&nbsp;-&nbsp;<span id="oldval_counter_'+ key +'_'+ new_counter_numnber +'">0</span>)&nbsp;*&nbsp;'+ tarif +'&nbsp;грн' +
    '</div>';

    $(html).insertBefore('#new_counters_for_' + key);
};

function add_card()
{
    var data = {
        action: 'addcard',
        birthday: $('#addcard_birthday').val(),
        pasp_number: $('#addcard_pasp-number').val(),
        card_number: $('#addcard_card-number').val()
    };

    $.ajax({
        dataType: 'json',
        data: data,
        type: 'POST',
        url : BASE_URL + '/ajax/json/add_card',
        success : function(response) {
            if (response.status) {
                $('.card-error-no-cards').remove();
                $('#addcard_error').html('').fadeOut(0);

                var new_html = 
                    '<div class="paybill-ps-card-item">' +
                        '<span id="khreshchatyk-card-' + response.card_id + '" class="niceCheck radio"><input value="' + response.card_id + '" type="radio" name="khreshchatyk-card" /></span>' +
                        '<label onclick="$(\'#khreshchatyk-card-' + response.card_id + '\').click();">' +
                            '<span class="text-label">' + response.card_number + '</span>' +
                        '</label>' +
                    '</div>';

                $('.paybill-ps-cards').prepend(new_html);
                $('.add-card-form').slideToggle();
                $(".niceCheck").click(function() {
                    changeCheck($(this), 'check-group');
                });
                
                $('#khreshchatyk-card-' + response.card_id).click();
            } else {
                $('#addcard_error').html(response.text).fadeIn(0);
            }
        }
    });
};

function increment_counter(key)
{
    var data = {
        action: 'increment',
        key: key,
        order_id: current_order_id
    };

    $.ajax({
        dataType: 'json',
        data: data,
        type: 'POST',
        url : BASE_URL + '/ajax/json/counter'
    });
};

function remove_user_card(card_id)
{
    var data = {
        action: 'remove_card',
        card_id: card_id
    };

    $.ajax({
        dataType: 'json',
        data: data,
        type: 'POST',
        url : BASE_URL + '/ajax/json/add_card',
        success : function(response) {
            close_feedback_msg();
            $('#user_card_'+card_id).remove();
        }
    });
};

function remove_user_card_popup(card_id)
{
    $('body').addClass('popup-open').append('<div id="global-owerlay"><div id="popup-box" class="popup-box"><div class="popup-content"></div></div></div>');
    var popup_html =
        '<h3 class="page-subtitle border-top">Дійсно хочете видалити картку?</h3>' + 
        '<div style="height: 130px; text-align: left;">Цю дію неможливо відмінити</div>' +
        '<div class="field-group buttons-here">' +
            '<button class="btn bold cancel-btn" onclick="close_feedback_msg(); return false;">Скасувати</button>' +
            '<button class="btn green bold success-btn" onclick="remove_user_card(\'' + card_id + '\');">Видалити</button>' +
        '</div>';
    
    $('#popup-box .popup-content').html(popup_html);
};

function tas_frame_load()
{
    $('#tas_frame_error').css('display', 'none');
    $('#tas_frame').css('display', '');
    clearTimeout(tas_timeout_id);
    
    if (tas_frame_not_load) {
        increment_counter('TAS_FRAME_LOAD_SLOW');
    } else {
        increment_counter('TAS_FRAME_LOAD');
    }
};

$(document).ready(function(){
    (function(){
        $('.spoiler-title').click(function(){
            var par = $(this).parent();
            if ($(par).hasClass('open')) {
                $(par).removeClass('open').find('.spoiler-text').first().slideUp(400);
            } else {
                $(par).addClass('open').find('.spoiler-text').first().slideDown(400);
            }
        });
        $('.spoiler-close').click(function(){
            $(this).parent().parent().find('.spoiler-title').click();
        });

        $('#reg-password, #reg-password-replica').keyup(function(){
            function clearGauge() { 
                gauge.removeClass('weak');
                gauge.removeClass('medium');
                gauge.removeClass('strong');
                gauge.removeClass('secure');
            }

            var gauge = $('#password-strength-container .gauge');
            var title = $('#password-strength-container .title');
            // var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\W).*$", "g");
            // var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
            var enoughRegex = new RegExp("(?=.{6,}).*", "g");
            var pwd = $(this).val();

            var score = zxcvbn(pwd).score;

            $('#password-strength-container').width($(this).outerWidth());

            if (pwd.length==0) {
                gauge.hide();
                title.html('');
            } else if (false == enoughRegex.test(pwd)) {
                gauge.hide();
                title.html('Введіть не менше 6 символів');
            } else if (score == 4) {
                clearGauge();
                gauge.addClass('secure');
                gauge.show();
                title.html('Відмінний пароль');
            } else if (score == 3) {
                clearGauge();
                gauge.addClass('strong');
                gauge.show();
                title.html('Гарний пароль');
            } else if (score == 2) {
                clearGauge();
                gauge.addClass('medium');
                gauge.show();
                title.html('Пароль середньої безпечності');
            } else {
                clearGauge();
                gauge.addClass('weak');
                gauge.show();
                title.html('Поганий пароль');
            }

            return true;
        });

    })();

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
