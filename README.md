gitki-base-bundle
=================

[![Build Status](https://travis-ci.org/dontdrinkandroot/gitki-base-bundle.php.svg?branch=master)](https://travis-ci.org/dontdrinkandroot/gitki-base-bundle.php)
[![Code Coverage](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-base-bundle.php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-base-bundle.php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-base-bundle.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-base-bundle.php/?branch=master)

About
-----

Symfony Bundle that allows you to easily integrate a git based wiki into you project.

This project is currently in alpha state. It is working but changes happen frequently.

### Features

* Git based
* Optional elasticsearch integration
* Full [markdown support](https://github.com/dontdrinkandroot/gitki-markdown-bundle.php) (commonmark) available
* Minimal configuration
* Easy to extend
* Easy to integrate

Installation
------------

Install via composer:

```
composer require dontdrinkandroot/gitki-base-bundle
```

Enable the bundle by adding the following line in the ```app/AppKernel.php``` file of your project:

```php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Dontdrinkandroot\Gitki\BaseBundle\DdrGitkiBaseBundle(),
        );

        // ...
    }
}
```

To use this bundle in your project the User class handed to the bundle  *must* implement the
```Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface```. Fortunately this is compatible with the FOSUserBundle.

Configuration
-------------

Configure the bundle in the ```app/config/config.yml```. At least the repository path is required which must point to a
git repository which is initialized and readable/writeable by the webserver.

```
# Default configuration for extension with alias: "ddr_gitki"
ddr_gitki:
    repository_path:      ~ # Required
    name:                 GitKi
    twig:
        show_breadcrumbs:     true
        show_toc:             true
        toc_max_level:        3
    elasticsearch:
        index_name:           ~ # Required
        host:                 localhost
        port:                 9200
    roles:
        watcher:              IS_AUTHENTICATED_ANONYMOUSLY
        committer:            ROLE_USER
        admin:                ROLE_USER
```

Add the routing to the ```app/config/routing.yml```:

```
ddr_gitki_base:
resource: "@DdrGitkiBaseBundle/Resources/config/routing.yml"
prefix: /wiki
```