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
        $country_packages = array();
        $country_packs    = acfcs_get_packages( 'packages' );
        $europe_price     = 0;
        $noram_price      = 0;
        $single_files     = array();
        $total_price      = 0;

        if ( is_array( $country_files ) ) {
            foreach( $country_files as $single_file ) {
                $single_file                   = (array) $single_file;
                $single_file[ 'country_name' ] = esc_attr__( $single_file[ 'country_name' ], 'acf-city-selector' );
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
                $country_package[ 'country_name' ] = esc_attr__( $country_package[ 'country_name' ], 'acf-city-selector' );
                $country_packages[]                = $country_package;
            }
        }
        ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="acfcs__container">
                <div class="admin_left">

                    <div class="acfcs__section acfcs__section--gopro">
                        <h2>
                            <?php esc_html_e( 'Get countries', 'acf-city-selector' ); ?>
                        </h2>
                        <p>
                            <?php esc_html_e( 'Default the plugin comes with 2 countries included, the Netherlands and Belgium but you might want to add more countries to choose from.', 'acf-city-selector' ); ?>
                        </p>
                        <p>
                            <?php esc_html_e( 'And now you can !! We have created several \'country packages\' for you to import as is.', 'acf-city-selector' ); ?>
                            <?php echo sprintf( __( 'Download them <a href="%s">%s</a>.', 'acf-city-selector' ), esc_url( ACFCS_WEBSITE_URL . '/get-countries/' ), 'here' ); ?>
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
                                <?php foreach( $single_files as $package ) { ?>
                                    <?php
                                        $europe_price = ( isset( $package[ 'continent' ] ) && 'europe' == $package[ 'continent' ] && ! empty( $package[ 'price' ] ) ) ? $europe_price + $package[ 'price' ] : $europe_price;
                                        $noram_price  = ( isset( $package[ 'continent' ] ) && 'noram' == $package[ 'continent' ] && ! empty( $package[ 'price' ] ) ) ? $noram_price + $package[ 'price' ] : $noram_price;
                                        $total_price  = ( ! empty( $package[ 'price' ] ) ) ? $total_price + $package[ 'price' ] : $total_price;
                                        $flag_folder  = ( isset( $package[ 'flag_folder' ] ) ) ? $package[ 'flag_folder' ] : ACFCS_WEBSITE_URL . '/flags/';
                                    ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo $flag_folder . $package[ 'country_code' ] . '.png'; ?>" alt="" />
                                        </td>

                                        <td>
                                            <?php echo $package[ 'country_name' ]; ?>
                                        </td>

                                        <td>
                                            <?php
                                                if ( ! empty( $package[ 'number_states' ] ) ) {
                                                    echo $package[ 'number_states' ];
                                                } else {
                                                    echo 'n/a';
                                                }
                                            ?>
                                        </td>

                                        <td>
                                            <?php echo $package[ 'number_cities' ]; ?>
                                        </td>

                                        <td>
                                            <?php
                                                if ( ! empty( $package[ 'price' ] ) ) {
                                                    echo '&euro; ' . $package[ 'price' ] . ',00';
                                                } else {
                                                    _e( 'FREE', 'acf-city-selector' );
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>

                        <?php if ( is_array( $country_packages ) && ! empty( $country_packages ) ) { ?>

                            <h2>
                                <?php esc_html_e( 'Combined country packages', 'acf-city-selector' ); ?>
                            </h2>

                            <table class="acfcs__table acfcs__table--packages">
                                <thead>
                                <tr>
                                    <th>
                                        <?php esc_html_e( 'Package', 'acf-city-selector' ); ?>
                                    </th>
                                    <th>
                                        <?php esc_html_e( 'Included countries', 'acf-city-selector' ); ?>
                                    </th>
                                    <th>
                                        <?php esc_html_e( 'As separate countries', 'acf-city-selector' ); ?>
                                    </th>
                                    <th>
                                        <?php esc_html_e( 'Package price', 'acf-city-selector' ); ?>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach( $country_packages as $package ) { ?>
                                    <tr>
                                        <td>
                                            <?php _e( $package[ 'country_name' ], 'acf-city-selector' ); ?>
                                        </td>

                                        <td>
                                            <?php
                                                if ( isset( $package[ 'included_countries' ] ) && is_array( $package[ 'included_countries' ] ) && ! empty( $package[ 'included_countries' ] ) ) {
                                                    foreach( $package[ 'included_countries' ] as $country ) {
                                                        $flag_folder  = ( isset( $package[ 'flag_folder' ] ) ) ? $package[ 'flag_folder' ] : ACFCS_WEBSITE_URL . '/flags/';
                                                        echo '<img src="' . $flag_folder . $country->value . '.png" alt="' . $country->value . '" class="flag" />';
                                                    }
                                                }
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                                if ( ! empty( $package[ 'price' ] ) ) {
                                                    if ( 'europe' == $package[ 'package_code' ] ) {
                                                        $individual_price = $europe_price;
                                                    } elseif ( 'noram' == $package[ 'package_code' ] ) {
                                                        $individual_price = $noram_price;
                                                    } elseif ( 'world' == $package[ 'package_code' ] ) {
                                                        $individual_price = $total_price;
                                                    }
                                                    if ( isset( $individual_price ) ) {
                                                        echo '&euro; ' . number_format( $individual_price, 2, ',', '' );
                                                    }
                                                } else {
                                                    echo '&nbsp;';
                                                }
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                                if ( ! empty( $package[ 'price' ] ) ) {
                                                    echo '&euro; ' . $package[ 'price' ] . ',00';
                                                } else {
                                                    echo '&nbsp;';
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
                            <a href="<?php echo ACFCS_WEBSITE_URL . '/get-countries/'; ?>" target="_blank" rel="noopener" class="button button-primary">
                                <?php esc_html_e( 'Get your country now', 'acf-city-selector' ); ?> !
                            </a>
                        </p>

                    </div>

                </div>

                <?php include 'admin-right.php'; ?>

            </div>
        </div>
        <?php
    }
