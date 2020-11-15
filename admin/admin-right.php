<div class="admin_right">

    <h3><?php esc_html_e( 'About the plugin', 'acf-city-selector' ); ?></h3>
    <p><?php echo sprintf( esc_html__( 'This plugin is an extension for %s. I built it because there was no properly working plugin which offered what I needed.', 'acf-city-selector' ), '<a href="https://www.advancedcustomfields.com/" rel="noopener" target="_blank">Advanced Custom Fields</a>' ); ?>
    <p><?php echo sprintf( __( '<a href="%s" rel="noopener" target="_blank">Click here</a> for the plugin\'s official website.', 'acf-city-selector' ), ACFCS_WEBSITE_URL . '/?utm_source=wpadmin&utm_medium=about_plugin&utm_campaign=acf-plugin' ); ?></p>

    <hr />

    <h3><?php esc_html_e( 'Support', 'acf-city-selector' ); ?></h3>
    <p><?php echo sprintf( esc_html__( 'If you need support for this plugin or if you have some good suggestions for improvements and/or new features, please turn to %s.', 'acf-city-selector' ), '<a href="https://github.com/Beee4life/acf-city-selector/issues" rel="noopener" target="_blank">Github</a>' ); ?></p>

    <hr />

    <p class="paypal_button">
        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL" target="_blank"><img src="<?php echo plugins_url( '/assets/img/paypal_donate.gif', dirname( __FILE__ )  ); ?>" alt="" class="acfcs__donate" /></a>
        <?php esc_html_e( 'If you like this plugin, buy me a coke to show your appreciation so I can continue to develop it.', 'acf-city-selector' ); ?>
    </p>

</div>
