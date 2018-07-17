<?php

namespace Ibrows\SimpleCMSBundle\Extension;

class TwigExtension extends \Twig_Extension implements \Ibrows\SimpleCMSBundle\Helper\HtmlFilter
{

    /**
     *
     * @var \Ibrows\SimpleCMSBundle\Model\ContentManager 
     */
    private $manager;

    /**
     * @var \Ibrows\SimpleCMSBundle\Security\SecurityHandler
     */
    private $handler;

    /**
     * @var \Twig_Environment
     */
    protected $env;

    /**
     *
     * @var  \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     *
     * @var  \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     *
     * @var  \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->env = $environment;
    }

    public function __construct(\Ibrows\SimpleCMSBundle\Model\ContentManager $manager, \Symfony\Component\Translation\TranslatorInterface $translator, \Symfony\Component\Routing\RouterInterface $router, \Symfony\Component\DependencyInjection\Container $container)
    {
        $this->manager = $manager;
        $this->translator = $translator;
        $this->router = $router;
        $this->container = $container;
    }

    public function setSecurityHandler(\Ibrows\SimpleCMSBundle\Security\SecurityHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'scms' => new \Twig_Filter_Method($this, 'content', array('is_safe' => array('html'))),
            'scms_collection' => new \Twig_Filter_Method($this, 'contentCollection', array('is_safe' => array('html'))),
            'scmsc' => new \Twig_Filter_Method($this, 'contentCollection', array('is_safe' => array('html'))),
            'scms_iseditmode' => new \Twig_Filter_Method($this, 'isGranted', array('is_safe' => array('html'))),
        );
    }

    public function getFunctions()
    {
        return array(
            'scms' => new \Twig_SimpleFunction($this, 'content', array('is_safe' => array('html'))),
            'scms_collection' => new \Twig_SimpleFunction($this, 'contentCollection', array('is_safe' => array('html'))),
            'scmsc' => new \Twig_SimpleFunction($this, 'contentCollection', array('is_safe' => array('html'))),
            'scms_iseditmode' => new \Twig_SimpleFunction($this, 'isGranted', array('is_safe' => array('html'))),
        );
    }


    public static function wrapOutputEdit(\Symfony\Component\Routing\RouterInterface $router, $out, $key, $type, array $arguments = array(), $default='')
    {
        $class = '';
        if (isset($arguments['inline']) && $arguments['inline'] == true) {
            $class = 'inline';
        }

        $editpath = $router->generate('ibrows_simple_cms_content_edit_key', array('key' => $key, 'type' => $type));
        $editpath .="?args=" . urlencode(serialize($arguments));
        $editpath .="&default=" . $default;
        $out = '<a href="' . $editpath . '" class="simplecms-editlink" ></a>' . $out;
        $out = '<a href="' . $router->generate('ibrows_simple_cms_content_delete_key', array('key' => $key, 'type' => $type)) . '" class="simplecms-deletelink" > </a>' . $out;
        $out = "<div class=\"simplcms-$key-$type simplecms-edit $class\" id=\"simplcms-$key-$type\" >$out</div>";

        return $out;
    }

    private function wrapOutputForEdit($out, $key, $type, array $arguments = array(), $default='')
    {
        return self::wrapOutputEdit($this->router, $out, $key, $type, $arguments, $default);
    }

    public function content($key, $type, array $arguments = array(), $locale = null, $default=null)
    {

        $debugmessage = '';

        if ($this->env->isDebug()) {
            $debugmessage .= "<!--debug IbrowsSimpleCMS\n";
            $debugmessage .= "type=$type \n";
            $debugmessage .= "key=$key \n";
            $debugmessage .= "default=$default \n";
            $debugmessage .= "arguments=" . print_r($arguments, true) . " \n";
            $debugmessage .= '-->';

            if ($default == '' || $default == null) {
                $default = "$key-$type";
            }
        }
        if ($locale == null) {
            $locale = $this->translator->getLocale();
        }
        $obj = $this->manager->find($type, $key, $locale);
        if ($obj) {
            $key = $obj->getKeyword();
            $out = $debugmessage . $obj->toHTML($this, $arguments);
        } else {
            $key = $this->manager->generateLocaledKeyword($key, $locale);
            $out = $default;
        }
        if (isset($arguments['before'])) {
            $out = $arguments['before'] . $out;
        }
        if (isset($arguments['after'])) {
            $out .= $arguments['after'];
        }

        $grant = $this->handler->isGranted('ibrows_simple_cms_content_edit_key', array('key' => $key, 'type' => $type));
        //$grant = $this->handler->isGranted('ibrows_simple_cms_content');
        if (!$grant) {
            return $out;
        }
        if ($out == '') {
            $out = "$key-$type";
        }

        return $this->wrapOutputForEdit($out, $key, $type, $arguments, $default);
    }

    public function contentCollection($key, $type, array $arguments = array(), $locale = null, $default=null, $noedit=false)
    {
        $debugmessage = '';

        if ($this->env->isDebug()) {
            $debugmessage .= "<!--debug IbrowsSimpleCMS Collection\n";
            $debugmessage .= "type=$type \n";
            $debugmessage .= "key=$key \n";
            $debugmessage .= "default=$default \n";
            $debugmessage .= "arguments=" . print_r($arguments, true) . " \n";
            $debugmessage .= '-->';

            if ($default == null) {
                $default = "$key-$type";
            }
        }
        if ($locale == null) {
            $locale = $this->translator->getLocale();
        }
        $objs = $this->manager->findAll($type, $key, $locale);
        $out = '';
        $grant = $this->handler->isGranted('ibrows_simple_cms_content');
        if ($noedit) {
            $grant = false;
        }
        $addkey = $this->manager->getNewGroupKey($key, $objs, $locale);
        if ($objs) {
            foreach ($objs as $objkey => $content) {
                /* @var $content \Ibrows\SimpleCMSBundle\Entity\ContentInterface */
                $outobj = $debugmessage . $content->toHTML($this, $arguments);
                if (isset($arguments['before'])) {
                    $outobj = $arguments['before'] . $outobj;
                }
                if (isset($arguments['after'])) {
                    $outobj .= $arguments['after'];
                }
                if ($grant && $this->handler->isGranted('ibrows_simple_cms_content_edit_key', array('key' => $content->getKeyword(), 'type' => $type))) {
                    $outobj = $this->wrapOutputForEdit($outobj, $content->getKeyword(), $type, $arguments, $default);
                }
                $out .= $outobj;
            }
        } else if (!$grant) {
            $out = $default;
        }

        if (!$grant) {
            return $out;
        }
        $class = '';
        if (isset($arguments['inline']) && $arguments['inline'] == true) {
            $class = 'inline';
        }
        //addlink
        if ($this->handler->isGranted('ibrows_simple_cms_content_create', array('type' => $type))) {
            $editpath = $this->env->getExtension('routing')->getPath('ibrows_simple_cms_content_edit_key', array('key' => $addkey, 'type' => $type));
            $editpath .="?args=" . urlencode(serialize($arguments));
            $editpath .="&default=" . $default;
            $outadd = '<a href="' . $editpath . '" class="simplecms-editlink simplecms-addlink" > </a> ADD ' . $default . '';
            $outadd = "<div class=\"simplcms-$addkey-$type simplecms-edit simplecms-add $class\" id=\"simplcms-$addkey-$type\" >$outadd</div>";
        }


        return "<div class=\"simplcms-collection-$key-$type simplecms-edit-collection $class\" id=\"simplcms-collection-$key-$type\" >$out$outadd</div>";
    }

    public function isGranted($key = null, $type = null)
    {
        $grant = $this->handler->isGranted('ibrows_simple_cms_content');
        if ($grant) {
            $grant = $this->handler->isGranted('ibrows_simple_cms_content_edit_key', array('key' => $key, 'type' => $type));
        }
        return $grant;
    }

    public function filterHtml($string)
    {
        return twig_escape_filter($this->env, $string);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'simplecms';
    }

    function generateUrl($name, $parameters = array(), $absolute = false)
    {
        return $this->router->generate($name, $parameters, $absolute);
        
    }
}