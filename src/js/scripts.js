"use strict";

$(document).on('ready', function() { 

    initEvents();

    $(function() { $('.matchHeight').matchHeight(); }); 

    $('a').each(function() {
        var a = new RegExp('/' + window.location.host + '/');
        if (!a.test(this.href) && this.href.length && (this.href != '#')) {
            $(this).click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                window.open(this.href, '_blank');
            });
        }
    });
});

/* All keyboard and mouse events */
function initEvents() {

    $('.add-new-object').on('click', function() {

        $('#modal-object-add').modal('show');
        return false;
    }); 

    // Управление удалением объекта
    $('.remove-object').on('click', function(e) {

        e.preventDefault();
        $('#remove_object_id').val($(this).data('object-id')).parent().submit();
        return false;
    });

    $('.remove-object-check').on('click', function(e) {

        e.preventDefault();
        $(this).parent().parent().find('.info-section').fadeOut(0);
        $(this).parent().parent().find('.remove-section').css("display", "flex").fadeIn(400);
    });

    $('.remove-object-cancel').on('click', function(e) {

        e.preventDefault();
        $(this).parent().parent().find('.info-section').fadeIn(400);
        $(this).parent().parent().find('.remove-section').fadeOut(0);
    });     

    // Выбираем все галочки в таблице
    $('#data-table').on('click change', 'input.check-all', function() {

        if (!$(this).data('checked')) {

            $(this).closest('table').find('.check-toggle').prop('checked', 'checked');
            $(this).data('checked', true);
        }
            else {

            $(this).closest('table').find('.check-toggle').prop('checked', '');
            $(this).data('checked', false);
        }
        return false;
    });

    // При нажатии на удалениче счетчика - передаем в модальное окно кнопке его ID
    $('.counter-delete').on('click', function($e) {

        $e.preventDefault();
        var id = $(this).data('id');
        $('#counter-delete-confirm').data('id', id);
    });

    // Эмулируем выпадающий список с помощью bootstrap dropdown
    $(".dropdown-menu li a").click(function($e){

        $e.preventDefault();
        $(this).parents(".dropdown").find('button').html($(this).text() + ' <span class="caret"></span>');
        $(this).parents(".dropdown").find('button').val($(this).data('value'));
    });
}

var directionsService, directionsDisplay;