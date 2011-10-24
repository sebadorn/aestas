<?php

// TODO: Protect against comment flood: IP can only comment every 6 seconds or so

require_once( '../includes/config.php' );

define( 'CONTEXT',      'comments-post'					);
define( 'PROTOCOL',		ae_URL::Protocol()				);
define( 'URL',			PROTOCOL . ae_URL::Blog()		);
define( 'URL_EXTENDED',	PROTOCOL . ae_URL::Complete()	);

ae_Permissions::InitRoleAndStatus();


/* Are comments even enabled in general? */

if( ae_Settings::getSetting( 'comments' ) == false ) {
	mysql_close( $db_connect );
	header( 'Location: ../' );
	exit;
}


/* Check for missing elements */

if( !isset( $_POST['comment'] )
		|| ( !isset( $_POST['comment_post_ID'] ) && !isset( $_POST['comment_page_ID'] ) ) ) {
	mysql_close( $db_connect );
	header( 'Location: ../' );
	exit;
}


$comment = new ae_Comment();

$comment->setIp( $_SERVER['REMOTE_ADDR'] );

$from = isset( $_POST['comment_post_ID'] ) ? 'post' : 'page';

if( $from == 'page' ) {
	$comment->setPostId( $_POST['comment_page_ID'] );
}
else {
	$comment->setPostId( $_POST['comment_post_ID'] );
}


/* Is the comment text empty?  */

$_POST['comment'] = trim( $_POST['comment'] );

if( empty( $_POST['comment'] ) ) {
	mysql_close( $db_connect );
	if( $from == 'post' ) {
		header( 'Location: ../?p=' . $comment->getPostId() );
	}
	else {
		header( 'Location: ../?page_id=' . $comment->getPostId() );
	}
	exit;
}

$comment->setContent( $_POST['comment'] );
$comment->contentCorrectHtml();
$comment->contentDefuse();
$comment->contentNl2Br();
$comment->contentBlockquoteStrict();


$comment->setAuthor( $_POST['author'] );

$comment->setUserId( ae_Permissions::getIdOfCurrentUser() );

$comment->setEmail( $_POST['email'] );

$comment->setUrl( $_POST['url'] );

$comment->setParentId( $_POST['comment_parent'] );


// Status

$status = 'unapproved';

$moderation = ae_Settings::getSetting( 'comments_moderate' );

if( $moderation == 'false'
		|| ( $moderation == 'once' && $comment->hasApprovedCommentFromBefore() ) ) {
	$status = 'approved';
}

$comment->setStatus( $status );



// Apply rules

$status_changed_by_rule = false;

$rule_actions = ae_Rules::Check(
	$comment->getAuthor(),
	$comment->getEmail(),
	$comment->getUrl(),
	$comment->getContent(),
	$comment->getIp()
);

foreach( $rule_actions as $rule ) {
	$rule_parts = explode( ';', $rule );
	if( count( $rule_parts ) != 3 || $rule_parts[0] != 'comment' ) {
		continue;
	}

	$rule_change = $rule_parts[1];
	$rule_result = $rule_parts[2];

	if( $rule_change == 'status' ) {
		$comment->setStatus( $rule_result );
		$status_changed_by_rule = true;
	}
	else if( $rule_change == 'user' ) {
		$comment->setUserId( $rule_result );
	}
}



// Has the honeypot been touched?

if( !$status_changed_by_rule && isset( $_POST['honey-comment'] ) ) { // Is there a honeypot?
	$_POST['honey-comment'] = trim( $_POST['honey-comment'] );
	$_POST['honey-author'] = trim( $_POST['honey-author'] );
	$_POST['honey-email'] = trim( $_POST['honey-url'] );
	$_POST['honey-url'] = trim( $_POST['honey-url'] );

	if( !empty( $_POST['honey-comment'] ) || !empty( $_POST['honey-author'] )
			|| !empty( $_POST['honey-email'] ) || !empty( $_POST['honey-url'] ) ) {

		$blackRule = new ae_Rule();
		$blackRule->setRuleConcern( 'comment_ip' );
		$blackRule->setRuleMatch( $comment->getIp() );
		$blackRule->setRulePrecision( 'exact' );
		$blackRule->setRuleResult( 'comment;status;spam' );
		$blackRule->setStatus( 'active' );
		$blackRule->save_new();

		Statistics::HoneypotInc();
		$comment->setStatus( 'spam' );
	}
}

$comment->save_new();


// Last comment ID

$jump = '';

if( $status == 'approved' ) {
	$jump = '#comment-' . $comment->getId();
}


// Giving a cookie to remember

ae_Cookies::SetCommentAuthorInfo( $comment->getAuthor(), $comment->getEmail(), $comment->getUrl() );


// Going back

if( $from == 'post' ) {
	if( ae_URL::StructureOfPost() == 'default' ) {
		header( 'Location: ../?p=' . $comment->getPostId() . $jump );
	}
	else {
		header( 'Location: ' . ae_URL::PermalinkOfPost( $comment->getPostId() ) . $jump );
	}
}
else {
	if( ae_URL::StructureOfPost() == 'default' ) {
		header( 'Location: ../?page_id=' . $comment->getPostId() . $jump );
	}
	else {
		header( 'Location: ' . ae_URL::PermalinkOfPage( $comment->getPostId() ) . $jump );
	}
}


mysql_close( $db_connect );
