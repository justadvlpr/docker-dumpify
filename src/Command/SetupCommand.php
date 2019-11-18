<?php

declare(strict_types=1);

namespace Dumpify\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('setup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $timezone = getenv('APP_TIMEZONE');
        $gid = getenv('APP_GID');
        $uid = getenv('APP_UID');

        exec(<<<EXEC
        # set timezone
        ln -snf /usr/share/zoneinfo/{$timezone} /etc/localtime
        echo {$timezone} > /etc/timezone
        
        # create user
        groupadd -g {$gid} dumpify
        useradd -r -u {$uid} -g dumpify --create-home dumpify
        
        # apply permissions for mounted folders
        chown -R dumpify:dumpify /backup
EXEC);
    }
}
