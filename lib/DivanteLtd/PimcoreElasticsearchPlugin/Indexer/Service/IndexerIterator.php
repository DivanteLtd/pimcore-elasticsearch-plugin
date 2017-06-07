<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        23/05/2017 12:09
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service;

use DivanteLtd\PimcoreElasticsearchPlugin\Indexer\AbstractIndexer;

/**
 * class IndexerIterator
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service
 */
class IndexerIterator implements \Iterator
{
    /** @var AbstractIndexer[] */
    private $indexers = [];

    /**
     * IndexerIterator constructor.
     * @param AbstractIndexer[] $indexers
     */
    public function __construct(array $indexers)
    {
        $this->indexers = $indexers;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->indexers);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->indexers);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->indexers);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->indexers);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key($this->indexers);

        return ($key !== null && $key !== false);
    }
}
