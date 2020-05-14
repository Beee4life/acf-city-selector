<?php
    /**
     * Return the field settings for a group (for use in js)
     *
     * @return array
     */
    function acfcs_get_field_settings() {

        $activate = false;
        $settings = [];

        if ( isset( $_GET[ 'user_id' ] ) ) {
            $activate = true;
            $user_id  = $_GET[ 'user_id' ];
        } elseif ( isset( $_GET[ 'post' ] ) ) {
            $post_id = $_GET[ 'post' ];
            if ( 'acf-field-group' != get_post_type( $post_id ) ) {
                $activate = true;
            }
        } elseif ( isset( $_GET[ 'id' ] ) ) {
            // this is for my own project
            $activate = true;
            $post_id  = $_GET[ 'id' ];
        } else {
            $activate = true;
            if ( defined( 'IS_PROFILE_PAGE' ) ) {
                $user_id = get_current_user_id();
            } else {
                $post_id = get_the_ID();
            }
        }

        if ( false != $activate ) {
            if ( isset( $user_id ) && false !== $user_id ) {
                $fields = get_field_objects( 'user_' . $user_id );
            } elseif ( isset( $post_id ) && false !== $post_id ) {
                $fields = get_field_objects( $post_id ); // all fields incl. index (in case of multiple fields)
            }
            // echo '<pre>'; var_dump($fields); echo '</pre>'; exit;

            /*
             * Get the field settings
             */
            if ( isset( $fields ) && is_array( $fields ) && count( $fields ) > 0 ) {
                foreach( $fields as $field ) {
                    if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                        // @TODO: look into multiple (single) fields
                        $settings[ 'default_country' ] = $field[ 'default_country' ];
                        $settings[ 'show_labels' ]     = $field[ 'show_labels' ] ;
                        $settings[ 'which_fields' ]    = $field[ 'which_fields' ];
                        break;
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'repeater' ) {
                        // @TODO: look into multiple repeaters
                        $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                        if ( false !== $array_key ) {
                            $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'default_country' ];
                            $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                            $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                            break;
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'group' ) {
                        $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                        // @TODO: look into multiple fields - array_search returns first instance only
                        if ( false !== $array_key ) {
                            $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'default_country' ];
                            $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                            $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                            break;
                        } else {
                            $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                            if ( false === $array_key ) {
                                $array_key = array_search( 'repeater', array_column( $field[ 'sub_fields' ], 'type' ) );
                                if ( false === $array_key ) {
                                    $array_key = array_search( 'flexible_content', array_column( $field[ 'sub_fields' ], 'type' ) );
                                    if ( false === $array_key ) {
                                        error_log('???');
                                    } else {
                                        error_log('flexible_content1');
                                    }
                                } else {
                                    if ( ! empty( $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ] ) ) {
                                        $acf_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ], 'type' ) );
                                        if ( false !== $acf_key ) {
                                            $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'default_country' ];
                                            $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'show_labels' ];
                                            $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'which_fields' ];
                                            break;
                                        } else {
                                            // @TODO: check for clone
                                        }
                                    }
                                }
                            } else {
                                $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'default_country' ];
                                $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                                $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                            }
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'flexible_content' ) {
                        $layouts = $field[ 'layouts' ];

                        foreach( $layouts as $layout ) {
                            $sub_fields = $layout[ 'sub_fields' ];
                            $acf_key = array_search( 'acf_city_selector', array_column( $sub_fields, 'type' ) );
                            if ( false !== $acf_key ) {
                                $settings[ 'default_country' ] = $sub_fields[ $acf_key ][ 'default_country' ];
                                $settings[ 'show_labels' ]     = $sub_fields[ $acf_key ][ 'show_labels' ];
                                $settings[ 'which_fields' ]    = $sub_fields[ $acf_key ][ 'which_fields' ];
                                break;
                            } else {
                                // check for repeater
                                $repeater_key = array_search( 'repeater', array_column( $sub_fields, 'type' ) );
                                if ( false !== $repeater_key ) {
                                    $acf_key = array_search( 'acf_city_selector', array_column( $sub_fields[ $repeater_key ][ 'sub_fields' ], 'type' ) );
                                    if ( false !== $acf_key ) {
                                        $settings[ 'default_country' ] = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $acf_key ][ 'default_country' ];
                                        $settings[ 'show_labels' ]     = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $acf_key ][ 'show_labels' ];
                                        $settings[ 'which_fields' ]    = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $acf_key ][ 'which_fields' ];
                                    }
                                } else {
                                    $group_key = array_search( 'group', array_column( $sub_fields, 'type' ) );
                                    if ( false !== $group_key ) {
                                    } else {
                                        // @TODO: check for other fields like clone
                                    }
                                }
                            }
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'clone' ) {
                    } else {
                    }
                }
            }
        }

        return $settings;

    }


