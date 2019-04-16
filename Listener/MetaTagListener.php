<?php

namespace Ibrows\SimpleCMSBundle\Listener;

use Ibrows\SimpleCMSBundle\Extension\TwigExtension;
use Ibrows\SimpleSeoBundle\Extension\TwigExtension as SeoTwigExtension ;
use Ibrows\SimpleSeoBundle\Routing\KeyGenerator  as SeoKeyGenerator;
use Ibrows\SimpleSeoBundle\Routing\UrlGenerator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Ibrows\SimpleCMSBundle\Security\SecurityHandler;

class MetaTagListener
{

    private $packages;
    private $router;
    private $securityHandler;
    private $translator;
    private $keyGenerator;

    public function __construct(Packages $packages, SecurityHandler $securityHandler, RouterInterface $router, \Symfony\Component\Translation\TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->packages = $packages;
        $this->router = $router;
        $this->securityHandler = $securityHandler;
        $this->keyGenerator = new SeoKeyGenerator();
    }

    /**
     * @return SeoKeyGenerator
     */
    public function getKeyGenerator()
    {
        return $this->keyGenerator;
    }

    /**
     * @param SeoKeyGenerator $keyGenerator
     */
    public function setKeyGenerator($keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
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
