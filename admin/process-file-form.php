<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    
    $selected_file_name = false;
    if ( isset( $_POST[ 'acfcs_select_file_nonce' ] ) ) {
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'acfcs_select_file_nonce' ] ) ), 'acfcs-select-file-nonce' ) ) {
            ACF_City_Selector::acfcs_errors()->add( 'error_nonce_no_match', esc_html__( 'Something went wrong, please try again.', 'acf-city-selector' ) );
            return;
        } else {
            $selected_file_name = isset( $_POST[ 'acfcs_file_name' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'acfcs_file_name' ] ) ) : false;
        }
    }
?>
<form method="post">
    <input name="acfcs_select_file_nonce" type="hidden" value="<?php echo esc_attr( wp_create_nonce( 'acfcs-select-file-nonce' ) ); ?>" />

    <div class="acfcs__process-file">
        <div class="acfcs__process-file-element acfcs__process-file-element--file">
            <?php echo sprintf( '<label for="acfcs_file_name">%s</label>', esc_attr__( 'File', 'acf-city-selector' ) ); ?>
            <select name="acfcs_file_name" id="acfcs_file_name">
                <?php if ( count( $file_index ) > 1 ) { ?>
                    <?php echo sprintf( '<option value="">%s</option>', esc_attr__( 'Select a file', 'acf-city-selector' ) ); ?>
                <?php } ?>
                <?php foreach ( $file_index as $file_name ) { ?>
                    <?php $selected = $selected_file_name == $file_name ? ' selected="selected"' : false; ?>
                    <?php echo sprintf( '<option value="%s"%s>%s</option>', esc_attr( $file_name ), esc_attr( $selected ), esc_attr( $file_name ) ); ?>
                <?php } ?>
            </select>
        </div>

        <div class="acfcs__process-file-element acfcs__process-file-element--delimiter">
            <?php $delimiters = [ ';', ',', '|' ]; ?>
            <?php echo sprintf( '<label for="acfcs_delimiter">%s</label>', esc_attr__( 'Delimiter', 'acf-city-selector' ) ); ?>
            <select name="acfcs_delimiter" id="acfcs_delimiter">
                <?php foreach( $delimiters as $delimiter ) { ?>
                    <?php $selected_delimiter = ( $delimiter == apply_filters( 'acfcs_delimiter', ';' ) ) ? ' selected' : false; ?>
                    <?php echo sprintf( '<option value="%s"%s>%s</option>', esc_attr( $delimiter ), esc_attr( $selected_delimiter ), esc_attr( $delimiter ) ); ?>
                <?php } ?>
            </select>
        </div>

        <div class="acfcs__process-file-element acfcs__process-file-element--maxlines">
            <?php echo sprintf( '<label for="acfcs_max_lines">%s</label>', esc_attr__( 'Max lines', 'acf-city-selector' ) ); ?>
            <input type="number" name="acfcs_max_lines" id="acfcs_max_lines" />
        </div>
    </div>

    <?php
        echo sprintf( '<input name="acfcs_verify" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Verify selected file', 'acf-city-selector' ) );
        echo sprintf( '<input name="acfcs_import" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Import selected file', 'acf-city-selector' ) );
        echo sprintf( '<input name="acfcs_remove" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Remove selected file', 'acf-city-selector' ) );
    ?>
</form>
