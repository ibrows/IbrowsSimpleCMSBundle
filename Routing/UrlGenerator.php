<?php



namespace Ibrows\SimpleCMSBundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 *
 * @api
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator
{
    const GENERATE_NORMAL_ROUTE = '!!!';

    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute)
    {
        if(key_exists(self::GENERATE_NORMAL_ROUTE, $parameters) ){
            unset($parameters[self::GENERATE_NORMAL_ROUTE]);
            return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);
        }
        
        //use the cached version with my name - do the standard request if its not work
        // dont generate assets                
        if (stripos($name, RouteLoader::ROUTE_BEGIN) !== 0 && stripos($name, '_assetic') !== 0) {
            try {
                $route = RouteLoader::getRouteName($name, array_merge($this->context->getParameters(), $parameters,$defaults ));
                return $this->generate( $route, $parameters, $absolute);
            } catch (RouteNotFoundException $e) {
                try {
                    $reqparams = array();
                    foreach ($requirements as $key => $val){
                        $reqparams[$key] = $parameters[$key];
                    }
                    $route = RouteLoader::getRouteName($name, array_merge($this->context->getParameters(), $reqparams,$defaults ));
                    return $this->generate( $route, $parameters, $absolute);
                } catch (RouteNotFoundException $e) {
                    // do nothing, go on and do the normal Request
                }
            }
        }


        return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);
    }

}
