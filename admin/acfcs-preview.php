<?php

    /**
     * Content for the settings page
     */
    function acfcs_preview_page() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'acf-city-selector' ) );
        }

        ACF_City_Selector::acfcs_show_admin_notices();
        ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br/></div>

            <h1>ACF City Selector</h1>

            <?php
                echo ACF_City_Selector::acfcs_admin_menu();

                $file_index = acfcs_check_if_files();
                $file_name  = ( isset( $_POST[ 'acfcs_file_name' ] ) ) ? $_POST[ 'acfcs_file_name' ] : false;
                $max_lines  = ( isset( $_POST[ 'acfcs_max_lines' ] ) ) ? $_POST[ 'acfcs_max_lines' ] : 100;
            ?>

            <div class="admin_left">
                <?php if ( ! empty( $file_index ) ) { ?>
                    <h2><?php esc_html_e( 'Preview data', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( 'Here you can preview any uploaded csv files.', 'acf-city-selector' ); ?></p>
                    <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'acf-city-selector' ); ?></p>

                    <div class="acfcs__section acfcs__section--preview">

                        <form name="select-preview-file" id="settings-form" action="" method="post">
                            <div class="acfcs__process-file">
                                <div class="acfcs__process-file-element">
                                    <label for="acfcs_file_name">
                                        <?php esc_html_e( 'File', 'acf-city-selector' ); ?>
                                    </label>
                                    <select name="acfcs_file_name" id="acfcs_file_name">
                                        <?php if ( count( $file_index ) > 1 ) { ?>
                                            <option value=""><?php esc_html_e( 'Select a file', 'acf-city-selector' ); ?></option>
                                        <?php } ?>
                                        <?php foreach ( $file_index as $file ) { ?>
                                            <?php $selected = ( isset( $_POST[ 'acfcs_file_name' ] ) && $_POST[ 'acfcs_file_name' ] == $file ) ? ' selected="selected"' : false; ?>
                                            <option value="<?php echo $file; ?>"<?php echo $selected; ?>><?php echo $file; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="acfcs__process-file-element">
                                    <?php $delimiters = [ ',', ';', '|' ]; ?>
                                    <label for="acfcs_delimiter">
                                        <?php esc_html_e( 'Delimiter', 'acf-city-selector' ); ?>
                                    </label>
                                    <select name="acfcs_delimiter" id="acfcs_delimiter">
                                        <?php foreach( $delimiters as $delimiter ) { ?>
                                            <?php $selected_delimiter = ( $delimiter == apply_filters( 'acfcs_delimiter', ',' ) ) ? ' selected' : false; ?>
                                            <option value="<?php echo $delimiter; ?>"<?php echo $selected_delimiter; ?>><?php echo $delimiter; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="acfcs__process-file-element">
                                    <label for="acfcs_max_lines">
                                        <?php esc_html_e( 'Max lines', 'acf-city-selector' ); ?>
                                    </label>
                                    <input type="number" name="acfcs_max_lines" id="acfcs_max_lines" value="<?php echo $max_lines; ?>" />
                                </div>
                            </div>

                            <div>
                                <input type="submit" class="button button-primary" value="<?php esc_html_e( 'View this file', 'acf-city-selector' ); ?>"/>
                            </div>
                        </form>
                    </div>

                <?php } else { ?>
                    <p>
                        <?php esc_html_e( 'You have no files to preview.', 'acf-city-selector' ); ?>
                    </p>
                <?php } ?>

                <?php
                    // Get imported data
                    if ( $file_name ) {
                        $delimiter = ( isset( $_POST[ 'acfcs_delimiter' ] ) ) ? $_POST[ 'acfcs_delimiter' ] : apply_filters( 'acfcs_delimiter', ',' );
                        $csv_info  = acfcs_csv_to_array( $file_name, $delimiter, true );

                        echo '<div class="acfcs__section acfcs__section--results">';
                        if ( isset( $csv_info[ 'data' ] ) && ! empty( $csv_info[ 'data' ] ) ) {
                            echo '<h2>' . esc_html__( 'CSV contents', 'acf-city-selector' ) . '</h2>';
                            echo '<p class="hide640"><small>' . esc_html__( 'Table scrolls horizontally.', 'acf-city-selector' ) . '</small></p>';
                            echo '<table class="acfcs__table acfcs__table--preview-result scrollable">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>' . esc_html__( 'City', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . esc_html__( 'State code', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . esc_html__( 'State', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . esc_html__( 'Country code', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . esc_html__( 'Country', 'acf-city-selector' ) . '</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            $line_number = 0;
                            foreach ( $csv_info[ 'data' ] as $line ) {
                                $line_number++;
                                echo '<tr>';
                                foreach ( $line as $column ) {
                                    echo '<td>';
                                    echo stripslashes( htmlspecialchars( $column ) );
                                    echo '</td>';
                                }
                                echo '</tr>';
                                if ( $line_number == $max_lines ) {
                                    break;
                                }
                            }
                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo '<p class="error_notice">';
                            echo sprintf( esc_html__( 'You either have errors in your CSV or there is no data. Verify this file on the <a href="%s">dashboard</a>.', 'acf-city-selector' ), admin_url( 'admin.php?page=' ) . 'csv2wp-dashboard' );
                            echo '</p>';
                        }
                        echo '</div>';
                    }
                ?>
            </div>

            <?php include 'admin-right.php'; ?>

        </div>
        <?php
    }
