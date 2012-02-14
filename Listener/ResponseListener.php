<?php

namespace Ibrows\SimpleCMSBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\templating\Helper\CoreAssetsHelper;
use Symfony\Component\Routing\RouterInterface;
use Ibrows\SimpleCMSBundle\Security\SecurityHandler;

class ResponseListener {

    private $assetHelper;
    private $router;
    private $includeLibs;
    private $includeJS;
    private $includeCSS;
    private $securityHandler;
    private $wysiwygconfig;

    public function __construct(CoreAssetsHelper $assetHelper, SecurityHandler $securityHandler, RouterInterface $router, $includeJS = true, $includeCSS = true, $includeLibs = true,array $wysiwygconfig = array()) {
        $this->assetHelper = $assetHelper;
        $this->router = $router;
        $this->includeLibs = $includeLibs;
        $this->includeJS = $includeJS;
        $this->includeCSS = $includeCSS;
        $this->securityHandler = $securityHandler;
        $this->wysiwygconfig = $wysiwygconfig;
    }

    public function onKernelResponse(FilterResponseEvent $event) {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }


        $response = $event->getResponse();
        $request = $event->getRequest();

        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()  || $request->get('_xml_http_request') || !$response->isSuccessful()) {
            return;
        }


        if (!$this->securityHandler->isGranted('ibrows_simple_cms_content')) {
            return;
        }
        $this->inject($response);

    }

    /**
     *
     * @param Response $response A Response instance
     */
    protected function inject(Response $response) {
        $content = $response->getContent();
        $pos = strripos($content, '</head>');
        if ($pos === false) {
            return false;
        }


        $scripts = '';
        $needed = array(
            'jquery-1.([^"]*).js' => 'js/jquery-1.6.4.min.js',
            'jquery-ui-1.([^"]*).js' => 'js/jquery-ui-1.8.16.custom.min.js',
            'jquery-ui([^"]*).css' => 'themes/darkness/jquery-ui.css',
            'jquery.form([^"]*).js' => 'js/jquery.form-2.8.5.js',
            'jquery.tinymce([^"]*).js' => 'js/tiny_mce/jquery.tinymce.js',
        );

        if ($this->includeLibs === true) {
            foreach ($needed as $key => $value) {
                if (preg_match("/$key\"/i",$content) === 0) {
                    $url = $this->assetHelper->getUrl('bundles/ibrowssimplecms/' . $value);
                    if (stripos($value, '.css')) {
                        $scripts .= ' <link rel="stylesheet" type="text/css" media="screen" href="' . $url . '" /> ';
                    } else {
                        $scripts .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
                    }
                }
            }
        }
    
        $this->wysiwygconfig['script_url'] = $this->assetHelper->getUrl('bundles/ibrowssimplecms/' . 'js/tiny_mce/tiny_mce.js');
         $confscript= <<<HTML
<script type="text/javascript">
    var simple_cms_wysiwyg_config = %s;
    var ajaxfilemanagerurl = "%s";
</script>
HTML;
            
        $scripts .= sprintf($confscript, json_encode( $this->wysiwygconfig ), $this->router->generate('ibrows_simple_cms_file_manager'));
        if ($this->includeJS === true) {
            $url = $this->assetHelper->getUrl('bundles/ibrowssimplecms/js/simplecms.js');
            $scripts .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
        }
        if ($this->includeCSS === true) {
            $url = $this->assetHelper->getUrl('bundles/ibrowssimplecms/css/simplecms.css');
            $scripts .= ' <link rel="stylesheet" type="text/css" media="screen" href="' . $url . '" /> ';
        }        
        $content = substr($content, 0, $pos) . $scripts . substr($content, $pos);
        $response->setContent($content); 
        return true;
    }

}
