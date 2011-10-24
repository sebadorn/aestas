<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'set', 'rules' );


/* Check for missing elements */

if( !isset( $_POST['rule-concern'], $_POST['rule-match'], $_POST['rule-precision'], $_POST['rule-result'] )
		|| trim( $_POST['rule-match'] ) == '' ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=set&show=rules&error=missing_data' );
	exit;
}


$rule = new ae_Rule();

$rule->setRuleConcern( $_POST['rule-concern'] );

$rule->setRuleMatch( $_POST['rule-match'] );

$rule->setRulePrecision( $_POST['rule-precision'] );

$rule->setRuleResult( $_POST['rule-result'] );

$rule->setStatus( 'active' );

if( $rule->save_new() ) {
	$outcome = 'success';
}
else {
	$outcome = 'error';
}


mysql_close( $db_connect );
header( 'Location: ../junction.php?area=set&show=rules&' . $outcome );
