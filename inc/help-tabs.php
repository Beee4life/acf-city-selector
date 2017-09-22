<?php

	/**
	 * Add help tabs
	 *
	 * @param $old_help  string
	 * @param $screen_id int
	 * @param $screen    object
	 */
	function acfcs_help_tabs( $old_help, $screen_id, $screen ) {

		// echo '<pre>'; var_dump($screen_id); echo '</pre>'; exit;

		$screen_array = array(
			'settings_page_acfcs-options',
			'settings_page_acfcs-settings',
		);
		if ( ! in_array( $screen_id, $screen_array ) ) {
			return false;
		}

		if ( 'settings_page_acfcs-options' == $screen_id ) {
			$screen->add_help_tab( array(
				'id'      => 'logs-overview',
				'title'   => esc_html__( 'Import data', 'action-logger' ),
				'content' =>
					'<h5>Import cities</h5>
					<p>' . esc_html__( 'On this page you can import cities. You can select cities from The Netherlands, Belgium and Luxembourg which come included in the plugin.', 'action-logger' ) . '</p>
					<p>' . esc_html__( 'You can also import raw csv data, but this has to be formatted (and ordered) in a certain way, otherwise it won\'t work.', 'action-logger' ) . '</p>
					<p>' . esc_html__( 'The required order is "City,State code,State,Country code,Country".', 'action-logger' ) . '</p>
					<table class="" cellpadding="0" cellspacing="0">
					<thead>
					<tr>
					<th>' . esc_html__( 'Field', 'action-logger' ) . '</th>
					<th>' . esc_html__( 'What to enter', 'action-logger' ) . '</th>
					<th>' . esc_html__( 'Note', 'action-logger' ) . '</th>
					</tr>					
					</thead>
					<tbody>
					<tr>
					<td>' . esc_html__( 'City', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'full name', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'no use of double quotes/".', 'action-logger' ) . '</td>
					</tr>
					<tr>
					<td>' . esc_html__( 'State code', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'state abbreviation', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'exactly 2 characters', 'action-logger' ) . '</td>
					</tr>
					<tr>
					<td>' . esc_html__( 'State', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'full state name', 'action-logger' ) . '</td>
					<td>&nbsp;</td>
					</tr>
					<tr>
					<td>' . esc_html__( 'Country code', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'country abbreviation', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'exactly 2 characters', 'action-logger' ) . '</td>
					</tr>
					<tr>
					<td>' . esc_html__( 'Country', 'action-logger' ) . '</td>
					<td>' . esc_html__( 'full country name', 'action-logger' ) . '</td>
					<td>&nbsp;</td>
					</tr>
					</tbody>
					</table>'
			) );

		}

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . esc_html__( 'Author\'s website', 'action-logger' ) . '</strong></p>' .
			'<p><a href="http://www.berryplasman.com?utm_source=' . $_SERVER[ 'SERVER_NAME' ] . '&utm_medium=plugin_admin&utm_campaign=free_promo">berryplasman.com</a></p>'
		);

		return $old_help;
	}
	add_filter( 'contextual_help', 'acfcs_help_tabs', 5, 3 );
