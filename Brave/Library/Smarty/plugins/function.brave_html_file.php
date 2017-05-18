<?php

function smarty_function_brave_html_file($params, &$smarty)
{
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
    }
    else {
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

    if (strlen($value)) {
        $save = $value;
        $file = "{$path}{$value}";
        $editDisplay = $display;
        $editDisable = $disable;
    }
    else {
        $viewDisplay = $display;
        $viewDisable = $disable;
    }
    
    $html.= '<div id="' . $id . '_view" class="bave_file_view" ' . $viewDisplay . '>';

    if (strlen($showValue)) {
        $html.= '<a href="' . $file . '" target="_blank">' . $showValue . '</a>';
        $html.= '<input type="hidden" id="' . $id . '_save_name" name="' . $showName . '" value="' . $showValue . '" ' . $viewDisable . ' />';
    }
    else {
        $html.= '<a href="' . $file . '" target="_blank">' . $save . '</a>';
    }

    $html.= ' [ <a href="javascript:void(0)" onclick="disableFile(\'' . $id . '\')">変更</a> ]';
    $html.= '<input type="hidden" id="' . $id . '_save" name="' . $name . '" value="' . $save . '" ' . $viewDisable . ' />';
    $html.= '</div>';
    
    $html.= '<div id="' . $id . '_edit" class="bave_file_edit" ' . $editDisplay . '>';
    $html.= '<input type="file" id="' . $id . '_upload" name="' . $name . '" ' . $editDisable . ' />';
    
    if (strlen($showName)) {
        $html.= ' ファイル名：<input type="text" id="' . $id . '_upload_name"  name="' . $showName . '" ' . $editDisable . ' />';
    }

    $html.= '</div>';
    
    $html.= '<script type="text/javascript">';
    $html.= 'function disableFile(id) {';
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
