<!doctype html>
<html>
<head><title><?php echo $statusCode ?> Error</title>
<style>
html,body,table{height: 100%}
body{font: 1em "Trebuchet MS", Helvetica, sans-serif}
table{max-width: 300px; margin: auto;vertical-align: center}
</style>
</head>
<body>
<table><tr><td>
<h1><?php echo $statusCode ?> Error</h1>
<p><strong>This is an error</strong> and probably it wasn't your fault.
We'll try to understand if there is something to fix. In the meanwhile
you can try to go back to the <a href="<?php echo url_for_home() ?>">homepage</a>.
</td></tr></table>
</body>