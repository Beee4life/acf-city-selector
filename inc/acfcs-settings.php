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

            <h1>ACF City Selector</h1>

            <?php echo ACF_City_Selector::acfcs_admin_menu(); ?>

            <div class="admin_left">

                <form method="post" action="">
                    <input name="acfcs_import_actions_nonce" value="<?php echo wp_create_nonce( 'acfcs-import-actions-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Import countries', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "Here you can (re-)import all cities for the individual countries listed below.", 'acf-city-selector' ); ?></p>

                    <ul>
                        <li>
                            <label for="import_be" class="screen-reader-text"></label>
                            <input type="checkbox" name="import_be" id="import_be" value="1" /> <?php esc_html_e( 'Import all cities in Belgium', 'acf-city-selector' ); ?> (1166)
                        </li>
                        <li>
                            <label for="import_nl" class="screen-reader-text"></label>
                            <input type="checkbox" name="import_nl" id="import_nl" value="1" /> <?php esc_html_e( 'Import all cities in Holland/The Netherlands', 'acf-city-selector' ); ?> (2449)
                        </li>
                    </ul>

                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Import selected countries', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />

                <?php $countries = acfcs_get_countries( false, false, true ); ?>
                <?php if ( ! empty( $countries ) ) { ?>
                <h2><?php esc_html_e( 'Remove countries', 'acf-city-selector' ); ?></h2>
                <form method="post" action="">
                    <input name="acfcs_remove_countries_nonce" value="<?php echo wp_create_nonce( 'acfcs-remove-countries-nonce' ); ?>" type="hidden" />
                    <p><?php esc_html_e( "Here you can remove a country and all its states and cities from the database.", 'acf-city-selector' ); ?></p>
                    <ul>
                        <?php foreach( $countries as $key => $value ) { ?>
                            <li>
                                <label for="delete_<?php echo strtolower( $key ); ?>" class="screen-reader-text"></label>
                                <input type="checkbox" name="delete_country[]" id="delete_<?php echo strtolower( $key ); ?>" value="<?php echo strtolower( $key ); ?>" /> <?php esc_html_e( $value, 'acf-city-selector' ); ?>
                            </li>
                        <?php } ?>
                    </ul>
                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Delete selected countries', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />
                <?php } ?>

                <form method="post" action="">
                    <input name="acfcs_delete_transients" value="<?php echo wp_create_nonce( 'acfcs-delete-transients-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Delete transients', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "If you're seeing unexpected results in your dropdowns, try clearing all transients with this option.", 'acf-city-selector' ); ?></p>
                    <input type="submit" class="button button-primary" value="<?php esc_html_e( "Delete transients", 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />

                <form method="post" action="">
                    <input name="acfcs_truncate_table_nonce" value="<?php echo wp_create_nonce( 'acfcs-truncate-table-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Clear the database', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( "By selecting this option, you will remove all cities, which are present in the database. This is useful if you don't need the preset cities or you want a fresh start.", 'acf-city-selector' ); ?></p>
                    <input type="submit" class="button button-primary"  onclick="return confirm( 'Are you sure you want to delete all cities ?' )" value="<?php esc_html_e( 'Delete everything', 'acf-city-selector' ); ?>" />
                </form>

                <br /><hr />

                <form method="post" action="">
                    <input name="acfcs_preserve_settings_nonce" value="<?php echo wp_create_nonce( 'acfcs-preserve-settings-nonce' ); ?>" type="hidden" />
                    <h2><?php esc_html_e( 'Save data', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( 'When the plugin is deleted, all settings and cities are deleted as well. Select this option to preserve this data upon deletion.', 'acf-city-selector' ); ?></p>
                    <?php $checked = get_option( 'acfcs_preserve_settings' ) ? ' checked="checked"' : false; ?>
                    <ul>
                        <li>
                            <span class="acfcs_input">
                                <label for="preserve_settings" class="screen-reader-text"></label>
                                <input type="checkbox" name="preserve_settings" id="preserve_settings" value="1" <?php echo $checked; ?>/> <?php esc_html_e( 'Preserve settings on plugin deletion', 'acf-city-selector' ); ?>
                            </span>
                        </li>
                    </ul>
                    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Save settings', 'acf-city-selector' ); ?>" />
                </form>
            </div>

            <?php include 'admin-right.php'; ?>

        </div>
        <?php
    }

