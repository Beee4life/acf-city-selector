<?php
    /*
     * Content for the settings page
     */
    function acfcs_country_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $packages = acfcs_get_packages();
        ?>


        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="admin_left">

                <div class="acfcs__section acfcs__section--gopro">
                    <h2><?php esc_html_e( 'Get countries', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( 'Default the plugin comes with 3 languages included, the Benelux; Belgium, Netherlands, Luxembourg, but you might want to add more countries to choose from. And now you can !!!', 'acf-city-selector' ); ?></p>
                    <p><?php esc_html_e( 'We have more countries available. You can buy a seperate packages for each country you need.', 'acf-city-selector' ); ?></p>
                </div>

                <div class="acfcs__section acfcs__section--packages">
                    <h2><?php esc_html_e( 'Country packages', 'acf-city-selector' ); ?></h2>

                    <table class="acfcs__table acfcs__table--packages">
                        <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php esc_html_e( 'Country', 'acf-city-selector' ); ?></th>
                            <th># <?php esc_html_e( 'States/Provinces', 'acf-city-selector' ); ?></th>
                            <th># <?php esc_html_e( 'Cities', 'acf-city-selector' ); ?></th>
                            <th><?php esc_html_e( 'Price', 'acf-city-selector' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ( is_array( $packages ) ) { ?>
                            <?php $total_price = 0; ?>
                            <?php foreach( $packages as $package ) { ?>
                                <?php $total_price = $total_price + $package->price; ?>
                                <tr>
                                    <td><img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/flags/' . $package->country_code . '.png'; ?>" alt="" /></td>
                                    <td><?php echo $package->country_name; ?></td>
                                    <td><?php echo $package->number_states; ?></td>
                                    <td><?php echo $package->number_cities; ?></td>
                                    <td>&euro; <?php echo $package->price; ?>,00</td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>

                    <p>
                        <?php echo __( "More countries will be added... Feel free to request certain countries, if they're not available yet.", 'acf-city-selector' ); ?>
                    </p>

                    <p>
                        <a href="<?php echo ACFCS_WEBSITE_URL . '/get-countries/'; ?>" target="_blank" rel="noopener" class="button button-primary">Get your country now !</a>
                    </p>

                </div>

            </div>

            <?php include( 'admin-right.php' ); ?>

        </div>
        <?php
    }
