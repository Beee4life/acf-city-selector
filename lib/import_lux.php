<?php global $wpdb; ?>

#
# Table structure for table 'cities'
#

IF (
    SELECT  *
    FROM    information_schema.tables
    WHERE   table_schema = 'sexdates'
    AND     table_name = '<?php echo $wpdb->prefix; ?>cities'
    LIMIT   1;
)

BEGIN

ELSE

    CREATE TABLE <?php echo $wpdb->prefix; ?>cities (
        id int(4) unsigned NOT NULL auto_increment,
        city_name_ascii varchar(50) NULL,
        state_code varchar(2) NULL,
        states varchar(50) NULL,
        country_code varchar(2) NULL,
        country varchar(50) NULL,
        PRIMARY KEY (id)
    ) ;

END;

INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Diekirch", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Differdange", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Dudelange", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Echternach", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Esch-sur-Alzette", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Ettelbruck", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Grevenmacher", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Luxembourg City", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Remich", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Rumelange", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Vianden", "na", "n/a", "LU", "Luxembourg");
INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES ("Wiltz", "na", "n/a", "LU", "Luxembourg");
