#!/bin/bash

echo "Copying files..."
rsync -rltDuvih --exclude="/public_html/layouts/resources/Logo/logo" --exclude="/app_data/registration.php" --exclude="/app_data/shop" --exclude="/config" --exclude="/user_privileges" --exclude="/install" --exclude="/tests" --no-owner --no-group /tmp/yetiforce/* /var/www/html
echo "Copied."

if [[ ! -d /var/www/html/config ]] || [[ ! "$(ls -A /var/www/html/config)" ]]; then
  echo "Copying config..."
  cp -rv /tmp/yetiforce/config /var/www/html
  echo "Copied."
fi

if [[ ! -d /var/www/html/user_privileges ]]; then
  echo "Copying users..."
  cp -rv /tmp/yetiforce/user_privileges /var/www/html
  echo "Copied."
fi

echo "Initializing..."
mkdir -p /var/www/html/tests
cp -rv /tmp/yetiforce/tests/setup /var/www/html/tests
/var/www/html/tests/setup/dependency.sh
php -f /var/www/html/tests/setup/docker_post_install.php
rm -rf /var/www/html/tests

cp -rv /tmp/yetiforce/install /var/www/html

echo "Initialized."

if [[ ! -f /var/www/html/public_html/modules/OSSMail/roundcube/index.php ]]; then
  echo "Installing Roundcube..."
  shopt -s dotglob
  src="https://github.com/YetiForceCompany/lib_roundcube/archive/developer.zip"
  dest="/var/www/html/public_html/modules/OSSMail/roundcube"
  zip="/tmp/roundcube.zip"
  if [ "$INSTALL_MODE" != "DEV" ]; then
    ver=`php -r '$v = require "config/version.php"; echo $v["lib_roundcube"];'`
    src="https://github.com/YetiForceCompany/lib_roundcube/archive/$ver.zip"
  fi
  mkdir -p "$dest"
  wget -O "$zip" "$src"
  unzip -d "$dest" "$zip" && f=("$dest"/*) && mv "$dest"/*/* "$dest" && rmdir "${f[@]}"
  echo "Installed."
fi

chown -R www-data:www-data /var/www/
chown -R www-data:www-data /tmp/xdebug.log
chmod u+x /var/www/html/cron/cron.sh
chmod a+r /.git-credentials

if [[ -f /var/www/html/config/Db.php ]]; then
  if [[ ! -d /var/www/html/install ]]; then
    mkdir -p /var/www/html/install
  fi
  cp -v /tmp/yetiforce/install/Update.php /var/www/html/install/Update.php
  cp -rv /tmp/yetiforce/install/update-file-overrides /var/www/html/install/

  php -f /var/www/html/install/Update.php
  rm -r /var/www/html/install
  echo "Updated."
fi

pid=0
cronLock=cache/cron-stop

[ -f $cronLock ] && rm "$cronLock"

stopCrons() {
  echo "Disabling crons..."
  touch $cronLock
  fpmPid=$(<"/run/php/php$PHP_VER-fpm.pid")
  echo "Stopping nginx ($pid)..."
  kill -s QUIT "$pid"
  wait "$pid"
  echo "Stopping FPM ($fpmPid)..."
  kill -s QUIT "$fpmPid"
  while [ -e /proc/$fpmPid ]
  do
    sleep 1
  done
  echo "Waiting for PHP processes to finish..."
  while pgrep php >/dev/null; do
    sleep 1
  done
  echo "Exiting..."
  exit 3
}

trap 'stopCrons' SIGQUIT
trap 'stopCrons' SIGTERM

service cron start
/etc/init.d/php$PHP_VER-fpm start
/usr/sbin/nginx -g "daemon off;" &
pid="$!"

# wait forever
while true
do
  tail -f /dev/null & wait ${!}
done
