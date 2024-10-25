<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
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
                    <?php foreach ( $file_index as $file ) { ?>
                        <?php $selected = ( $file_name == $file ) ? ' selected="selected"' : false; ?>
                        <option value="<?php echo esc_attr( $file ); ?>"<?php echo esc_attr( $selected ); ?>><?php echo esc_html( $file ); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="acfcs__process-file-element">
                <?php $delimiters = [ ';', ',', '|' ]; ?>
                <?php echo sprintf( '<label for="acfcs_delimiter">%s</label>', esc_attr__( 'Delimiter', 'acf-city-selector' ) ); ?>
                <select name="acfcs_delimiter" id="acfcs_delimiter">
                    <?php foreach( $delimiters as $delimiter_value ) { ?>
                        <?php $selected_delimiter = ( $delimiter_value == $delimiter ) ? ' selected' : false; ?>
                        <option value="<?php echo esc_attr( $delimiter_value ); ?>"<?php echo esc_attr( $selected_delimiter ); ?>><?php echo esc_html( $delimiter_value ); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="acfcs__process-file-element">
                <?php echo sprintf( '<label for="acfcs_max_lines">%s</label>', esc_attr__( 'Max lines', 'acf-city-selector' ) ); ?>
                <input type="number" name="acfcs_max_lines" id="acfcs_max_lines" value="<?php echo esc_attr( $max_lines_value ); ?>" />
            </div>
        </div>

        <div>
            <input type="submit" class="button button-primary" value="<?php esc_html_e( 'View this file', 'acf-city-selector' ); ?>"/>
        </div>
    </form>
</div>
