<?php
    /**
     * Content for the settings page
     */
    function acfcs_preview_page() {

        if ( ! current_user_can( apply_filters( 'acfcs_user_cap', 'manage_options' ) ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'acf-city-selector' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();
        ?>

        <div class="wrap acfcs">
            <h1>ACF City Selector</h1>

            <?php
                echo ACF_City_Selector::acfcs_admin_menu();

                $file_index      = acfcs_check_if_files();
                $file_name       = ( isset( $_POST[ 'acfcs_file_name' ] ) ) ? $_POST[ 'acfcs_file_name' ] : false;
                $max_lines       = ( isset( $_POST[ 'acfcs_max_lines' ] ) ) ? (int) $_POST[ 'acfcs_max_lines' ] : false;
                $max_lines_value = ( false != $max_lines ) ? $max_lines : 100;
                $delimiter       = ( isset( $_POST[ 'acfcs_delimiter' ] ) ) ? sanitize_text_field( $_POST[ 'acfcs_delimiter' ] ) : apply_filters( 'acfcs_delimiter', ';' );

                // Get imported data
                if ( $file_name ) {
                    $csv_info   = acfcs_csv_to_array( $file_name, '', $delimiter, true, $max_lines );
                    $file_index = acfcs_check_if_files();
                }
            ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">
                        <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Preview data', 'acf-city-selector' ) ); ?>

                        <?php if ( ! empty( $file_index ) ) { ?>
                            <?php include 'acfcs-preview-form.php'; ?>
                        <?php } else { ?>
                            <div>
                                <?php esc_html_e( 'You have no files to preview.', 'acf-city-selector' ); ?>
                                <?php echo sprintf( __( 'Upload a csv file from your %s.', 'acf-city-selector' ), sprintf( '<a href="%s">%s</a>', esc_url( admin_url( '/admin.php?page=acfcs-dashboard' ) ), __( 'dashboard', 'acf-city-selector' ) ) ); ?>
                            </div>
                        <?php } ?>

                        <?php
                            if ( $file_name ) {
                                echo '<div class="acfcs__section acfcs__section--results">';
                                if ( array_key_exists( 'error', $csv_info ) ) {
                                    if ( 'file_deleted' == $csv_info[ 'error' ] ) {
                                        $dismiss_button = sprintf( '<button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button>', esc_html__( 'Dismiss this notice', 'acf-city-selector' ) );
                                        $error_message  = sprintf( esc_html__( 'You either have errors in your CSV or there is no data. In case of an error, the file is deleted. Please check "%s".', 'acf-city-selector' ), $file_name );
                                        echo sprintf( '<div class="notice notice-error is-dismissable"><p>%s</p>%s</div>', $error_message, $dismiss_button );

                                    } elseif ( ! isset( $csv_info[ 'data' ] ) || ( isset( $csv_info[ 'data' ] ) && empty( $csv_info[ 'data' ] ) ) ) {
                                        $message = esc_html__( 'There appears to be no data in the file. Are you sure it has content and you selected the correct delimiter ?', 'acf-city-selector' );
                                        echo sprintf( '<div class="notice notice-error">%s</div>', $message );

                                    }
                                } elseif ( isset( $csv_info[ 'data' ] ) && ! empty( $csv_info[ 'data' ] ) ) {
                                    echo sprintf( '<h2>%s</h2>', esc_html__( 'CSV contents', 'acf-city-selector' ) );
                                    echo sprintf( '<p class="hide640"><small>%s</small></p>', esc_html__( 'Table scrolls horizontally.', 'acf-city-selector' ) );
                                    echo acfcs_render_preview_results( $csv_info[ 'data' ] );
                                }
                                echo '</div>';
                            }
                        ?>
                    </div>
                </div>

                <?php include 'admin-right.php'; ?>

            </div>
        </div>
        <?php
    }
