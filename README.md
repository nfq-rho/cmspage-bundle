NfqCmsPageBundle
=============================

## Installation

### Step 1: Download NfqCmsPageBundle using composer

Add NfqCmsPageBundle in to your composer.json:

	{
		"repositories": [
            {
              "type": "vcs",
              "url": "git@github.com:nfq-rho/cmspage-bundle.git"
            },
		],
    	"require": {
        	"nfq-rho/cmspage-bundle": "~0.4"
    	}
	}

### Step 2: Add bundle configuration

	nfq_cms_page:
        #The upload manager will use %kernel.root_dir/../web/ to generate absolute path
        #Don't forget to create this directory with proper permissions
        upload_dir: uploads/cms/
        types:
        	cms:
        		#Can be displayed for users through URL
        		public: true
        		#Set to true if this adapter need container
        		container_aware: false
        		#Set to true if this adapter has featured image
        		has_featured_image: true
			    html: #Serves as simple HTML content widget, which can be edited through CMS pages
            #These types of widgets do not have slug
            public: false
            container_aware: false
            has_featured_image: false
        places: ~

`places` config allows you to configure places for links to cms pages. You can configure, for example, Footer Block link     place and assign multiple pages for that place. Then in twig template just add following code:

        <ul>
            {% for link in cms_urls_in_place('foo_place_1', true) %}
                <li>{{ link|raw }}</li>
            {% endfor %}
        </ul>

        places:
            foo_place_1:
                #Friendly place title
                title: Footer Block
                #Maximum amount of items which can be assigned for this place
                limit: 3
        
	#Following config, configures tinyMce editor
	stfalcon_tinymce:
        include_jquery: false
        tinymce_jquery: true
        theme:
            simple:
                height: 400
                plugins:
                   - "advlist autolink lists link image charmap print preview hr anchor pagebreak"
                   - "searchreplace wordcount visualblocks visualchars code fullscreen"
                   - "insertdatetime media nonbreaking save table contextmenu directionality"
                   - "template paste textcolor noneditable -nfq_gallery"
                toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
                toolbar2: "print preview media | forecolor backcolor"

If you want to use translatable features, you must add following listener to your services.yml

	gedmo.listener.translatable:
		class: Gedmo\Translatable\TranslatableListener
		tags:
			- { name: doctrine.event_subscriber, connection: default }
		calls:
			- [ setAnnotationReader, [ "@annotation_reader" ] ]
			- [ setDefaultLocale, [ %locale% ] ]
			- [ setTranslationFallback, [ false ] ]

### Step 3: Enable the bundle

Enable the bundle in the kernel.:

	<?php
	// app/AppKernel.php

	public function registerBundles()
	{
	    $bundles = array(
        	// ...
        	new \Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
        	new \Nfq\CmsPageBundle\NfqCmsPageBundle(),
    	);
	}

Also it is suggested to install NfqFilemanagerBundle which adds file manager support for tinyMCE editor, which allows
to upload and use images in your CMS pages

