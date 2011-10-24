<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}


$nf_limit = ae_Settings::NewsfeedLimit();
$nf_alt = ae_Settings::getSetting( 'newsfeed_alternate' );
$nf_content = ae_Settings::getSetting( 'newsfeed_content' );

$nfc = explode( ';', $nf_content );
$ch_def = $ch_excerpt = $ch_full = $ch_short = '';
$shorten = '255';

switch( $nfc[0] ) {
	case 'default':
		$ch_def = ' checked="checked"';
		break;
	case 'excerpt':
		$ch_excerpt = ' checked="checked"';
		break;
	case 'full':
		$ch_full = ' checked="checked"';
		break;
	case 'shorten':
		$ch_short = ' checked="checked"';
		$shorten = $nfc[1];
		break;
	default:
		$ch_def = ' checked="checked"';
}
?>


<div id="set">

	<h1>Newsfeed</h1>

	<form accept-charset="utf-8" action="set/change-newsfeed.php" method="post">

		<fieldset>
			<legend>Posts in newsfeed</legend>
			<div>
				<input class="shorter" type="text" name="nf-posts" value="<?php echo $nf_limit ?>" /> posts
			</div>
		</fieldset>

		<fieldset>
			<legend>Alternate service</legend>
			<div>
				<input type="text" name="nf-alternate" value="<?php echo $nf_alt ?>" />
				Paste URL of newsfeed here (e.g. Feedburner).
			</div>
		</fieldset>

		<fieldset>
			<legend>Content</legend>
			<div>
				<ul>
					<li>
						<input type="radio" name="nf-content" value="default" id="default"<?php echo $ch_def ?> />
						<label for="default">As specified for each post (default).</label>
					</li>
					<li>
						<input type="radio" name="nf-content" value="excerpt" id="excerpt"<?php echo $ch_excerpt ?> />
						<label for="excerpt">Show only an excerpt.</label>
					</li>
					<li>
						<input type="radio" name="nf-content" value="full" id="full"<?php echo $ch_full ?> />
						<label for="full">Show the whole post.</label>
					</li>
					<li>
						<input type="radio" name="nf-content" value="shorten" id="shorten"<?php echo $ch_short ?> />
						<label for="shorten">Shorten (override individual settings).</label>
						<ul>
							<li>Shorten to <input class="short" type="text" name="shorten-to" value="<?php echo $shorten ?>" /> characters.</li>
						</ul>
					</li>
				</ul>
			</div>
		</fieldset>

		<hr />

		<div class="submit">
			<input type="submit" value="save changes" />
		</div>

	</form>

</div>
