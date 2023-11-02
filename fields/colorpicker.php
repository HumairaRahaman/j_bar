<?php
/**
 * @package     Expose
 * @version     4.0
 * @author      ThemeXpert http://www.themexpert.com
 * @copyright   Copyright (C) 2010 - 2011 ThemeXpert
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @filesource
 * @file        colorpicker.php
 **/

// Ensure this file is being included by a parent file
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldColorPicker extends JFormField{

    protected  $type = 'ColorPicker';

    protected function getInput(){
        $doc = JFactory::getDocument();
        $output = NULL;
        $input = '';
        
        if(!defined('EXPOSE_COLOR_PICKER')){
            $version = new JVersion();

            // Load jQuery only on Joomla 2.5.x
            if ( $version->isCompatible('2.5') && $version->isCompatible( '3.0' ) )
            {
                $jquery_path = JURI::root(true) . '/plugins/system/jbar/assets/jquery-1.8.2.min.js';
            } else if ($version->isCompatible('4.0')) {
                $jquery_path = JURI::root(true) . '/plugins/system/jbar/assets/jquery-3.6.3.min.js';
            }

            $colorpicker_path = JURI::root(true) . '/plugins/system/jbar/assets/colorpicker/js/colorpicker.js';
            $colorpicker_css_path = JURI::root(true) . '/plugins/system/jbar/assets/colorpicker/css/colorpicker.css';

            $doc->addScript($jquery_path);
            $doc->addScript($colorpicker_path);
            $doc->addStyleSheet($colorpicker_css_path);

            define('EXPOSE_COLOR_PICKER', 1);

        }

        $class		= (string) $this->element['class'];
        $pretext        = ($this->element['pretext'] != NULL) ? '<span class="pre-text hasTip" title="::'. JText::_(($this->element['pre-desc']) ? $this->element['pre-desc'] : $this->description) .'">'. JText::_($this->element['pretext']). '</span>' : '';

        $wrapstart  = '<div class="field-wrap clearfix '.$class.'">';
        $wrapend    = '</div>';

        $js = "
        jQuery(document).ready(function($){

            jQuery.noConflict();
            jQuery('#$this->id').ColorPicker({
                color: '{$this->value}',
                onShow: function (colpkr) {
                    jQuery(colpkr).fadeIn(500);
                    return false;
                },
                onHide: function (colpkr) {
                    jQuery(colpkr).fadeOut(500);
                    return false;
                },
                onChange: function (hsb, hex, rgb) {
                    jQuery('#$this->id div').css('backgroundColor', '#' + hex);
                    jQuery('#$this->id-field').val(hex);
                },
                onSubmit: function(hsb, hex, rgb, el) {
                    jQuery(el).val(hex);
                    jQuery(el).ColorPickerHide();
                }

            });
         });";

        $doc->addScriptDeclaration($js);

        $input .= '<input class="picker" type="hidden" name="'.$this->name.'" id="'.$this->id.'-field"' .
                   ' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"' . '/>';
        $input .= '<div id="'.$this->id.'" class="color-selector"><div style="background-color:#'.$this->value.'"></div></div>';

        return $wrapstart . $pretext . $input . $wrapend;
    }
}

