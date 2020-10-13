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
use Symfony\Component\Console\Output\Output;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\DBAL\DriverManager;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

/**
 * Command to generate the SQL needed to update the database schema to match
 * the current mapping information.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class UpdateSchemaDoctrineCommand extends UpdateCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:schema:customname:update')
            ->setDescription('Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata')
            ->addArgument('dbname', InputArgument::REQUIRED, 'The database name to use for this command')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command')
            ->setHelp(<<<EOT
The <info>doctrine:schema:customname:update</info> command generates the SQL needed to
synchronize the database schema with the current mapping metadata of the
default entity manager.

For example, if you add metadata for a new column to an entity, this command
would generate and output the SQL needed to add the new column to the database:

<info>php app/console doctrine:schema:customname:update --dump-sql</info>

Alternatively, you can execute the generated queries:

<info>php app/console doctrine:schema:customname:update --force</info>

You can also update the database schema for a specific entity manager:

<info>php app/console doctrine:schema:customname:update --em=default</info>

You can also update the database schema for a specific database name: (lugh_dbname)

<info>php app/console doctrine:schema:customname:update --dbname=</info>
EOT
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (($name = $input->getArgument('dbname')) === null) {
            throw new \RuntimeException("Argument 'dbname' is required in order to execute this command correctly.");
        }
        
        $application = $this->getApplication();
        $emName = $input->getOption('em');
        $name = 'lugh_' . $input->getArgument('dbname');
        
        
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager($emName);
        $params = $em->getConnection()->getParams();
        $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);
        
        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');

        parent::execute($input, $output);
    }
}
