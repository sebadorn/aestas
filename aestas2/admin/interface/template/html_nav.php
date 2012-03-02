<nav class="nav-container">

	<form class="search" action="<?php echo $params->current_path ?>/search.php" method="post" accept-charset="<?php echo $params->charset ?>">
		<input type="text" name="search" value="<?php echo $params->search_was ?>" />
		<input type="submit" value="" />
		<input type="hidden" name="area" value="<?php echo $params->area ?>" />
		<input type="hidden" name="show" value="<?php echo $params->show ?>" />
	</form>

	<ul class="nav-main">
		<?php foreach( $params->nav as $name => $data ): ?>
		<li class="<?php echo $data['css_class'] ?>">
			<a href="junction.php?area=<?php echo $data['link'] ?>">
				<?php echo $name ?>
			</a>

			<ul class="nav-sub">
			<?php foreach( $data['sub_nav'] as $sub_name => $sub_data ): ?>
				<li class="<?php echo $sub_data['css_class'] ?>">
					<a href="junction.php?area=<?php echo $data['link'] ?>&amp;show=<?php echo $sub_data['link'] ?>">
						<?php echo $sub_name ?>
					</a>
				</li>
			<?php endforeach ?>
			</ul>
		</li>
		<?php endforeach ?>
	</ul>

	<ul class="area-2">
		<li class="visit"><a href="../">Visit blog</a></li>
		<li class="logout"><a href="logout.php?ran=<?php echo rand( 1, 1000 ) ?>">Log out</a></li>
	</ul>

</nav>

<div class="container">
