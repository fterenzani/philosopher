<!doctype>
<html>
<head><title><?php echo $statusCode ?> Error</title></head>
<style>
body {font: 1em "Trebuchet MS", Helvetica, sans-serif;}
.container{max-width: 300px; padding: 20px;margin: auto;
position: relative;
top: 50%;
-webkit-transform: translateY(-50%);
-ms-transform: translateY(-50%);
transform: translateY(-50%);
}
</style>
<body>
<div class="container">
<h1><?php echo $statusCode ?> Error</h1>
<p><strong>This is an error</strong> and probably it wasn't your fault.
We'll try to understand if there is something to fix. In the meanwhile
you can try to go back to the <a href="<?php echo url_for_home() ?>">homepage</a>.
</div>
</body>