<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        22/05/2017 09:01
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Command;

use DivanteLtd\PimcoreElasticsearchPlugin\Exception\IndexNotConfigured;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\WrongConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * class CreateIndexCommand
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Command
 */
class IndexAliasCommand extends AbstractCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('divante-ltd:elasticsearch:index-alias')
            ->setDescription('Command allows to create new alias or update existing.')
            ->addArgument('index', InputArgument::REQUIRED, 'Index name.')
            ->addArgument('alias', InputArgument::REQUIRED, 'Alias name.');
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

        $indexName = $input->getArgument('index');
        $aliasName = $input->getArgument('alias');

        try {
            $this->configService->getIndex($indexName);
            $alias = $this->elasticSearchService->addOrUpdateAlias($indexName, $aliasName);

            if (!$alias) {
                $output->writeln(sprintf(
                    'Something went wrong, could not create or update alias "%s" for index "%s"',
                    $aliasName,
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
        }

        $output->writeln(sprintf('Alias with name %s added to index %s.', $aliasName, $indexName));

        return $this->responseCodes['success'];
    }
}
