<?php
	function connect_database() {
		$host = "";
		$port = "";
		$dbname = "";
		$uname = "";
		$pw = "";

		try {
			$connection = new PDO("mysql:host=".$host.";port=".$port.";dbname=".$dbname, $uname, $pw);
			$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $connection;
		} catch(PDOException $exception) {
			return false;
		}
	}
?>
