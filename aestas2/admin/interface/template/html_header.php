<!DOCTYPE html>

<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo $params->title ?></title>
	<link rel="stylesheet" type="text/css" href="interface/css/screen.css" />
<?php foreach( $params->css as $stylesheet ): ?>
	<link rel="stylesheet type="text/css" href="interface/css/<?php echo $stylesheet ?>" />
<?php endforeach ?>
	<script type="text/javascript" src="interface/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="interface/js/loaded-in-head.js"></script>
</head>
<body>
