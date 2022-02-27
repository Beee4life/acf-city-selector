<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
?>
<form method="post">
    <input name="acfcs_import_raw_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-import-raw-nonce' ); ?>" />
    <?php echo sprintf( '<label for="raw-import">%s</label>', esc_attr__( 'Raw CSV import', 'acf-city-selector' ) ); ?>
    <?php echo sprintf( '<textarea name="acfcs_raw_csv_import" id="raw-import" rows="5" placeholder="%s">%s</textarea>', $placeholder, $submitted_raw_data ); ?>
    <br />
    <?php
        echo sprintf( '<input name="acfcs_verify" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Verify CSV data', 'acf-city-selector' ) );
        echo sprintf( '<input name="acfcs_import" type="submit" class="button button-primary" value="%s" />', esc_attr__( 'Import CSV data', 'acf-city-selector' ) );
    ?>
</form>
