<?php
	/*
	 * Content for the settings page
	 */
	function acfcs_pro() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		acf_plugin_city_selector::acfcs_show_admin_notices();

    ?>

		<div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1><?php esc_html_e( 'ACF City Selector Settings', 'acf-city-selector' ); ?></h1>

			<?php echo acf_plugin_city_selector::acfcs_admin_menu(); ?>

            <!-- left part -->
            <div class="admin_left">

                <h2><?php esc_html_e( 'Go Pro', 'acf-city-selector' ); ?></h2>
                <p><?php esc_html_e( "Bla bla bla", 'acf-city-selector' ); ?></p>

            </div><!-- end .admin_left -->

            <?php include( 'admin-right.php' ); ?>

        </div><!-- end .wrap -->
		<?php
	}

