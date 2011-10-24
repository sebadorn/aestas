<?php


class ae_ErrorMessages {


	public static function CouldNotGeneratePermalink( $for ) {
		return 'Could not generate permalink for ' . $for . '. Missing information.';
	}


	public static function MySQLConnectFail() {
		return '<!DOCTYPE html>
<html>
<head><meta charset="utf-8" /><title>Site currently not available</title><style type="text/css">
	* { margin: 0; padding: 0; }
	body { background-color: #e0e0e4; color: #202020; font: 18px Georgia, serif; padding: 10% 20%; text-shadow: 2px 1px 0 #ffffff; }
	h1 { font-size: 200%; margin-bottom: 36px; }
	p { margin-bottom: 24px; }
	ul { margin-left: 24px; }
	li { font-style: italic; margin-bottom: 24px; }
	strong { display: block; font-style: normal; margin-bottom: 2px; }
</style></head><body><h1>Site currently not available</h1>
<p>I\'m sorry. It was not possible to establish a connection to the database server. :&nbsp;(</p><p>Causes could be:</p>
<ul><li><strong>Wrong connection information</strong>
Are you sure the host name, user name and password are spelled correctly?</li>
	<li><strong>The database is down</strong>
Maybe it is undergoing maintenance and will be up running again in a few minutes.</li></ul></body></html>';
	}


	public static function MySQLDbNameFail() {
		return '<!DOCTYPE html>
<html>
<head><meta charset="utf-8" /><title>Site currently not available</title><style type="text/css">
	* { margin: 0; padding: 0; }
	body { background-color: #e0e0e4; color: #202020; font: 18px Georgia, serif; padding: 10% 20%; text-shadow: 2px 1px 0 #ffffff; }
	h1 { font-size: 200%; margin-bottom: 36px; }
	p { margin-bottom: 24px; }
	ul { margin-left: 24px; }
	li { font-style: italic; margin-bottom: 24px; }
	strong { display: block; font-style: normal; margin-bottom: 2px; }
</style></head><body><h1>Site currently not available</h1>
<p>I\'m sorry, but the specified database is not available. :&nbsp;(</p>
<p>The good part is, that it was possible to connect to the database server.<br />
What did not work was to select the database of the CMS.</p>
<p>Causes could be:</p>
<ul><li><strong>Wrong connection information</strong>
Are you sure the database name is spelled correctly?</li>
<li><strong>The database does not exist</strong>
Please verifiy that a database with the specified name exists.</li></ul></body></html>';
	}


	public static function MySQLTableFail() {
		return '<!DOCTYPE html>
<html>
<head><meta charset="utf-8" /><title>Site currently not available</title><style type="text/css">
	* { margin: 0; padding: 0; }
	body { background-color: #e0e0e4; color: #202020; font: 18px Georgia, serif; padding: 10% 20%; text-shadow: 2px 1px 0 #ffffff; }
	h1 { font-size: 200%; margin-bottom: 36px; }
	p { margin-bottom: 24px; }
	ul { margin-left: 24px; }
	li { font-style: italic; margin-bottom: 24px; }
	strong { display: block; font-style: normal; margin-bottom: 2px; }
</style></head><body><h1>Site currently not available</h1>
<p>I\'m sorry. The needed database tables are not there. :&nbsp;(</p>
<p>The good part is, that it was possible to connect to the database server<br />
and to find the database of this CMS. But its tables are not there.</p>
<p>Causes could be:</p>
<ul><li><strong>Wrong prefix</strong>
Every table has a prefix to differentiate between possible multiple installations.<br />
Maybe the specified one and the actual one do not match.</li>
<li><strong>The database tables really do not exist</strong>
Maybe there was a problem during installation.</li></ul></body></html>';
	}


	public static function MySQLQuery( $errno, $errmsg ) {
		return 'MySQL query failed.' . PHP_EOL . 'Errno: ' . $errno . PHP_EOL . 'Errmsg: ' . $errmsg;
	}


	public static function NotAnId() {
		return 'ID expected, but value contained non-digit characters.';
	}


	public static function NotAnIp() {
		return 'Given string is not a well-formed IP address.';
	}


	public static function NotADate_MySQL() {
		return 'Given string is not a well-formed date as needed for MySQL.';
	}


	public static function NotCallableWithoutLogIn() {
		return 'This function cannot be called by someone who is not logged-in.';
	}


	public static function ProblemWithStatusOrId() {
		return 'Either missing a status to change to or ID contains non-digit characters.';
	}


	public static function TypeNotExpected( $expected, $given_value ) {
		return 'Expected ' . $expected . '. Instead got ' . gettype( $given_value ) . '.';
	}


	public static function ValueNotExpected( $expected, $given ) {
		return 'Expected ' . $expected . '. Instead received ' . $given . '.';
	}


	public static function Unknown( $name, $given ) {
		return 'Unknown ' . $name . ': ' . $given . '.';
	}


}
