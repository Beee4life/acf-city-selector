<?php
    /*
     * Content for the settings page
     */
    function acfcs_country_page() {

        if ( ! current_user_can( apply_filters( 'acfcs_user_cap', 'manage_options' ) ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $country_files    = acfcs_get_packages();
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
            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="acfcs__container">
                <div class="admin_left">
                    <div class="content">
                        <div class="acfcs__section acfcs__section--gopro">
                            <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Get countries', 'acf-city-selector' ) ); ?>
                            <?php echo sprintf( '<p>%s</p>', esc_html__( 'Default the plugin comes with 2 countries included, the Netherlands and Belgium but you might want to add more countries to choose from.', 'acf-city-selector' ) ); ?>
                            <p>
                                <?php esc_html_e( "And now you can !! We have created several 'country packages' for you to import 'as is'.", 'acf-city-selector' ); ?>
                                <?php echo sprintf( esc_html__( 'Download them %s.', 'acf-city-selector' ), sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( ACFCS_WEBSITE_URL . '/get-countries/' ), esc_html__( 'here', 'acf-city-selector' ) ) ); ?>
                            </p>
                        </div>

                        <div class="acfcs__section acfcs__section--packages">
                            <?php if ( is_array( $single_files ) && ! empty( $single_files ) ) { ?>
                                <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Country files', 'acf-city-selector' ) ); ?>
                                <?php echo sprintf( '<div class="hide400">%s</div>', esc_html__( 'Rotate your phone for a better view or scroll the list horizontally.', 'acf-city-selector' ) ); ?>

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
                                            <?php echo sprintf( '<td><img src="%s" alt="" /></td>', $flag_folder . $package[ 'country_code' ] . '.png' ); ?>
                                            <?php echo sprintf( '<td>%s</td>', $package[ 'country_name' ] ); ?>
                                            <?php echo sprintf( '<td>%s</td>', ( ! empty( $package[ 'number_states' ] ) ) ? $package[ 'number_states' ] : 'n/a' ); ?>
                                            <?php echo sprintf( '<td>%s</td>', $package[ 'number_cities' ] ); ?>
                                            <?php echo sprintf( '<td>%s</td>', ( ! empty( $package[ 'price' ] ) ) ? '&euro; ' . $package[ 'price' ] . ',00' : esc_html__( 'FREE', 'acf-city-selector' ) ); ?>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>

                            <?php if ( is_array( $country_packages ) && ! empty( $country_packages ) ) { ?>
                                <?php echo sprintf( '<h2>%s</h2>', esc_html__( 'Combined country packages', 'acf-city-selector' ) ); ?>

                                <table class="acfcs__table acfcs__table--packages">
                                    <thead>
                                    <tr>
                                        <?php echo sprintf( '<th>%s</th>', esc_html__( 'Package', 'acf-city-selector' ) ); ?>
                                        <?php echo sprintf( '<th>%s</th>', esc_html__( 'Included countries', 'acf-city-selector' ) ); ?>
                                        <?php echo sprintf( '<th>%s</th>', esc_html__( 'As separate countries', 'acf-city-selector' ) ); ?>
                                        <?php echo sprintf( '<th>%s</th>', esc_html__( 'Package price', 'acf-city-selector' ) ); ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach( $country_packages as $package ) { ?>
                                        <tr>
                                            <?php echo sprintf( '<td>%s</td>', __( $package[ 'country_name' ], 'acf-city-selector' ) ); ?>

                                            <td>
                                                <?php
                                                    if ( isset( $package[ 'included_countries' ] ) && is_array( $package[ 'included_countries' ] ) && ! empty( $package[ 'included_countries' ] ) ) {
                                                        foreach( $package[ 'included_countries' ] as $country ) {
                                                            $flag_folder  = ( isset( $package[ 'flag_folder' ] ) ) ? $package[ 'flag_folder' ] : ACFCS_WEBSITE_URL . '/flags/';
                                                            echo sprintf( '<img src="%s" alt="%s" class="flag" />', $flag_folder . $country->value . '.png', $country->value );
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

                                            <?php
                                                $price = ( ! empty( $package[ 'price' ] ) ) ? '&euro; ' . $package[ 'price' ] . ',00' : '&nbsp;';
                                                echo sprintf( '<td>%s</td>', $price );
                                            ?>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>

                            <?php echo sprintf( '<p>%s</p>', sprintf( __( "More countries will be added soon. Feel free to %s a country, if it's not available (yet).", 'acf-city-selector' ), sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( 'https://github.com/Beee4life/acf-city-selector/issues' ), __( 'request', 'acf-city-selector' ) ) ) ); ?>

                            <?php echo sprintf( '<p><a href="%s" target="_blank" rel="noopener" class="button button-primary">%s</a></p>', esc_url( ACFCS_WEBSITE_URL . '/get-countries/' ), esc_html__( 'Get your country now', 'acf-city-selector' ) ); ?>

                        </div>
                    </div>

                </div>

                <?php include 'admin-right.php'; ?>

            </div>
        </div>
        <?php
    }
