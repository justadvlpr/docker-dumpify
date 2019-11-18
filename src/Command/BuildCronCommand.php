<?php

declare(strict_types=1);

namespace Dumpify\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCronCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('build-cron');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbUser = getenv('APP_DB_USERNAME');
        $dbName = getenv('APP_DB_NAME');
        $dbHost = getenv('APP_DB_HOST');

        $backupName = getenv('APP_BACKUP_NAME');
        $backupName = str_replace(
            [
                '{date-and-time}',
                '{date}',
                '{time}',
            ],
            [
                "`date '+\%Y-\%m-\%d_\%H:\%M:\%S'`",
                "`date '+\%Y-\%m-\%d'`",
                "`date '+\%H:\%M:\%S'`",
            ],
            $backupName
        );

        $encryptEnabled = filter_var(getenv('APP_ENCRYPT_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        $encryptEngine = getenv('APP_ENCRYPT_ENGINE');

        if (empty(getenv('APP_CRON_SCHEDULE'))) {
            $output->writeln('<error>Time is empty!</error>');
            exit(1);
        }

        $cronSchedule = explode('||', getenv('APP_CRON_SCHEDULE'));

        $crontabContent = '';

        foreach ($cronSchedule as $schedule) {
            $backupCommand = rtrim(ltrim(trim($schedule), '"'), '"');

            $backupCommand .= " /usr/bin/mysqldump --user={$dbUser} --host={$dbHost} --password=\$APP_DB_PASSWORD --single-transaction --quick {$dbName}";

            if ($encryptEnabled) {
                switch ($encryptEngine) {
                    case 'gpg':
                        $backupCommand .= " | /usr/bin/gpg -c --passphrase \$APP_ENCRYPT_KEY --batch --yes -o \"/backup/{$backupName}.sql.gpg\"";

                        break;
                    default:
                        $output->writeln('<error>Engine not found!</error>');
                        exit(1);
                }
            }

            $crontabContent .= "{$backupCommand}\n\n";
        }

        file_put_contents('/app/runtime/crontab', $crontabContent);
    }
}
