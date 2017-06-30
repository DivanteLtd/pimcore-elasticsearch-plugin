<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        24/05/2017 09:25
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin\Event;

use DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service\IndexerService;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\AbstractElement;

/**
 * class EventManager
 * @package DivanteLtd\PimcoreElasticsearchPlugin\Event
 */
class EventManager
{
    /** @var \Zend_EventManager_EventManager */
    protected $eventManager;

    /**
     * EventManager constructor.
     * @param \Zend_EventManager_EventManager $eventManager
     */
    public function __construct(\Zend_EventManager_EventManager $eventManager)
    {
        $this->eventManager   = $eventManager;
    }

    /**
     * @return void
     */
    public function registerListeners()
    {
        $saveEvents = [
            "object.postAdd",
            "object.postUpdate",
            "document.postAdd",
            "document.postUpdate",
            "asset.postAdd",
            "asset.postUpdate"
        ];

        $deleteEvents = [
            "object.postDelete",
            "document.postDelete",
            "asset.postDelete"
        ];

        $this->eventManager->attach($saveEvents, [$this, 'saveIndex']);
        $this->eventManager->attach($deleteEvents, [$this, 'deleteIndex']);
    }

    /**
     * @param \Zend_EventManager_Event $event
     */
    public function saveIndex(\Zend_EventManager_Event $event)
    {
        $indexerService = $this->getIndexerService();
        $element = $event->getTarget();

        if (!($element instanceof AbstractElement)) {
            return;
        }

        if (!($element instanceof Asset) && method_exists($element, 'getPublished') && !$element->getPublished()) {
            $indexerService->delete($element);
            return;
        }

        $indexerService->save($element);
    }

    /**
     * @param \Zend_EventManager_Event $event
     */
    public function deleteIndex(\Zend_EventManager_Event $event)
    {
        $indexerService = $this->getIndexerService();
        $element = $event->getTarget();

        if (!($element instanceof AbstractElement)) {
            return;
        }

        $indexerService->delete($element);
    }

    /**
     * @return IndexerService
     */
    protected function getIndexerService()
    {
        return \Pimcore::getDiContainer()->get(
            'DivanteLtd\PimcoreElasticsearchPlugin\Indexer\Service\IndexerService'
        );
    }
}
