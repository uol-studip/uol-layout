<?php
/**
 * UOLLayoutPlugin.class.php
 *
 * ...
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 0.1a
 */

class UOLLayoutPlugin extends StudIPPlugin implements SystemPlugin
{
    const CSS            = '/assets/uol-layout.v11.css';
    const JS             = '/assets/uol-layout.v7.js';
    const ICON_ADMIN     = '/assets/images/admin.png';
    const ICON_COMMUNITY = '/assets/images/community.png';
    const ICON_HOME      = '/assets/images/home.png';

    private static $icons = array(
        'start'       => 'home',
        'messaging'   => 'mail',
        'community'   => 'community',
        'profile'     => 'profile',
        'admin'       => 'admin',
        'calendar'    => 'schedule',
        'search'      => 'search',
        'tools'       => 'tools',
        'browse'      => 'seminar',
        'meinstudium' => 'studium',
    );
    private $develop;

    public function __construct()
    {
        parent::__construct();

        $this->develop = defined('Studip\\ENV') && Studip\ENV === 'development';

        $this->addCSS(Request::int('debug', $this->develop));
        $this->addJS(Request::int('debug', $this->develop));

        $subnav = Navigation::getItem('/')->getSubNavigation();
        // Replace icon on first navigation item
        foreach ($subnav as $key => $nav) {
            if (!isset(self::$icons[$key]) or !$nav->isVisible()) {
                continue;
            }
            $image = $nav->getImage();
            if ($image === null) {
                continue;
            }

            $url = sprintf('%s/assets/images/%s.png',
                           $this->getPluginURL(), self::$icons[$key]);
            $nav->setImage($url, $image);

            $subnav[$key] = $nav;
        }

        // Favicon
        PageLayout::removeHeadElement('link', array('rel' => 'shortcut icon', 'href' => Assets::image_path('favicon.ico')));
        PageLayout::addHeadElement('link', array(
            'rel'  => 'shortcut icon',
            'href' => $this->getPluginURL() . '/assets/images/favicon.ico',
        ));

        // Mobile
        PageLayout::addHeadElement('meta', array(
            'name'    => 'viewport',
            'content' => 'width=device-width,initial-scale=1.0,maximum-scale=2.0,user-scalable=yes'
        ));

        // Initialize Studienmodule
        if ($auth->auth['uid']
            && !in_array($auth->auth['uid'], array('form','nobody'))
            && $studienmodulmanagement = PluginEngine::getPlugin('StudienmodulManagement')
            && method_exists($studienmodulmanagement, 'initializeHtmlHead'))
        {
            $studienmodulmanagement->initializeHtmlHead();
        }
    }

    private function assets_replace($match) {
        static $url = null;
        if (empty($url)) {
            $url = $this->getPluginURL();

            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $url)) {
                $url = 'https://elearning.uni-oldenburg.de/';
                Assets::set_assets_url($url . 'assets/');
            }
        }

        return file_exists(dirname(__FILE__) . '/' . $match[0])
             ? $url . '/' . $match[0]
             : Assets::image_path("$match[1].$match[2]");
    }

    private function addCSS($generate = false)
    {
        // Generate css-file
        $css_file = $this->getPluginPath() . self::CSS;
        if ($generate or !file_exists($css_file)) {
            $css = '';
            foreach (words('stylesheet studip uol-font header menu login search tabs footer uol portal mobile') as $file) {
                $content = file_get_contents($this->getPluginPath() . '/assets/' . $file . '.css');
                $css .= trim($content) . "\n";
            }
            $css = preg_replace_callback('/assets\/images\/([^)]+)\.(gif|jpe?g|png)/x', array($this, 'assets_replace'), $css);
            $css = preg_replace('/\/\*.*?\*\//sxm', '', $css); // Strip comments
            $css = preg_replace('/(^\s+|\s+$)/sxm', '', $css); // Strip leading and trailing whitespace
            $css = preg_replace('/\s+\{/sxm', '{', $css);      // Strip leading whitespace in front of {
            $css = str_replace("\n", '', $css);                // Remove all line breaks
            $css = str_replace('}', "}\n", $css);              // Reinsert line break after css rule
            $css = trim($css);

            file_put_contents($css_file, $css);
        }
        PageLayout::addStylesheet($this->getPluginURL() . self::CSS);

        // Print
        PageLayout::addStylesheet($this->getPluginURL() . '/assets/print.css', array('media' => 'print'));

        // Develop?
        if ($this->develop) {
            PageLayout::addStylesheet($this->getPluginURL() . '/assets/develop.css');
        }
    }

    private function addJS($generate = false)
    {
        // IE specific
        $script  = sprintf('<script src="%s" type="text/javascript"></script>',
                           $this->getPluginURL() . '/assets/html5shim.js');
        $script .= sprintf('<script src="%s" type="text/javascript"></script>',
                           $this->getPluginURL() . '/assets/json2.js');
        PageLayout::addComment($script, 'lt IE 9');

        //
        $js_file = $this->getPluginPath() . self::JS;
        if ($generate or !file_exists($js_file)) {
            $js = '';
            foreach (words('jquery.cookie uol') as $file) {
                $content = file_get_contents($this->getPluginPath() . '/assets/' . $file . '.js');
                $js .= trim($content) . "\n";
            }

            file_put_contents($js_file, $js);
        }
        PageLayout::addScript($this->getPluginURL() . self::JS);

/*
        PageLayout::addHeadElement('script', array(
            'src'  => $this->getPluginURL() . '/assets/uol.coffee',
            'type' => 'text/coffeescript',
        ));
*/
    }
}
