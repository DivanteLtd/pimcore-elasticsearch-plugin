<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        19/05/2017 14:28
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Command;

use DivanteLtd\PimcoreElasticsearchPlugin\Exception\ElasticError;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\WrongConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * class DeleteIndexCommand
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Command
 */
class RemoveIndexCommand extends AbstractCommand
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('divante-ltd:elasticsearch:remove-index')
            ->setDescription('Delete index')
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
            $indexToDelete = $this->elasticSearchService->deleteIndex($indexName);

            if (!$indexToDelete) {
                $output->writeln(sprintf(
                    'Error code: %s. Something went wrong, index "%s" not added',
                    $this->responseCodes['elastic-error'],
                    $indexName
                ));
                return $this->responseCodes['elastic-error'];
            }
        } catch (ElasticError $e) {
            $output->writeln(sprintf('Error code: %s. %s', $this->responseCodes['wrong-config'], $e->getMessage()));
            return $this->responseCodes['elastic-error'];
        }

        $output->writeln(sprintf('Index with name %s deleted.', $indexName));

        return $this->responseCodes['success'];
    }
}
