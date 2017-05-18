<?php

function smarty_function_brave_html_file_show($params, &$smarty) {
	$html = '';
	$path = $params['path'];
	$type = $params['type'];
	$value = $params['value'];
	if (!$value) {
		return '';
	}
	$options = array();
	if (isset($params['width'])) {
		$options[] = 'width="' . $params['width'] . '"';
	}

	if (isset($params['height'])) {
		$options[] = 'height="' . $params['height'] . '"';
	}

	$show_value = $params['show_value'] ? $params['show_value'] : $value;

	$html = '<a href="' . $path . $value . '" target="_blank">';
	if ($type == 'image') {
		$html .= '<img '.join(' ', $options).' src="' . $path . $value . '" class="upload" />';
	} else {
		$html .= $show_value;
	}
	$html .= '</a>';

	return $html;
}

?>
