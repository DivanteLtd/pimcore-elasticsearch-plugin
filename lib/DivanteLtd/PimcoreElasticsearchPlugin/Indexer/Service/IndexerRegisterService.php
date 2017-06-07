<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        23/05/2017 11:51
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service;

use DivanteLtd\PimcoreElasticsearchPlugin\Indexer\AbstractIndexer;

/**
 * class IndexerRegisterService
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service
 */
class IndexerRegisterService implements \IteratorAggregate
{
    /** @var AbstractIndexer[] */
    protected $indexers = [];

    /**
     * @param AbstractIndexer $indexer
     *
     * @return IndexerRegisterService
     */
    public function add(AbstractIndexer $indexer): IndexerRegisterService
    {
        $this->indexers[] = $indexer;

        return $this;
    }

    /**
     * @return IndexerIterator
     */
    public function getIterator(): IndexerIterator
    {
        return new IndexerIterator($this->indexers);
    }
}
