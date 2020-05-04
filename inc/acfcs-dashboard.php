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

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="admin_left">

                <div class="acfcs__section acfcs__section--upload-csv">

                    <h2><?php esc_html_e( 'Upload a CSV file', 'acf-city-selector' ); ?></h2>
                    <form enctype="multipart/form-data" method="post">
                        <input name="acfcs_upload_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-upload-csv-nonce' ); ?>" />
                        <input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
                        <label for="file_upload"><?php _e( 'Choose a (CSV) file to upload', 'acf-city-selector' ); ?></label>
                        <input name="csv_upload" type="file" accept=".csv" />
                        <br /><br />
                        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Upload file', 'acf-city-selector' ); ?>" />
                    </form>
                </div>

                <?php
                    $file_index = acfcs_check_if_files();
                    if ( ! empty( $file_index ) ) { ?>
                        <div class="acfcs__section acfcs__section--process-file">
                            <h2><?php esc_html_e( 'Select a file to import', 'acf-city-selector' ); ?></h2>

                            <form method="post">
                                <input name="acfcs_select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-select-file-nonce' ); ?>" />
                                <table class="acfcs__table acfcs__table--uploaded">
                                    <thead>
                                    <tr>
                                        <th><?php _e( 'File name', 'acf-city-selector' ); ?></th>
                                        <th><?php _e( 'Delimiter', 'acf-city-selector' ); ?></th>
                                        <th><?php _e( 'Max. lines', 'acf-city-selector' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <label>
                                                <select name="acfcs_file_name">
                                                    <?php if ( count( $file_index ) > 1 ) { ?>
                                                        <option value=""><?php esc_html_e( 'Select a file', 'acf-city-selector' ); ?></option>
                                                    <?php } ?>
                                                    <?php foreach ( $file_index as $file ) { ?>
                                                        <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </label>
                                        </td>
                                        <td>
                                            <label>
                                                <select name="acfcs_delimiter" id="acfcs_delimiter">
                                                    <option value=",">,</option>
                                                    <option value=";">;</option>
                                                    <option value="|">|</option>
                                                </select>
                                            </label>
                                        </td>
                                        <td>
                                            <label>
                                                <select name="acfcs_max_lines" id="acfcs_max_lines">
                                                    <option value=""><?php esc_html_e( 'All', 'acf-city-selector' ); ?></option>
                                                    <option value="5">5</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                    <option value="250">250</option>
                                                    <option value="500">500</option>
                                                    <option value="1000">1000</option>
                                                </select>
                                            </label>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify selected file', 'acf-city-selector' ); ?>" />
                                <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import selected file', 'acf-city-selector' ); ?>" />
                                <input name="remove" type="submit" class="button button-primary" value="<?php esc_html_e( 'Remove selected file', 'acf-city-selector' ); ?>" />
                            </form>
                        </div>
                <?php } ?>

                <?php if ( false != $show_raw_import ) { ?>
                    <?php $placeholder = "Amsterdam,NH,Noord-Holland,NL,Netherlands\nRotterdam,ZH,Zuid-Holland,NL,Netherlands"; ?>
                    <?php $submitted_raw_data = ( isset( $_POST[ 'raw_csv_import' ] ) ) ? $_POST[ 'raw_csv_import' ] : false; ?>
                    <div class="acfcs__section acfcs__section--raw-import">
                        <h2><?php esc_html_e( 'Import CSV data (from clipboard)', 'acf-city-selector' ); ?></h2>
                        <p>
                            <?php esc_html_e( 'Here you can paste CSV data from your clipboard.', 'acf-city-selector' ); ?>
                            <br />
                            <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'acf-city-selector' ); ?>
                            <br />
                            <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'acf-city-selector' ); ?>
                        </p>
                        <form method="post">
                            <input name="acfcs_import_raw_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-import-raw-nonce' ); ?>" />
                            <label>
                                <textarea name="acfcs_raw_csv_import" id="raw-import" rows="5" cols="100" placeholder="<?php echo $placeholder; ?>"><?php echo $submitted_raw_data; ?></textarea>
                            </label>
                            <br />
                            <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify CSV data', 'acf-city-selector' ); ?>" />
                            <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import CSV data', 'acf-city-selector' ); ?>" />
                        </form>
                    </div>
                <?php } ?>
            </div>

            <?php include( 'admin-right.php' ); ?>

        </div>
        <?php
    }

