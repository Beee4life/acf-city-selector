<?php
    /*
     * Content for the settings page
     */
    function acfcs_info_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();

        $countries    = acfcs_get_country_info();
        $prepare_json = [];

        if ( isset( $_POST[ 'acfcs_json' ] ) ) {
            print_r(unserialize($_POST[ 'acfcs_json' ]));
        }
        ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br /></div>

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="admin_left">

                <div class="acfcs__section">
                    <h2><?php esc_html_e( 'Info', 'acf-city-selector' ); ?></h2>
                    <p>
                        <?php esc_html_e( 'This page shows real-time info about your site and settings.', 'acf-city-selector' ); ?>
                        <br />
                        <?php esc_html_e( 'We might ask for this info if support is helping you fix a problem.', 'acf-city-selector' ); ?>
                    </p>
                </div>

                <div class="acfcs__section acfcs__section--countries">
                    <?php if ( ! empty( $countries ) ) { ?>
                        <h2><?php esc_html_e( 'Countries in database', 'acf-city-selector' ); ?></h2>
                        <table class="acfcs__table acfcs__table--info">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Country name', 'acf-city-selector' ); ?></th>
                                <th><?php esc_html_e( '# cities', 'acf-city-selector' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach( $countries as $country_code => $values ) { ?>
                                <?php $prepare_json[ 'countries' ][ $country_code ] = $values[ 'name' ] . ' (' . $values[ 'count' ] . ')'; ?>
                                <tr>
                                    <td><?php echo $values[ 'name' ]; ?></td>
                                    <td><?php echo $values[ 'count' ]; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <?php $prepare_json[ 'countries' ] = 'none'; ?>
                    <?php } ?>

                    <h2><?php esc_html_e( 'Server info', 'acf-city-selector' ); ?></h2>
                    <table class="acfcs__table acfcs__table--info">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'What ?', 'acf-city-selector' ); ?></th>
                            <th><?php esc_html_e( 'Value', 'acf-city-selector' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php $prepare_json[ 'server_info' ][ 'operating_system' ] = $_SERVER[ 'SERVER_SOFTWARE' ]; ?>
                            <td><?php esc_html_e( 'Operating system', 'acf-city-selector' ); ?></td>
                            <td><?php echo $_SERVER[ 'SERVER_SOFTWARE' ]; ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'server_info' ][ 'phpversion' ] = phpversion(); ?>
                            <td><?php esc_html_e( 'PHP version', 'acf-city-selector' ); ?></td>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'server_info' ][ 'server_ip' ] = $_SERVER[ 'SERVER_ADDR' ]; ?>
                            <td><?php esc_html_e( 'Server IP', 'acf-city-selector' ); ?></td>
                            <td><?php echo $_SERVER[ 'SERVER_ADDR' ]; ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'server_info' ][ 'server_port' ] = $_SERVER[ 'SERVER_PORT' ]; ?>
                            <td><?php esc_html_e( 'Server port', 'acf-city-selector' ); ?></td>
                            <td><?php echo $_SERVER[ 'SERVER_PORT' ]; ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'server_info' ][ 'scheme' ] = $_SERVER[ 'REQUEST_SCHEME' ]; ?>
                            <td><?php esc_html_e( 'Scheme', 'acf-city-selector' ); ?></td>
                            <td><?php echo $_SERVER[ 'REQUEST_SCHEME' ]; ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'server_info' ][ 'document_root' ] = $_SERVER[ 'DOCUMENT_ROOT' ]; ?>
                            <td><?php esc_html_e( 'Home path', 'acf-city-selector' ); ?></td>
                            <td><?php echo $_SERVER[ 'DOCUMENT_ROOT' ]; ?></td>
                        </tr>
                        </tbody>
                    </table>

                    <h2><?php esc_html_e( 'WordPress info', 'acf-city-selector' ); ?></h2>
                    <table class="acfcs__table acfcs__table--info">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'What ?', 'acf-city-selector' ); ?></th>
                            <th><?php esc_html_e( 'Value', 'acf-city-selector' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'wordpress_version' ] = get_bloginfo( 'version' ); ?>
                            <td><?php esc_html_e( 'WordPress version', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_bloginfo( 'version' ); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'home_url' ] = get_home_url(); ?>
                            <td><?php esc_html_e( 'Home URL', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_home_url(); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'admin_email' ] = get_bloginfo( 'admin_email' ); ?>
                            <td><?php esc_html_e( 'Admin email', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_bloginfo( 'admin_email' ); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'blog_public' ] = get_option( 'blog_public' ); ?>
                            <td><?php esc_html_e( 'Blog public', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_option( 'blog_public' ); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'users_can_register' ] = get_option( 'users_can_register' ); ?>
                            <td><?php esc_html_e( 'Users can register', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_option( 'users_can_register' ); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'page_on_front' ] = get_option( 'page_on_front' ); ?>
                            <td><?php esc_html_e( 'Page on front', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_option( 'page_on_front' ); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'current_theme' ] = get_option( 'current_theme' ); ?>
                            <td><?php esc_html_e( 'Current theme', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_option( 'current_theme' ); ?></td>
                        </tr>
                        <?php $stylesheet = get_option( 'stylesheet' ); ?>
                        <?php $template   = get_option( 'template' ); ?>
                        <?php if ( $stylesheet != $template ) { ?>
                            <tr>
                                <?php $prepare_json[ 'wordpress_info' ][ 'stylesheet' ] = $stylesheet; ?>
                                <td><?php esc_html_e( 'Stylesheet folder', 'acf-city-selector' ); ?></td>
                                <td><?php echo $stylesheet; ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'template' ] = get_option( 'template' ); ?>
                            <td><?php esc_html_e( 'Template folder', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_option( 'template' ); ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'charset' ] = get_option( 'charset' ); ?>
                            <td><?php esc_html_e( 'Charset', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_bloginfo( 'charset' ); ?></td>
                        </tr>
                        <tr>
                            <?php $text_direction = is_rtl() ? 'RTL' : 'LTR'; ?>
                            <?php $prepare_json[ 'wordpress_info' ][ 'text_direction' ] = $text_direction; ?>
                            <td><?php esc_html_e( 'Text direction', 'acf-city-selector' ); ?></td>
                            <td><?php echo $text_direction; ?></td>
                        </tr>
                        <tr>
                            <?php $prepare_json[ 'wordpress_info' ][ 'language' ] = get_bloginfo( 'language' ); ?>
                            <td><?php esc_html_e( 'Language', 'acf-city-selector' ); ?></td>
                            <td><?php echo get_bloginfo( 'language' ); ?></td>
                        </tr>
                        </tbody>
                    </table>

                    <?php if ( is_multisite() ) { ?>
                        <h2><?php esc_html_e( 'Multisite', 'acf-city-selector' ); ?></h2>
                        <table class="acfcs__table acfcs__table--info">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'What ?', 'acf-city-selector' ); ?></th>
                                <th><?php esc_html_e( 'Value', 'acf-city-selector' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <?php $main_site = ( is_main_site() ) ? __( 'Yes', 'acf-city-selector' ) : __( 'No', 'acf-city-selector' ); ?>
                                <?php $prepare_json[ 'multisite' ][ 'main_site' ] = $main_site; ?>
                                <td><?php esc_html_e( 'Main site', 'acf-city-selector' ); ?></td>
                                <td><?php echo $main_site; ?> </td>
                            </tr>
                            <tr>
                                <?php $registration = ( get_site_option( 'registration' ) ) ? 'TRUE' : 'FALSE'; ?>
                                <?php $prepare_json[ 'multisite' ][ 'registration' ] = $registration; ?>
                                <td><?php esc_html_e( 'Main registration', 'acf-city-selector' ); ?></td>
                                <td><?php echo $registration; ?> </td>
                            </tr>
                            <?php if ( class_exists( 'B3Onboarding' ) ) { ?>
                                <?php $subsite_registration = ( get_option( 'b3_registration_type' ) ) ? 'TRUE' : 'FALSE'; ?>
                                <?php $prepare_json[ 'multisite' ][ 'subsite_registration' ] = $subsite_registration; ?>
                                <tr>
                                    <td><?php esc_html_e( 'Subsite registration', 'acf-city-selector' ); ?></td>
                                    <td><?php echo $subsite_registration; ?> </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>

                    <?php $plugins = get_plugins(); ?>
                    <h2><?php esc_html_e( 'Active plugins', 'acf-city-selector' ); ?></h2>
                    <table class="acfcs__table acfcs__table--info">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'acf-city-selector' ); ?></th>
                            <th><?php esc_html_e( 'Version', 'acf-city-selector' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $plugins as $key => $value ) { ?>
                            <?php if ( is_plugin_active( $key ) ) { ?>
                                <?php $prepare_json[ 'plugins' ][] = [ 'name' => $value[ 'Name' ], 'version' => $value[ 'Version' ], 'author' => $value[ 'Author' ], 'author_uri' => $value[ 'AuthorURI' ] ]; ?>
                                <tr>
                                    <td>
                                        <?php echo $value[ 'Name' ]; ?>
                                    </td>
                                    <td>
                                        <?php echo $value[ 'Version' ]; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="acfcs__section acfcs__section--export">
                    <h2><?php esc_html_e( 'Download JSON', 'acf-city-selector' ); ?></h2>
                    <p>
                        <?php esc_html_e( "If you're in need of support, the info above might helpful for us to fix a problem.", 'acf-city-selector' ); ?>
                        <br />
                        <?php esc_html_e( 'You can download the settings to a JSON file below (and send it to us when asked).', 'acf-city-selector' ); ?>

                    </p>
                    <?php $file_name       = wp_upload_dir()[ 'basedir' ] . '/acfcs/debug.json'; ?>
                    <?php $serialized_json = json_encode( $prepare_json ); // encode json before saving ?>
                    <?php file_put_contents( $file_name, $serialized_json ); // write to file ?>
                    <form name="acfcs_export_json" method="POST" action="<?php echo plugin_dir_url(__FILE__); ?>acfcs-save-json.php">
                        <input type="hidden" name="acfcs_export_json" value="1" />
                        <input type="hidden" name="acfcs_json_file" value="<?php echo $file_name; ?>" />
                        <input type="submit" class="button button-primary" name="" value="<?php echo esc_html__( 'Download JSON file', 'acf-city-selector' ); ?>" />
                    </form>
                </div>

            </div>

            <?php include 'admin-right.php'; ?>

        </div>
        <?php
    }
