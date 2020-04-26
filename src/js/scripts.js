"use strict";

$(document).on('ready', function() { 

    initScrollAnimation();
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

$(window).on('scroll', function (event) {

    checkNavbar();
}).scroll();


/* All keyboard and mouse events */
function initEvents() {

    $('.add-new-object').on('click', function() {

        $('#modal-object-add').modal('show');
        return false;
    }); 

    // Управление удалением объекта
    $('.remove-object').on('click', function() {

        $('#remove_object_id').val($(this).data('object-id')).parent().submit();
        return false;
    });

    $('.remove-object-check').on('click', function() {

        $(this).parent().parent().find('.info-section').fadeOut();
        $(this).parent().parent().find('.remove-section').fadeIn();
    });

    $('.remove-object-cancel').on('click', function() {

        $(this).parent().parent().find('.info-section').fadeIn();
        $(this).parent().parent().find('.remove-section').fadeOut();
    });     

    // Показываем блок со счетчиком
    $('#data-table').on('click', 'a.counter', function() {

        if (!$(this).hasClass('counter-all')) {
            var number = $(this).closest("tr").data('number');
            // alert(number);
            $(this).toggleClass('counter-close').closest("tr").toggleClass('border-no')
            // .next('tr.item-counter').toggle();
            $('tr.item-counter-' + number).toggle();
        }

        return false;
    });


    $('#data-table').on('click', 'a.counter-all', function() {
        
        if ($('a.counter-all').hasClass('counter-close')) {
            $('tr.item-counter').hide();
            $('a.counter').removeClass('counter-close');
            $(this).addClass('counter-close');
        } else {
            $('tr.item-counter').show();
            $('a.counter').addClass('counter-close');
            $(this).removeClass('counter-close');
        }

        $('a.counter-all').toggleClass('counter-close');
        return false;
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

    // При подтверждении удаления делаем запрос
    $('#counter-delete-confirm').on('click', function() {

        var id = $(this).data('id');
        $('#counter-delete-confirm').data('id', id);

        // ajax request ...
    });


    // Эмулируем выпадающий список с помощью bootstrap dropdown
    $(".dropdown-menu li a").click(function($e){

        $e.preventDefault();
        $(this).parents(".dropdown").find('button').html($(this).text() + ' <span class="caret"></span>');
        $(this).parents(".dropdown").find('button').val($(this).data('value'));
    });
}

/* Scroll animation used for landing page */
function initScrollAnimation() {

    window.sr = ScrollReveal();

    var scrollZoomIn = {
        duration: 500,
        scale    : 0.8,
        afterReveal: function (domEl) { $(domEl).css('transition', 'all .3s ease'); }
    };

    var scrollTextFade = {
        duration: 400,
        afterReveal: function (domEl) { $(domEl).css('transition', 'all .3s ease'); }
    }

    var scrollTextLeft = {
        duration: 600,
        distance: '50%',
        origin: 'left',
        afterReveal: function (domEl) { $(domEl).css('transition', 'all .3s ease'); }
    }

    var scrollTextRight = {
        duration: 600,
        distance: '50%',
        origin: 'right',
        afterReveal: function (domEl) { $(domEl).css('transition', 'all .3s ease'); }
    }

    var scrollSliderFull = {
        duration: 500,
        scale : 1,
        easing   : 'ease-in-out',
        distance : '0px',
        afterReveal: function (domEl) { $(domEl).css('transition', 'all .3s ease'); }
    }
}

/* Navbar is set darker on main page on scroll */
function checkNavbar() {

    var scroll = $(window).scrollTop(),
        navBar = $('nav.navbar'),
        slideDiv = $('.slider-full');

    if (scroll > 1) navBar.addClass('dark'); else navBar.removeClass('dark');
}

var directionsService, directionsDisplay;