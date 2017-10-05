<?php
	/*
	 * Content for the settings page
	 */
	function acfcs_settings() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		ACF_City_Selector::acfcs_show_admin_notices();

		?>

		<div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1><?php esc_html_e( 'ACF City Selector Settings', 'acf-city-selector' ); ?></h1>

			<?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <!-- left part -->
            <div class="admin_left">

                <form method="post" action="">
                    <input name="import_actions_nonce" value="<?php echo wp_create_nonce( 'import-actions-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Import countries', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "Here you can (re-)import all cities for the individual countries listed below.", 'acf-city-selector' ); ?></p>
                    <p>
                        <label for="import_be" class="screen-reader-text"></label>
                        <input type="checkbox" name="import_be" id="import_be" value="1" /> <?php esc_html_e( 'Import all cities in Belgium', 'acf-city-selector' ); ?> (1166)
                    </p>
                    <p>
                        <label for="import_lux" class="screen-reader-text"></label>
                        <input type="checkbox" name="import_lux" id="import_lux" value="1" /> <?php esc_html_e( 'Import all cities in Luxembourg', 'acf-city-selector' ); ?> (12)
                    </p>
                    <p>
                        <label for="import_nl" class="screen-reader-text"></label>
                        <input type="checkbox" name="import_nl" id="import_nl" value="1" /> <?php esc_html_e( 'Import all cities in Holland/The Netherlands', 'acf-city-selector' ); ?> (2449)
                    </p>
                    <input name="" type="submit" class="button button-primary" value="<?php esc_html_e( 'Import selected countries', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />

                <form method="post" action="">
                    <input name="truncate_table_nonce" value="<?php echo wp_create_nonce( 'truncate-table-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Clear the database', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "By selecting this option, you will remove all cities, which are present in the database. This is handy if you don't need the preset cities or you want a fresh start.", 'acf-city-selector' ); ?></p>
                    <p>
                        <label for="delete_cities" class="screen-reader-text"></label>
                        <input type="checkbox" name="delete_cities" id="delete_cities" value="1" /> <?php esc_html_e( 'Delete all cities from the database', 'acf-city-selector' ); ?>
                    </p>
                    <input name="" type="submit" class="button button-primary"  onclick="return confirm( 'Are you sure you want to delete all cities ?' )" value="<?php esc_html_e( 'Nuke \'em', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />

                <form method="post" action="">
                    <input name="preserve_settings_nonce" value="<?php echo wp_create_nonce( 'preserve-settings-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Save data', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "When the plugin is deleted, all settings and cities are deleted as well. Select this option to preserve this data upon deletion.", 'acf-city-selector' ); ?></p>
                    <?php $checked = get_option( 'acfcs_preserve_settings' ) ? ' checked="checked"' : false; ?>
                    <p>
                        <span class="acfcs_input">
                            <label for="preserve_settings" class="screen-reader-text"></label>
                            <input type="checkbox" name="preserve_settings" id="preserve_settings" value="1" <?php echo $checked; ?>/> <?php esc_html_e( 'Preserve settings on plugin deletion', 'acf-city-selector' ); ?>
                        </span>
                    </p>
                    <input name="" type="submit" class="button button-primary" value="<?php esc_html_e( 'Save settings', 'acf-city-selector' ); ?>" />
                </form>

            </div><!-- end .admin_left -->

			<?php include( 'admin-right.php' ); ?>

        </div><!-- end .wrap -->
		<?php
	}

