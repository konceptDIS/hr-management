#!/bin/bash

/usr/sbin/a2dissite '*' && /usr/sbin/a2ensite leave-site.conf leave-site-ssl.conf
/usr/sbin/apache2ctl -D FOREGROUND