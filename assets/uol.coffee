(($, STUDIP) ->
    STUDIP ?= {}

    # Fix autocomplete in header
    $('#quicksearch .quicksearchbox').live 'autocompleteopen autocompleteclose', (event) ->
        $('body > ul.ui-autocomplete').toggleClass('header-search', event.type is 'autocompleteopen')

    # Sticky menu
    THRESHOLD = 39

    $(document).scroll ->
        $('body').toggleClass('scrolled', $(this).scrollTop() > THRESHOLD)
    $ -> $(document).scroll()


    # Shrink drop down
    shrinker = (reset = false) ->
        main = $('#main-navigation')
        return if !main.length or main.is ':hidden'

        nav  = main.children('ul')
        sink = nav.find('li.container')

        if reset
            sink.hide()
            sink.find('li').remove().insertBefore(sink)

        max  = nav.children().first().height()
        return if nav.height() < max * 1.5

        sink.show()
        while nav.height() >= 2 * max
            element = sink.prev().remove()
            sink.find('ul').prepend(element)

        options =
            length: nav.children(':not(.container)').length
            width : $(window).width()
        cookie  = JSON.stringify options

        $.cookie('navigation', cookie, {expires: 30, path: '/'});

    cookie = $.cookie('navigation') ? '{"width":0}'
    cookie = JSON.parse(cookie)

    # Throttle shrinker to execute at most three times per second
    STUDIP.NavigationShrinker = _.throttle(shrinker, 333);

    # Attach throttled shrinker to window's resize event
    $(window).resize -> STUDIP.NavigationShrinker(true)


    # Enable touch on dropdown
    $('#barTopMenu > li:has(ul)').live 'touchstart touchend', (event) ->
        $(this).find('ul').toggle event.type is 'touchstart'

    $ ->
        # Remove sidebar image
        if $('html').width() < 800
            $('table.infobox img:first, td.infobox-img').remove();

            # hide infobox but add button to unhide
            unhidecontainer = """
                <div class="unhidesidebar">
                    <img src="#{STUDIP.ABSOLUTE_URI_STUDIP}plugins_packages/UOL/UOLLayoutPlugin/assets/images/unhidebar.png" width="35">
                </div>
                """

            $('table.infobox').hide().after(unhidecontainer).parent().attr('width','*')

            $('div.unhidesidebar').click ->
                $(this).hide().parent().find('table.infobox').show()


        # Uni login stays visible while focussed
        $('#unilogin').find('input[type=text], input[type=password]').bind 'focus blur', (event) ->
            $('#unilogin').toggleClass('focussed', event.type is 'focus')
        if $('#unilogin').length
            $('<input type="hidden" name="resolution"/>').val([screen.width, screen.height].join('x')).appendTo('#unilogin')

        setTimeout ->
            STUDIP.NavigationShrinker(true)
        , 500
)(jQuery, STUDIP)
