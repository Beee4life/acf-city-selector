<form enctype="multipart/form-data" method="post">
    <input name="acfcs_upload_csv_nonce" type="hidden" value="<?php echo wp_create_nonce( 'acfcs-upload-csv-nonce' ); ?>" />
    <input type="hidden" name="MAX_FILE_SIZE" value="1024000" />

    <div class="upload-element">
        <?php echo sprintf( '<label for="csv_upload">%s</label>', esc_attr__( 'Choose a (CSV) file to upload', 'acf-city-selector' ) ); ?>
        <div class="form--upload form--csv_upload">
            <input type="file" name="csv_upload" id="csv_upload" accept=".csv" />
            <span class="val"></span>
            <span class="upload_button button-primary" data-type="csv_upload">
                <?php _e( 'Select file', 'acf-city-selector' ); ?>
            </span>
        </div>
    </div>
    <input type="submit" class="button button-primary" value="<?php esc_html_e( 'Upload CSV', 'acf-city-selector' ); ?>" />
</form>
