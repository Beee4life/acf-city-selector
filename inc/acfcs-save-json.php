<?php
    /*
     * This file handles the downloading of the JSON info
     */
    header('Content-disposition: attachment; filename=debug.json');
    header('Content-type: application/json');

    $json = file_get_contents( $_POST[ 'acfcs_json_file' ] );
    echo $json;
