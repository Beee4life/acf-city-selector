<?php
    /*
     * Content for the settings page
     */
    function acfcs_dashboard() {

        if ( ! current_user_can( apply_filters( 'acfcs_user_cap', 'manage_options' ) ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'acf-city-selector' ) );
        }
        
        $submitted_raw_data = false;
        if ( isset( $_POST[ 'acfcs_import_raw_nonce' ] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_import_raw_nonce' ] ) ), 'acfcs-import-raw-nonce' ) ) {
                ACF_City_Selector::acfcs_errors()->add( 'error_no_nonce_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            } else {
                $submitted_raw_data = ( isset( $_POST[ 'raw_csv_import' ] ) ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'raw_csv_import' ] ) ) : false;
            }
        }
        
        ACF_City_Selector::acfcs_show_admin_notices();

        $show_raw_import = true;
        ?>

        <div class="wrap acfcs">
            <?php echo sprintf( '<h1>%s</h1>', esc_html( get_admin_page_title() ) ); ?>

            <?php do_action( 'acfcs_admin_menu' ); ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">
                        <div class="acfcs__section acfcs__section--upload-csv">
                            <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Upload a CSV file', 'acf-city-selector' ) ); ?>
                            <?php include 'upload-file-form.php'; ?>
                        </div>

                        <?php
                            $file_index = acfcs_check_if_files();
                            if ( ! empty( $file_index ) ) { ?>
                                <div class="acfcs__section acfcs__section--process-file">
                                    <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Select a file to import', 'acf-city-selector' ) ); ?>
                                    <?php echo sprintf( '<p><small>%s</small></p>', esc_html__( 'Max lines has no effect when verifying. The entire file will be checked.', 'acf-city-selector' ) ); ?>
                                    <?php include 'process-file-form.php'; ?>
                                </div>
                        <?php } ?>

                        <?php if ( true === $show_raw_import ) { ?>
                            <?php $placeholder = "Amsterdam;NH;Noord-Holland;NL;Netherlands\nRotterdam;ZH;Zuid-Holland;NL;Netherlands"; ?>
                            <div class="acfcs__section acfcs__section--raw-import">
                                <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Import CSV data (from clipboard)', 'acf-city-selector' ) ); ?>
                                <p>
                                    <?php esc_html_e( 'Here you can paste CSV data from your clipboard.', 'acf-city-selector' ); ?>
                                    <br />
                                    <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'acf-city-selector' ); ?>
                                    <br />
                                    <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'acf-city-selector' ); ?>
                                </p>
                                <?php include 'raw-input-form.php'; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <?php include 'admin-right.php'; ?>
            </div>

        </div>
        <?php
    }

