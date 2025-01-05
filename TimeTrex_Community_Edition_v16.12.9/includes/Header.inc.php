<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo ( ( isset( $META_TITLE ) && $META_TITLE != '' ) ? $META_TITLE . ' | ' : '' ) . APPLICATION_NAME ?></title>
	<base href="<?php echo $BASE_URL; ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="Description" content="Workforce Management Software for tracking employee time and attendance, employee time clock software, employee scheduling software and payroll software all in a single package. Also calculate complex over time and premium time business policies and can identify labor costs attributed to branches and departments. Managers can now track and monitor their workforce easily."/>
	<script src="../html5/global/Debug.js?v=<?php echo APPLICATION_BUILD ?>"></script>
	<script src="../html5/global/CookieSetting.js?v=<?php echo APPLICATION_BUILD ?>"></script>
	<script src="../html5/framework/jquery.min.js?v=<?php echo APPLICATION_BUILD ?>"></script>

	<!--
	    TODO
		Various files have had paths changed or been commented out to avoid exceptions and restore appearance and functionality for
		ForgotPassword.php, ConfirmEmail.php and DownForMaintenance.php. Eventually these files should go through
		webpack the same as quick punch, recruitment and rest of the application.
	-->

	<?php
	//require_once( '../../../includes/API.inc.php' );
	//TTi18n::chooseBestLocale();
	//$api_auth = TTNew( 'APIAuthentication' ); /** @var APIAuthentication $api_auth */
	?>
	<!--<script src="../html5/global/APIGlobal.js.php?disable_db=1&v=<?php //echo APPLICATION_BUILD ?>"></script>-->
	<!--<script>
		var APIGlobal = function() {};
		APIGlobal.pre_login_data = <?php //echo json_encode( $api_auth->getPreLoginData() );?>//; //Convert getPreLoginData() array to JS.
	</script>-->
	<!--<script src="../../node_modules/underscore/underscore-min.js?v=--><?php //echo APPLICATION_BUILD ?><!--"></script>-->
	<!--<script src="../../node_modules/backbone/backbone-min.js?v=--><?php //echo APPLICATION_BUILD ?><!--"></script>-->
	<!--<script src="../html5/global/Global.js?v=--><?php //echo APPLICATION_BUILD ?><!--"></script>-->
	<script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.js?v=<?php echo APPLICATION_BUILD ?>"></script>
	<link rel="stylesheet" type="text/css" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css?v=<?php echo APPLICATION_BUILD ?>">
	<style rel="stylesheet" type="text/css">
		body {
			min-height: 100%;
			height: auto;
			width: 100%;
			position: absolute;
		}

		.footer {
			/*min-height: 68px;*/
			height: 68px;
			width: 100%;
			padding: 14px 0 14px;
			background-color: #262626;
			text-align: center;
			padding-top: 24px;
			margin-top: 40px;
			position: absolute;
			bottom: 0;
			left: 0;
		}

		.footer .footer-menu a:hover {
			text-decoration: none;
			cursor: pointer;
		}

		.footer .footer-copyright {
			color: #787878;
			margin: 0;
		}

		.company-logo img {
			max-height: 51px;
		}

		.navbar-DownForMaintenance {
			margin: 5px -15px 5px -15px;
		}

		.navbar-DownForMaintenance .company-logo img {
			max-height: 51px;
		}

		#contentBox {
			background: #fff;
			margin: 0 auto;
			position: relative;
			left: 0;
			padding: 0;
			/*width: 600px;*/
			max-width: 768px;
			/*padding: 15px;*/
		}

		#contentBox-DownForMaintenance, #contentBox-ConfirmEmail, #contentBox-ForgotPassword {
			background: #fff;
			margin: 0 auto;
			position: relative;
			left: 0;
			/*padding: 20px;*/
			/*width: 600px;*/
			border: 1px solid #779bbe;
			text-align: center;
		}

		.textTitle2 {
			color: #036;
			font-size: 16px;
			font-weight: bold;
			padding: 0;
			padding-left: 10px;
			margin: 0;
		}

		#contentBox-ForgotPassword .form-control-static {
			text-align: left;
		}

		#contentBox-DownForMaintenance .textTitle2, #contentBox-ConfirmEmail .textTitle2, #contentBox-ForgotPassword .textTitle2 {
			padding-left: 0;
			margin: 0;
			height: 60px;
			background: rgb(49, 84, 130);
			line-height: 60px;
			color: #fff;
		}

		/*#contentBox-ForgotPassword .textTitle2 {*/
		/*padding-left: 0;*/
		/*margin: 0;*/
		/*height: 60px;*/
		/*background: rgb(49,84,130);*/
		/*line-height: 60px;*/
		/*color: #fff;*/
		/*margin-bottom: 15px;*/
		/*}*/
		#contentBox-ForgotPassword .form-horizontal {
			margin: 15px;
		}

		#contentBox-ForgotPassword label {
			color: rgb(49, 84, 130);
		}

		@media (max-width: 767px) {
			#contentBox-ForgotPassword label {
				text-align: left;
			}
		}

		#contentBox-ForgotPassword .form-control {
			border-color: rgb(49, 84, 130);;
		}

		#contentBox-ForgotPassword .button {
			background: rgb(49, 84, 130);
			color: #FFFFFF;
		}

		#rowWarning {
			margin: 15px 30px;
			padding: 5px;
			border: 0px solid #c30;
			background: #FFFF00;
			font-weight: bold;
		}

		#contentBox-DownForMaintenance #rowWarning, #contentBox-ConfirmEmail #rowWarning, #contentBox-ForgotPassword #rowWarning {
			margin: 0;
			padding-top: 20px;
			padding-bottom: 20px;
			background: #FFFFFF;
			/*line-height: 60px;*/
			/*height: 60px;*/
		}

		#rowError {
			margin: 15px 30px;
			padding: 5px;
			border: 0px solid #c30;
			background: #FF0000;
			font-weight: bold;
		}
	</style>
</head>
<body>
<nav class="navbar navbar-default">
	<div class="container">
		<div class="navbar-header">
			<a tabindex="-1" class="company-logo" href="https://www.timetrex.com" title="Workforce Management Software"><img src="<?php echo Environment::getImagesURL(); ?>/timetrex_logo_wbg_small2.png" alt="Workforce Management Software"></a>
		</div>
	</div>
</nav>