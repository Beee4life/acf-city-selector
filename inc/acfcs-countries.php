<?php
    /*
     * Content for the settings page
     */
    function acfcs_country_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $country_files    = acfcs_get_packages( 'single' );
        $country_packages = [];
        $country_packs    = acfcs_get_packages( 'packages' );
        $single_files     = [];

        if ( is_array( $country_files ) ) {
            foreach( $country_files as $single_file ) {
                $single_file                   = (array) $single_file;
                $single_file[ 'country_name' ] = __( $single_file[ 'country_name' ], 'acf-city-selector' );
                $single_files[]                = $single_file;
            }
            if ( ! empty( $single_files ) ) {
                $country_name = array_column( $single_files, 'country_name' );
                array_multisort( $country_name, SORT_ASC, $single_files );
            }
        }
        if ( is_array( $country_packs ) ) {
            foreach( $country_packs as $country_package ) {
                $country_package                   = (array) $country_package;
                $country_package[ 'country_name' ] = __( $country_package[ 'country_name' ], 'acf-city-selector' );
                $country_packages[]                = $country_package;
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
                        <?php esc_html_e( 'Default the plugin comes with 2 countries included, Belgium and the Netherlands but you might want to add more countries to choose from.', 'acf-city-selector' ); ?>
                    </p>
                    <p>
                        <?php esc_html_e( 'And now you can !! We have created several \'country packages\' for you to import as is.', 'acf-city-selector' ); ?>
                    </p>
                </div>

                <div class="acfcs__section acfcs__section--packages">
                    <?php if ( is_array( $single_files ) && ! empty( $single_files ) ) { ?>
                        <h2>
                            <?php esc_html_e( 'Country files', 'acf-city-selector' ); ?>
                        </h2>

                        <div class="hide400">
                            <?php esc_html_e( 'Rotate your phone for a better view or scroll the list horizontally.', 'acf-city-selector' ); ?>
                        </div>

                        <table class="acfcs__table acfcs__table--packages scrollable">
                            <thead>
                            <tr>
                                <th colspan="2"><?php esc_html_e( 'Country', 'acf-city-selector' ); ?></th>
                                <th># <?php esc_html_e( 'States/Provinces', 'acf-city-selector' ); ?></th>
                                <th># <?php esc_html_e( 'Cities', 'acf-city-selector' ); ?></th>
                                <th><?php esc_html_e( 'Price', 'acf-city-selector' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $total_price = 0; ?>
                            <?php foreach( $single_files as $package ) { ?>
                                <?php $total_price = ( ! empty( $package[ 'price' ] ) ) ? $total_price + $package[ 'price' ] : $total_price; ?>
                                <?php if ( file_exists( ACFCS_PLUGIN_PATH . 'assets/img/flags/' . $package[ 'country_code' ] . '.png' ) && ( isset( $package[ 'active' ] ) && true == $package[ 'active' ] ) ) { ?>
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
                                                    echo sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', ACFCS_WEBSITE_URL . '/get-countries/?utm_source=wpadmin&utm_medium=free_download&utm_campaign=acf-plugin', __( 'Free', 'acf-city-selector' ) );
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>

                    <?php if ( is_array( $country_packages ) && ! empty( $country_packages ) ) { ?>

                        <h2><?php esc_html_e( 'Combined country packages', 'acf-city-selector' ); ?></h2>

                        <table class="acfcs__table acfcs__table--packages">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Package', 'acf-city-selector' ); ?></th>
                                <th><?php esc_html_e( 'Included countries', 'acf-city-selector' ); ?></th>
                                <th><?php esc_html_e( 'Price', 'acf-city-selector' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $total_price = 0; ?>
                            <?php foreach( $country_packages as $package ) { ?>
                                <?php $total_price = ( ! empty( $package[ 'price' ] ) ) ? $total_price + $package[ 'price' ] : $total_price; ?>
                                <tr>
                                    <td>
                                        <?php echo __( $package[ 'country_name' ], 'acf-city-selector' ); ?>
                                    </td>

                                    <td>
                                        <?php
                                            if ( isset( $package[ 'included_countries' ] ) && is_array( $package[ 'included_countries' ] ) && ! empty( $package[ 'included_countries' ] ) ) {
                                                foreach( $package[ 'included_countries' ] as $country ) {
                                                    echo '<img src="' . ACFCS_PLUGIN_URL . 'assets/img/flags/' . $country->value . '.png" alt="' . $country->value . '" class="flag" />';
                                                }
                                            }
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                            if ( ! empty( $package[ 'price' ] ) ) {
                                                echo '&euro; ' . $package[ 'price' ] . ',00';
                                            } else {
                                                echo sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', ACFCS_WEBSITE_URL . '/get-countries/?utm_source=wpadmin&utm_medium=free_download&utm_campaign=acf-plugin', __( 'Free', 'acf-city-selector' ) );
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>

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
