<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        19/05/2017 08:58
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Service;

use DivanteLtd\PimcoreElasticsearchPlugin\Exception\ElasticError;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\IndexAlreadyExists;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\WrongConfig;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use ONGR\ElasticsearchDSL\Search;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Object\AbstractObject;

/**
 * class ElasticSearchService
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Service
 */
class ElasticSearchService
{
    /** @var ConfigService */
    protected $configService;

    /** @var Client */
    protected $elasticClient;

    /** @var ElementService  */
    protected $elementService;

    /**
     * ElasticSearchService constructor.
     */
    public function __construct()
    {
        $this->configService = \Pimcore::getDiContainer()->get('DivanteLtd\PimcoreElasticsearchPlugin\Service\ConfigService');
        $this->elementService = new ElementService();
        $this->configureClient();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function configureClient()
    {
        if (!$this->configService->getHosts()) {
            throw new WrongConfig("Invalid ElasticSearch configuration. Hosts not defined");
        }

        $this->elasticClient = ClientBuilder::create()->setHosts($this->configService->getHosts())->build();
    }

    /**
     * @param string $indexName
     * @param array  $body
     *
     * @return array
     * @throws IndexAlreadyExists
     */
    public function createIndex(string $indexName, array $body): array
    {
        if ($this->elasticClient->indices()->exists(['index' => $indexName])) {
            throw new IndexAlreadyExists("Index already exists");
        }

        return $this->elasticClient->indices()->create(['index' => $indexName, 'body' => $body]);
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws ElasticError
     */
    public function deleteIndex(string $name): array
    {
        $params = ["index" => $name];
        if (!$this->elasticClient->indices()->exists($params)) {
            throw new ElasticError("Index not exists, nothing to delete");
        }

        return $this->elasticClient->indices()->delete($params);
    }

    /**
     * @param string $indexName
     * @param string $aliasName
     *
     * @return array
     */
    public function addOrUpdateAlias(string $indexName, string $aliasName): array
    {
        $params = [
            'index' => $indexName,
            'name'  => $aliasName
        ];

        if ($this->elasticClient->indices()->existsAlias(['name' => $aliasName])) {
            $this->deleteAliases($aliasName);
        }

        return $this->elasticClient->indices()->putAlias($params);
    }

    /**
     * @param string $aliasName
     *
     * @return array
     */
    protected function deleteAliases(string $aliasName): array
    {
        $aliasesToDelete = ['index' => '_all', 'name' => $aliasName];
        return $this->elasticClient->indices()->deleteAlias($aliasesToDelete);
    }

    /**
     * @param string $index
     * @param string $type
     * @param AbstractElement $element
     *
     * @return bool
     */
    public function exists(string $index, string $type, AbstractElement $element): bool
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'id'    => $this->elementService->getElementId($type, $element)
        ];

        return $this->elasticClient->exists($params);
    }

    /**
     * @param string $index
     * @param string $type
     * @param AbstractElement $element
     * @param array $body
     *
     * @return array
     */
    public function save(string $index, string $type, AbstractElement $element, array $body): array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'id'    => $this->elementService->getElementId($type, $element),
            'body'  => $body
        ];

        return $this->elasticClient->index($params);
    }

    /**
     * @param string $index
     * @param string $type
     * @param AbstractElement $element
     *
     * @return array
     */
    public function delete(string $index, string $type, AbstractElement $element): array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'id'    => $this->elementService->getElementId($type, $element),
        ];

        return $this->elasticClient->delete($params);
    }


    /**
     * @param string $index can be alias name
     * @param Search $search
     *
     * @return array
     */
    public function findRaw(string $index, Search $search)
    {
        return $this->elasticClient->search(['index' => $index, 'body' => $search->toArray()]);
    }

    /**
     * @param string $index can be alias name
     * @param Search $search
     *
     * @return array
     * @throws \Exception
     */
    public function find(string $index, Search $search)
    {
        $results = $this->findRaw($index, $search);
        $ret = [];

        if (!isset($results["hits"]["hits"])) {
            return $ret;
        }
        return $this->arrayToObjects($results['hits']['hits']);
    }

    /**
     * @param array $hits
     *
     * @return object[]
     * @throws \Exception
     */
    public function arrayToObjects(array $hits)
    {
        $ret = [];

        /** @var array $result */
        foreach ($hits as $result) {
            $id = $this->elementService->extractId($result['_id']);
            $elementType = $this->elementService->extractElementType($result['_id']);

            switch ($elementType) {
                case 'asset':
                    $ret[] = Asset::getById($id);
                    break;
                case 'document':
                    $ret[] = Document::getById($id);
                    break;
                case 'object':
                    $ret[] = AbstractObject::getById($id);
                    break;
                default:
                    throw new \Exception(sprintf(
                        'Unknown element type. Should be asset, document or object but got %s.',
                        $elementType
                    ));
            }
        }
        return $ret;
    }
}
