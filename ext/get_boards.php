<?php
	// Get all Boards
	$statement = $database->prepare("SELECT `id`, `description`, (SELECT COUNT(`posts`.`id`) FROM `posts` WHERE `posts`.`board` = `boards`.`id` AND `posts`.`inreplyto` IS NULL), (SELECT COUNT(`posts`.`id`) FROM `posts` WHERE `posts`.`board` = `boards`.`id`) FROM `boards` ORDER BY `id` ASC");
	$statement->execute();
	$results = $statement->fetchAll();
?>