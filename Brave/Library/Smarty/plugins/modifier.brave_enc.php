<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty substr modifier plugin
 *
 * Type:     modifier<br>
 * Name:     substr<br>
 * Purpose:  simple search/substr
 * @link http://smarty.php.net/manual/en/language.modifier.substr.php
 *          substr (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_brave_enc($string,$sep = ' ')
{
    $len = strlen($string);
	return str_repeat("*" . $sep,$len);
}

/* vim: set expandtab: */

?>
