<?php
function smarty_modifier_brave_check_date($date)
{
	$date = str_replace("-","/",$date);
	if (strlen($date)) {
		$date = substr($date,0,10);
	}
	
	if(preg_match("/0000-00-00/",$date)) {
		return '';
	}
	
	if(!preg_match("#[\d]{4}/[\d]{2}/[\d]{2}#",$date)) {
		return '';
	}

	return $date;
}
?>