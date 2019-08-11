<?php
	if ( ! function_exists( 'acfcs_donate_meta_box' ) ) {
		function acfcs_donate_meta_box() {
			if ( apply_filters( 'remove_acfcs_donate_nag', false ) ) {
				return;
			}

			$id       = 'donate-acf-cs';
			$title    = '<a style="text-decoration: none; font-size: 1em;" href="https://github.com/beee4life" target="_blank" rel="noopener">' . sprintf( esc_html__( '%s says "Thank you"', 'acf-city-selector' ), 'Beee' ) . '</a>';
			$callback = 'show_donate_meta_box';
			$screens  = array();
			$context  = 'side';
			$priority = 'low';
			add_meta_box( $id, $title, $callback, $screens, $context, $priority );

		} // end function donate_meta_box
		add_action( 'add_meta_boxes', 'acfcs_donate_meta_box' );

		function show_donate_meta_box() {
			echo '<p style="margin-bottom: 0;">' . sprintf( __( 'Thank you for installing the \'City Selector\' plugin. I hope you enjoy it. Please <a href="%s" target="_blank">consider a donation</a> if you do, so I can continue to improve it even more.', 'acf-city-selector' ), esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=24H4ULSQAT9ZL' ) ) . '</p>';
		}
	} // end if !function_exists

