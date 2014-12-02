<?php

namespace Ibrows\SimpleCMSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader as Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IbrowsSimpleCMSExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('ibrows_simple_cms.include_js_libs',  $config['include_js_libs'] );
        $container->setParameter('ibrows_simple_cms.include_js',  $config['include_js'] );
        $container->setParameter('ibrows_simple_cms.include_tiny',  $config['include_tiny'] );
        $container->setParameter('ibrows_simple_cms.include_css',  $config['include_css'] );
        $container->setParameter('ibrows_simple_cms.localized_alias',  $config['localized_alias'] );
        $container->setParameter('ibrows_simple_cms.upload_dir',  $config['upload_dir']);
        $container->setParameter('ibrows_simple_cms.global_role',  $config['role']);
        $container->setParameter('ibrows_simple_cms.wysiwyg_config', $config['wysiwyg']);

        $securitymap = array();
        foreach($config['types'] as $key => $type){
            $securitymap[$key] = $type['security'];
        }
        $container->setParameter('ibrows_simple_cms.securitymap',  $securitymap);

        $container->setDefinition('ibrows_simple_cms.content_manager', new \Symfony\Component\DependencyInjection\Definition(
            'Ibrows\SimpleCMSBundle\Model\ContentManager',
             array( new Reference('ibrows_simple_cms.entity_manager'),$config['types'])
        ))

        // ->setFactoryClass('%newsletter_factory.class%' )
        //->setFactoryMethod('get')
        ;

    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['IbrowsSimpleSeoBundle'])) {
            return;
        }

        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $configSeo = array('localized_alias' => $config['localized_alias']);
        $configSeo['entity_class'] = $config['types']['metatags']['class'];
        $container->prependExtensionConfig('ibrows_simple_seo', $configSeo);
    }
}
