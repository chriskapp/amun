amun
====

## About

Amun is a content managment framework written in PHP 5 and based on the PSX 
framework. The goal of Amun is to build a more federated and programmable web. 
This is achieved by providing an RESTful API for managing and distributing 
content in standard formats like JSON, XML and Atom. More informations at the
website at http://amun-project.org

## Features

* Uses OAuth for API authentication
* Supports OpenID login
* Provides Web Host Metadata with LRDD and WebFinger support
* RESTful API designed after the OpenSocial Core API Server Specification
* Supports Portable Contacts
* Provides XRDS for service discovering
* FOAF support for profiles
* Active messaging integration with STOMP
* Javascript and CSS concatenation to optimize page load
* User group and right managment

## Installation

To install Amun simply clone the repository or download an release. After this
enter you database connection data in the configuration.php file and run the
following command:

    composer install

Amun uses a custom plugin to install the database structure through composer. If 
something went wrong simply use "composer update" and the installer will try to
install all missing services. For more informations please read the manual.

## Services

Amun has its own package server at http://packages.amun-project.org to 
distribute all required services.


[![Build Status](https://travis-ci.org/k42b3/amun.png)](https://travis-ci.org/k42b3/amun)
