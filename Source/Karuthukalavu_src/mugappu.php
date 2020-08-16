<?php
header("Access-Control-Allow-Origin:'self'");
header("Content-Security-Policy:default-src 'self'");
?>
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>கருத்துக்களவு சோதனை</title>
<!-- <link rel="stylesheet" href="js/jquery-ui.min.css" />
<link rel="stylesheet" href="js/jquery-ui.structure.min.css" />
<link rel="stylesheet" href="js/jquery-ui.theme.min.css" /> -->
<link rel="stylesheet" href="paani.css" />
</head>
<body class="lefrog">
	<div id="main" class="main">
		<div class="header clear">
			<div class="logo tim"></div>
			<div class="title">கருத்துக்களவு ஆய்வி</div>
			<div class="logo tva" title="http://www.tamilvu.org/"></div>
			<div class="logo bsa" title="http://www.bsauniv.ac.in/"></div>
		</div>
		<div id="menu">
			<ul class="menu clear">
				<li data-page="sothanai">முகப்பு</li>
				<li data-page="karuthu">கருத்து</li>
				<li data-page="patri">பற்றி</li>
			</ul>
		</div>
		<div id="content" class="content clear"></div>
		<div class="footer clear">
			<span>இத்தளத்தை பற்றிய உங்கள் கருத்துக்களை <a data-page="karuthu">இங்கே</a> பதியவும். இத்தளத்தை பயன்படுத்துவதன் மூலம் தாங்கள் <a data-page="patri#varaiyaraikal">இத்தொடுப்பில்</a>
				உள்ள வரையறைகளுக்கு இணங்குகிறீர்கள்.</span>
		</div>
	</div>
	<script type="text/javascript" src="js/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="js/mugappu.js"></script>
</body>
</html>
