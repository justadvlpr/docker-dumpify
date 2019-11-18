#!/usr/bin/env bash

set -e

declare -p | grep -Ev 'BASHOPTS|BASH_VERSINFO|EUID|PPID|SHELLOPTS|UID' > /envs.env

/app/bin/dumpify setup

/app/bin/dumpify build-cron

crontab -u dumpify /app/runtime/crontab
/etc/init.d/cron start

php -a