<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
* Smarty function plugin
* Requires PHP >= 4.3.0
* -------------------------------------------------------------
* Type:     function
* Name:     fckeditor
* Version:  1.0
* Author:   gazoot (gazoot care of gmail dot com)
* Purpose:  Creates a FCKeditor, a very powerful textarea replacement.
* -------------------------------------------------------------
* @param InstanceName Editor instance name (form field name)
* @param BasePath optional Path to the FCKeditor directory. Need only be set once on page. Default: /FCKeditor/
* @param Value optional data that control will start with, default is taken from the javascript file
* @param Width optional width (css units)
* @param Height optional height (css units)
* @param ToolbarSet optional what toolbar to use from configuration
* @param CheckBrowser optional check the browser compatibility when rendering the editor
* @param DisplayErrors optional show error messages on errors while rendering the editor
*
* Default values for optional parameters (except BasePath) are taken from fckeditor.js.
*
* All other parameters used in the function will be put into the configuration section,
* CustomConfigurationsPath is useful for example.
* See http://wiki.fckeditor.net/Developer%27s_Guide/Configuration/Configurations_File for more configuration info.
*/
function smarty_function_fckeditor($params, &$smarty)
{
   if(!isset($params['name']) || empty($params['name']))
   {
      $smarty->trigger_error('fckeditor: required parameter "name" missing');
   }

   static $base_arguments = array();
   static $config_arguments = array();

   // Test if editor has been loaded before
   if(!count($base_arguments)) $init = TRUE;
   else $init = FALSE;
   
   // BasePath must be specified once.
   if(isset($params['BasePath']))
   {
      $base_arguments['BasePath'] = $params['BasePath'];
   }
   else if(empty($base_arguments['BasePath']))
   {
      $base_arguments['BasePath'] = 'fckeditor/';
   }

   $base_arguments['name'] = $params['name'];

   if(isset($params['value']))
      $base_arguments['value'] = $params['value'];
   else
      $base_arguments['value'] = '';

   if(isset($params['width'])) $base_arguments['width'] = $params['width'];
   if(isset($params['height'])) $base_arguments['height'] = $params['height'];
   if(isset($params['ToolbarSet'])) $base_arguments['ToolbarSet'] = $params['ToolbarSet'];
   if(isset($params['CheckBrowser'])) $base_arguments['CheckBrowser'] = $params['CheckBrowser'];
   if(isset($params['DisplayErrors'])) $base_arguments['DisplayErrors'] = $params['DisplayErrors'];

   // Use all other parameters for the config array (replace if needed)
   $other_arguments = array_diff_assoc($params, $base_arguments);
   $config_arguments = array_merge($config_arguments, $other_arguments);

   $out = '';

   if($init)
   {
      $out .= '<script type="text/javascript" src="' . $base_arguments['BasePath'] . 'fckeditor.js"></script>';
   }

   // created textarea
   $out.= '<textarea class="textarea" name="' . $base_arguments['name'] . '">' . htmlspecialchars($base_arguments['value']) . '</textarea>';

   // bind fckeditor
   $out .= "\n<script type=\"text/javascript\">\n";
   $out .= "var oFCKeditor = new FCKeditor('" . $base_arguments['name'] . "');\n";

   foreach($base_arguments as $key => $value)
   {
      if(!is_bool($value))
      {
         // Fix newlines, javascript cannot handle multiple line strings very well.
         $value = '"' . preg_replace("/[\r\n]+/", '" + $0"', addslashes($value)) . '"';
      }

      // need not set value
      if (!preg_match('/^value$/is', $key))
      {
        $out .= "oFCKeditor.$key = $value; ";
      }
   }
   
   foreach($config_arguments as $key => $value)
   {
      if(!is_bool($value))
      {
         $value = '"' . preg_replace("/[\r\n]+/", '" + $0"', addslashes($value)) . '"';
      }
      $out .= "oFCKeditor.Config[\"$key\"] = $value; ";
   }

   // bind, do not create
   //$out .= "\noFCKeditor.Create();\n";
   $out .= "\noFCKeditor.ReplaceTextarea();\n";
   $out .= "</script>\n";

   return $out;
}

/* vim: set expandtab: */

?> 