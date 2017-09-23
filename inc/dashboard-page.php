<?php
	/*
	 * Content for the settings page
	 */
	function acfcs_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		acf_plugin_city_selector::acfcs_show_admin_notices();

    ?>

		<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1><?php esc_html_e( 'ACF City Selector', 'acf-city-selector' ); ?></h1>

            <?php echo acf_plugin_city_selector::acfcs_admin_menu(); ?>

            <p><?php echo sprintf( esc_html__( 'On this page you can find some helpful info about the %s plugin as well as some settings.', 'acf-city-selector' ), 'ACF City Selector' ); ?></p>

            <!-- left part -->
            <div class="admin_left">

                <form method="post" action="">
                    <input name="import_actions_nonce" value="<?php echo wp_create_nonce( 'import-actions-nonce' ); ?>" type="hidden" />

                    <h2><?php esc_html_e( 'Import countries', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "Here you can (re-)import all cities for the individual countries listed below.", 'acf-city-selector' ); ?></p>

                    <p>
                        <span class="acfcs_input">
                            <label for="import_nl" class="screen-reader-text"></label>
                            <input type="checkbox" name="import_nl" id="import_nl" value="1" /> <?php esc_html_e( 'Import cities in Holland/The Netherlands', 'acf-city-selector' ); ?> (2449)
                        </span>
                    </p>

                    <p>
                        <span class="acfcs_input">
                            <label for="import_be" class="screen-reader-text"></label>
                            <input type="checkbox" name="import_be" id="import_be" value="1" /> <?php esc_html_e( 'Import cities in Belgium', 'acf-city-selector' ); ?> (1166)
                        </span>
                    </p>

                    <p>
                        <span class="acfcs_input">
                            <label for="import_lux" class="screen-reader-text"></label>
                            <input type="checkbox" name="import_lux" id="import_lux" value="1" /> <?php esc_html_e( 'Import cities in Luxembourg', 'acf-city-selector' ); ?> (12)
                        </span>
                    </p>

                    <input name="" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import selected countries', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />

                <h2>Import raw CSV data</h2>
                <p>Make sure the cursor is ON the last line (after the last character), NOT on a new line.<br />This is seen as a new entry and creates an error !!!</p>
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
                    <input name="verify" type="submit" class="button button-primary" value="Verify CSV data" />
                    <input name="import" type="submit" class="button button-primary" value="Import CSV data" />
                </form>

            </div><!-- end .admin_left -->

			<?php include( 'admin-right.php' ); ?>

        </div><!-- end .wrap -->
		<?php
	}

