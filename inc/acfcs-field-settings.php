<?php
    /**
     * Return the field settings for a group (for use in js), only if post has saved values
     *
     * @return array
     */
    function acfcs_get_field_settings( $fields = array() ) {

        $acf_version = get_option( 'acf_version' );
        $activate    = false;
        $settings    = array();

        if ( ! empty( $fields ) ) {
            $activate = true;
        } elseif ( isset( $_GET[ 'user_id' ] ) ) {
            $activate = true;
            $user_id  = (int) $_GET[ 'user_id' ];
        } elseif ( isset( $_GET[ 'post' ] ) ) {
            $post_id = (int) $_GET[ 'post' ];
            if ( 'acf-field-group' != get_post_type( $post_id ) ) {
                $activate = true;
            }
        } elseif ( isset( $_GET[ 'id' ] ) ) {
            // this is for my own project, will be gone in a future version
            $activate = true;
            $post_id  = (int) $_GET[ 'id' ];
        } else {
            $activate = true;
            if ( defined( 'IS_PROFILE_PAGE' ) ) {
                $user_id = get_current_user_id();
            } else {
                // @TODO: add IF for taxonomy
                $post_id = get_the_ID();
            }
        }

        if ( false != $activate ) {
            if ( empty( $fields ) ) {
                if ( isset( $user_id ) && false !== $user_id ) {
                    $fields = get_field_objects( 'user_' . $user_id );
                } elseif ( isset( $post_id ) && false !== $post_id ) {
                    $fields = get_field_objects( $post_id );
                    if ( empty( $fields ) ) {
                        if ( 5 == substr( $acf_version, 0, 1 ) ) {
                            $groups = acf_get_field_groups( array( 'post_id' => $post_id ) );
                            foreach( $groups as $key => $values ) {
                                if ( isset( $values[ 'key' ] ) ) {
                                    $group_fields = acf_get_fields( $values[ 'key' ] );
                                    foreach( $group_fields as $field ) {
                                        if ( in_array( $field[ 'type' ], [ 'acf_city_selector', 'repeater', 'flexible_content', 'group' ] ) ) {
                                            $new_fields[] = $field;
                                        }
                                    }
                                    if ( isset( $new_fields ) ) {
                                        $fields = $new_fields;
                                    }
                                }
                            }
                        } else {
                            $groups = apply_filters('acf/get_field_groups', [] );
                            foreach( $groups as $group ) {
                                if ( isset( $group[ 'id' ] ) ) {
                                    $group_fields = apply_filters( 'acf/field_group/get_fields', array(), $group[ 'id' ] );
                                    foreach( $group_fields as $field ) {
                                        if ( 'acf_city_selector' == $field[ 'type' ] ) {
                                            $new_fields[] = $field;
                                        }
                                    }
                                    if ( isset( $new_fields ) ) {
                                        $fields = $new_fields;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /*
             * Get the field settings
             *
             * @TODO: check for multiple fields, array_search only returns first instance
             */
            if ( isset( $fields ) && is_array( $fields ) && count( $fields ) > 0 ) {
                foreach( $fields as $field ) {
                    if ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'acf_city_selector' ) {
                        $settings[ 'default_country' ] = $field[ 'default_country' ];
                        $settings[ 'show_labels' ]     = $field[ 'show_labels' ];
                        $settings[ 'use_select2' ]     = ( isset( $field[ 'use_select2' ] ) ) ? $field[ 'use_select2' ] : false;
                        $settings[ 'which_fields' ]    = $field[ 'which_fields' ];
                        break;
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'repeater' ) {
                        $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                        if ( false !== $array_key ) {
                            $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'default_country' ];
                            $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                            $settings[ 'use_select2' ]     = $field[ 'sub_fields' ][ $array_key ][ 'use_select2' ];
                            $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                            break;
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'flexible_content' ) {
                        $layouts = $field[ 'layouts' ];
                        foreach( $layouts as $layout ) {
                            $sub_fields = $layout[ 'sub_fields' ];
                            $array_key  = array_search( 'acf_city_selector', array_column( $sub_fields, 'type' ) );
                            if ( false !== $array_key ) {
                                $settings[ 'default_country' ] = $sub_fields[ $array_key ][ 'default_country' ];
                                $settings[ 'show_labels' ]     = $sub_fields[ $array_key ][ 'show_labels' ];
                                $settings[ 'use_select2' ]     = $sub_fields[ $array_key ][ 'use_select2' ];
                                $settings[ 'which_fields' ]    = $sub_fields[ $array_key ][ 'which_fields' ];
                                break;
                            } else {
                                // check for repeater
                                $repeater_key = array_search( 'repeater', array_column( $sub_fields, 'type' ) );
                                if ( false !== $repeater_key ) {
                                    $array_key = array_search( 'acf_city_selector', array_column( $sub_fields[ $repeater_key ][ 'sub_fields' ], 'type' ) );
                                    if ( false !== $array_key ) {
                                        $settings[ 'default_country' ] = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $array_key ][ 'default_country' ];
                                        $settings[ 'show_labels' ]     = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                                        $settings[ 'use_select2' ]     = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $array_key ][ 'use_select2' ];
                                        $settings[ 'which_fields' ]    = $sub_fields[ $repeater_key ][ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                                        break;
                                    }
                                } else {
                                    $group_key = array_search( 'group', array_column( $sub_fields, 'type' ) );
                                    if ( false !== $group_key ) {
                                        // @TODO: finish
                                    }
                                }
                            }
                        }
                    } elseif ( isset( $field[ 'type' ] ) && $field[ 'type' ] == 'group' ) {
                        $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                        if ( false !== $array_key ) {
                            $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'default_country' ];
                            $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                            $settings[ 'use_select2' ]     = $field[ 'sub_fields' ][ $array_key ][ 'use_select2' ];
                            $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                            break;
                        } else {
                            $array_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ], 'type' ) );
                            if ( false === $array_key ) {
                                $array_key = array_search( 'repeater', array_column( $field[ 'sub_fields' ], 'type' ) );
                                if ( false === $array_key ) {
                                    $array_key = array_search( 'flexible_content', array_column( $field[ 'sub_fields' ], 'type' ) );
                                    // @TODO: finish
                                } else {
                                    if ( ! empty( $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ] ) ) {
                                        $acf_key = array_search( 'acf_city_selector', array_column( $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ], 'type' ) );
                                        if ( false !== $acf_key ) {
                                            $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'default_country' ];
                                            $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'show_labels' ];
                                            $settings[ 'use_select2' ]     = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'use_select2' ];
                                            $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'sub_fields' ][ $acf_key ][ 'which_fields' ];
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $settings[ 'default_country' ] = $field[ 'sub_fields' ][ $array_key ][ 'default_country' ];
                                $settings[ 'show_labels' ]     = $field[ 'sub_fields' ][ $array_key ][ 'show_labels' ];
                                $settings[ 'use_select2' ]     = $field[ 'sub_fields' ][ $array_key ][ 'use_select2' ];
                                $settings[ 'which_fields' ]    = $field[ 'sub_fields' ][ $array_key ][ 'which_fields' ];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $settings;
    }
