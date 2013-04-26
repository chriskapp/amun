version=`php -r "echo version_compare(PHP_VERSION, '5.4.0');"`
if [ $version -ge 0 ]; then echo 'Starting PHP webserver'; php -S 0.0.0.0:8000 & fi