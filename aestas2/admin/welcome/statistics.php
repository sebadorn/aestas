<?php

if( !defined( 'ROLE' ) ) {
	header( 'Location: ../index.php' );
	exit;
}

$year = isset( $_GET['year'] ) ? $_GET['year'] : date( 'Y' );
$month = isset( $_GET['month'] ) ? $_GET['month'] : date( 'm' );
$vispage = isset( $_GET['visstats'] ) ? $_GET['visstats'] : date( 'Y' );

ae_ReferrerStats::InitStats( $year, $month );
?>


<div id="manage" class="stats">

	<h1>Statistics</h1>

	<h2>Visits</h2>

	<?php echo ae_VisitorStats::VisitorStatsBrowseLinks() ?>
	<table class="monthlybars">
		<?php echo ae_VisitorStats::MonthlyBars( $vispage ) ?>
	</table>


	<form class="selectdate cleaninfo" method="get" action="junction.php" accept-charset="utf-8">

		<select name="year">
			<option value="<?php date( 'Y' ) ?>"><?php echo date( 'Y' ) ?></option>
			<option value="<?php echo date( 'Y' ) - 1 ?>"><?php echo date( 'Y' ) - 1 ?></option>
		</select>

		<select name="month">
			<?php echo ae_Misc::MonthsForSelect( $month ) ?>
		</select>

		<div>
			<input type="hidden" name="area" value="<?php echo $_GET['area'] ?>" />
			<input type="hidden" name="show" value="<?php echo $_GET['show'] ?>" />
			<input type="submit" value="Show" />
		</div>

	</form>


	<div id="referrer">
		<ul class="change hideifnojs">
			<li class="recentsearch active">Recent searches</li>
			<li class="topsearch">Top searches</li>
			<li class="recentref">Recent referrer</li>
			<li class="topref">Top referrer</li>
		</ul>

		<table class="recentsearch hideonload">
			<caption>Recent</caption>
			<thead>
				<tr>
					<th class="searchengine">URL</th>
					<th class="keywords">Keywords</th>
					<th class="date">Date</th>
				</tr>
			</thead>
			<tbody>
				<?php echo ae_ReferrerStats::NewSearchesForTable() ?>
			</tbody>
		</table>

		<table class="topsearch hideonload">
			<caption>Top</caption>
			<thead>
				<tr>
					<th class="keywords">Keywords</th>
					<th class="visits">Visits</th>
				</tr>
			</thead>
			<tbody>
				<?php echo ae_ReferrerStats::TopSearchesForTable() ?>
			</tbody>
		</table>

		<table class="recentref hideonload">
			<caption>Recent</caption>
			<thead>
				<tr>
					<th class="link">URL</th>
					<th class="date">Date</th>
				</tr>
			</thead>
			<tbody>
				<?php echo ae_ReferrerStats::NewReferrerForTable() ?>
			</tbody>
		</table>

		<table class="topref hideonload">
			<caption>Top</caption>
			<thead>
				<tr>
					<th class="link">URL</th>
					<th class="visits">Visits</th>
				</tr>
			</thead>
			<tbody>
				<?php echo ae_ReferrerStats::TopReferrerForTable() ?>
			</tbody>
		</table>
	</div>

</div>
