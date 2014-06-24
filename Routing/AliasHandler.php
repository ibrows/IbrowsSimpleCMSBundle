<?php

namespace Ibrows\SimpleCMSBundle\Routing;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Ibrows\SimpleCMSBundle\Entity\MetaTagContent;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AliasHandler
 * @package Ibrows\SimpleCMSBundle\Routing
 */
class AliasHandler implements  EventSubscriber
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param $key
     * @return array An array of parameters
     */
    public function  getPathInfoFromMetaTagKey($key)
    {
        $info = \Ibrows\SimpleCMSBundle\Extension\TwigExtension::generatePathInfoFromMetaTagKey($key);

        return $this->router->match($info);
    }

    /**
     * @param $route
     * @return array
     */
    public function  getDefaults($route)
    {
        $route = $this->router->getRouteCollection()->get($route);
        if ($route) {
            return $route->getDefaults();
        }

        return array();
    }


    private $resetRouterCache = false;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        $events =  array(Events::postUpdate);
        $events[] =  Events::postFlush;
        return $events;
    }

    public function postUpdate(LifecycleEventArgs $args){
        if($args->getEntity() instanceof MetaTagContent){
            $this->resetRouterCache = true;
        }
    }

    public function postPersist(LifecycleEventArgs $args){
        if($args->getEntity() instanceof MetaTagContent){
            $this->resetRouterCache = true;
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if($this->resetRouterCache){
            $this->resetRouterCache = false;
            $this->resetRouterCache();
        }
    }


    public function resetRouterCache()
    {
        if(!$this->router instanceof Router){

            return;
        }
        $cachedir = $this->router->getOption('cache_dir');
        $cacheclass = $this->router->getOption('matcher_cache_class');
        $cachedebug = $this->router->getOption('debug');
        $cache = new ConfigCache($cachedir . '/' . $cacheclass . '.php', $cachedebug);
        if (file_exists($cache->__toString())) {
            unlink($cache->__toString());
        }
        $cacheclass = $this->router->getOption('generator_cache_class');
        $cache = new ConfigCache($cachedir . '/' . $cacheclass . '.php', $cachedebug);
        if (file_exists($cache->__toString())) {
            unlink($cache->__toString());
        }
        // here i have to make sure, that cache not will be right with the old in memory routecollection
        $this->router->setOption('cache_dir', null);

    }


}