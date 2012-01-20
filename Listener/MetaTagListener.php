<?php

namespace Ibrows\SimpleCMSBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\templating\Helper\CoreAssetsHelper;
use Symfony\Component\Routing\RouterInterface;
use Ibrows\SimpleCMSBundle\Security\SecurityHandler;
use Ibrows\SimpleCMSBundle\Extension\TwigExtension;

class MetaTagListener
{

    private $assetHelper;
    private $router;
    private $securityHandler;

    public function __construct(CoreAssetsHelper $assetHelper, SecurityHandler $securityHandler, RouterInterface $router)
    {
        $this->assetHelper = $assetHelper;
        $this->router = $router;
        $this->securityHandler = $securityHandler;
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
        if (strripos($content, TwigExtension::initMetaTagString()) === false) {
            return false;
        }
        
        $key = TwigExtension::generateMetaTagKey($request,$this->router, $request->getLocale());
        $label = "edit metatags of " . $key;
        $editbox = TwigExtension::wrapOutputEdit(
                        $this->router, $label, $key, 'metatags', array('output' => $label)
        );
        $content = substr($content, 0, $pos) . $editbox . substr($content, $pos);
        $response->setContent($content);

        return true;
    }

}
