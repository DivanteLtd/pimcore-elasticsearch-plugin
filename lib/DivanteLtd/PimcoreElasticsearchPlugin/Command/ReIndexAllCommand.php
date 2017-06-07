<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        19/05/2017 08:23
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Command;

use DivanteLtd\PimcoreElasticsearchPlugin\Exception\WrongConfig;
use DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service\IndexerService;
use Pimcore\Model\Asset\Listing as AssetsListing;
use Pimcore\Model\Document\Listing as DocListing;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Listing\AbstractListing;
use Pimcore\Model\Object\Listing as ObjListing;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * class ReIndexAllCommand
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Command
 */
class ReIndexAllCommand extends AbstractCommand
{
    const ELEMENTS_LIMIT = 100;

    /** @var IndexerService */
    protected $indexerService;

    /** @var int */
    protected $docsCount = 0;

    /** @var int */
    protected $objCount = 0;

    /** @var int */
    protected $assetsCount = 0;

    /** @var int */
    protected $elementsCount = 0;

    /** @var ProgressBar */
    protected $progressBar;

    /** @var array */
    protected $availableActions = [
        'documents' => 'reIndexDocs',
        'objects'   => 'reIndexObj',
        'assets'    => 'reIndexAssets'
    ];

    /** @var array */
    protected $selectedActions = [];

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('divante-ltd:elasticsearch:reindex-all')
            ->setDescription('ReIndex All elements')
            ->addOption(
                'documents',
                null,
                InputOption::VALUE_NONE,
                "Re-index only documents"
            )->addOption(
                'objects',
                null,
                InputOption::VALUE_NONE,
                "Re-index only objects"
            )->addOption(
                'assets',
                null,
                InputOption::VALUE_NONE,
                "Re-index only assets"
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->indexerService = new IndexerService();
        } catch (WrongConfig $e) {
            $output->writeln(sprintf('Error code: %s. %s', $this->responseCodes['wrong-config'], $e->getMessage()));
            return $this->responseCodes['wrong-config'];
        }

        $actionsToCall = $this->getActions($input);
        $this->countElements();

        $this->progressBar = new ProgressBar($output, $this->elementsCount);
        $this->progressBar->start();

        foreach ($actionsToCall as $action) {
            call_user_func([$this, $action]);
        }

        $this->progressBar->finish();
        $output->writeln(sprintf("\nRe-indexed: %s elements.", $this->elementsCount));

        return $this->responseCodes['success'];
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getActions(InputInterface $input): array
    {
        foreach ($this->availableActions as $option => $action) {
            if ($input->getOption($option)) {
                $this->selectedActions[$option] = $action;
            }
        }

        if (empty($this->selectedActions)) {
            $this->selectedActions = $this->availableActions;
        }

        return $this->selectedActions;
    }

    /**
     * @return void
     */
    protected function countElements()
    {
        $this->docsCount   = $this->getDocumentsAmount();
        $this->objCount    = $this->getObjectsAmount();
        $this->assetsCount = $this->getAssetsAmount();

        $this->elementsCount = $this->docsCount + $this->objCount + $this->assetsCount;
    }

    /**
     * @return void
     */
    protected function reIndexDocs()
    {
        $offset = 0;
        while ($offset < $this->docsCount) {
            $docsToReindex = $this->getDocsListPart($offset);
            $this->reIndexElements($docsToReindex);

            $offset += self::ELEMENTS_LIMIT;
        }
    }

    /**
     * @return void
     */
    protected function reIndexObj()
    {
        $offset = 0;
        while ($offset < $this->objCount) {
            $objToReindex = $this->getObjListPart($offset);
            $this->reIndexElements($objToReindex);

            $offset += self::ELEMENTS_LIMIT;
        }
    }

    /**
     * @return void
     */
    protected function reIndexAssets()
    {
        $offset = 0;
        while ($offset < $this->objCount) {
            $assetsToReindex = $this->getAssetsListPart($offset);
            $this->reIndexElements($assetsToReindex);

            $offset += self::ELEMENTS_LIMIT;
        }
    }

    /**
     * @param int $offset
     *
     * @return DocListing
     */
    protected function getDocsListPart(int $offset): DocListing
    {
        $listing = $this->getDocumentsListing();
        $listing->setOffset($offset)
            ->setLimit(self::ELEMENTS_LIMIT);

        return $listing;
    }

    /**
     * @param int $offset
     *
     * @return ObjListing
     */
    protected function getObjListPart(int $offset): ObjListing
    {
        $listing = $this->getObjectsListing();
        $listing->setOffset($offset)
            ->setLimit(self::ELEMENTS_LIMIT);

        return $listing;
    }

    /**
     * @param int $offset
     *
     * @return AssetsListing
     */
    protected function getAssetsListPart(int $offset): AssetsListing
    {
        $listing = $this->getAssetsListing();
        $listing->setOffset($offset)
            ->setLimit(self::ELEMENTS_LIMIT);

        return $listing;
    }

    /**
     * @return DocListing
     */
    protected function getDocumentsListing(): DocListing
    {
        $listing = new DocListing();
        $listing->setCondition('type = ?', 'page');

        return $listing;
    }

    /**
     * @return int
     */
    protected function getDocumentsAmount(): int
    {
        if (!isset($this->selectedActions['documents'])) {
            return 0;
        }

        $listing = $this->getDocumentsListing();

        return $listing->getTotalCount();
    }

    /**
     * @return ObjListing
     */
    protected function getObjectsListing(): ObjListing
    {
        $listing = new ObjListing();
        $listing->setCondition('o_type != ?', 'folder');

        return $listing;
    }

    /**
     * @return int
     */
    protected function getObjectsAmount(): int
    {
        if (!isset($this->selectedActions['objects'])) {
            return 0;
        }

        $listing = $this->getObjectsListing();

        return $listing->getTotalCount();
    }

    /**
     * @return AssetsListing
     */
    protected function getAssetsListing(): AssetsListing
    {
        $listing = new AssetsListing();
        $listing->setCondition('type != ?', 'folder');

        return $listing;
    }

    /**
     * @return int
     */
    protected function getAssetsAmount(): int
    {
        if (!isset($this->selectedActions['assets'])) {
            return 0;
        }

        $listing = $this->getAssetsListing();

        return $listing->getTotalCount();
    }

    /**
     * @param AbstractListing $elements
     *
     * @return void
     */
    protected function reIndexElements(AbstractListing $elements)
    {
        /** @var AbstractElement $element */
        foreach ($elements as $element) {
            $this->indexerService->save($element);
            $this->progressBar->advance();
        }
    }
}
