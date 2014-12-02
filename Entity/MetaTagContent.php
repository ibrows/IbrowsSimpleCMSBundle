<?php

namespace Ibrows\SimpleCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ibrows\SimpleSeoBundle\Model\ContentInterface as SeoContentInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Ibrows\SimpleCMSBundle\Entity\TextContent
 *
 * @ORM\Table(name="scms_metatagscontent")
 * @ORM\Entity()
 * @DoctrineAssert\UniqueEntity("alias")
 */
class MetaTagContent extends Content implements SeoContentInterface
{

    /**
     * @var $metatags
     * @ORM\Column(type="array")
     *
     */
    protected $metatags;
    static $preventvars = array('title', 'keywords', 'description');

    /**
     * @var string $alias
     *
     * @ORM\Column(name="alias", type="string", length=255, unique=true, nullable=true)
     */
    protected $alias = null;

    /**
     * @var $pathinfo
     * @ORM\Column(type="array")
     *
     */
    protected $pathinfo;


    protected $changedPathInfo = false;


    public function setRouteDefaults(array $defaults)
    {
        $this->pathinfo['__defaults'] = $defaults;
    }

    public function getRouteDefaults()
    {
        if (array_key_exists('__defaults', $this->pathinfo) && is_array($this->pathinfo['__defaults'])) {
            return $this->pathinfo['__defaults'];
        }
        return array();
    }


    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        if ($alias == $this->alias) {
            //nothing changed
            return;
        }

        if (empty($alias)) {
            $this->alias = null;
        } else {
            $this->alias = $alias;
        }
        $this->setPathinfo();
    }

    public function getMetatags()
    {
        $return = '';
        if (is_array($this->metatags)) {
            foreach ($this->metatags as $key => $val) {
                if (!in_array($key, self::$preventvars)) {
                    $return .= "$key=$val\n";
                }
            }
        }
        return $return;
    }

    public function setMetatags($metatags)
    {
        foreach (explode("\n", $metatags) as $val) {
            $pos = strpos($val, '=');
            if ($pos === false) {
                continue;
            }
            $key = substr($val, 0, $pos);
            if (!in_array($key, self::$preventvars)) {
                $this->metatags[$key] = substr($val, ++$pos);
            }
        }
    }

    public function getMetatag($metatag)
    {
        if (!isset($this->metatags[$metatag])) {
            return null;
        }
        return $this->metatags[$metatag];
    }

    public function setMetatag($metatag, $value)
    {
        $this->metatags[$metatag] = $value;
    }

    public function getTitle()
    {
        return $this->getMetatag('title');
    }

    public function setTitle($title)
    {
        $this->setMetatag('title', $title);
    }

    public function getKeywords()
    {
        return $this->getMetatag('keywords');
    }

    public function setKeywords($keywords)
    {
        $this->setMetatag('keywords', $keywords);
    }

    public function getDescription()
    {
        return $this->getMetatag('description');
    }

    public function setDescription($description)
    {
        $this->setMetatag('description', $description);
    }

    //return html
    public function toHTML(\Ibrows\SimpleCMSBundle\Helper\HtmlFilter $filter, array $args)
    {
        if (isset($args['output'])) {
            return $args['output'];
        }
        if (!isset($args['pre'])) {
            $args['pre'] = "\n       ";
        }
        if (!is_array($this->metatags)) {
            $this->metatags = array();
        }
        $metatagoutput = '';
        foreach ($this->metatags as $key => $tag) {
            if (isset($args[$key])) {
                $tag = $tag . ' ' . $args[$key];
            }
            if ($key == 'title') {
                $metatagoutput .= $args['pre'] . "<title>" . $filter->filterHtml($tag) . "</title>";
                continue;
            }
            $metatagoutput .= $args['pre'] . self::createMetaTag($filter->filterHtml($key), $filter->filterHtml($tag));
        }


        return $metatagoutput;;
    }

    static public function createMetaTag($name, $content, $extras = array())
    {
        $metastring = '';
        $metastring .= '<meta name="' . $name . '"';
        foreach ($extras as $key => $extra) {
            $metastring .= " $key=\"$extra\"";
        }
        $metastring .= ' content="' . $content . '" />';
        return $metastring;
    }


    public function getPathinfo()
    {
        return $this->pathinfo;
    }

    private function setPathinfo()
    {
        $this->changedPathInfo = true;
    }

    public function setChangedPathInfo(array $pathInfo)
    {
        $this->pathinfo = $pathInfo;
    }

    /**
     * @return boolean
     */
    public function isChangedPathInfo()
    {
        return $this->changedPathInfo;
    }

    /**
     * @return array
     */
    public function getMetaTagArray()
    {
        if (!is_array($this->metatags)) {
            return array();
        }
        return $this->metatags;
    }

    public function getHtmlRenderServiceId()
    {
        return 'ibrows_simple_seo.meta_tag_renderer';
    }


}