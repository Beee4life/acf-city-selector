<?php
    /*
     * Content for the settings page
     */
    function acfcs_country_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $packages      = [];
        $rest_packages = acfcs_get_packages();
        if ( is_array( $rest_packages ) ) {
            foreach( $rest_packages as $package ) {
                $package                   = (array) $package;
                $package[ 'country_name' ] = __( $package[ 'country_name' ], 'acf-city-selector' );
                $packages[]                = $package;
            }
            if ( ! empty( $packages ) ) {
                usort( $packages, function( $a, $b ) {
                    return $a[ 'country_name' ] <=> $b[ 'country_name' ];
                } );
            }
        }
        ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="admin_left">

                <div class="acfcs__section acfcs__section--gopro">
                    <h2><?php esc_html_e( 'Get countries', 'acf-city-selector' ); ?></h2>
                    <p>
                        <?php esc_html_e( 'Default the plugin comes with 4 countries included, the Benelux (Belgium, Netherlands, Luxembourg) and Andorra but you might want to add more countries to choose from.', 'acf-city-selector' ); ?>
                    </p>
                    <p>
                        <?php esc_html_e( 'And now you can !! We have created several \'country packages\' for you to import as is.', 'acf-city-selector' ); ?>
                    </p>
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
                                <?php $total_price = ( ! empty( $package[ 'price' ] ) ) ? $total_price + $package[ 'price' ] : $total_price; ?>
                                <tr>
                                    <td><img src="<?php echo ACFCS_PLUGIN_URL . 'assets/img/flags/' . $package[ 'country_code' ] . '.png'; ?>" alt="" /></td>
                                    <td><?php echo __( $package[ 'country_name' ], 'acf-city-selector' ); ?></td>
                                    <td>
                                        <?php
                                            if ( ! empty( $package[ 'number_states' ] ) ) {
                                                echo $package[ 'number_states' ];
                                            } else {
                                                echo 'n/a';
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo $package[ 'number_cities' ]; ?></td>
                                    <td>
                                        <?php
                                            if ( ! empty( $package[ 'price' ] ) ) {
                                                echo '&euro; ' . $package[ 'price' ] . ',00';
                                            } else {
                                                echo sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', ACFCS_WEBSITE_URL . '/get-countries/?utm_source=wpadmin&utm_medium=free_download&utm_campaign=acf-plugin', __( 'Free' ) );
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>

                    <p>
                        <?php echo sprintf( __( 'More countries will be added soon. Feel free to <a href="%s" target="_blank" rel="noopener">request</a> a country, if it\'s not available (yet).', 'acf-city-selector' ), esc_url( 'https://github.com/Beee4life/acf-city-selector/issues' ) ); ?>
                    </p>

                    <p>
                        <a href="<?php echo ACFCS_WEBSITE_URL . '/get-countries/'; ?>" target="_blank" rel="noopener" class="button button-primary"><?php echo __( 'Get your country now', 'acf-city-selector' ); ?> !</a>
                    </p>

                </div>

            </div>

            <?php include 'admin-right.php'; ?>

        </div>
        <?php
    }
