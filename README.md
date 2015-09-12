# [Reimagine Belonging Website](http://www.reimaginebelonging.com)

### ToC
- [About this Project](#about-the-project)
- [Website](#website)
- [Installation](#installation-and-development-setup)
- [Website development](#website-development)
- [Managing WordPress](managing-wordpress-dependencies-with-composer)
- [Must-use plugins](#must-use-plugins)
- [Contributing](#contributing)

## About the Project

**Reimagine Belonging** is a multimedia project that explores question of migration, citizenship and belonging by utilizing video documentary, academic research and modern web technology. This is non-profit, open-source project which is made possible by a collaborative effort of volunteers across the globe.

The heart of Reimagine Belonging is its the content, which consists of two main sections: a collection of [video interviews](http://www.reimaginebelonging.com/stories) with children of immigrants and a selection of [historical events](http://www.reimaginebelonging.com/history) that caused migration or in some way connected to questions of migration, citizenship and belonging.

Currently, the project researches history of migration, citizenship and belonging in the United States and Germany and the project's content is is available in [English](http://www.reimaginebelonging.com) and [German](http://www.reimaginebelonging.de). However we are looking forward to expand our research base to other countries/regions.

You are welcome to join our multinational team to help with the research or translation of the content to other languages.

**Our content repositories:**

* United States timeline events (URL)
* Germany timeline events (URL)
* Video interviews subtitles (URL)

You are welcome to contribute to any of these repositories including [this one](https://github.com/rslnk/reimagine-belonging). We have [Contributing Guidelines](https://github.com/rslnk/reimagine-belonging/blob/master/CONTRIBUTING.md) to help you get started. If you have any questions or would like to contribute to the project in some other way, just [drop us a line](mailto:christina@withwingsandroots.com)!

**Follow** us on [Facebook](https://www.facebook.com/withWINGSandROOTS), [Twitter](https://twitter.com/wingsrootsfilm).

You can also check our [public roadmap](http://#) on Trello or sign up to our [newsletter](http://#) to stay up to date with the project' development.

## Website

This website is build with WordPress, using [Bedrock](https://roots.io/bedrock) development stack and [Sage](ttps://roots.io/sage) as a base/starter theme and [AngularJS](https://angularjs.org) for dynamic HTML views used in [Stories Collection](http://www.reimaginebelonging.com/stories) and in [Timeline](http://www.reimaginebelonging.com/stories) events.

### Software / Features

Reimagine Belonging website utilizes the following features:

* [WordPress](https://wordpress.org) for website's content management
* [Bedrock](https://roots.io/bedrock) development tools and WordPress project structure
* [Composer](http://getcomposer.org) for dependency management
* [Gulp](http://gulpjs.com) build script for automated workflow
* [Bower](http://bower.io) for front-end package management
* [BrowserSync](http://www.browsersync.io) for synchronized browser testing
* [asset-builder](https://github.com/austinpray/asset-builder) for assembling dependencies in order to run them through asset pipeline
* [AngularJS](https://angularjs.org) for HTML dynamic views
* [Kouto Swiss](http://kouto-swiss.io) CSS toolbox for Stylus
* [Soil](https://github.com/roots/soil) plugin for cleaner WordPress markup, root relative URLs and [other optimizations](### Soil plugin)
* [ACF PRO](http://www.advancedcustomfields.com) plugin for building edit screens and custom field data management
* HTML5 Boilerplate's .htaccess [plugin](https://github.com/roots/wp-h5bp-htaccess)

## Installation and Development setup

The following documentation contains step by step instructions on how to install project on your local machine and should help you get started with [Reimagine Belonging](http://www.reimaginebelonging.org) website development.

### Requirements

* Git
* PHP >= 5.4
* Node.js >= 0.10 — [Install](https://nodejs.org/download)
* npm >=2.1.5 — [Install](https://www.npmjs.com/package/latest-version)
* Gulp (`npm install -g gulp`)
* Bower (`npm install -g bower`)

### Project structure

Reimagine Belonging website utilizes [Bedrock project organization](https://github.com/roots/bedrock/wiki/Folder-structure), which is similar to putting WordPress in its own subdirectory:

* All required files are stored in `web/` directory including the vendor'd `wp/` source, and the `wp-content` source.
* `wp-content` has been named `app` to better reflect its contents.
* `wp-config.php` remains in the `web/` because it's required by WordPress, but it only acts as a loader. The actual configuration files have been moved to `config/` for better separation.
* `vendor/` is where the Composer managed dependencies are installed to.
* `wp/` is where the WordPress core lives.

```
site/
├── composer.json
├── config/
│   ├── application.php
│   └── environments/
│       ├── development.php
│       ├── staging.php
│       └── production.php
├── vendor/
└── web/
    ├── app/
    │   ├── mu-plugins/
    │   ├── plugins/
    │   └── themes/
    ├── media/
    ├── index.php
    ├── wp-config.php
    └── wp/
```

Note: `config/application.php` is the main config file that contains what `wp-config.php` usually would. Base options are set in there.

### Development server

There are two ways to set up your development server to start working on Reimagine Belonging website:

##### 1. Configuration WordPress with environment specific files

1. Clone the git repo - `git clone https://github.com/rslnk/reimagine-belonging.git`
2. Run `composer install`
3. Copy `.env.example` to `.env` and update environment variables:
  * `DB_NAME` - Database name
  * `DB_USER` - Database user
  * `DB_PASSWORD` - Database password
  * `DB_HOST` - Database host
  * `WP_ENV` - Set to environment (`development`, `staging`, `production`)
  * `WP_HOME` - Full URL to WordPress home (http://example.dev)
  * `WP_SITEURL` - Full URL to WordPress including subdirectory (http://example.dev/wp)
4. Set your site vhost document root to `/path/to/site/web/`
5. Access WP admin at `http://example.dev/wp/wp-admin`

##### 2. Use trellis-reimaginebelonging solution

To setup your development machine fully compatible with the latests Reimagine Belonging production websites environments, clone/fork [trellis-reimaginebelonging](https://bitbucket.org/rslnk/trellis-reimaginebelonging). Ask project's administrator to provide you with an access to this repo. Server setup with trellis-reimaginebelonging allows:

* Easy development environments with Vagrant
* Easy server provisioning with Ansible (Ubuntu 14.04, PHP 5.6 or HHVM, MariaDB)
* One-command deploys

## Website development

Reimagine Belonging WordPress theme files are stored in `web/app/themes/theme/`.

```
app/
├── mu-plugins/
│   ├── acf-pro/
│   ├── base-setup/
│   ├── soil/
│   ├── bedrock-autoloader.php
│   ├── disallow-indexing.php
│   └── register-theme-directory.php
├── plugins/
└── themes/
    └── theme/                  - Reimagine Belonging WordPpress theme includes build script, templates and assets
        ├── assets/             - Build assets
        │   ├── icons/           - svg icons
        │   ├── images/          - Images
        │   ├── ng/              - AngularJS apps
        │   ├── scripts/         - JavaScripts
        │   ├── styles/          - CSS styles
        │   └── manifest.json
        ├── dist/               - Distributives
        ├── e2e/                - End to end tests
        ├── lib/                - Site config
        ├── templates/          — WordPress content templates
        ├── gulpfile.js         — Build script
        └── packages.json       — Build dependencies
```

This project uses [Gulp](http://gulpjs.com) as its build system and [Bower](http://bower.io) to manage front-end packages. To get started with the building process make sure your development machine meets the following requirements:

| Prerequisite    | Check version | How to install
| --------------- | ------------- | ------------- |
| Node.js 0.12.x  | `node -v`     | [nodejs.org](http://nodejs.org) |
| gulp >= 3.8.10  | `gulp -v`     | `npm install -g gulp` |
| Bower >= 1.3.12 | `bower -v`    | `npm install -g bower` |

We recommend you update to the latest version of npm: `npm install -g npm@latest`.

**Install Gulp, Bower and build dependencies:**

1. Install [Gulp](http://gulpjs.com) and [Bower](http://bower.io) globally with `npm install -g gulp bower`
2. Navigate to the theme directory `web/app/themes/theme/`, then run `npm install`
3. Run `bower install`

You now have all the necessary dependencies to run the build process. To start working on the development or run building process use:

### Available Gulp commands

* `gulp` — Compile and optimize the files in your assets directory
* `gulp watch` — Compile assets when file changes are made
* `gulp --production` — Compile assets for production (no source maps).
* `gulp --tasks` — Lists all the available tasks and what they do

### Using BrowserSync

To use BrowserSync during `gulp watch` your local development hostname should be set to `http://reimaginebelonging.dev` to reflect `devUrl` set at the bottom of `assets/manifest.json`.

## Managing WordPress dependencies with Composer

[Composer](http://getcomposer.org) is used to manage any 3rd party library like WordPress plugins as a dependency including WordPress itself.

All plugins required for Reimagine Belonging to run delcared in `/composer.json` file. Any new plugin must be added under the `require` directive. Namespace  for WordPress plugins from WordPress Packagist is always `wpackagist-plugin`.

Whenever a new plugin is being added or the WordPress version updated, a `composer update` command should be run in order to install new packages.

`plugins`, and `mu-plugins` are Git ignored by default since Composer manages them. Custom plugins that *aren't* managed by Composer, are whitelisted in `/.gitignore` file.

## Must-use plugins

The following must-use WordPress plugins are required in order for Reimagine Belonging website to work:

* Base Setup >= 1.0
* [Soil](https://roots.io/plugins/soil) >= 3.3.0
* [ACF PRO](http://www.advancedcustomfields.com/pro) >= 5.2.5

### Base Setup plugin

Site specific mu-plugin that automatically sets the following WordPress defaults:

* Sets upload path to /media;
* Sets upload path URL to example.com/media
* Disables year/month folder structure for uploads
* Sets permalink structure to post name
* Creates data api endpoint URL
* Sets save/load path to ACF PRO JSON custom fields

### Bedrock Autoloader plugin

An autoloader that enables standard plugins to be required just like must-use plugins. The autoloaded plugins are included after all mu-plugins and standard plugins have been loaded.

### Disallow Indexing plugin

Disallow indexing on non-production environments.

### Soil plugin

[Soil](https://roots.io/plugins/soil) is a WordPress plugin which contains a collection of modules to apply theme-agnostic front-end modifications.

* Loads jQuery from Google’s CDN with a local fallback (as recommended by HTML5 Boilerplate)
* Cleaner output of WordPress `wp_head` and enqueued assets
* Cleaner output of walker for WordPress navigation menus
* Replaces WordPress absolute URLs with root relative URLs
* Redirects search results from /?s=query to /search/query/
* Support for HTML5 Boilerplate’s Google Analytics snippet
* Forces all WordPress enqueued JavaScript to the footer
* Globally disables trackbacks and pingbacks
* Removes the version query string from all styles and scripts.

### ACF PRO plugin

[ACF PRO](http://www.advancedcustomfields.com/pro) plugin for building edit screens and custom field data management.

### HTML5 Boilerplate's .htaccess plugin

[wp-h5bp-htaccess plugin](https://github.com/roots/wp-h5bp-htaccess) provides HTML5 Boilerplate's .htaccess for WordPress.

## Contributing

Contributions are welcome from everyone. We have **contributing guidelines** (URL) to help you get started.
