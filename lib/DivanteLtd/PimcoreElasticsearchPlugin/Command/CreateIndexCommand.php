<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        19/05/2017 08:23
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Command;

use DivanteLtd\PimcoreElasticsearchPlugin\Exception\IndexAlreadyExists;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\IndexNotConfigured;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\WrongConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * class CreateIndexCommand
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Command
 */
class CreateIndexCommand extends AbstractCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('divante-ltd:elasticsearch:create-index')
            ->setDescription('Create new index')
            ->addArgument('name', InputArgument::REQUIRED, 'Index name.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);
        } catch (WrongConfig $e) {
            $output->writeln(sprintf('Error code: %s. %s', $this->responseCodes['wrong-config'], $e->getMessage()));
            return $this->responseCodes['wrong-config'];
        }

        $indexName = $input->getArgument('name');

        try {
            $index    = $this->configService->getIndex($indexName);
            $newIndex = $this->elasticSearchService->createIndex($indexName, $index);

            if (!$newIndex) {
                $output->writeln(sprintf(
                    'Error code: %s. Something went wrong, index "%s" not added',
                    $this->responseCodes['elastic-error'],
                    $indexName
                ));
                return $this->responseCodes['elastic-error'];
            }
        } catch (IndexNotConfigured $e) {
            $output->writeln(sprintf(
                'Error code: %s. Index "%s" not found in Config',
                $this->responseCodes['wrong-config'],
                $indexName
            ));
            return $this->responseCodes['wrong-config'];
        } catch (IndexAlreadyExists $e) {
            $output->writeln(sprintf(
                'Error code: %s. Index "%s" already exists',
                $this->responseCodes['index-exists'],
                $indexName
            ));
            return $this->responseCodes['index-exists'];
        }

        $output->writeln(sprintf('Index with name %s added.', $indexName));

        return $this->responseCodes['success'];
    }
}
