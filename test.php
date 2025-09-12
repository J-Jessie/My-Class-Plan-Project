<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo $undefined_variable; // This should trigger a warning
sudo nano /etc/php/8.2.12/apache2/php.ini
