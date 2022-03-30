// функции шапки сайта
function initCustomHeader() {
  console.log('init');

  window.test = function() {
    console.log($('.customHeader').html());
  }
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
