<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}


$ch_comm = ( ae_Settings::getSetting( 'comments' ) == 'true' ) ? ' checked="checked"' : '';
$ch_pings = ( ae_Settings::getSetting( 'pings' ) == 'true' ) ? ' checked="checked"' : '';


$mod = ae_Settings::getSetting( 'comments_moderate' );

$ch_mod = $ch_once = '';

if( $mod == 'true' || $mod == 'once' ) {
	$ch_mod = ' checked="checked"';
	if( $mod == 'once' ) {
		$ch_once = ' checked="checked"';
	}
}


$ch_grav = ( ae_Settings::getSetting( 'gravatar' ) == 'true' ) ? ' checked="checked"' : '';


$sel_g = $sel_pg = $sel_r = $sel_x = '';

switch( ae_Settings::getSetting( 'gravatar_rating' ) ) {
	case 'g':
		$sel_g = ' checked="checked"';
		break;
	case 'pg':
		$sel_pg = ' checked="checked"';
		break;
	case 'r':
		$sel_r = ' checked="checked"';
		break;
	case 'x':
		$sel_x = ' checked="checked"';
		break;
}


$sel_own = $sel_blank = $sel_myst = $sel_monster = $sel_identicon = $sel_wavatar = '';

switch( ae_Settings::getSetting( 'gravatar_default' ) ) {
	case 'own':
		$sel_own = ' checked="checked"';
		break;
	case '404':
		$sel_blank = ' checked="checked"';
		break;
	case 'mm':
		$sel_myst = ' checked="checked"';
		break;
	case 'identicon':
		$sel_identicon = ' checked="checked"';
		break;
	case 'monsterid':
		$sel_monster = ' checked="checked"';
		break;
	case 'wavatar':
		$sel_wavatar = ' checked="checked"';
		break;
}

?>
<header class="content-menu">
	<h1>Discussion</h1>
</header>

<div class="content settings discussion">

	<form accept-charset="utf-8" action="set/change-comments.php" method="post">


		<fieldset>
			<legend>Comments</legend>
			<div>
				<ul>
					<li>
						<input type="checkbox" name="comments" value="true" id="allow-comments"<?php echo $ch_comm ?> />
						<label for="allow-comments">Allow comments.</label>
					</li>
					<li>
						<input type="checkbox" name="pings" value="true" id="pings"<?php echo $ch_pings ?> />
						<label for="pings">Accept incoming trackbacks.</label>
					</li>
				</ul>
			</div>
		</fieldset>


		<fieldset>
			<legend>Before a comment appears</legend>
			<div>
				<ul>
					<li>
						<input type="checkbox" name="moderation" value="true" id="appr"<?php echo $ch_mod ?> />
						<label for="appr">Comments need to be approved.</label>
						<br />
						<ul>
							<li>
								<input type="checkbox" name="moderate-once" value="true" id="appr-once"<?php echo $ch_once ?> />
								<label for="appr-once">Comment authors with previously approved comments will not be put in queue.</label>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</fieldset>


		<hr />


		<fieldset id="avatars">
			<legend>Avatars</legend>
			<div>
				<input type="checkbox" name="grav" value="true" id="grav"<?php echo $ch_grav ?> />
				<label for="grav">Gravatar service</label>

				<fieldset class="extended<?php echo empty( $ch_grav ) ? ' hideonload' : ''; ?>">
					<legend>Maximum rating</legend>
					<div>
						<ul>
							<li>
								<input type="radio" name="rating" value="g" id="grav-g"<?php echo $sel_g ?> />
								<label for="grav-g">G – suitable for all audiences</label>
							</li>
							<li>
								<input type="radio" name="rating" value="pg" id="grav-pg"<?php echo $sel_pg ?> />
								<label for="grav-pg">PG – possibly offensive, usually for audiences 13 and above</label>
							</li>
							<li>
								<input type="radio" name="rating" value="r" id="grav-r"<?php echo $sel_r ?> />
								<label for="grav-r">R – intended for adult audiences above 17</label>
							</li>
							<li>
								<input type="radio" name="rating" value="x" id="grav-x"<?php echo $sel_x ?> />
								<label for="grav-x">X – even more mature than above</label>
							</li>
						</ul>
					</div>
				</fieldset>

				<fieldset class="extended<?php echo empty( $ch_grav ) ? ' hideonload' : ''; ?>">
					<legend>Default avatar</legend>
					<div>
						<ul>
							<li>
								<input type="radio" name="default-ava" value="own" id="grav-own"<?php echo $sel_own ?> />
								<label for="grav-own">Own</label>
							</li>
							<li>
								<input type="radio" name="default-ava" value="404" id="grav-blank"<?php echo $sel_blank ?> />
								<label for="grav-blank">Blank (404)</label>
							</li>
							<li>
								<input type="radio" name="default-ava" value="mm" id="grav-mystery"<?php echo $sel_myst ?> />
								<label for="grav-mystery">Mystery Man</label>
							</li>
							<li>
								<input type="radio" name="default-ava" value="identicon" id="grav-identicon"<?php echo $sel_identicon ?> />
								<label for="grav-identicon">Identicon</label>
							</li>
							<li>
								<input type="radio" name="default-ava" value="monsterid" id="grav-monster"<?php echo $sel_monster ?> />
								<label for="grav-monster">Monster ID</label>
							</li>
							<li>
								<input type="radio" name="default-ava" value="wavatar" id="grav-wavatar"<?php echo $sel_wavatar ?> />
								<label for="grav-wavatar">Wavatar</label>
							</li>
						</ul>
					</div>
				</fieldset>

			</div>
		</fieldset>

		<hr />

		<div class="submit">
			<input type="submit" value="save changes" />
		</div>

	</form>

</div>
