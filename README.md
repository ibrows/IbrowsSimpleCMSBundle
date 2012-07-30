IbrowsSimpleCMSBundle - Simple CMS for anyone anywhere
========================================================

Setup the bundle
-------------

1.  Fetch the source code
2.  Add the namespace to your autoloader
3.  Add the bundle to your `AppKernel` class
4.  Add routing
5.  Generate Schema
6.  Permissions
7.  Enjoy


How to use simple CMS
-------------

Just add a tag to your twig file to allow user to edit a specific entity type (by default, text & image is provided)

Some Examples

``` twig
{# add a text with key 'mycustomtextidentifier'  #}
{{ 'mycustomtextidentifier'|scms('text') }}

{# add a image with key 'mycustomimageidentifier'  #}
{{ scms('mycustomimageidentifier','image') }}

{# add a collections of texts with key 'mycustomtextidentifier'  #}
{{ 'mycustomtextidentifier'|scms_collection('text') }}

{# add a collections of images with key 'mycustomimageidentifier'  #}
{{ scms_collection('mycustomimageidentifier','image') }}

```




Advanced examples
-----------------

Single image with `my` class and inline editorstyle (instead of block) and a specific locale (default is the current locale)

``` twig
{{ scms('mycustomidentifier','image',{'inline':true,'attr':{'class':'mycssclass'} },'de_CH'  ) }}
```

Flexible amount of wysiwyg text elements

``` twig
{{ scms_collection('mycustomidentifier','text',{'html':true}) }}
```


Install & setup the bundle
--------------------------

1.  Fetch the source code


	Add IbrowsSimpleCMSBundle in your composer.json:
	
	```js
	{
	    "require": {
	        "ibrows/simplecms-bundle": "*"
	    }
	}
	```
	
	Now tell composer to download the bundle by running the command:
	
	``` bash
	$ php composer.phar update ibrows/simplecms-bundle
	```
	
	Composer will install the bundle to your project's `ibrows/simplecms-bundle` directory.


2.  Add the bundle to your `AppKernel` class

    ``` php

    // app/AppKernerl.php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Ibrows\SimpleCMSBundle\IbrowsSimpleCMSBundle(),
            // ...
        );
        // ...
    }
    
    ```

3.  Add routing

    ``` yaml

    // app/config/routing.yml

    IbrowsSimpleCMSBundle:
        resource: "@IbrowsSimpleCMSBundle/Controller/"
        type:     annotation
        prefix:   /scms  

    ```

4.  Generate Schema

    ``` bash
    php app/console doctrine:schema:update  --force

    ```
5.  Permissions

    Get permissions for FileUpload, default folder is web-dir `uploads/documents` 


Minimal configuration
---------------------

This bundle requires Nothing !


Additional configuration
------------------------

### Edit default config 
    # app/config/conf.yml

        ibrows_simple_cms:
          include_js_libs: true
          upload_dir: 'uploads/documents'
          role: ROLE_IS_AUTHENTICATED_ANONYMOUSLY



### Add security per type

    # app/config/conf.yml

        ibrows_simple_cms:
          types:
        # defaults
            text: { class: Ibrows\SimpleCMSBundle\Entity\TextContent , type: Ibrows\SimpleCMSBundle\Form\TextContentType, security:{general: ROLE_ADMIN} }
            image: { class: Ibrows\SimpleCMSBundle\Entity\ImageContent, type: Ibrows\SimpleCMSBundle\Form\FileContentType, security:{general: ROLE_ADMIN, show: ROLE_SUPER_ADMIN, create: ROLE_SUPER_ADMIN , edit: ROLE_SUPER_ADMIN , delete: ROLE_SUPER_ADMIN  } }

### Edit TinyMCE Options


    # app/config/conf.yml

        ibrows_simple_cms:
          wysiwyg:
            theme: 'advanced'
            theme_advanced_buttons1: 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect'
        # other configs...



### Add types  
Add / Edit types of Content:

    # app/config/conf.yml

        ibrows_simple_cms:
          types:
            # defaults
            text: { class: Ibrows\SimpleCMSBundle\Entity\TextContent , type: Ibrows\SimpleCMSBundle\Form\TextContentType }
            image: { class: Ibrows\SimpleCMSBundle\Entity\ImageContent, type: Ibrows\SimpleCMSBundle\Form\FileContentType}
            # custom
            mytext: { class: Ibrows\SimpleCMSBundle\Entity\TextContent , type: Ibrows\SimpleCMSBundle\Form\TextContentType , repository: Ibrows\SimpleCMSBundle\Repository\TextContent, label:first}
            mycustomentity: { class: Ibrows\XXXBundle\Entity\YYYContent , type: Ibrows\SimpleCMSBundle\Form\YYYContentType , repository: Ibrows\SimpleCMSBundle\Repository\Content, label:myone}


Your YYYContent Entity have to implement `Ibrows\SimpleCMSBundle\Entity\ContentInterface` or extend `Ibrows\SimpleCMSBundle\Entity\Content` or a Child of it.
It's also a good idea to extend `Ibrows\SimpleCMSBundle\ContentType` in your FormType.



Screenshots
-----------
![SimpleCMS1](http://ibrows.ch/tl_files/content/newsblog/teaserimages/simple1.png "Simple CMS")

![SimpleCMS2](http://ibrows.ch/tl_files/content/newsblog/teaserimages/simple2.png "Simple CMS")

![SimpleCMS3](http://ibrows.ch/tl_files/content/newsblog/teaserimages/simple3.png "Simple CMS")



TODO
----
 
  - create the ODM version

AUTHORS
----
 
 Developed at iBROWS GmbH Zurich: 
 Marc Steiner
 Dominik Zogg
 Olivier Kofler
 
 Twitter: 
 @iBRWOSWEB
 

