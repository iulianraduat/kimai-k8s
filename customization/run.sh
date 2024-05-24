cd /mnt
cp -r . /opt/kimai
cd /opt/kimai
bin/console kimai:reload --env=prod
chown -R www-data:www-data var/cache
