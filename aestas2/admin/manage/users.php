<?php

if( !ae_Permissions::hasRights( $area, $content ) ) {
	echo ae_PageStructure::MissingRights();
}
else {

	if( !isset( $_GET['page'] ) || !ae_Validate::isDigit( $_GET['page'] ) ) {
		$_GET['page'] = 1;
	}
	define( 'PAGE', $_GET['page'] - 1 );

	$filter = ae_ContentOfUser::FilterForUsers();
	$muq = new ae_ManageUserQuery( $filter );
	$user_count = $muq->count_users();

?>


<div id="manage">

	<h1>Users</h1>


	<ul id="status_filter">
		<?php echo ae_ContentOfUser::RoleFilterUserNav() ?>
	</ul>


	<form accept-charset="utf-8" action="manage/apply.php" class="bulk_apply" method="post">

		<div class="bulk_apply">
		<?php if( ROLE == 'admin' ) : ?>
			Selected:
			<select name="bulk">
				<option value="active">employ</option>
				<option value="suspended">suspend</option>
				<option value="trash">delete</option>
			</select>

			<input type="submit" name="submit" value="Apply" />
			<input name="show" type="hidden" value="<?php echo ae_PageStructure::getShowContent() ?>" />
			<input name="from" type="hidden" value="<?php echo $filter['status'] ?>" />
		<?php endif; ?>
		</div>

		<p class="cleaninfo">
			Show and edit <a href="?area=manage&amp;show=users&amp;edit=<?php echo ae_Permissions::getIdOfCurrentUser() ?>">my profile</a>.
		</p>

		<nav class="page-nav page-nav-top">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $muq->getLimit(), $user_count ); ?>
		</nav>


		<?php if( $muq->have_users() ) : ?>
		<table id="users">
			<thead>
				<tr>
					<th></th>
					<th>Name</th>
					<th></th>
					<th>Role</th>
					<th>Mail</th>
					<th>Url</th>
					<th>Posts written</th>
					<th>Pages written</th>
					<th>Files uploaded</th>
				</tr>
			</thead>
			<tbody>
			<?php while( $muq->have_users() ) : $muq->the_user() ?>
				<tr class="user <?php echo $muq->user_status() ?>" id="<?php echo $muq->user_ID() ?>">

					<td class="check">
						<input name="id[]" type="checkbox" value="<?php echo $muq->user_ID() ?>" />
					</td>

					<td class="name">
						<?php echo $muq->user_name() ?>
					</td>

					<td>
						<span class="actions_trigger">
							<?php if( ROLE == 'admin' ) : ?>
							<div class="actions">
								<?php echo ae_ContentOfUser::UserActions( $muq ) ?>
							</div>
							<?php endif; ?>
						</span>
					</td>

					<td class="role"><?php echo $muq->user_role() ?></td>

					<td class="mail"><?php echo $muq->user_email_link() ?></td>

					<td class="url"><?php echo $muq->user_url_link() ?></td>

					<td class="count"><?php echo ae_ContentOfUser::UserWrittenPosts( $muq ) ?></td>

					<td class="count"><?php echo ae_ContentOfUser::UserWrittenPages( $muq ) ?></td>

					<td class="count"><?php echo ae_ContentOfUser::UserUploadedFiles( $muq ) ?></td>

				</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
		<?php endif; ?>


		<nav class="page-nav page-nav-bottom">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $muq->getLimit(), $user_count ); ?>
		</nav>

	</form>

</div>

<?php } ?>