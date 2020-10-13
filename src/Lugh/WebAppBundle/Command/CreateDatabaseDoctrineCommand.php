<?php

/*
 * This file is part of the Doctrine Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lugh\WebAppBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand as DoctrineCommand;

/**
 * Database tool allows you to easily drop and create your configured databases.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class CreateDatabaseDoctrineCommand extends DoctrineCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:database:customname:create')
            ->setDescription('Creates the configured custom databases')
            ->addArgument('dbname', InputArgument::REQUIRED, 'The dbname to use for this command')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The connection to use for this command')
            ->setHelp(<<<EOT
The <info>doctrine:database:customname:create</info> command creates the default
connections database:

<info>php app/console doctrine:database:customname:create</info>

You can also optionally specify the name of a connection and database name to create the
database for:

<info>php app/console doctrine:database:create --dbname=base --connection=default</info>
EOT
        );
    }
    

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (($sql = $input->getArgument('dbname')) === null) {
            throw new \RuntimeException("Argument 'dbname' is required in order to execute this command correctly.");
        }
        
        $connection = $this->getDoctrineConnection($input->getOption('connection'));

        $params = $connection->getParams();

        $tmpConnection = DriverManager::getConnection($params);

        $name = 'lugh_' . $input->getArgument('dbname');
        $error = false;
        try {
            $tmpConnection->getSchemaManager()->createDatabase($name);
            $output->writeln(sprintf('<info>Created database for connection named <comment>%s</comment></info>', $name));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Could not create database for connection named <comment>%s</comment></error>', $name));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        $tmpConnection->close();

        return $error ? 1 : 0;
    }
}
