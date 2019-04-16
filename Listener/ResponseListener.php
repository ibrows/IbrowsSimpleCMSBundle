<?php

namespace Ibrows\SimpleCMSBundle\Listener;

use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Ibrows\SimpleCMSBundle\Security\SecurityHandler;

class ResponseListener {

    private $packages;
    private $router;
    private $includeLibs;
    private $includeJS;
    private $includeTiny;
    private $includeCSS;
    private $securityHandler;
    private $wysiwygconfig;

    public function __construct(Packages $packages, SecurityHandler $securityHandler, RouterInterface $router, $includeJS = true, $includeCSS = true, $includeLibs = true,  $includeTiny = true,array $wysiwygconfig = array()) {
        $this->packages = $packages;
        $this->router = $router;
        $this->includeLibs = $includeLibs;
        $this->includeJS = $includeJS;
        $this->includeTiny = $includeTiny;
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
            'jquery-1.([^"]*).js' => 'js/jquery-1.10.2.min.js',
            'bootstrap.([^"]*).js' => 'js/bootstrap-modal-3.0.0.js',
            'bootstrap([^"]*).css' => 'css/bootstrap/bootstrap.css',
            'jquery.form([^"]*).js' => 'js/jquery.form-3.43.0.min.js',
        );
        if ($this->includeTiny === true) {
            $needed['jquery.tinymce([^"]*).js'] = 'js/tiny_mce/jquery.tinymce.js';
        }
        if ($this->includeLibs === true) {
            foreach ($needed as $key => $value) {
                if (preg_match("/$key\"/i",$content) === 0) {
                    $url = $this->packages->getUrl('bundles/ibrowssimplecms/' . $value);
                    if (stripos($value, '.css')) {
                        $scripts .= ' <link rel="stylesheet" type="text/css" media="screen" href="' . $url . '" /> ';
                    } else {
                        $scripts .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
                    }
                }
            }
        }

        $this->wysiwygconfig['script_url'] = $this->packages->getUrl('bundles/ibrowssimplecms/' . 'js/tiny_mce/tiny_mce.js');
         $confscript= <<<HTML
<script type="text/javascript">
    var simple_cms_wysiwyg_config = %s;
    var ajaxfilemanagerurl = "%s";
</script>
HTML;
        if ($this->includeTiny === true) {
            $scripts .= sprintf($confscript, json_encode( $this->wysiwygconfig ), $this->router->generate('ibrows_simple_cms_file_manager'));
        }
        if ($this->includeJS === true) {
            $url = $this->packages->getUrl('bundles/ibrowssimplecms/js/simplecms.js');
            $scripts .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
        }
        if ($this->includeCSS === true) {
            $url = $this->packages->getUrl('bundles/ibrowssimplecms/css/simplecms.css');
            $scripts .= ' <link rel="stylesheet" type="text/css" media="screen" href="' . $url . '" /> ';
        }
        $content = substr($content, 0, $pos) . $scripts . substr($content, $pos);
        $response->setContent($content);
        return true;
    }

}
