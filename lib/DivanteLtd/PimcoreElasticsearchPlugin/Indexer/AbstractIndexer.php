<?php

/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        23/05/2017 10:55
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Indexer;

use Pimcore\Model\Element\AbstractElement;

/**
 * class AbstractIndexer
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Indexer
 */
abstract class AbstractIndexer
{
    /**
     * @param AbstractElement $element
     * @return bool
     */
    abstract public function isIndexable(AbstractElement $element): bool;

    /**
     * @param AbstractElement $element
     * @return array
     */
    abstract public function buildDocument(AbstractElement $element): array;

    /**
     * @return string
     */
    abstract public function getIndexName(): string;

    /**
     * @return string
     */
    abstract public function getType(): string;
}
