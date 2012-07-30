<?php

namespace Ibrows\SimpleCMSBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FileContentType extends ContentType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder                        
            ->add('name')
            ->add('file')
            ->add('description')    
        ;
    }

    public function getName()
    {
        return 'ibrows_simplecmsbundle_filecontenttype';
    }
    

}
