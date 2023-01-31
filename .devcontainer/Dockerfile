ARG VARIANT=8.2

FROM mcr.microsoft.com/devcontainers/php:0-${VARIANT}

ENV DEBIAN_FRONTEND=noninteractive

# Install MariaDB client
RUN \
  apt-get update && \
  # MariaDB
  apt-get install -y mariadb-client && \
  docker-php-ext-install mysqli pdo pdo_mysql && \
  # Postgres
  apt-get install sudo && \
  apt-get install -y libpq-dev && \
  apt-get install -y postgresql postgresql-contrib && \
  docker-php-ext-install pdo pdo_pgsql && \
  # Cleanup
  apt-get clean -y && \
  rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ARG USERNAME
ENV XDEBUG_MODE=off

# https://code.visualstudio.com/remote/advancedcontainers/avoid-extension-reinstalls
RUN \
  # Referred as volume (vscode-extensions) in docker-compose.yml
  mkdir -p /home/$USERNAME/.vscode-server/extensions && \
  chown -R $USERNAME /home/$USERNAME/.vscode-server

# https://www.garygitton.fr/setup-xdebug-php-docker/
RUN \
  sed -i 's/xdebug.mode = debug/xdebug.mode = off/g' /usr/local/etc/php/conf.d/xdebug.ini
