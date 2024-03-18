FROM debian:bullseye

ARG DEBIAN_FRONTEND=noninteractive
ARG BUILD_PHP_VER=7.4
ARG BUILD_INSTALL_MODE=PROD
ARG BUILD_SSL=OFF
ARG BUILD_TZ=Europe/Warsaw

ENV PHP_VER ${BUILD_PHP_VER}
#BUILD_INSTALL_MODE = PROD , DEV
ENV INSTALL_MODE ${BUILD_INSTALL_MODE}
ENV TZ ${BUILD_TZ}

ENV PROVIDER docker

RUN echo ttf-mscorefonts-installer msttcorefonts/accepted-mscorefonts-eula select true | debconf-set-selections && \
		apt-get update && \
		apt-get install -y --no-install-recommends \
		apt-utils \
		curl \
		openssl \
		wget \
		ca-certificates \
		apt-transport-https \
		lsb-release \
		gnupg \
		rsync && \
		wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
		echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list && \
		curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
		echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
		echo "deb http://deb.debian.org/debian bullseye contrib" | tee /etc/apt/sources.list.d/bulleseye-contrib.list && \
		apt-get update && \
		apt-get install -y --no-install-recommends \
		nginx \
		nginx-extras \
		ghostscript \
		"php${BUILD_PHP_VER}"-fpm \
		"php${BUILD_PHP_VER}"-mysql \
		"php${BUILD_PHP_VER}"-curl \
		"php${BUILD_PHP_VER}"-intl \
		"php${BUILD_PHP_VER}"-gd \
		"php${BUILD_PHP_VER}"-bcmath \
		"php${BUILD_PHP_VER}"-soap \
		"php${BUILD_PHP_VER}"-ldap \
		"php${BUILD_PHP_VER}"-imap \
		"php${BUILD_PHP_VER}"-xml \
		"php${BUILD_PHP_VER}"-cli \
		"php${BUILD_PHP_VER}"-zip \
		"php${BUILD_PHP_VER}"-json \
		"php${BUILD_PHP_VER}"-opcache \
		"php${BUILD_PHP_VER}"-mbstring \
		"php${BUILD_PHP_VER}"-apcu \
		"php${BUILD_PHP_VER}"-imagick \
		"php${BUILD_PHP_VER}"-ssh2 \
		php-sodium \
		zip \
		unzip \
		git \
		git-lfs \
		nodejs \
		npm \
		yarn \
		vim \
		cron \
		logrotate \
		ure \
		openjdk-11-jre \
		fonts-opensymbol \
		hyphen-fr \
		hyphen-de \
		hyphen-en-us \
		hyphen-it \
		hyphen-ru \
		fonts-dejavu \
		fonts-dejavu-core \
		fonts-dejavu-extra \
		fonts-droid-fallback \
		fonts-dustin \
		fonts-f500 \
		fonts-fanwood \
		fonts-freefont-ttf \
		fonts-liberation \
		fonts-lmodern \
		fonts-lyx \
		fonts-sil-gentium \
		fonts-texgyre \
		fonts-tlwg-purisa \
    fonts-crosextra-carlito \
    fonts-crosextra-caladea \
  	ttf-mscorefonts-installer \
    tesseract-ocr \
		libsm6 libglu1-mesa libxinerama1 && \
		(([ "${BUILD_INSTALL_MODE}" = "DEV" ] && apt-get install -y --no-install-recommends "php${BUILD_PHP_VER}"-xdebug) || true) && \
		echo "deb http://deb.debian.org/debian bookworm main" | tee /etc/apt/sources.list.d/bookworm.list && \
		echo "APT::Default-Release \"stable\";" | tee /etc/apt/apt.conf.d/default-release && \
		apt-get update && \
		apt install -y --no-install-recommends -t bookworm poppler-utils && \
		apt-get -y clean && \
		apt-get autoremove --yes && \
		rm -rf /var/lib/apt/lists/* && \
		rm /var/www/html/index.nginx-debian.html

COPY ./ /tmp/yetiforce/

RUN (([ "${BUILD_SSL}" = "OFF" ] && mv /tmp/yetiforce/tests/setup/nginx/www.conf /etc/nginx/sites-available/default) || mv /tmp/yetiforce/tests/setup/nginx/www.ssl.conf /etc/nginx/sites-available/default) && \
		mv /tmp/yetiforce/tests/setup/nginx/yetiforce.conf /etc/nginx/yetiforce.conf && \
		echo "worker_shutdown_timeout 240;" >> /etc/nginx/modules-enabled/00-mod-shutdown.conf && \
		mv /tmp/yetiforce/tests/setup/fpm/www.conf /etc/php/$PHP_VER/fpm/pool.d/www.conf && \
		mv /tmp/yetiforce/tests/setup/crons.conf /etc/cron.d/yetiforcecrm && \
		mv /tmp/yetiforce/tests/setup/logrotate.conf /etc/logrotate.d/yetiforce && \
		rm /etc/cron.daily/logrotate && \
		echo "" >> /etc/cron.d/yetiforcecrm && \
		chmod 600 /etc/cron.d/yetiforcecrm && \
		chmod 600 /etc/logrotate.d/yetiforce && \
		echo "installing LibreOffice" && \
		mkdir /tmp/libreoffice && \
		tar --wildcards -C /tmp/libreoffice --strip-components=2 -xvf /tmp/yetiforce/install/software/LibreOffice_7.3.4_Linux_x86-64_deb.tar.gz *.deb && \
		rm /tmp/libreoffice/*debian-menus*.deb /tmp/libreoffice/*draw*.deb /tmp/libreoffice/*firebird*.deb \
		  /tmp/libreoffice/*integration*.deb /tmp/libreoffice/*impress*.deb /tmp/libreoffice/*onlineupdate*.deb \
			/tmp/libreoffice/*base*.deb /tmp/libreoffice/*librelogo*.deb /tmp/libreoffice/*dict-es*.deb \
			/tmp/libreoffice/*dict-fr*.deb /tmp/libreoffice/*ogltrans*.deb /tmp/libreoffice/*postgresql*.deb && \
		rm /tmp/yetiforce/install/software/LibreOffice_7.3.4_Linux_x86-64_deb.tar.gz && \
		dpkg -i /tmp/libreoffice/*.deb && \
		([ ! -f /usr/bin/libreoffice ] && ln -s /opt/libreoffice7.3/program/soffice /usr/bin/libreoffice) && \
		(([ "${BUILD_INSTALL_MODE}" = "DEV" ] && mv /tmp/yetiforce/tests/setup/php/dev.ini /etc/php/${BUILD_PHP_VER}/mods-available/yetiforce.ini) || mv /tmp/yetiforce/tests/setup/php/prod.ini /etc/php/${BUILD_PHP_VER}/mods-available/yetiforce.ini) && \
		mv /tmp/yetiforce/tests/setup/docker_entrypoint.sh / && \
		# crontab /etc/cron.d/yetiforcecrm && \
		ln -s "/etc/php/${BUILD_PHP_VER}/mods-available/yetiforce.ini" "/etc/php/${BUILD_PHP_VER}/cli/conf.d/30-yetiforce.ini" && \
		ln -s "/etc/php/${BUILD_PHP_VER}/mods-available/yetiforce.ini" "/etc/php/${BUILD_PHP_VER}/fpm/conf.d/30-yetiforce.ini" && \
		sed -i "s/7.3/${BUILD_PHP_VER}/" /etc/nginx/sites-available/default && \
		sed -i "s/7.3/${BUILD_PHP_VER}/" /docker_entrypoint.sh && \
		sed -i "s/7.3/${BUILD_PHP_VER}/" /docker_entrypoint.sh && \
		curl -sS https://getcomposer.org/installer | php && \
		mv composer.phar /usr/local/bin/composer && \
		chmod +x /usr/local/bin/composer && \
		chmod +x /tmp/yetiforce/tests/setup/dependency.sh && \
		chmod +x /docker_entrypoint.sh && \
		echo "PROVIDER=docker" > /etc/environment && \
		git config --system user.email "32871390+michal-b-kaminski@users.noreply.github.com" && \
		git config --system user.name "PDSS - export" && \
		git config --system credential.helper 'store --file=/.git-credentials' && \
		mkdir -p /etc/pki/tls/certs && \
		wget -O /etc/pki/tls/certs/ca-bundle.crt https://curl.se/ca/cacert.pem && \
		ln -sf "/usr/share/zoneinfo/${BUILD_TZ}" /etc/localtime && \
		sed -i 's/invoke-rc.d nginx rotate/service nginx rotate/' /etc/logrotate.d/nginx

WORKDIR /var/www/html

EXPOSE 80
EXPOSE 443

VOLUME /var/www/html

ENTRYPOINT [ "/docker_entrypoint.sh" ]
