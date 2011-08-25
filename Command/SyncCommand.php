<?php

/*
 * This file is part of HeriWebServiceBundle.
 *
 * (c) Alexandre Mogère
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Heri\WebServiceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends ContainerAwareCommand
{
    protected
      $client,
      $input;
    
    protected function configure()
    {
        $this
            ->setName('webservice:load')
            ->setDescription('Synchronize entity with webservice')
            ->addArgument('service', InputArgument::REQUIRED, 'Name of service')
            ->addOption('record', null, InputOption::VALUE_OPTIONAL, 'Record primary key')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->getContainer()->get('ws.client');
        $config = $this->getContainer()->getParameter('ws.client.config');
        
        $service->configure($config, $input);
        $this->client = $service->getClient();
        
        while ($record = $this->client->getNextRecord())
        {
            $tStart = microtime(true);
            $this->pushRecord($record);
            $output->writeln(
                '<comment>Time execution in seconds: ' .
                number_format(microtime(true) - $tStart, 3).'</comment>'
            );
        }
    }
    
    /**
     * Send record to WebService
     *
     * @param  Doctrine_Record $record
     * @return boolean
     */
    protected function pushRecord($record = null)
    {
        try
        {
          $this->client->rehydrate($record);
          $this->client->push();
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
        
        return $this->client->postSynchronize();
    }
}