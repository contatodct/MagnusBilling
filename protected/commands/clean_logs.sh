#!/bin/bash

cd /var/log

rm -rf *202*
rm -rf asterisk/*202*
rm -rf httpd/*202*

rm -rf /var/www/html/mbilling/protected/runtime/*.log.*

echo '' > /var/log/fail2ban.log
echo '' > /var/log/messages
echo '' > /var/log/opensips
echo '' > /var/log/secure
echo '' > /var/log/maillog
echo '' > /var/log/mysqld.log
echo '' > /var/log/cron
echo '' > /var/log/asteriskSlave.log
echo '' > /var/log/asterisk/messages
echo '' > /var/log/asterisk/fail2ban
echo '' > /var/log/asterisk/queue_log
echo '' > /var/log/httpd/access_log
echo '' > /var/log/httpd/error_log
echo '' > /var/log/httpd/deflate_log
echo '' > /var/log/httpd/ssl_access_log
echo '' > /var/log/httpd/ssl_error_log
echo '' > /var/log/httpd/ssl_request_log
