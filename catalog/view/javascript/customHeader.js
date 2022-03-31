// функции шапки сайта
function initCustomHeader() {
  // обработчик события изменения активной ссылки меню
  window.eventUpdatedActiveLink = function(activeLink = null) {
    let submenuId = $(activeLink).data('submenu-id');
    if (typeof submenuId == 'undefined') return false;
    let submenuIdPrefix = submenuId.slice(0, submenuId.lastIndexOf("_")+1);
    let submenu = $('.customHeader [data-submenu='+submenuId+']').get(0);
    let submenuParent = $(submenu).parent();

    // скрываю все вложенные меню
    $(submenuParent).find('.js-submenu[data-submenu^='+submenuIdPrefix+']').addClass('h-hide');
    // показываю родительский блок, если он был скрыт
    $(submenuParent).removeClass('h-hide');
    // показываю вложенное меню для активной ссылки
    $(submenu).removeClass('h-hide');
  }

  $(window).scroll(function() {
    // если страницу прокрутили
    if ($(this).scrollTop() > 0) {
      // то скрываю 2 и 3 уровни меню
      $('.customHeader__menu_2, .customHeader__menu_3, customHeader__desktop .js-submenu').addClass('h-hide');
      // изменяю отступы у верхнего меню
      $('.customHeader__menu_1').addClass('menu1_scroll');
      // перемещаю кнопки в меню верхнего уровня
      $('.customHeader__desktop .menu__buttons').detach().appendTo('.customHeader__menu_1');
    } else {
      $('.customHeader__menu_2 .js-submenu').addClass('h-hide');

      // показываю или первое вложенное меню 2 уровня, или последнее активное, если такое есть
      let activeSubmenuSelector = ':first';
      let activeSubmenuId = $('.customHeader__menu_1 .menu1__link_active').data('submenu-id');
      if (typeof activeSubmenuId !== 'undefined') {
        let activeSubmenuSelector = '[data-submenu='+activeSubmenuId+']';
      }

      $('.customHeader__menu_2, .customHeader__menu_2 .js-submenu'+activeSubmenuSelector).removeClass('h-hide');
      $('.customHeader__menu_1').removeClass('menu1_scroll');
      $('.customHeader__desktop .menu__buttons').detach().appendTo('.customHeader__menu_2');
    }
  });

  // если навели мышку на ссылку в меню 1 уровня
  $('.customHeader__menu_1 .menu1__link').mouseover(function() {
    // то устанавливаю эту ссылку активной
    $('.customHeader__menu_1 .menu1__link').removeClass('menu1__link_active');
    $(this).addClass('menu1__link_active');
    // скрываю меню 3 уровня
    $('.customHeader__menu_3').addClass('h-hide');
    // обновляю меню 2 уровня
    eventUpdatedActiveLink(this);
  });

  // если навели мышку на ссылку в меню 2 уровня
  $('.customHeader__menu_2 .menu2__link').mouseover(function() {
    // то устанавливаю эту ссылку активной
    $('.customHeader__menu_2 .menu2__link').removeClass('menu2__link_active');
    $(this).addClass('menu2__link_active');
    // обновляю меню 3 уровня
    eventUpdatedActiveLink(this);
  });
}

// загружаю jQuery, если он ещё не загружен
if (typeof jQuery === 'undefined') {
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';
    script.type = 'text/javascript';
    document.getElementsByTagName('head')[0].appendChild(script);
}

// когда загрузился jQuery, загружаю функции для шапки сайта
document.addEventListener("DOMContentLoaded", function load() {
  if (!window.jQuery) return setTimeout(load, 50);

  initCustomHeader()
}, false);
