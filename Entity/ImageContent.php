<?php

namespace Ibrows\SimpleCMSBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ibrows\SimpleCMSBundle\Entity\ImageContent
 * 
 * @ORM\Table(name="scms_imagecontent")
 * @ORM\Entity(repositoryClass="Ibrows\SimpleCMSBundle\Repository\ContentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ImageContent extends Content
{

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * 
     * @Assert\File(maxSize="6000000000")
     */
    protected $file;

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        //remove old file
        if ($this->getPath()) {
            if (file_exists($this->getPath())) {
                unlink($this->getAbsolutePath());
            }
        }
        $this->file = $file;
        $this->path = ''; //path must be changed when file change
    }

    public function getWebPath($permlink = false)
    {
        if ($permlink) {
            return $this->getSymLink(true);
        }
        return null === $this->getPath() ? null : $this->getPath();
    }

    protected function getUploadRootDir()
    {
        return dirname($this->getAbsolutePath());
    }

    protected function getAbsolutePath()
    {
        // the absolute directory path where uploaded documents should be saved
        //rootpath

        return $this->params
                ->getParameter('kernel.root_dir') . '/../web/' . $this->getPath();
    }

    protected function getUploadDir()
    {
        return $this->params
                ->getParameter('ibrows_simple_cms.upload_dir');
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {

        if (null !== $this->file) {
            $name = preg_replace('/([^a-z0-9\-\_])/i', '', $this->getName());
            $xtension = $this->file
                    ->guessExtension();
            if ($xtension == null) {
                $xtension = $this->file
                        ->getExtension();
                if ($xtension == null) {
                    $xtension = ExtensionGuesser::getInstance()->guess($this->file
                                    ->getClientMimeType());
                }
            }
            $this->setPath($this->getUploadDir() . '/' . $name . time() . '.' . $xtension);
        }
    }

    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function upload()
    {

        if (null === $this->file) {
            return;
        }
        $filename = str_replace(dirname($this->getAbsolutePath()), '', $this->getAbsolutePath());
        $this->file
                ->move($this->getUploadRootDir(), $filename);

        if (is_file($this->getSymLink())) {
            unlink($this->getSymLink());
        }
        if (!is_dir(dirname($this->getSymLink()))) {
            mkdir(dirname($this->getSymLink()));
        }
        link($this->getAbsolutePath(), $this->getSymLink());
        unset($this->file);
    }

    public function getSymLink($relative = false)
    {
        $name = preg_replace('/([^a-z0-9\-\_])/i', '', $this->getName());
        $ext = strrchr($this->getPath(), '.');
        if (!$relative) {
            return dirname($this->getAbsolutePath()) . '/sym/' . $name . $ext;
        } else {
            return dirname($this->getPath()) . '/sym/' . $name . $ext;
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if (is_file($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }
        if (is_file($this->getSymLink())) {
            unlink($this->getSymLink());
        }

    }

    //return html
    public function toHTML(\Ibrows\SimpleCMSBundle\Helper\HtmlFilter $filter, array $args)
    {
        $return = '';
        $name = $filter->filterHtml($this->getName());
        $description = $filter->filterHtml($this->getDescription());
        if (empty($description)) {
            $description = $name;
        }
        $config = array(
            'attr' => array(
                'class' => 'simplecms-imagecontent', 'alt' => $description, 'title' => $name
            )
        );

        if (isset($args['forceimage']) && $args['forceimage'] == true) {
            $image = true;
        } else if (isset($args['forcefile']) && $args['forcefile'] == true) {
            $image = false;
        } else {
            try {
                $mimetpye = \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser::getInstance()->guess($this->getWebPath());
                if (strpos($mimetpye, 'image') !== 0) {
                    $image = false;
                } else {
                    $image = true;
                }
            } catch (\Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException $e) {
                $image = true;
            }
        }
        if (!$image) {
            //if not a image
            $config = array(
                    'attr' => array(
                        'class' => 'simplecms-downloadcontent', 'target' => '_blank', 'title' => $name
                    )
            );
        }
        $arr = parent::mergeUserArgs($args, $config);
        foreach ($arr['attr'] as $key => $val) {
            $return .= " $key=\"$val\"";
        }
        $path = '/' . $this->getWebPath((isset($args['permlink']) && $args['permlink'] == true));
        if (isset($args['absolute']) && $args['absolute'] == true) {
            $path = $this->selfURL() . $path;
        }
        if (isset($args['noname']) && $args['noname'] == true) {
            $name = null;
        }        
        if (!$image) {
            //if not a image
            return '<a href="' . $path . '" ' . $return . ' >' . $name . ' </a>';
        }
        $return = '<img src="' . $path . '" ' . $return . ' >';
        return $return;
        ;
    }
   
    private  function selfURL()
    {
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol =  substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos($_SERVER["SERVER_PROTOCOL"], "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port ;
    }

}
