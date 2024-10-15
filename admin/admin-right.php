<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
?>
<div class="admin_right">
    <div class="content">
        <?php
            echo sprintf( '<h3>%s</h3>', esc_html__( 'About the plugin', 'acf-city-selector' ) );

            /* translators: %s link tag. %s acf link, %s anchor */
            echo sprintf( '<p>%s</p>', sprintf( esc_html__( 'This plugin is an extension for %s. I built it because there was no properly working plugin which offered what I needed.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/" rel="noopener" target="_blank">Advanced Custom Fields</a>' ) );

            /* translators: %s link tag. %s acf link, %s anchor */
            echo sprintf( '<p>%s</p>', sprintf( __( "%s for the plugin's official website.", 'acf-city-selector' ), sprintf( '<a href="%s" rel="noopener" target="_blank">%s</a>', ACFCS_WEBSITE_URL . '/?utm_source=wpadmin&utm_medium=about_plugin&utm_campaign=acf-plugin', __( 'Click here', 'acf-city-selector' ) ) ) );

            echo '<hr />';

            /* translators: %s support header */
            echo sprintf( '<h3>%s</h3>', esc_html__( 'Support', 'acf-city-selector' ) );

            /* translators: %s support text */
            echo sprintf( '<p>%s</p>', sprintf( esc_html__( 'If you need support for this plugin, please turn to %s.', 'acf-city-selector' ), '<a href="https://github.com/Beee4life/acf-city-selector/issues" rel="noopener" target="_blank">Github</a>' ) );

            echo '<hr />';

            /* translators: %s request header */
            echo sprintf( '<h3>%s</h3>', esc_html__( 'Requests', 'acf-city-selector' ) );

            /* translators: %s request text */
            echo sprintf( '<p>%s</p>', esc_html__( 'If you have some good suggestions for improvements and/or new features, please share them with us, maybe we can incorporate it.', 'acf-city-selector' ) );
        ?>
    </div>
</div>
