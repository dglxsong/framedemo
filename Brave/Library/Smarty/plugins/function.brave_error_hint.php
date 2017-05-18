<?php

function smarty_function_brave_error_hint($params, &$smarty) {
    $result = '';

	$class = $params['class'];
	$errors = $params['errors'];
	$tmp = preg_split('/,/',$params['name'],-1,PREG_SPLIT_NO_EMPTY);
	$tag = $params['tag'] ? $params['tag'] : 'p';

	if (empty($errors) || !$tmp) {
		return $result;
	}

	foreach($tmp as $key) {
		if($errors[$key]) {
			$result .= '<' . $tag . ' class="error text-danger"' . '>' . $errors[$key] . '</' . $tag . '>';
			break;
		}
	}

	return $result;
}  

?>
