<?php
/**
 * @category    dam-psbrands.dev
 * @date        08/06/2017 11:49
 * @author      Kamil Karkus <kkarkus@divante.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Service;

use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Object\AbstractObject;

/**
 * Class ElementService
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Service
 */
class ElementService
{
    /**
     * @param string          $type
     * @param AbstractElement $element
     *
     * @return string
     */
    public function getElementId(string $type, AbstractElement $element): string
    {
        $elementType = '';
        if ($element instanceof Document) {
            $elementType = 'document';
        } elseif ($element instanceof Asset) {
            $elementType = 'asset';
        } elseif ($element instanceof AbstractObject) {
            $elementType = 'object';
        }
        return sprintf('%s_%s_%d', $type, $elementType, $element->getId());
    }

    /**
     * @param string $input
     *
     * @return int
     * @throws \Exception
     */
    public function extractId(string $input): int
    {
        $matches = [];
        if (!preg_match('/\_(?P<id>\d+)$/', $input, $matches)) {
            throw new \Exception('Could not extract id.');
        }
        return $matches['id'];
    }


    /**
     * @param string $input
     *
     * @return string asset|document|object
     * @throws \Exception
     */
    public function extractElementType(string $input): string
    {
        $matches = [];
        if (!preg_match('/\_(?P<type>\w+)\_\d+$/', $input, $matches)) {
            throw new \Exception('Could not extract element type.');
        }
        return $matches['type'];
    }
}
