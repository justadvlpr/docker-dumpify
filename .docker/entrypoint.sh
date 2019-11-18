#!/usr/bin/env bash

set -e

declare -p | grep -Ev 'BASHOPTS|BASH_VERSINFO|EUID|PPID|SHELLOPTS|UID' > /env.sh

/app/bin/dumpify setup

/app/bin/dumpify build-cron

chmod +x /app/runtime/*
chown -R dumpify:dumpify /app/runtime

crontab -u dumpify /var/spool/cron/crontabs/dumpify
/etc/init.d/cron start

php -a