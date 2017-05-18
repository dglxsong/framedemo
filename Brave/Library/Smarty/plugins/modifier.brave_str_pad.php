<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty capitalize modifier plugin
 * 
 * Type:     modifier<br>
 * Name:     capitalize<br>
 * Purpose:  capitalize words in the string
 * 
 * @link 
 * @author Monte Ohrt <monte at ohrt dot com> 
 * @param string $ 
 * @return string 
 */
function smarty_modifier_brave_str_pad($string, $len = 2, $pad = '0', $pad_type = 'left')
{ 
	$type = STR_PAD_LEFT;
    switch($pad_type) {
		case 'left':
			$type = STR_PAD_LEFT;
			break;
		case 'right':
			$type = STR_PAD_RIGHT;
			break;
		case 'both':
			$type = STR_PAD_BOTH;
			break;
		default:
			break;
	}

	
    return str_pad($string, $len, $pad, $type);
} 

?>