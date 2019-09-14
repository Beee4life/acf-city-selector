<?php
    function acfcs_move_array_element( &$array, $from_index, $to_index ) {
        $out = array_splice( $array, $from_index, 1 );
        array_splice( $array, $to_index, 0, $out );
    }
