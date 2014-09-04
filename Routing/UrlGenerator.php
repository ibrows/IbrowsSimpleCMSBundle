<?php

namespace Ibrows\SimpleCMSBundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * @api
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator
{
    const GENERATE_NORMAL_ROUTE = '!!!';

    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens)
    {
        if(array_key_exists(self::GENERATE_NORMAL_ROUTE, $parameters) ){
            unset($parameters[self::GENERATE_NORMAL_ROUTE]);
            return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens);
        }

        //use the cached version with my name - do the standard request if its not work
        // dont generate assets                
        if (stripos($name, RouteLoader::ROUTE_BEGIN) !== 0 && stripos($name, '_assetic') !== 0) {
            $mergedParams = array_replace($this->context->getParameters(),$defaults, $parameters);
            $routeName = RouteLoader::getRouteName($name, $mergedParams );
            try {
                return $this->generate( $routeName, $parameters, $referenceType );
            } catch (RouteNotFoundException $e) {}

            //check route without unknown params
            foreach ($mergedParams as $key => $val) {
                if (!in_array($key,$variables)  && $key != '_controller') {
                    unset($mergedParams[$key]);
                }
            }
            $routeName = RouteLoader::getRouteName($name, $mergedParams );
            try {
                return $this->generate( $routeName, $parameters, $referenceType );
            } catch (RouteNotFoundException $e) {}

            //check route without defaults
            foreach ($mergedParams as $key => $val) {
                if (array_key_exists($key,$defaults)  && $key != '_controller') {
                    unset($mergedParams[$key]);
                }
            }
            $routeName = RouteLoader::getRouteName($name, $mergedParams );
            try {
                return $this->generate( $routeName, $parameters, $referenceType );
            } catch (RouteNotFoundException $e) {}

            //check route with only requirements
            foreach ($mergedParams as $key => $val) {
                if (!array_key_exists($key,$requirements)  && $key != '_controller') {
                    unset($mergedParams[$key]);
                }
            }
            $routeName = RouteLoader::getRouteName($name, $mergedParams );
            try {
                return $this->generate( $routeName, $parameters, $referenceType );
            } catch (RouteNotFoundException $e) {}


        }


        return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens);
    }

}
