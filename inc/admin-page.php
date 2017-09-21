<?php
	/*
	 * Content for the settings page
	 */
	function acfcs_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		acf_plugin_city_selector::al_show_admin_notices();

		if ( isset( $_POST["db_actions_nonce"] ) ) {
			if ( ! wp_verify_nonce( $_POST["db_actions_nonce"], 'db-actions-nonce' ) ) {
				return;
			} else {
				if ( isset( $_POST['import_nl'] ) && 1 == $_POST["import_nl"] ) {
					echo '<div class="updated"><p><strong>' . __( 'You successfully imported all cities in The Netherlands.', 'acf-city-selector' ) . '</strong></p></div>';
				}
				if ( isset( $_POST['import_be'] ) && 1 == $_POST["import_be"] ) {
					echo '<div class="updated"><p><strong>' . __( 'You successfully imported all cities in Belgium.', 'acf-city-selector' ) . '</strong></p></div>';
				}
				if ( isset( $_POST['import_lux'] ) && 1 == $_POST["import_lux"] ) {
					echo '<div class="updated"><p><strong>' . __( 'You successfully imported all cities in Luxembourg.', 'acf-city-selector' ) . '</strong></p></div>';
				}
			}
		}
		?>

		<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1><?php esc_html_e( 'ACF City Selector', 'acf-city-selector' ); ?></h1>
            <p><?php sprintf( esc_html__( 'On this page you can find some helpful info about the %s plugin as well as some settings.', 'acf-city-selector' ), 'ACF City Selector' ); ?></p>


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
	                // $submitted_raw_data = 'Amsterdam,NH,Noord-Holland,NL,Netherlands';
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

            <div class="admin_right">

                <h3><?php esc_html_e( 'About the plugin', 'acf-city-selector' ); ?></h3>
                <p><?php echo sprintf( __( 'This plugin is an extension for %s. I built it because there was no properly working plugin which did this.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/" target="_blank">Advanced Custom Fields</a>' ); ?>
                <p><?php echo sprintf( __( '<a href="%s" target="_blank">Click here</a> for a demo on my own website.', 'acf-city-selector' ), esc_url( 'http://www.berryplasman.com/wordpress/acf-city-selector/?utm_source=wpadmin&utm_medium=about_plugin&utm_campaign=acf-plugin' ) ); ?></p>

                <hr />

                <h3><?php esc_html_e( 'About Beee', 'acf-city-selector' ); ?></h3>
                <p><?php esc_html_e( 'If you need a Wordpress designer/coder to do work on your site, hit me up.', 'acf-city-selector' ); ?></p>

                <hr />

                <h3>Support</h3>
                <p><?php echo sprintf( __( 'If you need support for this plugin or if you have some good suggestions for improvements and/or new features, please turn to %s.', 'acf-city-selector' ), '<a href="https://github.com/Beee4life/acf-city-selector/issues" target="_blank">Github</a>' ); ?>
                </p>
                <hr />

                <p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank"><img src="<?php echo plugins_url( 'assets/img/paypal_donate.gif', dirname(__FILE__) ); ?>" alt="" class="donateimg" /></a>
                    <?php esc_html_e( 'If you like this plugin, buy me a coke to show your appreciation so I can continue to develop it.', 'acf-city-selector' ); ?></p>

            </div><!-- end .admin_right -->
        </div><!-- end .wrap -->
		<?php
	}

