version=`php -r "echo version_compare(PHP_VERSION, '5.4.0');"`
if [$version >= 0]; then echo 'Starting PHP webserver'; php -S localhost:80 -t ../public & fi