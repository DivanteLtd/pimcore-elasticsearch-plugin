# PimcoreElasticsearchPlugin

## Requirements

- PHP 7 or newer
- Pimcore 4
- Elasticsearch 5
- Composer

## Installation

### First step

#### via Composer

```
composer require divante-ltd/pimcore-elasticsearch-plugin
```

#### manually

- Download this repository into your plugins directory.
- Download manually dependencies (see composer.json).
- Follow next steps in this instruction.

### Second step

Open Extension tab in admin panel and install plugin.
After this, installation is finished.

## Usage

### How to use config service?

```
$container = \Pimcore::getDiContainer();
/** @var \DivanteLtd\PimcoreElasticsearchPlugin\Service\ConfigService $configService */
$configService = $container->get('DivanteLtd\PimcoreElasticsearchPlugin\Service\ConfigService');

$configService->setHosts(["127.0.0.1:9200"]);

$indices = [
    "exampleIndex" => [
        "mappings" => [
            "newsType" => [
                "properties" => [
                    "title" => [
                        "type" => "string",
                        "analyzer" => "standard"
                    ]
                ]
            ]
        ],
        //...
    ]
];
foreach ($indices as $indexName => $body) {
    $configService->addIndex($indexName, $body);
}

```

INFO: remember config service must be configured before you call elasticsearch service, e.g.: in plugin init method.

### Creating index

```
php pimcore/cli/console.php divante-ltd:elasticsearch:create-index exampleIndex
```

It will create index in elasticsearch.

INFO: it allows only to create indices which where added to config service.

### Removing index

```
php pimcore/cli/console.php divante-ltd:elasticsearch:remove-index exampleIndex
```

It will remove index from elasticsearch.

### Indexers

```

use DivanteLtd\PimcoreElasticsearchPlugin\Indexer\AbstractIndexer;

class ExampleIndexer extends AbstractIndexer
{

    /**
     * @param AbstractElement $element
     *
     * @return bool
     */
    public function isIndexable(AbstractElement $element): bool
    {
        return $element instanceof News;
    }

    /**
     * @param AbstractElement|News $element
     *
     * @return array
     */
    public function buildDocument(AbstractElement $element): array
    {
        return [
            'title' => $element->getTitle(),
        ];
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return 'exampleIndex';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'newsType';
    }
}
```

You need also to register this indexer, so it can work with reindex all command and event listeners like object.postAdd, etc.

```
/** @var \DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service\IndexerRegisterService $indexerRegister */
$indexerRegister = \Pimcore::getDiContainer()->get(
    'DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service\IndexerRegisterService'
);
$indexerRegister->add(new ExampleIndexer());
```

From now everytime you add, edit or delete object which is instance of News class, it will be added, saved or deleted from elastic.

### How to reindex everything?

```
php pimcore/cli/console.php divante-ltd:elasticsearch:reindex-all
```

If you want to reindex only e.g. objects:

```
php pimcore/cli/console.php divante-ltd:elasticsearch:reindex-all --objects
```


### How to search?

```
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TypeQuery;
use ONGR\ElasticsearchDSL\Search;
use DivanteLtd\PimcoreElasticsearchPlugin\Service\ElasticSearchService;


/** @var ElasticSearchService $elasticSearchService */
$elasticSearchService = \Pimcore::getDiContainer()->get(
    'DivanteLtd\PimcoreElasticsearchPlugin\Service\ElasticSearchService'
);
$boolQuery = new BoolQuery();
$boolQuery->addParameter("minimum_should_match", 1);
$boolQuery->addParameter("boost", 1);
$boolQuery->add(new TypeQuery('newsType'), BoolQuery::MUST);
$boolQuery->add(new MatchQuery('title', $query), BoolQuery::SHOULD);
$search = new Search();
$search
    ->addQuery($boolQuery)
    ->setFrom(0)
    ->setSize(10);
//reults should be array of News objects
$results = $elasticSearchService->find('newsType', $search);
```

For more examples take a look here: http://docs.ongr.io/ElasticsearchDSL
