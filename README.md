gitki-bundle
============

[![Build Status](https://travis-ci.org/dontdrinkandroot/gitki-bundle.php.svg?branch=master)](https://travis-ci.org/dontdrinkandroot/gitki-bundle.php)
[![Code Coverage](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-bundle.php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-bundle.php/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-bundle.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dontdrinkandroot/gitki-bundle.php/?branch=master)

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
