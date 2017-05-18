<?php

function smarty_modifier_price($v) {
    if(!$v) {
        return '0.00';
    }
    
    return number_format($v, 2, '.', '');
}

?>
