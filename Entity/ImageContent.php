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
    protected $path;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * 
     * @Assert\File(maxSize="6000000000")
     */
    protected $file;    
    

    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        //remove old file
        if($this->getPath()){
            if(file_exists($this->getPath())){
                unlink($this->getAbsolutePath());
            }    
        }
        $this->file = $file;
        $this->path='';//path must be changed when file change
    }



    public function getWebPath()
    {
        return null === $this->getPath() ? null : $this->getPath();
    }

    protected function getUploadRootDir()
    {
        return dirname( $this->getAbsolutePath());
    }
    
    protected function getAbsolutePath()
    {
        // the absolute directory path where uploaded documents should be saved
        //rootpath

        return  $this->params->getParameter('kernel.root_dir').'/../web/'.$this->getPath();
    }    


    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return $this->params->getParameter('ibrows_simple_cms.upload_dir');

    }        
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        
        if (null !== $this->file) {            
            $name = preg_replace('/([^a-z0-9\-\_])/i', '', $this->getName());
            $this->setPath($this->getUploadDir().'/'.$name. time().'.'. $this->file->guessExtension());
            
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
        $this->file->move($this->getUploadRootDir(), $filename); 

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

  
    
    //return html
    public function toHTML(\Ibrows\SimpleCMSBundle\Helper\HtmlFilter $filter,array $args){        
        $return ='';        
        $name = $filter->filterHtml($this->getName());
        $config = array('attr'=>array('class'=>'simplecms-imagecontent','alt'=>$name,'title'=>$name));
        $image = true;
        try{
            $mimetpye = \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser::getInstance()->guess($this->getWebPath() );       
            if(strpos($mimetpye, 'image')!==0){
                $image = false;
            }  
        }  catch (\Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException $e ){

        }
        if(!$image){
            //if not a image
            $config = array('attr'=>array('class'=>'simplecms-downloadcontent','title'=>$name));
        }               
        $arr = parent::mergeUserArgs($args, $config);
        foreach($arr['attr'] as $key => $val){
            $return .= "$key=\"$val\"";
        }
        
        if(!$image){
            //if not a image
            return '<a href="/'.$this->getWebPath().'" '.$return.' ">'.$name.' </a>';
        }            
        $return = '<img src="/'.$this->getWebPath().'" '.$return.' ">';
        return $return;
            ;
    }
}