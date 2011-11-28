<?php

namespace Ibrows\SimpleCMSBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class IbrowsSimpleCMSBundle extends Bundle
{
    
    public function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        parent::build($container);

       $container->addCompilerPass(new DependencyInjection\Compiler\RoutingPass());
    }    
}
