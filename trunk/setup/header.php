<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo htmlspecialchars($_SESSION["setup-title"], ENT_HTML5) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- With IE 8 and 9, use only edge engine rendering (more compliant with web standards) -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

	<style>
		body {
		    background-color: #F8ECE0;
		}
		h1 {
		    border-bottom: 1px dashed #000000;
		    margin: 0;
		    padding: 0;
		}
	</style>
</head>
<body>
<h1><?php echo htmlspecialchars($_SESSION["setup-title"], ENT_HTML5) ?></h1>
<nav><?php echo htmlspecialchars($_SESSION["setup-nav"], ENT_HTML5) ?></nav>
