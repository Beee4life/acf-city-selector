<?php
    
    /**
     * Content for the settings page
     */
    function acfcs_preview_page() {
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sorry, you do not have sufficient permissions to access this page.', 'acf-city-selector' ) );
        }
        ?>

        <div class="wrap acfcs">
            <div id="icon-options-general" class="icon32"><br/></div>
    
            <h1>ACF City Selector</h1>
            
            <?php
                ACF_City_Selector::acfcs_show_admin_notices();

                echo ACF_City_Selector::acfcs_admin_menu();

                $delimiter  = ( isset( $_POST[ 'acfcs_delimiter' ] ) ) ? $_POST[ 'acfcs_delimiter' ] : false;
                $file_index = acfcs_check_if_files();
                $file_name  = ( isset( $_POST[ 'acfcs_file_name' ] ) ) ? $_POST[ 'acfcs_file_name' ] : false;
                $max_lines  = ( isset( $_POST[ 'acfcs_max_lines' ] ) ) ? $_POST[ 'acfcs_max_lines' ] : false;
            ?>
    
            <div class="admin_left">
                <?php if ( $file_index ) { ?>
                    <h2><?php esc_html_e( 'Preview data', 'acf-city-selector' ); ?></h2>
                    <p><?php esc_html_e( 'Here you can preview any uploaded csv files.', 'acf-city-selector' ); ?></p>
                    <p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'acf-city-selector' ); ?></p>
    
                    <div class="acfcs__section acfcs__section--preview-form">
        
                        <form name="select-preview-file" id="settings-form" action="" method="post">
                            <table class="acfcs__table acfcs__table--preview-form">
                                <thead>
                                <tr>
                                    <th><?php esc_html_e( 'File name', 'acf-city-selector' ); ?></th>
                                    <th><?php esc_html_e( 'Delimiter', 'acf-city-selector' ); ?></th>
                                    <th><?php esc_html_e( 'Max. lines', 'acf-city-selector' ); ?></th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>
                                        <label>
                                            <select name="acfcs_file_name" id="select-preview-file">
                                                <?php $posted_file = ( isset( $_POST[ 'acfcs_file_name' ] ) ) ? $_POST[ 'acfcs_file_name' ] : false; ?>
                                                <?php if ( count( $file_index ) > 1 ) { ?>
                                                    <option value=""><?php esc_html_e( 'Select a file', 'acf-city-selector' ); ?></option>
                                                <?php } ?>
                                                <?php foreach ( $file_index as $file ) { ?>
                                                    <?php $selected_file = ( $posted_file == $file ) ? ' selected' : false; ?>
                                                    <option value="<?php echo $file; ?>"<?php echo $selected_file; ?>><?php echo $file; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>
    
                                    <td>
                                        <?php $delimiters = [ ",", ";" ]; ?>
                                        <label>
                                            <select name="acfcs_delimiter" id="acfcs_delimiter">
                                                <?php foreach( $delimiters as $limiter ) { ?>
                                                    <?php $selected_delimiter = ( $delimiter == $limiter ) ? ' selected' : false; ?>
                                                    <option value="<?php echo $limiter; ?>"<?php echo $selected_delimiter; ?>><?php echo $limiter; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>
    
                                    <td class="xhidden">
                                        <?php $amounts = [ 5, 10, 25, 50, 100, 250, 500, 1000 ]; ?>
                                        <label>
                                            <select name="acfcs_max_lines" id="acfcs_max_lines">
                                                <option value=""><?php esc_html_e( 'All', 'acf-city-selector' ); ?></option>
                                                <?php foreach( $amounts as $amount ) { ?>
                                                    <?php $selected_lines = ( $max_lines == $amount ) ? ' selected' : false; ?>
                                                    <option value="<?php echo $amount; ?>"<?php echo $selected_lines; ?>><?php echo $amount; ?></option>
                                                <?php } ?>
                                            </select>
                                        </label>
                                    </td>
    
                                    <td class="submit">
                                        <input type="submit" class="button button-primary" value="<?php esc_html_e( 'View this file', 'acf-city-selector' ); ?>"/>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
    
                <?php } else { ?>
                    <p><?php esc_html_e( 'You have no files to preview.', 'acf-city-selector' ); ?></p>
                <?php } ?>
    
                <?php
                    // Get imported data
                    if ( $file_name ) {
                        $csv_info = acfcs_csv_to_array( $file_name, $delimiter, true );
                        
                        echo '<div class="acfcs__section acfcs__section--results">';
                        if ( isset( $csv_info[ 'data' ] ) && ! empty( $csv_info[ 'data' ] ) ) {
                            echo '<h2>' . __( 'CSV contents', 'acf-city-selector' ) . '</h2>';
                            echo '<table class="acfcs__table acfcs__table--preview-result">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>' . __( 'City', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . __( 'State code', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . __( 'State', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . __( 'Country code', 'acf-city-selector' ) . '</th>';
                            echo '<th>' . __( 'Country', 'acf-city-selector' ) . '</th>';
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
                            echo sprintf( __( 'You either have errors in your CSV or there is no data. Verify this file on the <a href="%s">dashboard</a>.', 'acf-city-selector' ), admin_url( 'admin.php?page=' ) . 'csv2wp-dashboard' );
                            echo '</p>';
                        }
                        echo '</div>';
                    }
                ?>
            </div>

            <?php include( 'admin-right.php' ); ?>

        </div>
        <?php
    }
