/*!
 * jQuery Cookie Plugin
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */
(function($) {
    $.cookie = function(key, value, options) {

        // key and at least value given, set cookie...
        if (arguments.length > 1 && (!/Object/.test(Object.prototype.toString.call(value)) || value === null || value === undefined)) {
            options = $.extend({}, options);

            if (value === null || value === undefined) {
                options.expires = -1;
            }

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = String(value);

            return (document.cookie = [
                encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // key and possibly options given, get cookie...
        options = value || {};
        var decode = options.raw ? function(s) { return s; } : decodeURIComponent;

        var pairs = document.cookie.split('; ');
        for (var i = 0, pair; pair = pairs[i] && pairs[i].split('='); i++) {
            if (decode(pair[0]) === key) return decode(pair[1] || ''); // IE saves cookies with empty string as "c; ", e.g. without "=" as opposed to EOMB, thus pair[1] may be undefined
        }
        return null;
    };
})(jQuery);
(function($, STUDIP) {
  var THRESHOLD, cookie, shrinker, _ref;
  if (STUDIP == null) STUDIP = {};
  $('#quicksearch .quicksearchbox').live('autocompleteopen autocompleteclose', function(event) {
    return $('body > ul.ui-autocomplete').toggleClass('header-search', event.type === 'autocompleteopen');
  });
  THRESHOLD = 39;
  $(document).scroll(function() {
    return $('body').toggleClass('scrolled', $(this).scrollTop() > THRESHOLD);
  });
  $(function() {
    return $(document).scroll();
  });
  shrinker = function(reset) {
    var cookie, element, main, max, nav, options, sink;
    if (reset == null) reset = false;
    main = $('#main-navigation');
    if (!main.length || main.is(':hidden')) return;
    nav = main.children('ul');
    sink = nav.find('li.container');
    if (reset) {
      sink.hide();
      sink.find('li').remove().insertBefore(sink);
    }
    max = nav.children().first().height();
    if (nav.height() < max * 1.5) return;
    sink.show();
    while (nav.height() >= 2 * max) {
      element = sink.prev().remove();
      sink.find('ul').prepend(element);
    }
    options = {
      length: nav.children(':not(.container)').length,
      width: $(window).width()
    };
    cookie = JSON.stringify(options);
    return $.cookie('navigation', cookie, {
      expires: 30,
      path: '/'
    });
  };
  cookie = (_ref = $.cookie('navigation')) != null ? _ref : '{"width":0}';
  cookie = JSON.parse(cookie);
  STUDIP.NavigationShrinker = _.throttle(shrinker, 333);
  $(window).resize(function() {
    return STUDIP.NavigationShrinker(true);
  });
  $('#barTopMenu > li:has(ul)').live('touchstart touchend', function(event) {
    return $(this).find('ul').toggle(event.type === 'touchstart');
  });
  return $(function() {
    var unhidecontainer;
    if ($('html').width() < 800) {
      $('table.infobox img:first, td.infobox-img').remove();
      unhidecontainer = "<div class=\"unhidesidebar\">\n    <img src=\"" + STUDIP.ABSOLUTE_URI_STUDIP + "plugins_packages/UOL/UOLLayoutPlugin/assets/images/unhidebar.png\" width=\"35\">\n</div>";
      $('table.infobox').hide().after(unhidecontainer).parent().attr('width', '*');
      $('div.unhidesidebar').click(function() {
        $('#layout_infobox').width(250).css('z-index', 100);
        return $(this).hide().parent().find('table.infobox').show();
      });
      $('table.infobox').click(function () {
          $(this).hide();
          $('div.unhidesidebar').show();
          $('#layout_infobox').width(0).css('z-index', 0);
      })
      $('#layout_infobox').css({
          position: 'absolute',
          right: 8,
          top: jQuery('#layout_page').position().top + 10,
          width: 0
      });
      $('#layout_content').css({
          marginRight: '35px'
      });
      $('td#main_content').next('td').remove();
    }
    $('#unilogin').find('input[type=text], input[type=password]').bind('focus blur', function(event) {
      return $('#unilogin').toggleClass('focussed', event.type === 'focus');
    });
    if ($('#unilogin').length) {
      $('<input type="hidden" name="resolution"/>').val([screen.width, screen.height].join('x')).appendTo('#unilogin');
    }
    return setTimeout(function () { STUDIP.NavigationShrinker(true); }, 500);
  });
})(jQuery, STUDIP);
