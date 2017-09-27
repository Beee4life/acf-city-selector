<?php
	/*
	 * Content for the settings page
	 */
	function acfcs_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		acf_plugin_city_selector::acfcs_show_admin_notices();

    ?>

		<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1><?php esc_html_e( 'ACF City Selector', 'acf-city-selector' ); ?></h1>

            <?php echo acf_plugin_city_selector::acfcs_admin_menu(); ?>

            <p><?php esc_html_e( 'On this page you can find some helpful info about the ACF City Selector plugin as well as some settings.', 'acf-city-selector' ); ?></p>

            <!-- left part -->
            <div class="admin_left">

                <h2><?php esc_html_e( 'Upload a csv file', 'acf-city-selector' ); ?></h2>
                <form enctype="multipart/form-data" method="post">
                    <input name="upload_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'upload-csv-nonce' ); ?>" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
                    <label for="file_upload">Choose a (csv) file to upload</label>
                    <input name="csv_upload" type="file" accept=".csv" />
                    <br /><br />
                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Upload file', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />
                <h2><?php esc_html_e( 'Select a file to import', 'acf-city-selector' ); ?></h2>

	            <?php
		            $target_folder = wp_upload_dir()['basedir'] . '/acfcs/';
		            $file_index    = scandir( $target_folder );

		            if ( $file_index ) {

			            $has_files = false;
			            if ( 0 < count( $file_index ) ) {
				            ?>
                            <form method="post">
                                <input name="select_file_nonce" type="hidden" value="<?php echo wp_create_nonce( 'select-file-nonce' ); ?>" />
                                <table class="uploaded_files" cellpadding="0" cellspacing="0">
						            <?php
							            foreach( $file_index as $file_name ) {
								            if ( '.DS_Store' != $file_name && '.' != $file_name && '..' != $file_name ) {
									            $has_files = true;
									            echo '<tr>';
									            echo '<td><input name="file_name[]" type="checkbox" value="' . $file_name . '"></td>';
									            echo '<td>' . $file_name . '</td>';
									            echo '</tr>';
								            }
							            }
						            ?>
                                </table>
					            <?php if ( $has_files ) { ?>
                                    <br />
                                    <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify selected file(s)', 'acf-city-selector' ); ?>" />
                                    <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import selected file(s)', 'acf-city-selector' ); ?>" />
                                    <input name="remove" type="submit" class="button button-primary" value="<?php esc_html_e( 'Remove selected file(s)', 'acf-city-selector' ); ?>" />
						        <?php } ?>
                            </form>

				            <?php
			            }
			            if ( false == $has_files && '.DS_Store' != $file_index[0] ) {
				            echo '<ul><li>' . esc_html__( 'No files uploaded', 'acf-city-selector' ) . '</li></ul>';
			            }
			            echo '</ul>';
		            }
                ?>
                <br /><hr />

                <h2><?php esc_html_e( 'Import raw CSV data', 'acf-city-selector' ); ?></h2>
                <p>
	                <?php esc_html_e( 'Make sure the cursor is ON the last line (after the last character), NOT on a new line.', 'acf-city-selector' ); ?>
                    <br />
	                <?php esc_html_e( 'This is seen as a new entry and creates an error !!!', 'acf-city-selector' ); ?>
                </p>
                <?php
                    $submitted_raw_data = false;
                    if ( isset( $_POST[ 'raw_csv_import' ] ) ) {
                        $submitted_raw_data = $_POST[ 'raw_csv_import' ];
                    }
                ?>
                <form method="post">
                    <input name="import_raw_nonce" type="hidden" value="<?php echo wp_create_nonce( 'import-raw-nonce' ); ?>" />
                    <label for="raw-import"></label>
                    <textarea name="raw_csv_import" id="raw-import" rows="5" cols="100" placeholder="Amsterdam,NH,Noord-Holland,NL,Netherlands"><?php echo $submitted_raw_data; ?></textarea>
                    <br />
                    <input name="verify" type="submit" class="button button-primary" value="<?php esc_html_e( 'Verify CSV data', 'acf-city-selector' ); ?>" />
                    <input name="import" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import CSV data', 'acf-city-selector' ); ?>" />
                </form>

            </div><!-- end .admin_left -->

			<?php include( 'admin-right.php' ); ?>

        </div><!-- end .wrap -->
		<?php
	}

