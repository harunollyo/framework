@echo off

set "DIR=%~dp0"

php -n -d auto_prepend_file= "%DIR%run-phpcs.php" %*
