<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php' );
	exit;
}


$themes = ae_Theme::getThemes();
$theme = ae_Settings::getSetting( 'blog_theme' );

?>
<header class="content-menu">
	<h1>Choose Theme</h1>
</header>

<div class="content theme choose">

	<?php if( ae_Cookies::isInThemePreview() ) : ?>
	<form accept-charset="utf-8" action="theme/preview.php" class="endpreview cleaninfo" method="post">
		<p>
			At the moment you (and only you) will see the blog with another theme.
			<input name="endpreview" type="submit" value="End preview" />
		</p>
	</form>
	<?php endif; ?>


	<?php if( isset( $_GET['delete'] ) && $_GET['delete'] != $theme ) : ?>
	<form accept-charset="utf-8" action="theme/delete.php" class="endpreview cleaninfo" method="post">
		<p>
			You are about to delete every file in <code>"theme/<?php echo $_GET['delete'] ?>/"</code>.
			Is that in your interest?
			<input name="del_theme" type="hidden" value="<?php echo $_GET['delete'] ?>" />
			<a class="button" href="?area=theme&amp;show=choosetheme">No. Cancel.</a>
			<input name="delete" type="submit" value="Delete" />
		</p>
	</form>
	<?php endif; ?>


	<?php foreach( $themes as $theme_dir => $name ) { ?>
	<div class="theme<?php if( $theme_dir == $theme ) { echo ' current'; } ?>">

			<?php
				$system = ae_Theme::getSystem( $theme_dir );
				 if( $system == 'wordpress' ) :
					 $wp_theme = ae_Theme::getWordpressInfo( $theme_dir );
					 $screenshot = ae_Theme::getScreenshot( $system, $theme_dir );
			?>

			<h2><?php echo $name ?><span><?php echo $wp_theme['version'] ?></span></h2>

			<?php if( !empty( $wp_theme['author_uri'] ) ) : ?>
			<a class="author" href="<?php echo $wp_theme['author_uri'] ?>">
				by <?php echo $wp_theme['author'] ?>
			</a>
			<?php else : ?>
			<span class="author">by <?php echo $wp_theme['author'] ?></span>
			<?php endif; ?>

			<div class="screenshot"><?php echo $screenshot ?></div>

			<?php if( $theme_dir == $theme ) : ?>
				<p class="used">You are currently using this theme.</p>
			<?php endif; ?>

			<p class="desc"><?php echo $wp_theme['description'] ?></p>

			<?php if( !empty( $wp_theme['uri'] ) ) : ?>
			<p><span>Website:</span> <a href="<?php echo $wp_theme['uri'] ?>"><?php echo $wp_theme['uri'] ?></a></p>
			<?php endif; ?>

			<p class="tags"><span>Tags:</span> <?php echo $wp_theme['tags'] ?></p>

		<?php else : ?>

			<h2><?php echo $name ?></h2>
			<span class="author">author unknown</span>

			<?php if( ae_Theme::getScreenshot( $system, $theme_dir ) != '' ) : ?>
			<div class="screenshot"><?php echo ae_Theme::getScreenshot( $system, $theme_dir ) ?></div>
			<?php endif; ?>

			<?php if( $theme_dir == $theme ) : ?>
				<p class="used">You are currently using this theme.</p>
			<?php endif; ?>

		<?php endif; ?>

		<span class="edit">
			<a class="button" href="?area=theme&amp;show=edittheme&amp;themedir=<?php echo urlencode( $theme_dir ) ?>">Edit files</a>
		</span>

		<?php if( $theme_dir != $theme ) : ?>
			<form accept-charset="utf-8" action="theme/preview.php" method="post">
				<div>
					<input name="preview" type="submit" value="Preview" />
					<input name="theme" type="hidden" value="<?php echo $theme_dir ?>" />
					<input name="system" type="hidden" value="<?php echo $system ?>" />
				</div>
			</form>
			<form accept-charset="utf-8" action="theme/use.php" method="post">
				<div>
					<input name="use" type="submit" value="Use" />
					<input name="theme" type="hidden" value="<?php echo $theme_dir ?>" />
					<input name="system" type="hidden" value="<?php echo $system ?>" />
				</div>
			</form>
			<a class="button" href="?area=theme&amp;show=choosetheme&amp;delete=<?php echo $theme_dir ?>">Delete</a>
		<?php endif; ?>

		<span class="system"><?php echo $system ?></span>

	</div>
	<?php } ?>

</div>
