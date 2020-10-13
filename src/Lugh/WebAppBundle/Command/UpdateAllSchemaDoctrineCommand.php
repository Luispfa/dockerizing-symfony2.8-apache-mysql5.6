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
class UpdateAllSchemaDoctrineCommand extends UpdateCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:schema:all:update')
            ->setDescription('Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata')
            ->addOption('em', null, InputOption::VALUE_OPTIONAL, 'The entity manager to use for this command')
            ->setHelp(<<<EOT
The <info>doctrine:schema:all:update</info> command generates the SQL needed to
synchronize the database schema with the current mapping metadata of the
default entity manager.

For example, if you add metadata for a new column to an entity, this command
would generate and output the SQL needed to add the new column to the database:

<info>php app/console doctrine:schema:all:update --dump-sql</info>

Alternatively, you can execute the generated queries:

<info>php app/console doctrine:schema:all:update --force</info>

You can also update the database schema for a specific entity manager:

<info>php app/console doctrine:schema:all:update --em=default</info>

EOT
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager('db_connection');
        $platforms =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findAll();
        
        foreach ($platforms as $platform) {
            $output->writeln('Database: ' . $platform->getDbname());
            $this->localexecute($input, $output, $platform->getDbname());
        }
        $output->writeln('Database: ' . 'base');
        $this->localexecute($input, $output, 'base');
        
    }
    
    private function localexecute(InputInterface $input, OutputInterface $output, $dbname)
    {
        
        $application = $this->getApplication();
        $emName = $input->getOption('em');
        $name = 'lugh_' . $dbname;
        
        
        $em = $application->getKernel()->getContainer()->get('doctrine')->getManager($emName);
        $params = $em->getConnection()->getParams();
        $application->getKernel()->getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);
        
        $helperSet = $application->getHelperSet();
        $helperSet->set(new ConnectionHelper($em->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($em), 'em');

        parent::execute($input, $output);
        
    }
}
