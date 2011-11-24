<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php?error=notloggedin' );
	exit;
}


if( !isset( $_GET['page'] ) || !ae_Validate::isDigit( $_GET['page'] ) ) {
	$_GET['page'] = 1;
}
define( 'PAGE', $_GET['page'] - 1 );


$filter = ae_ContentOfRules::FilterForRules();

$rules = new ae_ManageRules( $filter );
$rules_count = ae_ManageRules::CountRulesByStatus( $filter['status'] );
?>


<div id="manage">

	<h1>Rules</h1>


	<ul id="status_filter">
		<?php echo ae_ContentOfRules::StatusFilterNav() ?>
	</ul>

	<form class="rule_add" method="post" accept-charset="utf-8" action="set/rule-add.php">
		<table>
			<thead>
				<tr>
					<th>concern</th>
					<th>precision</th>
					<th>match</th>
					<th>result</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr class="rule">

					<td class="rule-concern">
						<select name="rule-concern">
							<option value="comment_ip">comment IP</option>
							<option value="comment_email">comment mail</option>
							<option value="comment_author">comment name</option>
							<option value="comment_content">comment text</option>
							<option value="comment_url">comment url</option>
						</select>
					</td>

					<td class="rule-precision">
						<select name="rule-precision">
							<option value="contains">contains</option>
							<option value="exact">exact match</option>
							<option value="regex">regular expression</option>
						</select>
					</td>

					<td class="rule-match">
						<input type="text" name="rule-match" value="" />
					</td>

					<td class="rule-result">
						<select name="rule-result">
							<?php echo ae_ContentOfRules::OptionsForRuleResults() ?>
						</select>
					</td>

					<td class="rule-status">
						<input type="submit" value="add" />
					</td>
				</tr>
			</tbody>
		</table>
	</form>

	<form accept-charset="utf-8" action="set/apply-on-rules.php" class="bulk_apply" method="post">

		<?php if( ROLE == 'admin' ) : ?>
		<div class="bulk_apply">
			<select name="bulk">
				<?php if( $filter['status'] != 'active' ): ?>
				<option value="active">Activate</option>
				<?php endif; ?>
				<?php if( $filter['status'] != 'trash' ): ?>
				<option value="trash">Trash</option>
				<?php else: ?>
				<option value="trash">Delete</option>
				<?php endif; ?>
			</select>

			<input type="hidden" name="from" value="<?php echo $filter['status'] ?>" />

			<input type="submit" value="Apply" />
		</div>
		<?php endif; ?>


		<div class="page-nav page-nav-top">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $rules->getLimit(), $rules_count ) ?>
		</div>

		<?php if( $rules->have_rules() ) : ?>
		<table id="rules">
			<thead>
				<tr>
					<th></th>
					<th></th>
					<th>concern</th>
					<th>precision</th>
					<th>match</th>
					<th>result</th>
					<th>status</th>
				</tr>
			</thead>
			<tbody>
				<?php while( $rules->have_rules() ) : $rules->the_rule() ?>
				<tr class="rule" id="<?php echo $rules->id() ?>">

					<td class="check">
						<input id="rule-<?php echo $rules->id() ?>" name="id[]" type="checkbox" value="<?php echo $rules->id() ?>" />
					</td>

					<td class="action_cell">
						<div class="actions_trigger">
							<div class="actions">
								<?php echo ae_ContentOfRules::Actions( $rules ) ?>
							</div>
						</div>
					</td>

					<td class="rule-concern">
						<?php echo $rules->rule_concern() ?>
					</td>

					<td class="rule-precision">
						<?php echo $rules->rule_precision() ?>
					</td>

					<td class="rule-match">
						<?php echo $rules->rule_match() ?>
					</td>

					<td class="rule-result">
						<?php echo $rules->rule_result() ?>
					</td>

					<td class="rule-status">
						<?php echo $rules->status() ?>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
		<?php endif; ?>


		<div class="page-nav page-nav-bottom">
			<?php echo ae_PageStructure::BuildPageflip( PAGE, $rules->getLimit(), $rules_count ) ?>
		</div>

	</form>


</div>
