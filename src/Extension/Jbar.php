<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.sef
 *
 * @copyright   (C) 2023 ThemeXpert Ltd. <https://www.themexpert.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Jbar\Extension;

use JHtml;
use Joomla\CMS\Event\Application\AfterInitialiseDocumentEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Version;
use Joomla\CMS\Uri\Uri;


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

/**
 * Joomla! SEF Plugin.
 *
 * @since  1.5
 */
final class Jbar extends CMSPlugin implements SubscriberInterface
{


    public static function getSubscribedEvents(): array
    {

        return [
            'onAfterInitialiseDocument' => ['onAfterInitialiseDocument', Priority::HIGH],
            'onAfterRender' => ['onAfterRender'],
        ];
    }

    public function onAfterInitialiseDocument(AfterInitialiseDocumentEvent $event)
    {
        $app = $this->getApplication();
        if (!$app->isClient('site')) return;
        $page = $this->params->get('load_page', 'all');

        $wa = $event->getDocument()
            ->getWebAssetManager();
        $style = $this->loadStyle();


        if ($page == 'homepage') {
            $current_menu_id = $this->app->input->get('Itemid');
            if ($current_menu_id == NULL) {
                // Load jQuery
                $this->loadjQuery();

                // Load style
                $wa->addInlineStyle($style);

                // Load Script
                $this->loadScript();
            }
            return;
        }

        if (!$this->isLoadable()) return;

        // Load jQuery
        $this->loadjQuery();

        // Load style
        $wa->addInlineStyle($style);

        // Load Script
        $this->loadScript();

    }
    
    public function onAfterRender()
    {
        $app = $this->getApplication();
        if (!$app->isClient('site')) {
            return;
        }
        if (!$this->isLoadable()) return;


        $doc = $this->getApplication()->getDocument();
        $docType = $doc->getType();

        // // Verification
        if ($docType !== 'html') {
            return;
        }
        $this->loadStyle();
        $this->loadjQuery();
        $this->loadScript();

        $body = $this->getApplication()->getBody();

        $message = $this->params->get('message');

        // Set some NULL variable
        $link = $target = $closable = $btn = '';
        // Check if the link is set to visible or not
        if ($this->params->get('link_type', 1)) {
            $link_text = $this->params->get('link_text');
            $link_url = $this->params->get('link_url');

            if ((int)$this->params->get('target') == 2) {
                $target = 'target="_blank"';
            }

            if ((int)$this->params->get('link_type') == 2) {
                $btn = 'class="jBtn"';
            }

            $link = ' <a href="' . $link_url . '" ' . $target . $btn . '>' . $link_text . '</a>';
        }

        if (!(int)$this->params->get('closable')) {
            $closable = ' jHide';
        }

        $position = ' j' . $this->params->get('tab_position', 'right');

        $jbar = '
        <!-- jBar by ThemeXpert.com 1.5 -->
        <div id="jBar">
            <div class="jBar">
                <span class="helloinner">
                    <p class="text">' . $message . $link . '</p>
                    <span class="jTrigger downarrow arrow' . $closable . $position . '">Arrow up</span>
                </span>
            </div>
            <span class="jRibbon jTrigger arrow' . $closable . $position . '">Arrow down</span>
        </div>
        <!-- jBar by ThemeXpert.com 1.5 -->
    ';

        $pattern = "/<\/?body+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/";

        preg_match($pattern, $body, $match);

        $body = str_replace($match[0], $match[0] . $jbar, $body);

        $this->getApplication()->setBody($body);

    }

    function isLoadable()
    {
        $page = $this->params->get('load_page', 'all');

        switch ($page) {
            case 'all':
            case 'default':
                return TRUE;

            case 'homepage':

                $menu = $this->app->getMenu();

                // Though initial method dosend found the active menu we have to compare with menu id
                if ($menu->getActive() == $menu->getDefault()) {
                    return TRUE;
                } else {
                    return FALSE;
                }

            case 'selected':

                $ids = $this->params->get('load_page_id');

                if (!empty($ids)) {
                    $menu_ids = explode(',', $ids);
                    $current_menu_id = $this->app->input->get('Itemid');
                    if (in_array($current_menu_id, $menu_ids)) {
                        return TRUE;

                    } else {
                        return FALSE;
                    }

                } else {
                    return FALSE;
                }

        }
    }

    function loadStyle()
    {
        $doc = $this->getApplication()->getDocument();
        //Generate css and add to head
        $bar_color = '#' . $this->params->get('bar_color');
        $text_color = '#' . $this->params->get('text_color');
        $link_color = '#' . $this->params->get('link_color');
        $border_color = '#' . $this->params->get('border_color');
        $font = $this->params->get('font');

        $image_path = Uri::root(true) . '/plugins/system/jbar/assets/';
        $arrow_down = $image_path . 'arrow-down.png';
        $arrow_up = $image_path . 'arrow-up.png';

        $position = 'relative';
        $jribbon_position = '';
        if ($this->params->get('sticky', 0)) {
            $position = 'fixed';
            $jribbon_position = 'position: fixed';
        }

        $css = "#jBar .jBar {background: $bar_color; padding: 5px; width:100%; position:$position; z-index: 99999; }";
        $css .= "#jBar .jBar p{color: $text_color; padding: 7px 0; text-align: center; margin: 0; font-family: $font}";
        $css .= "#jBar .jBar a{color: $link_color ;}";

        $css .= "#jBar .jright{position:absolute; right: 65px;}";
        $css .= "#jBar .jleft{position:absolute; left: 65px;}";
        $css .= "#jBar .arrow{ text-indent: -9999px; cursor: pointer; width: 17px; height: 19px; }";

        $css .= "#jBar .downarrow{ background: url($arrow_up) no-repeat 50% 50%; top: 10px }";

        $css .= "#jBar .jBtn{background: $link_color; color: $text_color !important; padding: 2px 6px; border-radius: 5px; -webkit-border-radius: 5px; -moz-border-radius: 5px;}";

        $css .= "#jBar .jRibbon{ padding: 3px; top:0; right: 60px; display: none; background: url($arrow_down) no-repeat 50% 50% $bar_color; width: 35px; height: 33px; border: 3px solid $border_color; border-top: none; box-shadow: 0 2px 5px #777; cursor: pointer; border-radius: 0 0 3px 3px; $jribbon_position; z-index: 9999; }";

        $css .= "#jBar .jHide{ display: none !important; }";

        if ($this->params->get('border')) {
            $css .= "#jBar .jBar{border-bottom: 3px solid $border_color ;}";
        }

        if ($this->params->get('mobile_disabled', 1)) {
            $css .= "@media (max-width: 480px) {#jBar {display:none;} }";
        }

        return ($css);
    }

    function loadScript()
    {
        $doc = $this->getApplication()->getDocument();
        $show_wait = $this->params->get('show_wait');
        $hide_after = $this->params->get('hide_after');
        $js_in = '';

        if ($show_wait != -1) {
            $js_in .= "jQuery('.jRibbon').delay(500).fadeIn(400).slideUp(600).addClass('up');";
        }
        if ($hide_after != -1) {
            $js_in .= "jQuery('.jBar').delay($hide_after).slideUp(function() {
                         jQuery('.jRibbon').toggleClass('up').slideDown();
                     });";
        }

        $js = "jQuery(document).ready(function() {
                 jQuery('.jBar').hide().delay($show_wait).slideDown(300);
                 $js_in

                 jQuery('.jTrigger').click(function(){
                     if( jQuery('.jRibbon').hasClass('up') ){
                         jQuery('.jRibbon').toggleClass('up').slideDown();
                     }else{
                         jQuery('.jRibbon').toggleClass('up').slideUp();
                     }

                     jQuery('.jBar').slideToggle();
                 });
             });";

        $doc->addScriptDeclaration($js);
    }

    function loadjQuery()
    {
        $doc = $this->getApplication()->getDocument();

        $version = new Version();

        if ($this->params->get('jquery')) {
            // if its running on J2.5.x then load jquery from module core othwerwise framework library
            if ($version->isCompatible('2.5') && $version->isCompatible('3.0')) {

                $jquery = Uri::root(true) . '/plugins/system/jbar/assets/jquery-1.8.2.min.js';
                $jquery_noconflict = Uri::root(true) . '/plugins/system/jbar/assets/jquery-noconflict.js';
                $doc->addScript($jquery);
                $doc->addScript($jquery_noconflict);


            } elseif ($version->isCompatible('4.0')) {
                $jquery = Uri::root(true) . '/plugins/system/jbar/assets/jquery-3.6.3.min.js';
                $jquery_noconflict = Uri::root(true) . '/plugins/system/jbar/assets/jquery-noconflict.js';
                $doc->addScript($jquery);
                $doc->addScript($jquery_noconflict);
            } else {
                JHtml::_('jquery.framework');
            }
        }
    }
}
