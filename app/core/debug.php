<?php

// Make sure the registry object is present. If not lets get it.
if (!isset($registry)) {
	$registry = Registry::getInstance();
}

if ($registry->debug_mode) {

	// Make sure the DB object is present. If not lets get it.
	if (!isset($oDB)) {
		$oDB = mysql_db::getInstance();
	}

	$queries = $oDB->getQueries();
	?>
	<br clear="all" /><br /><br /><br /><br />
	<hr />
	<div style="font-size: 1.3em; margin-bottom: 0;">
		<h2>SESSION VARIABLES</h2>
		
		<?php foreach ($_SESSION as $k => $v): ?>
			<div style="border: 1px solid #C3C3C3; padding: 10px; margin-bottom: 0;"><strong><?= $k ?>:</strong> <?= $v ?></div>
		<?php endforeach; ?>
		
	</div>
	<hr />

	<div style="font-size: 1.3em; margin-bottom: 10px;">
		<h2>Total Page Execution Time: <?= number_format(($pExTimeEnd - $pExTimeStart),5) ?> seconds</h2>
		<hr />
		
		<h2>Total DB Queries/Execution Time: <?= sizeof($queries) ?> / <?= number_format($oDB->getTotalQueryExTime(),5) ?> seconds</h2>
		<?php for ($i = 0; $i < sizeof($queries); $i++): ?>
			<div style="border: 1px solid #C3C3C3; padding: 10px; margin-bottom: 10px;">
				<strong>Query:</strong> <?= $queries[$i]['query'] ?><br />
				<strong>File:</strong> <?= $queries[$i]['file'] ?><br />
				<strong>Line:</strong> <?= $queries[$i]['line'] ?><br />
				<strong>Execution Time:</strong> <?= $queries[$i]['executionTime'] ?><br />
			</div>
		<?php endfor; ?>
	</div>

	<?php
}
