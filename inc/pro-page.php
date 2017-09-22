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
                <p><?php esc_html_e( "Default the plugin comes with 3 languages included, namely the Benelux; Belgium, Netherlands, Luxembourg, but you might want to add more countries to choose from. And now you can !!!", 'acf-city-selector' ); ?></p>
                <p><?php esc_html_e( "We have more countries available. You can either buy a seperate country packages or get a Pro subscription and get every new update when we'll make a new country available.", 'acf-city-selector' ); ?></p>

                <hr />

                <h2><?php esc_html_e( 'Pro subscription', 'acf-city-selector' ); ?></h2>
                <p><?php esc_html_e( "Buy once and get all coming countries for free !!! One price for all packages.", 'acf-city-selector' ); ?></p>
                <p><?php esc_html_e( "Order now !!!", 'acf-city-selector' ); ?></p>

                <hr />

                <h2><?php esc_html_e( 'Country packages', 'acf-city-selector' ); ?></h2>

                <table cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th>Country</th>
                        <th># states/provences</th>
                        <th># cities</th>
                        <th>Price</th>
                        <th>Order</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>[country]</td>
                        <td>#</td>
                        <td>#</td>
                        <td>$ xx,-</td>
                        <td><a href="">link</a></td>
                    </tr>
                    </tbody>
                </table>


            </div><!-- end .admin_left -->

            <?php include( 'admin-right.php' ); ?>

        </div><!-- end .wrap -->
		<?php
	}

