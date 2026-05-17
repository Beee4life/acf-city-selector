<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    if ( isset( $_POST[ 'acfcs_preview_nonce' ] ) ) {
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_preview_nonce' ] ) ), 'acfcs-preview-nonce' ) ) {
            return;
        } else {
            $acfcs_limit     = 100;
            $acfcs_file_name = ( isset( $_POST[ 'acfcs_file_name' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_file_name' ] ) ) : false;
            $acfcs_max_lines = ( isset( $_POST[ 'acfcs_max_lines' ] ) ) ? (int) $_POST[ 'acfcs_max_lines' ] : $acfcs_limit;
            $acfcs_delimiter = ( isset( $_POST[ 'acfcs_delimiter' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_delimiter' ] ) ) : apply_filters( 'acfcs_delimiter', ';' );
        }
    }
?>
<p><?php esc_html_e( 'Here you can preview any uploaded csv files.', 'acf-city-selector' ); ?></p>
<p><?php esc_html_e( 'Please keep in mind that all csv files are verified before displaying (and therefor can be deleted, when errors are encountered).', 'acf-city-selector' ); ?></p>

<div class="acfcs__section acfcs__section--preview">
    <form name="select-preview-file" id="settings-form" action="" method="post">
        <input type="hidden" name="acfcs_preview_nonce" value="<?php echo esc_attr( wp_create_nonce( 'acfcs-preview-nonce' ) ); ?>" />
        <div class="acfcs__process-file">
            <div class="acfcs__process-file-element">
                <?php echo sprintf( '<label for="acfcs_file_name">%s</label>', esc_attr__( 'File', 'acf-city-selector' ) ); ?>
                <select name="acfcs_file_name" id="acfcs_file_name">
                    <?php if ( count( $file_index ) > 1 ) { ?>
                        <option value=""><?php esc_html_e( 'Select a file', 'acf-city-selector' ); ?></option>
                    <?php } ?>
                    <?php foreach ( $file_index as $acfcs_file ) { ?>
                        <?php $acfcs_curent_file = ( $acfcs_file_name == $acfcs_file ) ? ' selected="selected"' : false; ?>
                        <option value="<?php echo esc_attr( $acfcs_file ); ?>"<?php echo esc_attr( $acfcs_curent_file ); ?>><?php echo esc_html( $acfcs_file ); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="acfcs__process-file-element">
                <?php $acfcs_delimiters = [ ';', ',', '|' ]; ?>
                <?php echo sprintf( '<label for="acfcs_delimiter">%s</label>', esc_attr__( 'Delimiter', 'acf-city-selector' ) ); ?>
                <select name="acfcs_delimiter" id="acfcs_delimiter">
                    <?php foreach( $acfcs_delimiters as $acfcs_delimiter_value ) { ?>
                        <?php $acfcs_current_delimiter = ( $acfcs_delimiter_value == $acfcs_delimiter ) ? ' selected' : false; ?>
                        <option value="<?php echo esc_attr( $acfcs_delimiter_value ); ?>"<?php echo esc_attr( $acfcs_current_delimiter ); ?>><?php echo esc_html( $acfcs_delimiter_value ); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="acfcs__process-file-element">
                <?php echo sprintf( '<label for="acfcs_max_lines">%s</label>', esc_attr__( 'Max lines', 'acf-city-selector' ) ); ?>
                <input type="number" name="acfcs_max_lines" id="acfcs_max_lines" value="<?php echo esc_attr( $acfcs_max_lines ); ?>" />
            </div>
        </div>

        <div>
            <input type="submit" class="button button-primary" value="<?php esc_html_e( 'View this file', 'acf-city-selector' ); ?>"/>
        </div>
    </form>
</div>
