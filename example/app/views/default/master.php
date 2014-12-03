<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="pragma" content="no-cache" />

	<meta charset='utf-8'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="author" content="ElPepe aunsoyjoven {at} hotmail.com" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"><!-- Remove if you're not building a responsive site. (But then why would you do such a thing?) -->	
	<title><?php echo !isset($data->title)?'':$data->title; ?> | Stabilizat</title>
<?php 		echo !isset($data->css)?'':$data->css; ?>
	<link rel="shortcut icon" type="image/png" <?php //sizes="16x16" ?> href="<?php echo !isset($data->templateurl)?'':$data->templateurl; ?>/public/img/favicon.ico"/>
</head>
	<body class="<?php echo $data->templatename; ?>">
	
		<div class="header">
			<div class="inner">
<?php 			echo !isset($data->header)?'':$data->header; ?>
			</div>
		</div>
		
		<div class="content">
			<div class="inner">
				<!-- h1><?php echo $data->title; ?></h1 -->
<?php 			echo !isset($data->content)?'':$data->content; ?>
			</div>
		</div>
		
		<div class="sidebar">
			<div class="inner">
<?php 			echo !isset($data->sidebar_left)?'':$data->sidebar_left; ?>
				<div class="footer">
					<div class="inner">
<?php 					echo !isset($data->footer)?'':$data->footer; ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="printable"></div>
		
<?php 		echo !isset($data->js)?'':$data->js; ?>
	</body>	
</html>
