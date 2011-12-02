<?php

namespace Ibrows\SimpleCMSBundle\Routing;

use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

class RouteLoader extends FileLoader
{

    /**
     * @var \Ibrows\SimpleCMSBundle\Model\ContentManager
     */
    protected $manager;

    /**
     *
     * @var  \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    const ROUTE_BEGIN = 'scms_';
    const ROUTE_END = '_scms';

    /**
     * @param \Ibrows\SimpleCMSBundle\Model\ContentManager $pool
     */
    public function __construct(\Ibrows\SimpleCMSBundle\Model\ContentManager $manager, \Symfony\Component\Routing\RouterInterface $router)
    {
        $this->manager = $manager;
        $this->router = $router;
    }

    /**
     * @param string $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        if ($type == 'ibrows_router') {
            return true;
        }
        return false;
    }

    /**
     * @param string $resource
     * @param null $type
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
        $collection = new SymfonyRouteCollection();
        $repo = $this->manager->getRepository('metatags');
        /* @var $repo \Ibrows\SimpleCMSBundle\Repository\MetaTagRepository         */
        $results = $repo->findAllAlias();
        foreach ($results as $metatag) {
            $pathinfo = unserialize($metatag['pathinfo']);
            $oldroute = $pathinfo['_route'];
            $route = new Route($metatag['alias'], $pathinfo, array(), array());
            $collection->add(self::getRouteName($oldroute, $pathinfo), $route);
        }
        return $collection;
    }

    public static function getRouteName($routename, $parameters)
    {
        $routename = self::ROUTE_BEGIN . $routename . self::ROUTE_END;
        foreach ($parameters as $key => $value) {
            if (strpos($key, '_') !== 0) {
                //escape '_'
                $key = self::escape($key);
                $value = self::escape($value);
                $routename .= "_{$key}_{$value}";
            }
        }
        return $routename;
    }

    private static function escape($underlinedstring)
    {
        return str_replace('_', '.', $underlinedstring);
    }

    private static function unescape($string)
    {
        return str_replace('.', '_', $string);
    }

    public static function getPathinfo($newroutename)
    {
        $matches = array();
        $result = preg_match('!' . self::ROUTE_BEGIN . '(.*)' . self::ROUTE_END . '(.*)!', $newroutename, $matches);
        $pathinfo = array();
        $pathinfo['_route'] = $matches[1];
        $matches = explode('_', $matches[2]);

        $key = false;
        foreach ($matches as $value) {
            if (!empty($value)) {
                if (!$key) {
                    $key = self::unescape($value);
                } else {
                    $pathinfo[$key] = self::unescape($value);
                    $key = false;
                }
            }
        }
        return $pathinfo;
    }

}