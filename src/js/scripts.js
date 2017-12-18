"use strict";

$(document).on('ready', function() { 

    initScrollAnimation();
    initParallax();
    initMap();
    initSwiper();
    initEvents();
    theadBg();

    $(function() { $('.matchHeight').matchHeight(); }); 

    /*
    $('.navbar-affix').affix({
          offset: {
            top: $('#top-bar').height(),
          }
    });
    */
   
    $("a[rel^='prettyPhoto']").prettyPhoto({
        showTitle: false,
        deeplinking: false,
        slideshow: false,
        animation_speed: 0,
        theme: 'facebook',
        show_title:false,
        overlay_gallery:false,
        social_tools: ''
    });
    $('a').each(function() {
        var a = new RegExp('/' + window.location.host + '/');
        if (!a.test(this.href) && this.href.length && (this.href != '#')) {
            if ($(this).attr('rel') !== 'prettyPhoto') {
                $(this).click(function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    window.open(this.href, '_blank');
                });
            }
        }
    });
});

$(window).on('scroll', function (event) {

    checkNavbar();
}).scroll();


$(window).on('resize', function(){

    theadBg();
});

$(window).on('load', function(){

    theadBg();
});

// Генерируем заголовки на всю ширину
function theadBg() {

    if ($('.thead-bg').length) {

        var headGreen = $('.datailbill-table .head-green'),
            headGreenLighter = $('.datailbill-table .head-green-lighter'),
            headGray =  $('.datailbill-table .head-gray'),
            headGray2 =  $('.datailbill-table .head-gray-2'),
            headGreen2 =  $('.datailbill-table .head-green-2');

        if (headGreen.length) $('.thead-bg .head-green').css('height', headGreen[0].getBoundingClientRect().height + 'px');
        if (headGreenLighter.length) $('.thead-bg .head-green-lighter').css('height', headGreenLighter[0].getBoundingClientRect().height + 'px');
        if (headGreen2.length) $('.thead-bg .head-green-2').css('height', headGreen2[0].getBoundingClientRect().height + 'px');
        if (headGray.length) $('.thead-bg .head-gray').css('height', headGray[0].getBoundingClientRect().height + 'px');
        if (headGray2.length) $('.thead-bg .head-gray-2').css('height', headGray2[0].getBoundingClientRect().height + 'px');
    }
}

/* All keyboard and mouse events */
function initEvents() {

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

    // Эмулируем выбор метода оплаты
    $('#payment-items').on('click', 'a', function($e) {

        $e.preventDefault();
        $('#payment-items a').removeClass('active');
        $(this).addClass('active');
        $('#payment-type').value($(this).val());

        return false;
    });

    // Эмулируем выпадающий список с помощью bootstrap dropdown
    $(".dropdown-menu li a").click(function($e){

        $e.preventDefault();
        $(this).parents(".dropdown").find('button').html($(this).text() + ' <span class="caret"></span>');
        $(this).parents(".dropdown").find('button').val($(this).data('value'));
    });

    $("#service-table .icon-phone").mouseenter(function() {

        $(this).closest('tr').find('.phone-more').fadeIn();
    }).mouseleave(function() {

        $(this).closest('tr').find('.phone-more').fadeOut();
    });

    $('#service-table').on('click', '.icon-map', function() {

        $(this).closest('tr').next('.item-map').show().next().hide();
        initMap($('#map-service-' + $(this).data('id')));
        // $('.matchHeight').matchHeight();
        return false;
    });

    $('#service-table').on('click', '.icon-webcam', function() {

        $(this).closest('tr').next().hide().next('.item-web').show();
        $('.matchHeight').matchHeight();
        return false;
    });

    $('#service-table').on('click', '.close', function() {

        $(this).closest('tr').hide();
        return false;
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

    /* Every element initialized once */
/*  if ($('#home-1').length) sr.reveal('#home-1 h1, #home-1 p, #home-1 .social', scrollSliderFull, 200);*/
    if ($('#home-1').length) sr.reveal('#home-1 h1, #home-1 p, #home-1 .social', scrollTextLeft, 200);
    if ($('#home-1').length) sr.reveal('#home-1 .social', scrollTextRight, 200);
    if ($('#home-debt').length) sr.reveal('#home-debt .list', scrollTextLeft, 200);
    if ($('#home-debt').length) sr.reveal('#home-debt .header', scrollTextFade, 200);
    if ($('#home-service').length) sr.reveal('#home-service .icons > div', scrollZoomIn, 100);
    if ($('#home-service').length) sr.reveal('#home-service .header', scrollTextFade, 200);

    if ($('#home-comfort').length) sr.reveal('#home-comfort .header, #home-comfort p, #home-comfort .btn', scrollTextLeft, 200);
    if ($('#home-wiki').length) sr.reveal('#home-wiki .header, #home-wiki p, #home-wiki .btn', scrollTextLeft, 200);
    if ($('#home-account .block-white').length) sr.reveal('#home-account .block-white .btn', scrollZoomIn, 20);
}

/* Swiper slider initialization */
function initSwiper() {

    var newsSwiper = new Swiper('.news-slider', {
        direction   : 'horizontal',

        speed       : 1000,
        nextButton  : '.arrow-right',
        prevButton  : '.arrow-left',
    
        autoplay    : 7000,
        autoplayDisableOnInteraction    : false,
    });

/*
    $(window).on('resize', function(){

        var ww = $(window).width()
        if ($('#testimonials-slider').length) {

            if (ww > 1000) { clientsSwiper.params.slidesPerView = 3; }
            if (ww <= 1000) { clientsSwiper.params.slidesPerView = 2; }
            if (ww <= 479) { clientsSwiper.params.slidesPerView = 1; }      
        
            clientsSwiper.update();         
        }
    }).resize();
*/
}

/* Navbar is set darker on main page on scroll */
function checkNavbar() {

    var scroll = $(window).scrollTop(),
        navBar = $('nav.navbar'),
        slideDiv = $('.slider-full');

    if (scroll > 1) navBar.addClass('dark'); else navBar.removeClass('dark');
}

/* Google maps init */
function initMap(mapEl) {

    mapEl = typeof mapEl !== 'undefined' ? mapEl : $('#map');
    if (mapEl.length) {

        var uluru = {lat: mapEl.data('lat'), lng: mapEl.data('lng')};
        var map = new google.maps.Map(document.getElementById(mapEl.attr('id')), {
          zoom: mapEl.data('zoom'),
          center: uluru,
          scrollwheel: false,
          styles: mapStyles
        });

        var marker = new google.maps.Marker({
          position: uluru,
/*        icon: base_href + 'assets/images/location-black.png',*/
          map: map
        });
    }
}

function initParallax() {

  if (/Mobi/.test(navigator.userAgent)) return false;
  $('.parallax').each(function() {
    
    $(this).parallax("50%", 0.5);    
  });
}
