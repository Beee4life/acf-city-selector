<?php
    /*
     * Content for the settings page
     */
    function acfcs_go_pro() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

    ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>
    
            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>
            
            <?php $show_pro_subscription = true; ?>
    
            <div class="admin_left">
    
                <div class="acfcs__section">
                    <h2><?php esc_html_e( 'Go Pro', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "Default the plugin comes with 3 languages included, namely the Benelux; Belgium, Netherlands, Luxembourg, but you might want to add more countries to choose from. And now you can !!!", 'acf-city-selector' ); ?></p>
                    <p><?php esc_html_e( "We have more countries available. You can either buy a seperate country packages or get a Pro subscription and get every new update when we'll make a new country available.", 'acf-city-selector' ); ?></p>
                </div>
    
                <?php if ( defined( 'WP_TESTING' ) && WP_TESTING == 1 && false != $show_pro_subscription ) { ?>
                    <div class="acfcs__section acfcs__section--subscription">
                        <h2><?php esc_html_e( 'Pro subscription', 'acf-city-selector' ); ?></h2>
                        <p><?php esc_html_e( "Buy once and get all coming countries for free !!! One price for all packages.", 'acf-city-selector' ); ?></p>
                        <p><?php esc_html_e( "Order now !!!", 'acf-city-selector' ); ?></p>
                        <?php // @TODO: add link to acfcs site ?>
                    </div>
                <?php } ?>
    
                <div class="acfcs__section">
                    <?php
                        $packages = [
                            [
                                'country' => __( 'Canada', 'acf-city-selector' ),
                                'states'  => '',
                                'cities'  => 3018,
                                'price'   => '',
                                'link'    => '#',
                            ],
                            [
                                'country' => __( 'France', 'acf-city-selector' ),
                                'states'  => '',
                                'cities'  => 13529,
                                'price'   => '',
                                'link'    => '#',
                            ],
                            [
                                'country' => __( 'Germany', 'acf-city-selector' ),
                                'states'  => '',
                                'cities'  => 9716,
                                'price'   => '',
                                'link'    => '#',
                            ],
                            [
                                'country' => __( 'Switzerland', 'acf-city-selector' ),
                                'states'  => '',
                                'cities'  => 1528,
                                'price'   => '',
                                'link'    => '#',
                            ],
                        ];
                    ?>
                    
                    <h2><?php esc_html_e( 'Country packages', 'acf-city-selector' ); ?></h2>
    
                    <table class="acfcs__table acfcs__table--packages">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Country', 'acf-city-selector' ); ?></th>
                            <th># <?php esc_html_e( 'States/Provinces', 'acf-city-selector' ); ?></th>
                            <th># <?php esc_html_e( 'Cities', 'acf-city-selector' ); ?></th>
                            <th><?php esc_html_e( 'Price', 'acf-city-selector' ); ?></th>
                            <th><?php esc_html_e( 'Order', 'acf-city-selector' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $packages as $package ) { ?>
                            <tr>
                                <td><?php echo $package[ 'country' ]; ?></td>
                                <td>#</td>
                                <td><?php echo $package[ 'cities' ]; ?></td>
                                <td>$ xx,-</td>
                                <td><a href="<?php echo $package[ 'link' ]; ?>">link</a></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php include( 'admin-right.php' ); ?>
            
        </div>
        <?php
    }

