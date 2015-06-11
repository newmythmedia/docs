<?php
/**
 * This file is the main page display layout. It must have a {content} tag
 * in order for the content to show up within the page. Other than that, it must
 * include all CSS, etc.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<title><?= $title ?></title>

	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets.php?theme=default&file=styles.css">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>

	<!-- Navbar -->
	<nav class="navbar navbar-default">
	  <div class="container">
	    <!-- Brand and toggle get grouped for better mobile display -->
	    <div class="navbar-header">
	      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
	        <span class="sr-only">Toggle navigation</span>
	        <span class="icon-bar"></span>
	        <span class="icon-bar"></span>
	        <span class="icon-bar"></span>
	      </button>
	      <a class="navbar-brand" href="#"><?= $site_name ?></a>
	    </div>

		  <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">

	            <?php if (isset($collection_names) && is_array($collection_names)) : ?>
	                <?php foreach ($collection_names as $collection) : ?>
	                <li>
		                <a href="/<?= $collection ?>"><?= ucfirst(strtolower($collection)) ?></a>
	                </li>
	                <?php endforeach; ?>
	            <?php endif; ?>

            </ul>
		  </div>

	  </div>
	</nav>

	<div class="container outer-wrap">

		<!-- Side Navigation -->
		<div class="col-md-2 sidebar">
			<?= $sidebar ?>
		</div>

		<!-- Body -->
		<div class="col-md-10 main">
			{contents}
		</div>


	</div>


	<!-- Footer -->
	<div class="footer">
		<div class="container">
			<p>Page rendered in {elapsed_time} seconds using {memory_usage}.</p>
		</div>
	</div>

</body>
</html>