<?php

function smarty_modifier_integer($v) {
    return $v ? (int) $v : 0;
}

?>
