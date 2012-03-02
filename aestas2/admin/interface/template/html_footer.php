</div> <!-- end of div.container -->

<?php foreach( $params->js as $script ): ?>
<script type="text/javascript" src="interface/js/<?php echo $script ?>"></script>
<?php endforeach ?>

<footer class="loadstats">
	<div class="stats">
		<span><?php echo $params->time_needed ?></span>.
		Memory use at <span><?php echo $params->mem_use ?></span>.
		Memory peak at <span><?php echo $params->mem_peak ?></span>.
		<span><?php echo $params->db_queries ?></span>.
	</div>
</footer>
