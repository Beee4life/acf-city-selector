<?php global $wpdb; ?>

#
# Table structure for table 'cities'
#

DROP TABLE IF EXISTS <?php echo $wpdb->prefix; ?>cities;
CREATE TABLE <?php echo $wpdb->prefix; ?>cities (
    id int(4) unsigned NOT NULL auto_increment,
    city_name_ascii varchar(50) NULL,
    state_code varchar(3) NULL,
    states varchar(50) NULL,
    country_code varchar(3) NULL,
    country varchar(50) NULL,
    PRIMARY KEY (id)
) ;

INSERT INTO <?php echo $wpdb->prefix; ?>cities ( city_name_ascii, state_code, states, country_code, country ) VALUES( "Amsterdam", "NH", "Noord-Holland", "NL", "Netherlands" );
