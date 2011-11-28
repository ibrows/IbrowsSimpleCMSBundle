<?php

namespace Ibrows\SimpleCMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class MetaTagContentType extends ContentType
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
                ->add('alias', 'text', array())
                ->add('title', 'text', array())
                ->add('keywords', 'text', array())
                ->add('description', 'textarea', array())
                ->add('metatags', 'textarea', array('label' => 'additional metatags'))

        ;
    }

    public function getName()
    {
        return 'ibrows_simplecmsbundle_metatagcontenttype';
    }

}

