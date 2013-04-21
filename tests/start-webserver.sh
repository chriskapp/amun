version=`php -r "echo version_compare(PHP_VERSION, '5.4.0');"`
if [ $version -ge 0 ]; then echo 'Starting PHP webserver'; php -S 127.0.0.1:80 -t .. & fi
