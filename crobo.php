<?php
$array = [1,7,null,56, 'Crobo'];

print_r(array_remove_nulls($array));

function array_remove_nulls($array){
    return array_filter($array, function($el){return !is_null($el);});
}