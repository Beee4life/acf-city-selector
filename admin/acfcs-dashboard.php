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
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>
                <?php echo get_admin_page_title(); ?>
            </h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">

                        <div class="acfcs__section acfcs__section--upload-csv">

                            <h2><?php esc_html_e( 'Upload a CSV file', 'acf-city-selector' ); ?></h2>
                            <form enctype="multipart/form-data" method="post">
                                <input name="acfcs_upload_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-upload-csv-nonce' ); ?>" />
                                <input type="hidden" name="MAX_FILE_SIZE" value="1024000" />

                                <div class="upload-element">
                                    <label for="csv_upload">
                                        <?php esc_html_e( 'Choose a (CSV) file to upload', 'acf-city-selector' ); ?>
                                    </label>
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
                                    <h2>
                                        <?php esc_html_e( 'Select a file to import', 'acf-city-selector' ); ?>
                                    </h2>

                                    <form method="post">
                                        <input name="acfcs_select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-select-file-nonce' ); ?>" />

                                        <div class="acfcs__process-file">
                                            <div class="acfcs__process-file-element acfcs__process-file-element--file">
                                                <label for="acfcs_file_name">
                                                    <?php esc_html_e( 'File', 'acf-city-selector' ); ?>
                                                </label>
                                                <select name="acfcs_file_name" id="acfcs_file_name">
                                                    <?php if ( count( $file_index ) > 1 ) { ?>
                                                        <option value="">
                                                            <?php esc_html_e( 'Select a file', 'acf-city-selector' ); ?>
                                                        </option>
                                                    <?php } ?>
                                                    <?php foreach ( $file_index as $file_name ) { ?>
                                                        <?php $selected = ( isset( $_POST[ 'acfcs_file_name' ] ) && $_POST[ 'acfcs_file_name' ] == $file_name ) ? ' selected="selected"' : false; ?>
                                                        <option value="<?php echo $file_name; ?>"<?php echo $selected; ?>>
                                                            <?php echo $file_name; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="acfcs__process-file-element acfcs__process-file-element--delimiter">
                                                <?php $delimiters = [ ';', ',', '|' ]; ?>
                                                <label for="acfcs_delimiter">
                                                    <?php esc_html_e( 'Delimiter', 'acf-city-selector' ); ?>
                                                </label>
                                                <select name="acfcs_delimiter" id="acfcs_delimiter">
                                                    <?php foreach( $delimiters as $delimiter ) { ?>
                                                        <?php $selected_delimiter = ( $delimiter == apply_filters( 'acfcs_delimiter', ';' ) ) ? ' selected' : false; ?>
                                                        <option value="<?php echo $delimiter; ?>"<?php echo $selected_delimiter; ?>>
                                                            <?php echo $delimiter; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="acfcs__process-file-element acfcs__process-file-element--maxlines">
                                                <label for="acfcs_max_lines">
                                                    <?php esc_html_e( 'Max lines', 'acf-city-selector' ); ?>
                                                </label>
                                                <input type="number" name="acfcs_max_lines" id="acfcs_max_lines" />
                                            </div>
                                        </div>

                                        <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify selected file', 'acf-city-selector' ); ?>" />
                                        <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import selected file', 'acf-city-selector' ); ?>" />
                                        <input name="remove" type="submit" class="button button-primary" value="<?php esc_html_e( 'Remove selected file', 'acf-city-selector' ); ?>" />
                                    </form>
                                </div>
                        <?php } ?>

                        <?php if ( true === $show_raw_import ) { ?>
                            <?php $placeholder = "Amsterdam;NH;Noord-Holland;NL;Netherlands\nRotterdam;ZH;Zuid-Holland;NL;Netherlands"; ?>
                            <?php $submitted_raw_data = ( isset( $_POST[ 'raw_csv_import' ] ) ) ? $_POST[ 'raw_csv_import' ] : false; ?>
                            <div class="acfcs__section acfcs__section--raw-import">
                                <h2>
                                    <?php esc_html_e( 'Import CSV data (from clipboard)', 'acf-city-selector' ); ?>
                                </h2>
                                <p>
                                    <?php esc_html_e( 'Here you can paste CSV data from your clipboard.', 'acf-city-selector' ); ?>
                                    <br />
                                    <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'acf-city-selector' ); ?>
                                    <br />
                                    <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'acf-city-selector' ); ?>
                                </p>
                                <form method="post">
                                    <input name="acfcs_import_raw_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-import-raw-nonce' ); ?>" />
                                    <label for="raw-import">
                                        <?php esc_html_e( 'Raw CSV import', 'acf-city-selector' ); ?>
                                    </label>
                                    <textarea name="acfcs_raw_csv_import" id="raw-import" rows="5" placeholder="<?php echo $placeholder; ?>"><?php echo $submitted_raw_data; ?></textarea>
                                    <br />
                                    <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify CSV data', 'acf-city-selector' ); ?>" />
                                    <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import CSV data', 'acf-city-selector' ); ?>" />
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

