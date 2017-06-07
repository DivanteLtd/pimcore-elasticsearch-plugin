<?php
/**
 * @category    DivanteLtdPimcoreElasticsearchPlugin
 * @date        19/05/2017 08:58
 * @author      Bartosz Idzikowski <bidzikowski@divante.pl>
 * @author      Kamil Karkus <kkarkus@divnate.pl>
 * @copyright   Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

namespace DivanteLtd\PimcoreElasticsearchPlugin;

use DI\ContainerBuilder;
use DivanteLtd\PimcoreElasticsearchPlugin\Command\CreateIndexCommand;
use DivanteLtd\PimcoreElasticsearchPlugin\Command\RemoveIndexCommand;
use DivanteLtd\PimcoreElasticsearchPlugin\Command\IndexAliasCommand;
use DivanteLtd\PimcoreElasticsearchPlugin\Command\ReIndexAllCommand;
use DivanteLtd\PimcoreElasticsearchPlugin\Event\EventManager;
use DivanteLtd\PimcoreElasticsearchPlugin\Exception\WrongConfig;
use Pimcore\API\Plugin as PluginLib;
use Pimcore\Console\Application;
use Pimcore\Logger;
use Zend_EventManager_Event;

/**
 * class Plugin
 * @package DivanteElasticSearchPlugin
 */
class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    /**
     * @return void
     */
    public function init()
    {
        $this->defineConstants();

        parent::init();

        $this->addDependencyInjection();
        $this->registerListeners();
        $this->registerCommands();
    }

    /**
     * @return void
     */
    protected function defineConstants()
    {
        define('DIVANTELTD_ELASTICSEARCH_PLUGIN_DIR', __DIR__ . '/../../..');
    }

    /**
     * @return void
     */
    protected function addDependencyInjection()
    {
        \Pimcore::getEventManager()->attach("system.di.init", function ($event) {
            /** @var Zend_EventManager_Event $event */
            /** @var ContainerBuilder $builder */
            $builder  = $event->getTarget();
            $filePath = DIVANTELTD_ELASTICSEARCH_PLUGIN_DIR . '/config/di.php';

            if (file_exists($filePath)) {
                $builder->addDefinitions($filePath);
            }
        });
    }

    /**
     * @return void
     */
    protected function registerCommands()
    {
        \Pimcore::getEventManager()->attach(
            "system.console.init",
            function (\Zend_EventManager_Event $event) {
                /** @var Application $application */
                $application = $event->getTarget();
                $application->add(new CreateIndexCommand());
                $application->add(new RemoveIndexCommand());
                $application->add(new IndexAliasCommand());
                $application->add(new ReIndexAllCommand());
            }
        );
    }

    /**
     * @return void
     */
    protected function registerListeners()
    {
        try {
            $eventManager      = \Pimcore::getEventManager();
            $eventEventManager = new EventManager($eventManager);
            $eventEventManager->registerListeners();
        } catch (WrongConfig $e) {
            Logger::err($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public static function install()
    {
        return "DivanteLtdElasticsearchPlugin has been installed.";
    }

    /**
     * @return string
     */
    public static function uninstall()
    {
        return "DivanteLtdElasticsearchPlugin has been uninstalled.";
    }

    /**
     * @return bool
     */
    public static function isInstalled()
    {
        return true;
    }
}
