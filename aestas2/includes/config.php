<?php

/* Error reporting
 * 
 * Should be disabled (=0) due to security. If someone tries something funny,
 * we shouldn't tell him if it is "warmer" or "colder" where she/he hits.
 */

error_reporting( E_ALL | E_STRICT );



/* Password hashing and encryption
 *
 * Make passwords securer by adding some text to them.
 * Feel free to change the default text when installing your blog.
 * WARNING: Do not change after creating your user!
 */

$salt = ')23:ru8-19{4%89!j+#j84weq2';



/*
 * Number of rounds for round hashing
 *
 * To make stored password hashes more secure,
 * there are more than one rounds of hashing.
 * The number of rounds should be greater than 1000.
 * WARNING: Do not change after creating your user!
 */

$round_hashing = 4000;



/* Language
 *
 * An information in which language your blog is written.
 * Scheme: IETF language tag
 * Example values: 'de', 'en', 'en-US'
 */

$lang = 'de';



/* Connection to database */

// Name of database
$db_name = 'aestas2';

// Your Username
$db_user = 'sebadosebadorn';

// Your Password
$db_pass = 'xhtml10strict';

// In most cases the hostname is "localhost"
$db_host = 'localhost';


// Prefix of tables
$db_prefix = 'ae_';




include_once( 'preparations.php' );
