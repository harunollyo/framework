@echo off

set "DIR=%~dp0"

php -d auto_prepend_file= "%DIR%run-phpcs.php" phpcbf %*
