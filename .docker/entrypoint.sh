#!/usr/bin/env bash

set -e

/app/bin/dumpify setup

/app/bin/dumpify build-cron

crontab -u dumpify /app/runtime/crontab
/etc/init.d/cron start

php -a