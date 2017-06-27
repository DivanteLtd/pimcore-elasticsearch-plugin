<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        25/05/2017 11:22
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service;

use DivanteLtd\PimcoreElasticsearchPlugin\Indexer\AbstractIndexer;
use Pimcore\Model\Element\AbstractElement;

/**
 * class IndexerService
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service
 */
class IndexerService
{
    /**
     * IndexerService constructor.
     */
    public function __construct()
    {
        $diContainer = \Pimcore::getDiContainer();

        $this->indexerRegisterService = $diContainer->get(
            'DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service\IndexerRegisterService'
        );
        $this->elasticSearchService   = $diContainer->get(
            'DivanteLtd\PimcoreElasticsearchPlugin\Service\ElasticSearchService'
        );
    }

    /**
     * @param AbstractElement $element
     */
    public function save(AbstractElement $element)
    {
        /* @var AbstractIndexer $indexer */
        foreach ($this->indexerRegisterService as $indexer) {
            if (!$indexer->isIndexable($element)) {
                continue;
            }

            $this->elasticSearchService->save(
                $indexer->getIndexName(),
                $indexer->getType(),
                $element,
                $indexer->buildDocument($element)
            );
        }
    }

    /**
     * @param AbstractElement $element
     */
    public function delete(AbstractElement $element)
    {
        /* @var AbstractIndexer $indexer */
        foreach ($this->indexerRegisterService as $indexer) {
            if (!$indexer->isIndexable($element)) {
                continue;
            }

            if (!$this->elasticSearchService->exists($indexer->getIndexName(), $indexer->getType(), $element)) {
                continue;
            }

            $this->elasticSearchService->delete($indexer->getIndexName(), $indexer->getType(), $element);
        }
    }
}
