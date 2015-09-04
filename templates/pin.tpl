<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
    <title>Copypaste service - Pin</title>
    <link rel="stylesheet" href="static/css/style.css">
    <script type="text/javascript" src="//yastatic.net/jquery/2.1.3/jquery.min.js"></script>
    <script type="text/javascript" src="https://rawgit.com/benmajor/jQuery-Touch-Events/master/src/1.0.0/jquery.mobile-events.min.js"></script>
    <script type="text/javascript" src="/static/js/pin.js"></script>

    <style>
		/* Reset */

		* {
		  -webkit-tap-highlight-color: rgba(0,0,0,0);
		}

		body {
		  -webkit-touch-callout: none;
		  -webkit-text-size-adjust: none;
		  -webkit-user-select: none;
		  font-size:12px;
		  height:100%;
		  width:100%;
		  margin:0px;
		  padding:0px;
		  background:#141B18;
		}

		ul {
		  list-style: none;
		  padding: 0;
		  margin: 0;
		}
		/* Reset */

		#wrap {
		  position: absolute;
		  top: 50%;
		  left: 50%;
		  width: 196px;
		  margin-left: -100px;
		  margin-top: -200px;
		}

		#display {
		  position: relative;
		  top: -10px;
		  padding: 10px;
		  text-align: center;
		  border-bottom: 1px solid #00D881;
		}

		#numpad {
		  position: relative;
		}

		#display li {
		  display: inline-block;
		  font-family: monospace;
		  font-size: 200%;
		  padding: 5px 12px;
		  background: #FDD147;
		  color: black;
		  margin-right: 1px;
		}

		#numpad li {
		  font-family: monospace;
		  font-size: 200%;
		  border-radius: 51%;
		  float: left;
		  padding: 12px 20px;
		  background: #00D881;
		  color: black;
		  margin: 5px;
		  cursor: pointer;
		  -webkit-transition: box-shadow 0.3s ease-in-out;
		  -moz-transition: box-shadow 0.3s ease-in-out;
		  -o-transition: box-shadow 0.3s ease-in-out;
		  -ms-transition: box-shadow 0.3s ease-in-out;
		  transition: box-shadow 0.3s ease-in-out; 
		}

		.pressed {
		  box-shadow: inset 2px 2px 12px 5px rgba(0,0,0,0.5);
		}

		.clear {
		  clear: both;
		}

		#title {
		  font-family: monospace;
		  color: #00D881;
		  text-align: center;
		  font-size: 120%;
		}
    </style>
</head>
<body>
	<input type="hidden" id="link-url" value="{$link.url}">
	<div id="wrap">
	<p id="title">This is Secure Link, enter PIN and press [E] key</p>
  <ul id="display">
    <li>+</li>
    <li>+</li>
    <li>+</li>
    <li>+</li>
    <div class="clear"></div>
  </ul>
  <ul id="numpad">
    <li>1</li>
    <li>2</li>
    <li>3</li>
    <li>4</li>
    <li>5</li>
    <li>6</li>
    <li>7</li>
    <li>8</li>
    <li>9</li>
    <li>C</li>
    <li>0</li>
    <li>E</li>
  </ul>
</div>
</body>
</html>