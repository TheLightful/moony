<?php
    // function __autoload($classname) {
    //     $filename = "./". $classname .".class.php";
    // }
	include_once('Moon.class.php');
	$m = new Moon();
	// var_dump($m);
	// echo $m->age;
	// die();

    // include_once("MoonStatic.class.php");
    // $luna = new MoonStatic();
    // list($MoonPhase, $MoonAge, $MoonDist, $MoonAng, $SunDist, $SunAng, $mpfrac) = $luna->phase(2016, 05, 20, 00, 00, 01);
    // $lunera = $luna->phase(2016, 05, 21, 12, 30, 01);
    // var_dump($lunera);
    // die();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Moony - North Emisphere</title>
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<style>
		body{
			background-color: #e1e1e1;
			background-image: url('img/bg.jpg');
			background-position: top center;
		}
		.moon{
			z-index: 5;
			position: absolute;
			top: 1px;
			left: 13px;
			/*padding-left: 6px;*/
			float:left;
			-webkit-filter: opacity(.3) brightness(.8) contrast(1.4);
			filter: opacity(.3) brightness(.8) contrast(1.4);
			width: 300px;
			height: 300px;
		}
		canvas{
			filter: blur(.1);
		}
		.panel{
			margin-top: 3rem;
		}
		.panel .panel-heading{
			background-color: #0D363C;
			color: #fff;
			font-weight: 700;
		}
		.list-group-item{
			background-color: #e1e1e1;
		}
	</style>
</head>
<body>
	
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<canvas width="300" height="300" id="moon"></canvas>
				<div class="moon">
					<img src="img/moon2.png" alt="">
				</div>
			</div>
			<div class="col-md-8">
				
				<div class="panel">
					<div class="panel-heading">
						Moony
					</div>
						<ul class="list-group">
							<? if(!is_null($m->phase)){  ?>
								<li class="list-group-item">
									Phase: <?= $m->phase ?>
								</li>
							<? } ?>
							<li class="list-group-item">
								Illumination: <?= $m->illumination ?> %
							</li>
							<li class="list-group-item">
								Distance: <?= $m->distance ?> km
							</li>
							<li class="list-group-item">
								Age: <?= $m->age ?> days
							</li>
							<li class="list-group-item">
								<strong><?= $m->getStatus() ?></strong>
							</li>

						</ul>
				</div>

				<p>
					
				</p>
			</div>
		</div>
	</div>

	
	<script>
		function drawMoon(luminosity, moonAge){
			var canvas = document.getElementById('moon');
			var ctx = canvas.getContext('2d');
			console.log(moonAge);

			// calculate xPos for the Bezier control points
			// Our limit goes between -10 and 310, so it's 320 - 10
			// get the xPos from luminosity( 0% -> -10, 100% -> 310)
			var xPos = ((luminosity * 320) / 100) - 10;
			console.log(xPos, luminosity);
			
			// Clear the canvas to start from scratch
			ctx.clearRect(0, 0, canvas.width, canvas.height);

			// Draw moon bg: dark grey circle
			ctx.beginPath();
			ctx.moveTo(150,30);
			ctx.arc(150,150,120,0,2*Math.PI);
			ctx.fillStyle = '#111';
			ctx.fill();
			// ctx.closePath();


			// TO DO :
			
			// Draw the moon itself
			ctx.beginPath();
			// Start from the top center
			ctx.moveTo(150,30);

			if( moonAge < 14 ){ //waning moon

				ctx.bezierCurveTo(310,30,310,270,150,270);
				ctx.bezierCurveTo(300-xPos,270,300-xPos,30,150,30);

			}else{ // waxing moon

				// Draw the current moon phase with the bezier
				ctx.bezierCurveTo(xPos,30,xPos,270,150,270);
				// and Draw another bezier outside the circle throught the left
				ctx.bezierCurveTo(-10,270,-10,30,150,30);
			}
			

			ctx.lineWidth = 3;
			ctx.fillStyle = 'white';
			ctx.fill();
			ctx.strokeStyle = 'white';
			// ctx.stroke();
		}

		drawMoon(<?php echo $m->illumination ?>, <?php echo $m->age ?>);
		// drawMoon(85, 17);
		
	</script>
	<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> -->
	
</body>
</html>