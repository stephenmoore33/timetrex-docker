#!/bin/bash

envsubst < /var/www/html/timetrex/timetrex.ini.php.template > /var/www/html/timetrex/timetrex.ini.php
chown www-data:www-data /var/www/html/timetrex/timetrex.ini.php
chmod 664 /var/www/html/timetrex/timetrex.ini.php


# 2) Start Apache
exec apache2ctl -D FOREGROUND