<?php
$search_action = $params->current_path . '/search.php';
$j_a_link = 'junction.php?area=';
?>

<nav class="nav">
	<form class="search" action="<?php echo $search_action ?>" method="post" accept-charset="<?php echo $params->charset ?>">
		<input type="text" name="search" value="<?php echo $params->search_was ?>" />
		<input type="submit" value="" />
		<input type="hidden" name="area" value="<?php echo $params->area ?>" />
		<input type="hidden" name="show" value="<?php echo $params->show ?>" />
	</form>

	<ul class="area-nav">
	<?php foreach( $params->nav as $name => $data ): ?>
		<li class="<?php echo $data['css_class'] ?>">
			<a href="<?php echo $j_a_link . $data['link'] ?>" class="icon">
				<?php echo $name ?>
			</a>

			<ul class="area-sub-nav">
			<?php foreach( $data['sub_nav'] as $sub_name => $sub_data ): ?>
				<?php $sub_link = $j_a_link . $data['link'] . '&amp;show=' . $sub_data['link']; ?>
				<li class="<?php echo $sub_data['css_class'] ?>">
					<a href="<?php echo $sub_link ?>">
						<?php echo $sub_name ?>
					</a>
				</li>
			<?php endforeach ?>
			</ul>
		</li>
	<?php endforeach ?>
	</ul>

	<a class="go-to-blog" href="../">Visit blog</a>
	<a class="logout" href="logout.php">Log out</a>
</nav>
