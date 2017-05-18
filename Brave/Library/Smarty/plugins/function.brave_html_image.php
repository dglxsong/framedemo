<?php

function smarty_function_brave_html_image($params, &$smarty) {
	$value = '';
	$upload = '';
	$path = '';
	$html = '';
	$display = 'style="display:none"';
	$disable = 'disabled="disabled"';
	$showName = '';
	$showValue = '';

	if (!isset($params['name'])) {
		return $html;
	} else {
		$name = $params['name'];
		$id = preg_replace('/[\[\]]+/', '_', $name);
	}

	if (isset($params['path'])) {
		$path = $params['path'];
	}

	if (isset($params['value']) && !is_array($params['value'])) {
		$value = $params['value'];
	}

	if (isset($params['show_name'])) {
		$showName = $params['show_name'];
	}

	if (isset($params['show_value'])) {
		$showValue = $params['show_value'];
	}

	$options = array();
	if (isset($params['width'])) {
		$options[] = 'width="' . $params['width'] . '"';
	}

	if (isset($params['height'])) {
		$options[] = 'height="' . $params['height'] . '"';
	}
	
	if (strlen($value)) {
		$save = $value;
		$image = "{$path}{$value}";
		$editDisplay = $display;
		$editDisable = $disable;
	} else {
		$viewDisplay = $display;
		$viewDisable = $disable;
	}

	$html.= '<div id="' . $id . '_view" class="bave_imge_view" ' . $viewDisplay . '>';
	$html.= '<a href="' . $image . '" target="_blank"><img ' . join(' ', $options) . ' src="' . $image . '" /></a> ';
	$html.= '<input type="hidden" id="' . $id . '_save" name="' . $name . '" value="' . $save . '" ' . $viewDisable . ' /><span>';

	if (strlen($showName)) {
		$html.= '[ ' . $showValue . ' ]';
		$html.= '<input type="hidden" id="' . $id . '_save_name" name="' . $showName . '" value="' . $showValue . '" ' . $viewDisable . ' />';
	}

	$html.= '<a href="javascript:void(0)" onclick="disableImage(\'' . $id . '\')">[ ←画像変更 ]</a></span>';
	$html.= '</div>';

	$html.= '<div id="' . $id . '_edit" class="bave_imge_edit" ' . $editDisplay . '>';
	$html.= '<input type="file" size="30" id="' . $id . '_upload" name="' . $name . '" ' . $editDisable . ' />';

	if (strlen($showName)) {
		$html.= ' 画像名：<input type="text" id="' . $id . '_upload_name" name="' . $showName . '"' . $editDisable . ' />';
	}

	$html.= '</div>';

	$html.= '<script type="text/javascript">';
	$html.= 'function disableImage(id) {';
	$html.= '$("#" + id + "_view").hide();';
	$html.= '$("#" + id + "_edit").show();';
	$html.= '$("#" + id + "_save").attr("disabled", true);';
	$html.= '$("#" + id + "_save_name").attr("disabled", true);';
	$html.= '$("#" + id + "_upload").attr("disabled", false);';
	$html.= '$("#" + id + "_upload_name").attr("disabled", false);';
	$html.= '}';
	$html.= '</script>';

	return $html;
}

?>
