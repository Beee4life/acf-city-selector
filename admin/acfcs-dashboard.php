<?php
    /*
     * Content for the settings page
     */
    function acfcs_dashboard() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $show_raw_import = true;
        ?>

        <div class="wrap acfcs">
            <?php echo sprintf( '<h1>%s</h1>', get_admin_page_title() ); ?>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">
                        <div class="acfcs__section acfcs__section--upload-csv">
                            <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Upload a CSV file', 'acf-city-selector' ) ); ?>

                            <form enctype="multipart/form-data" method="post">
                                <input name="acfcs_upload_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-upload-csv-nonce' ); ?>" />
                                <input type="hidden" name="MAX_FILE_SIZE" value="1024000" />

                                <div class="upload-element">
                                    <?php echo sprintf( '<label for="csv_upload">%s</label>', esc_attr__( 'Choose a (CSV) file to upload', 'acf-city-selector' ) ); ?>
                                    <div class="form--upload form--csv_upload">
                                        <input type="file" name="csv_upload" id="csv_upload" accept=".csv" />
                                        <span class="val"></span>
                                        <span class="upload_button button-primary" data-type="csv_upload">
                                            <?php _e( 'Select file', 'acf-city-selector' ); ?>
                                        </span>
                                    </div>
                                </div>
                                <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Upload CSV', 'acf-city-selector' ); ?>" />
                            </form>
                        </div>

                        <?php
                            $file_index = acfcs_check_if_files();
                            if ( ! empty( $file_index ) ) { ?>
                                <div class="acfcs__section acfcs__section--process-file">
                                    <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Select a file to import', 'acf-city-selector' ) ); ?>
                                    <?php echo sprintf( '<p><small>%s</small></p>', esc_html__( 'Max lines has no effect when verifying. The entire file will be checked.', 'acf-city-selector' ) ); ?>

                                    <form method="post">
                                        <input name="acfcs_select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-select-file-nonce' ); ?>" />

                                        <div class="acfcs__process-file">
                                            <div class="acfcs__process-file-element acfcs__process-file-element--file">
                                                <?php echo sprintf( '<label for="acfcs_file_name">%s</label>', esc_attr__( 'File', 'acf-city-selector' ) ); ?>
                                                <select name="acfcs_file_name" id="acfcs_file_name">
                                                    <?php if ( count( $file_index ) > 1 ) { ?>
                                                        <?php echo sprintf( '<option value="">%s</option>', esc_attr__( 'Select a file', 'acf-city-selector' ) ); ?>
                                                    <?php } ?>
                                                    <?php foreach ( $file_index as $file_name ) { ?>
                                                        <?php $selected = ( isset( $_POST[ 'acfcs_file_name' ] ) && $_POST[ 'acfcs_file_name' ] == $file_name ) ? ' selected="selected"' : false; ?>
                                                        <?php echo sprintf( '<option value="%s"%s>%s</option>', $file_name, $selected, $file_name ); ?>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="acfcs__process-file-element acfcs__process-file-element--delimiter">
                                                <?php $delimiters = [ ';', ',', '|' ]; ?>
                                                <?php echo sprintf( '<label for="acfcs_delimiter">%s</label>', esc_attr__( 'Delimiter', 'acf-city-selector' ) ); ?>
                                                <select name="acfcs_delimiter" id="acfcs_delimiter">
                                                    <?php foreach( $delimiters as $delimiter ) { ?>
                                                        <?php $selected_delimiter = ( $delimiter == apply_filters( 'acfcs_delimiter', ';' ) ) ? ' selected' : false; ?>
                                                        <?php echo sprintf( '<option value="%s"%s>%s</option>', $delimiter, $selected_delimiter, $delimiter ); ?>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="acfcs__process-file-element acfcs__process-file-element--maxlines">
                                                <?php echo sprintf( '<label for="acfcs_max_lines">%s</label>', esc_attr__( 'Max lines', 'acf-city-selector' ) ); ?>
                                                <input type="number" name="acfcs_max_lines" id="acfcs_max_lines" />
                                            </div>
                                        </div>

                                        <?php
                                            echo sprintf( '<input name="acfcs_verify" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Verify selected file', 'acf-city-selector' ) );
                                            echo sprintf( '<input name="acfcs_import" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Import selected file', 'acf-city-selector' ) );
                                            echo sprintf( '<input name="acfcs_remove" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Remove selected file', 'acf-city-selector' ) );
                                        ?>
                                    </form>
                                </div>
                        <?php } ?>

                        <?php if ( true === $show_raw_import ) { ?>
                            <?php $placeholder = "Amsterdam;NH;Noord-Holland;NL;Netherlands\nRotterdam;ZH;Zuid-Holland;NL;Netherlands"; ?>
                            <?php $submitted_raw_data = ( isset( $_POST[ 'raw_csv_import' ] ) ) ? sanitize_textarea_field( $_POST[ 'raw_csv_import' ] ) : false; ?>
                            <div class="acfcs__section acfcs__section--raw-import">
                                <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Import CSV data (from clipboard)', 'acf-city-selector' ) ); ?>
                                <p>
                                    <?php esc_html_e( 'Here you can paste CSV data from your clipboard.', 'acf-city-selector' ); ?>
                                    <br />
                                    <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'acf-city-selector' ); ?>
                                    <br />
                                    <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'acf-city-selector' ); ?>
                                </p>
                                <form method="post">
                                    <input name="acfcs_import_raw_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-import-raw-nonce' ); ?>" />
                                    <?php echo sprintf( '<label for="raw-import">%s</label>', esc_attr__( 'Raw CSV import', 'acf-city-selector' ) ); ?>
                                    <?php echo sprintf( '<textarea name="acfcs_raw_csv_import" id="raw-import" rows="5" placeholder="%s">%s</textarea>', $placeholder, $submitted_raw_data ); ?>
                                    <br />
                                    <?php
                                        echo sprintf( '<input name="acfcs_verify" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Verify CSV data', 'acf-city-selector' ) );
                                        echo sprintf( '<input name="acfcs_import" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Import CSV data', 'acf-city-selector' ) );
                                    ?>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <?php include 'admin-right.php'; ?>
            </div>

        </div>
        <?php
    }

