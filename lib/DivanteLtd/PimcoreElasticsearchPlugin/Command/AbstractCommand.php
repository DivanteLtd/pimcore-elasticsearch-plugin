<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        22/05/2017 14:11
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Command;

use DivanteLtd\PimcoreElasticsearchPlugin\Service\ConfigService;
use DivanteLtd\PimcoreElasticsearchPlugin\Service\ElasticSearchService;
use Pimcore\Console\AbstractCommand as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * class AbstractCommand
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Command
 */
abstract class AbstractCommand extends BaseCommand
{
    /** @var  array */
    protected $responseCodes = [
        'success'       => 0,
        'wrong-config'  => 1,
        'index-exists'  => 2,
        'elastic-error' => 3,
    ];

    /** @var  ConfigService */
    protected $configService;

    /** @var  ElasticSearchService */
    protected $elasticSearchService;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configService        = \Pimcore::getDiContainer()->get(
            'DivanteLtd\PimcoreElasticsearchPlugin\Service\ConfigService'
        );
        $this->elasticSearchService = \Pimcore::getDiContainer()->get(
            'DivanteLtd\PimcoreElasticsearchPlugin\Service\ElasticSearchService'
        );
        return 0;
    }
}
