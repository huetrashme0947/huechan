<?php
	$offset_time = isset($_GET["offset_time"]) ? date("Y-m-d H:i:s", $_GET["offset_time"]) : null;

	// Build Query
	$query = "SELECT `id` FROM `reports` WHERE `status` IS NULL ";
	if ($offset_time != null) { $query .= "AND `datetime` > ? "; }
	$query .= "ORDER BY `datetime` ASC LIMIT 20;";
	
	// Get Reports from database
	$statement = $database->prepare($query);
	$statement->execute($offset_time != null ? [$offset_time] : null);
	$results = $statement->fetchAll();

	if (sizeof($results) < 20) { $ret_last = true; }

	$reports = array();
	foreach ($results as $column) { $reports[] = $column[0]; }
	$reports_assocs = array();
	foreach ($reports as $report) {
		$report_assoc = getReportAssoc($database, $report, false);
		if ($report_assoc != null && $report_assoc != 0) { $report_assocs[] = $report_assoc; }
	}
	$reports = $report_assocs;
	if ($ret_last) { $reports[] = "last"; }

	// If everything went well, disconnect database and exit
	$database = null;
	exit("OK ".json_encode($reports));
?>