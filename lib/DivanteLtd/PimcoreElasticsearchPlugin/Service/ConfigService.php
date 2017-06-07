<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        23/05/2017 12:09
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Service;

use DivanteLtd\PimcoreElasticsearchPlugin\Exception\IndexNotConfigured;

/**
 * Class ConfigService
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Service
 */
class ConfigService
{
    /** @var  array */
    protected $indices = [];

    /** @var  array */
    protected $hosts = [];

    /**
     * @return array
     */
    public function getIndices(): array
    {
        return $this->indices;
    }

    /**
     * @param array $hosts
     *
     * @return ConfigService
     */
    public function setHosts(array $hosts): ConfigService
    {
        $this->hosts = $hosts;

        return $this;
    }

    /**
     * @return array
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    /**
     * @param string $name
     * @param array $body
     *
     * @return ConfigService
     */
    public function addIndex(string $name, array $body): ConfigService
    {
        $this->indices[$name] = $body;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws IndexNotConfigured
     */
    public function getIndex(string $name): array
    {
        if (!isset($this->indices[$name])) {
            throw new IndexNotConfigured($name);
        }

        return $this->indices[$name];
    }
}
