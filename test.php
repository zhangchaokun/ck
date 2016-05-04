<?php


echo "<pre>";
var_dump($_SERVER);
echo "</pre>";

var_dump(strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']));

echo strlen($_SERVER['SCRIPT_NAME']);
echo substr($_SERVER['REQUEST_URI'],strlen($_SERVER['SCRIPT_NAME']));