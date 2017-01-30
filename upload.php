<?php

error_reporting (E_ALL ^ E_NOTICE);
session_start(); 

if($_POST["image_data"]){

	if($_POST['stime']!=$_SESSION['stime']){
		$_SESSION['stime'] = $_POST['stime'];

		$base64_image_content = $_POST['image_data'];
		//匹配出图片的格式
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
			$type = $result[2];
			$new_file = "upload/".date('Ymd',time())."/";
			if(!file_exists($new_file))
			{
				@mkdir($new_file, 0777);
			}
			$new_file = $new_file.time();
			$new_name = $new_file.".{$type}";
			$new_name_png = $new_file.".png";
			$out_image = $new_file."_hc".".jpg";
			if (file_put_contents($new_name, base64_decode(str_replace($result[1], '', $base64_image_content)))){
				// echo '新文件保存成功：', $new_name;

				list($width, $height)=getimagesize($new_name);

				
				$n_w=335;
				$n_h=335;
				$new=imagecreatetruecolor($n_w, $n_h);
				$img=imagecreatefromjpeg($new_name);
				//copy部分图像并调整
				imagecopyresized($new, $img,0, 0,0, 0,$n_w, $n_h, $width, $height);
				//图像输出新图片、另存为
				imagejpeg($new, $new_name);
				imagedestroy($new);
				imagedestroy($img);
				
				#裁剪
				$imgg = radius_img($new_name, 167);
				imagepng($imgg,$new_name_png);
				imagedestroy($imgg);

				#合成
				//背景图片 
				$bg_img = Imagecreatefromjpeg('css/bg.jpg'); 

				$image01 = Imagecreatefrompng($new_name_png); 
				$image01_size = getimagesize($new_name_png);

				imagecopy($bg_img,$image01,151,258,0,0,$image01_size[0],$image01_size[1]);  
				// Imagejpeg($bg_img); 
				Imagejpeg($bg_img,$out_image); 
				imagedestroy($bg_img);
				$_SESSION['last_image'] = $out_image;
				echo "<script>alert('保存成功！')</script>";

			}else{
				$_SESSION['last_image'] = '';
				echo '保存失败,请重新操作！';
			}
		}
	}

}



/**
 * blog:http://www.zhaokeli.com
 * 处理圆角图片
 * @param  string  $imgpath 源图片路径
 * @param  integer $radius  圆角半径长度默认为15,处理成圆型
 * @return [type]           [description]
 */
function radius_img($imgpath = './t.png', $radius = 15) {
	$ext     = pathinfo($imgpath);
	$src_img = null;
	switch ($ext['extension']) {
	case 'jpg':
	case 'jpeg':
		$src_img = imagecreatefromjpeg($imgpath);
		break;
	case 'png':
		$src_img = imagecreatefrompng($imgpath);
		break;
	}
	$wh = getimagesize($imgpath);
	$w  = $wh[0];
	$h  = $wh[1];
	// $radius = $radius == 0 ? (min($w, $h) / 2) : $radius;
	$img = imagecreatetruecolor($w, $h);
	//这一句一定要有
	imagesavealpha($img, true);
	//拾取一个完全透明的颜色,最后一个参数127为全透明
	$bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
	imagefill($img, 0, 0, $bg);
	$r = $radius; //圆 角半径
	for ($x = 0; $x < $w; $x++) {
		for ($y = 0; $y < $h; $y++) {
			$rgbColor = imagecolorat($src_img, $x, $y);
			if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
				//不在四角的范围内,直接画
				imagesetpixel($img, $x, $y, $rgbColor);
			} else {
				//在四角的范围内选择画
				//上左
				$y_x = $r; //圆心X坐标
				$y_y = $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
				//上右
				$y_x = $w - $r; //圆心X坐标
				$y_y = $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
				//下左
				$y_x = $r; //圆心X坐标
				$y_y = $h - $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
				//下右
				$y_x = $w - $r; //圆心X坐标
				$y_y = $h - $r; //圆心Y坐标
				if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
			}
		}
	}
	return $img;
}





?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>图片处理</title>
</head>
<body>
	<!-- <h1><a href="./index.php">返回图片上传</a></h1> -->
	<?php if($_SESSION['last_image']){ ?>
	<img src="<?=$_SESSION['last_image']?>" style="width:100%" alt="">
	<?php }else{ ?>
	<img src="css/bg.jpg" style="width:100%"  alt="">
	<?php } ?>
</body>
</html>






