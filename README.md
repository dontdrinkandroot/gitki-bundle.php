gitki-bundle
============

[![Latest Stable Version](http://poser.pugx.org/dontdrinkandroot/gitki-bundle/v)](https://packagist.org/packages/dontdrinkandroot/gitki-bundle)
[![License](http://poser.pugx.org/dontdrinkandroot/gitki-bundle/license)](https://packagist.org/packages/dontdrinkandroot/gitki-bundle)
![Continuous Integration](https://github.com/dontdrinkandroot/gitki-bundle.php/actions/workflows/continuous-integration.yml/badge.svg)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=dontdrinkandroot_gitki-bundle.php&metric=coverage)](https://sonarcloud.io/dashboard?id=dontdrinkandroot_gitki-bundle.php)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=dontdrinkandroot_gitki-bundle.php&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=dontdrinkandroot_gitki-bundle.php)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=dontdrinkandroot_gitki-bundle.php&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=dontdrinkandroot_gitki-bundle.php)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=dontdrinkandroot_gitki-bundle.php&metric=security_rating)](https://sonarcloud.io/dashboard?id=dontdrinkandroot_gitki-bundle.php)

About
-----

Symfony Bundle that allows you to easily integrate a git based wiki into you project.

This project is currently in alpha state. It is working but changes happen frequently.

### Features

* Git based
* Fully integrated markdown support (commonmark)
* Optional elasticsearch integration
* Minimal configuration
* Easy to extend
* Easy to integrate

Installation
------------

Install via composer:

```
composer require dontdrinkandroot/gitki-bundle
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
            new Dontdrinkandroot\GitkiBundle\DdrGitkiBundle(),
        );

        // ...
    }
}
```

To use this bundle in your project the User class handed to the bundle  *must* implement the
```Dontdrinkandroot\GitkiBundle\Model\GitUserInterface```. Fortunately this is compatible with the FOSUserBundle.

Configuration
-------------

Configure the bundle in the ```app/config/config.yml```. At least the repository path is required which must point to a
git repository which is initialized and readable/writeable by the webserver.

```
# Default configuration for extension with alias: "ddr_gitki"
ddr_gitki:

    # The path to the git repository containing the wiki files. Must end with slash.
    repository_path:      ~ # Required

    # When enabled breadcrumbs are shown for easy navigation
    show_breadcrumbs:     true

    # When enabled the files and folders of the containing directory are shown while viewing a file
    show_directory_contents: true

    # Markdown specific configuration
    markdown:

        # When disabled all html content is escaped
        allow_html:           false
        toc:

            # Show the table of contents
            enabled:              true

            # Max depth of the table of contents
            max_level:            3

    # Configure elasticsearch integration
    elasticsearch:
        index_name:           ~ # Required
        host:                 localhost
        port:                 9200

    # Maps user roles to internal roles
    roles:

        # Is allowed to view content
        watcher:              IS_AUTHENTICATED_ANONYMOUSLY

        # Is allowed to edit content
        committer:            ROLE_USER
        admin:                ROLE_ADMIN

    # The file names that are used as a directory index. Searched in the order defined.
    index_files:

        # Defaults:
        - index.md
        - README.md
        - index.txt
        - README.txt
```

Add the routing to the ```app/config/routing.yml```:

```
ddr_gitki_base:
resource: "@DdrGitkiBundle/Resources/config/routing.yml"
prefix: /wiki
```

Development
-----------

Run elasticsearch in docker locally:

``docker run -p 127.0.0.1:9200:9200 -p 127.0.0.1:9300:9300 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:7.16.3``

[Source](https://www.elastic.co/guide/en/elasticsearch/reference/current/docker.html)
