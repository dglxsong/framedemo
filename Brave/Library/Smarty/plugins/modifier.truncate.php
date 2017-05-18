<?php

/**
 * Smarty plugin
 *
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty truncate modifier plugin
 * 
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *               optionally splitting in the middle of a word, and
 *               appending the $etc string or inserting $etc into the middle.
 * 
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php truncate (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com> 
 * @param string $string input string
 * @param integer $length lenght of truncated text
 * @param string $etc end string
 * @param boolean $break_words truncate at word boundary
 * @param boolean $middle truncate in the middle of text
 * @return string truncated string
 */
function smarty_modifier_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
	if ($length == 0)
		return '';



	// $string has utf-8 encoding
	if (iconv_strlen($string,"utf-8") > $length) {
		$length -= min($length, iconv_strlen($etc,"utf-8"));
		if (!$break_words && !$middle) {
//                    $string = preg_replace('/\s+?(\S+)?$/u', '', mb_substr($string, 0, $length + 1));
		}
		if (!$middle) {
			return iconv_substr($string, 0, $length,"utf-8") . $etc;
		} else {
			return iconv_substr($string, 0, $length / 2,"utf-8") . $etc . iconv_substr($string, - $length / 2,"utf-8");
		}
	} else {
		return $string;
	}
}

?>