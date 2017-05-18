<?php

function smarty_function_ckeditor($params, &$smarty)
{
    $out = '';
    isset($params['libpath']) or $params['libpath'] = './ckeditor/';
    
   if(!isset($params['name']) || empty($params['name']))
   {
      return $out;
   }
   
   $GLOBALS['ckeditor_config'] or $out.= '<script type="text/javascript" src="' . $params['libpath'] . 'ckeditor.js"></script>';
   $GLOBALS['ckeditor_config'] = true;
   
   $out.= '<textarea class="ckeditor" id="ckeditor_' . $params['name'] . '" name="' . $params['name'] . '">' . htmlspecialchars($params['value']) . '</textarea>';
   $out.= '<script type="text/javascript">
CKEDITOR.replace("ckeditor_' . $params['name'] . '",
{
filebrowserBrowseUrl: "./ckfinder/ckfinder.html",
filebrowserUploadUrl: "./ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files",
filebrowserImageBrowseUrl: "./ckfinder/ckfinder.html?Type=Images",
filebrowserImageUploadUrl: "./ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images",
width: "' . $params['width'] . '",
height: "' . $params['height'] . '"
});
</script>';

   return $out;
}

?> 