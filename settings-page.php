<?php
	/*
	 * Content for the settings page
	 */
	function acfcs_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// @TODO: show proper error notices

		if ( isset( $_POST["db_actions_nonce"] ) ) {
			if ( ! wp_verify_nonce( $_POST["db_actions_nonce"], 'db-actions-nonce' ) ) {
				return;
			} else {
				if ( isset( $_POST['delete_cities'] ) && 1 == $_POST["delete_cities"] ) {
					echo '<div class="updated"><p><strong>' . __( 'Your cities table has been emptied.', 'acf-city-selector' ) . '</strong></p></div>';
				}
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

            <h1><?php esc_html_e( 'ACF City Selector Settings', 'acf-city-selector' ); ?></h1>
            <p><?php sprintf( esc_html__( 'On this page you can find some helpful info about the %s plugin as well as some settings.', 'acf-city-selector' ), 'ACF City Selector' ); ?></p>

            <!-- left part -->
            <div class="admin_left">

                <!--<h2>--><?php //esc_html_e( 'General info', 'acf-city-selector' ); ?><!--</h2>-->
                <!--<p>--><?php //sprintf( esc_html__( 'This plugin requires %s to be activated to work.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/">Advanced Custom Fields</a>' ); ?><!--</p>-->
                <!---->
                <!--<hr />-->

                <form method="post" action="">
                    <input name="preserve_settings_nonce" value="<?php echo wp_create_nonce( 'preserve-settings-nonce' ); ?>" type="hidden" />

                    <h2><?php esc_html_e( 'Preserve settings', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "When the plugin is deleted, all settings and cities are deleted as well. Select this option to save all your cities/settings.", 'acf-city-selector' ); ?></p>

                    <?php $checked = get_option( 'acfcs_preserve_settings' ) ? ' checked="checked"' : false; ?>
                    <p>
                        <span class="acfcs_input">
                            <label for="preserve_settings" class="screen-reader-text"></label>
                            <input type="checkbox" name="preserve_settings" id="preserve_settings" value="1" <?php echo $checked; ?>/> <?php esc_html_e( 'Preserve settings on plugin deletion', 'acf-city-selector' ); ?>
                        </span>
                    </p>

                    <input name="" type="submit" class="button button-primary" value="<?php esc_html_e( 'Save settings', 'acf-city-selector' ); ?>" />
                    <?php //submit_button(); ?>
                </form>

                <br /><hr />

                <form method="post" action="">
                    <input name="truncate_table_nonce" value="<?php echo wp_create_nonce( 'truncate-table-nonce' ); ?>" type="hidden" />

                    <h2><?php esc_html_e( 'Clear the database', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "By selecting this option, you will remove all cities, which are present in the database. This is handy if you don't need the preset cities or you want a fresh start.", 'acf-city-selector' ); ?></p>

                    <p>
                        <span class="acfcs_input">
                            <label for="delete_cities" class="screen-reader-text"></label>
                            <input type="checkbox" name="delete_cities" id="delete_cities" value="1" /> <?php esc_html_e( 'Delete all cities from the database', 'acf-city-selector' ); ?>
                        </span>
                    </p>

                    <input name="" type="submit" class="button button-primary"  onclick="return confirm( 'Are you sure you want to delete all cities ?' )" value="<?php esc_html_e( 'Nuke \'em', 'acf-city-selector' ); ?>" />
                    <?php //submit_button(); ?>
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

                <p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank"><img src="<?php echo plugins_url( 'assets/img/paypal_donate.gif', __FILE__ ); ?>" alt="" class="donateimg" /></a>
                    <?php esc_html_e( 'If you like this plugin, buy me a coke to show your appreciation so I can continue to develop it.', 'acf-city-selector' ); ?></p>

            </div><!-- end .admin_right -->
        </div><!-- end .wrap -->
		<?php
	}

