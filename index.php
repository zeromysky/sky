<!doctype html>
<html lang="zh">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="format-detection" content="telephone=no, email=no" />
	<title>图像处理</title>
	<link rel="stylesheet" type="text/css" href="css/normalize.css" />
	<link rel="stylesheet" type="text/css" href="css/default.css">
	<style>
	#clipArea {
		margin: 20px;
		height: 360px;
	}
	#file,
	#clipBtn {
		margin: 20px;
	}
	#view {
		-webkit-border-radius: 50%;
		-moz-border-border-radius: 50%;
		margin: 0 auto;
		width: 335px;
		height: 335px;
	}
	</style>
	<!--[if IE]>
		<script src="http://libs.useso.com/js/html5shiv/3.7/html5shiv.min.js"></script>
	<![endif]-->
</head>
<body ontouchstart="">
	<article class="htmleaf-container">
		
		<div id="clipArea"></div>
		<input type="file" id="file">
		<button id="clipBtn">截取</button>
		<div id="view"></div>
		<br>
		<form name="thumbnail" action="./upload.php" method="post">
			<input type="hidden" name="image_data" value="" id="image_data" />
			<input type="hidden" name="stime" value="<?=time()?>" >
			<input type="submit" name="upload_thumbnail" value="保存" id="submit_save"  />
		</form>
		<br>
	</article>
	
	<script>window.jQuery || document.write('<script src="js/jquery-2.1.1.min.js"><\/script>')</script>
	<script src="js/iscroll-zoom.js"></script>
	<script src="js/hammer.js"></script>
	<script src="js/jquery.photoClip.js"></script>
	<script>
	//document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
	$("#clipArea").photoClip({
		width: 335,
		height: 335,
		file: "#file",
		view: "#view",
		ok: "#clipBtn",
		loadStart: function() {
			// console.log("照片读取中");
		},
		loadComplete: function() {
			// console.log("照片读取完成");
		},
		clipFinish: function(dataURL) {
			$('#image_data').val(dataURL);
			// console.log(dataURL);
		}
	});

	$("#submit_save").click(function(){
		if($('#image_data').val()==''){
			alert("上传后，请裁剪！")
			return false;
		}
	});
	</script>
</body>
</html>