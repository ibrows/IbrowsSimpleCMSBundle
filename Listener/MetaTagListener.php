<?php

namespace Ibrows\SimpleCMSBundle\Listener;

use Ibrows\SimpleCMSBundle\Extension\TwigExtension;
use Ibrows\SimpleSeoBundle\Extension\TwigExtension as SeoTwigExtension ;
use Ibrows\SimpleSeoBundle\Routing\KeyGenerator  as SeoKeyGenerator;
use Ibrows\SimpleSeoBundle\Routing\UrlGenerator;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\templating\Helper\CoreAssetsHelper;
use Symfony\Component\Routing\RouterInterface;
use Ibrows\SimpleCMSBundle\Security\SecurityHandler;

class MetaTagListener
{

    private $assetHelper;
    private $router;
    private $securityHandler;
    private $translator;
    private $keyGenerator;

    public function __construct(CoreAssetsHelper $assetHelper, SecurityHandler $securityHandler, RouterInterface $router, \Symfony\Component\Translation\TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->assetHelper = $assetHelper;
        $this->router = $router;
        $this->securityHandler = $securityHandler;
        $this->keyGenerator = new SeoKeyGenerator();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {

        $response = $event->getResponse();
        $request = $event->getRequest();


        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest() || $request->get('_xml_http_request') || !$response->isSuccessful()) {
            return;
        }

        if (!$this->securityHandler->isGranted('ibrows_simple_cms_content')) {
            return false;
        }
        $content = $response->getContent();

        $pos = strripos($content, '</body>');
        if ($pos === false) {
            return false;
        }
        if (strripos($content, SeoTwigExtension::initMetaTagString()) === false) {
            return false;
        }


        $key = $this->keyGenerator->generateMetaTagKey($request,$this->router, $this->translator->getLocale());
        $label = "edit metatags of " . $key;
        $editbox = TwigExtension::wrapOutputEdit(
                        $this->router, $label, $key, 'metatags', array('output' => $label)
        );
        $content = substr($content, 0, $pos) . $editbox . substr($content, $pos);
        $response->setContent($content);

        return true;
    }

}
