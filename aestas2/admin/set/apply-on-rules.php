<?php

require_once( '../../includes/config.php' );

ae_Permissions::InitRoleAndStatus();
ae_Permissions::CheckInScript( 'set', 'rules' );


// Scenario 1: Delete expired IPs
if( isset( $_POST['del_expired'] ) ) {
	ae_Rules::DeleteExpiredIps();
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=set&show=rules' );
	exit;
}


$queue = array();

// Scenario 2: Bulk apply for many.
if( isset( $_POST['bulk'], $_POST['id'] ) ) {
	$status = $_POST['bulk'];
	$queue = $_POST['id'];

	if( !ae_Validate::isRuleStatus( $status ) ) {
		mysql_close( $db_connect );
		header( 'Location: ../junction.php?area=set&show=rules' );
		exit;
	}
}

// Scenario 3: Only a single one shall be changed.
else {
	ae_ManageActions::InitStatusAndId( $_GET, 'rules' );
	$queue[] = ae_ManageActions::getId();
	$status = ae_ManageActions::getStatus();
}


if( empty( $queue ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../junction.php?area=set&show=rules&error=emptyqueue' );
	exit;
}



// Now process all the IDs in the queue.
foreach( $queue as $id ) {
	$element = ae_Rule::getRuleById( $id );
	if( $status != null ) {
		$element->update_status( $status );
	}
}


mysql_close( $db_connect );
$from = isset( $_GET['from'] ) ? '&status=' . $_GET['from'] : '';
$from = isset( $_POST['from'] ) ? '&status=' . $_POST['from'] : $from;
header( 'Location: ../junction.php?area=set&show=rules' . $from );
exit;
