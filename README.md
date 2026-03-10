# [APO Box Account](http://github.com/loadsys/apobox-account)

[![Build Status](https://magnum.travis-ci.com/loadsys/apobox-account.svg?token=TR2m3eb3eWoS3zc7Lcc7&branch=staging)](https://magnum.travis-ci.com/loadsys/apobox-account)

This app allows users to create an APO Box account and manage that account (including orders, custom requests, addresses, and payment details). It allows for two levels of admin users (managers and employees) to perform all administrative tasks required to manage users and orders.

* Production URL: https://account.apobox.com
* Staging URL: N/A
* Project Management URL: https://app.asana.com/0/1201061507514168/1201061507650415
* Documentation: [/docs folder](/docs/) and [Asana](https://app.asana.com/0/1201061507514168/1201061507650415)

## Environment

### Hosting ###

This section documents the minimum required tools for hosting this application.

* [CakePHP](https://github.com/cakephp/cakephp/tree/2.9.5) v2.9.5+
* PHP v5.6
	* intl
	* pdo + mysql
	* mbstring
	* openssl
	* memcached
* Nginx v1.10
* MySQL v5.5
* Memcached (production)

(These tools are all provided in the bundled vagrant environment, described below.)


### Developer-specific ###

The following tools **should be installed on your development machine** in order to work with this project:

* PHP v5.5+ (Mac system default should work fine.)
* [composer](http://getcomposer.org/) for dependency management.
* [git](https://git-scm.com/)
* [vagrant](http://www.vagrantup.com/downloads.html) v1.6+ for dev VM hosting, along with:
	* [VirtualBox](https://www.virtualbox.org/) v4.3+ (free)
	* or [VMware Fusion](http://www.vmware.com/products/fusion) v6+ plus the [vagrant VMware plugin](https://www.vagrantup.com/vmware) (not free, but **fast**)
* [node.js](http://nodejs.org/download/) + [npm](https://npmjs.org/) + [grunt-cli](http://gruntjs.com/getting-started) for automatically running tests.

Vagrant + VirtualBox (or VMware) provide the following additional tools. There are no "optional" installs. Developers must be able to run tests, generate phpDocs and run `phpcs` locally before committing. Thankfully, the vagrant VM provides most of the necessary tools, including:

* PHP's [xdebug extension](http://xdebug.org/) v2+
* [phpunit](http://phpunit.de/) v3.7
* [nodejs](http://nodejs.org/) + [npm](https://www.npmjs.org/) (for auto-running tests)
* [phpDocumentor](http://phpdoc.org/) v2
* [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) v2


### Included Libraries and Submodules

Libraries should be included with Composer whenever possible. Git submodules should be used as a fallback, and directly bundling the code into the project repo as a last resort. See `composer.json` for libraries included in this application.

JSPM Provides package management for Javascript files. A list of current JS dependencies can be found under `jspm.dependencies` of `package.json`.

### cron Tasks

Application specific cron tasks will be installed during production provisioning. Any new entries should be added to `production.sh` in the `provision` directory of this project. The `production.sh` provisioning script will not overwrite or duplicate existing cron tasks.


## Installation

### Development (vagrant)

Developers are expected to use the vagrant environment for all local work.

```bash
git clone git@github.com:loadsys/apobox-account.git ./
cd apobox-account
./bootstrap.sh vagrant
```

This will bring up a new vagrant VM. Due to the way the site interacts with the API (they share session, you'll need to add the following entry to your `/etc/hosts` file. After that the site will be available at [https://apobox.dev](https://apobox.dev).

    192.168.15.43 apobox.dev

The database schema and test data are imported as part of the provisioning. See [/provision/mysql_import.sh](/provision/mysql_import.sh) for specifics. You'll have a customer and an admin, both with the email `test@loadsys.com` and password `password`.

#### Build Dependencies

Both Node.js and npm are required to be installed locally on your system. On Mac OS X the preferred method is via [Homebrew](http://brew.sh/):

```
brew install node
```

JavaScript, CSS, email templates, the Chrome scale app packaging, and the sign up widgets are built and managed by Grunt. The `Gruntfile.js` file configures the build tasks, and requires Grunt to be installed globally on your system:

```
npm install -g grunt-cli
```

To install all `npm` dependencies required in `Gruntfile.js`, from the project root run:

```
npm install
```

Note that the `bin/deps-install` script will also run the `npm install` command.

JavaScript is managed by [jspm](http://jspm.io/), which should have been installed by the `npm install` command. To install all local jspm dependencies, from the project root run:

```
jspm install
```

Sass compilation and linting, along with email template generation require some Ruby gem executables that are called from their respective Grunt tasks. The Ruby gem requirements and versions are managed by [bundler](https://bundler.io/), which is the only global gem requirement. To install:

```
gem install bundler
```

Once bundler is installed, to install the required gems from the project root run:

```
bundle install
```

#### Email

Email templates are automatically compiled by a Grunt `watch` task or by running `grunt email`.

Viewing generated email is handled by [MailHog](https://github.com/mailhog/MailHog) and is available locally at [http://localhost:8025](http://localhost:8025) or [http://apobox.dev:8025](http://apobox.dev:8025).


### Production (bare metal)

1. Create or provision a new database server.
	* Assign a user permissions to that database.
	* (Locally) Update the `Config/core.php` with the new credentials and commit/push them to GitHub.
1. `cd` into the webroot.
1. Clone the project:
	`git clone https://github.com/loadsys/apobox-account.git ./`
	`./bootstrap.sh production`
1. Set other production-specific configs in `Config/core-local.php`.

See [/docs/production.md](/docs/production.md) for more details about production access and configuration.

### Including the App Via an Iframe

Including this application inside of another application is easy using an iframe. An example iframe on a otherwise blank page should look something like this

```
<iframe
	name="apobox-account-app"
	src="http://__PATH_TO_PRODUCTION_URL__/account"
	frameborder="0"
	scrolling="auto"
	width="100%"
	height="100%"
	marginwidth="0"
	marginheight="0"
></iframe>
```

This will allow the iframe to take up the full width and height of the parent. If the iframe, for example, does not take up the full height of the page, the parent element's styles will need to be adjusted so that the iframe's parent's height is the full height of the window.

### Writeable Directories

Writeable directories are set by the provisioning scripts.


## Contributing

<!--
_Information a developer would need to work on the project in the "correct" way. (Tests, etc.)_
-->

### After Pulling

Things to do after pulling updates from the remote repo.

On your host:

* `bin/deps-install` (Install any changes/updated dependencies from git submodules, composer, pear, npm, etc.)
* `jspm install` (Updates JS dependencies.)
* `bin/clear-cache` (Make sure temp files are reset between host/vm use.)

If VM configuration has changed:

* `vagrant provision` (Make any changes to the VM's config that may be necessary.)


### Configuration

App configuration is stored in `Config/core.php`. This configuration is then added to (or overwritten by) anything defined in the environment-specific config file, such as `Config/core-vagrant.php` or `Config/core-staging.php`.

Database configurations for all environments are stored in their respective configuration files and switched using an environment variable.

The bundled vagrant VM automatically sets `APP_ENV=vagrant` both on the command line (via `vagrant ssh` and in the nginx context.) If you want to work with the project on your machine locally, you need to `export APP_ENV=dev` (or whatever environment you want to match for `core-*.php` and in `database.php`) before running `bin/cake`.

### CSS Changes

CSS is built from scss source files in `webroot/sass`. To compile, run either `grunt` or `grunt css`. The `grunt watch` task will monitor `webroot/sass` for any changes and will compile as needed.

### Database Changes

All SQL changes should be recorded in `Config/Schema/db_updates.sql`. The contents of this file are applied manually in production and automatically in development as part of the initial provisioning. Developers with existing installs should watch this file and import changes manually.

Because the MySQL DB runs inside of the vagrant VM, you must connect to it via SSH. The easiest way to do this is using [Sequel Pro](http://sequelpro.com/).

Create a new "SSH" connection with the following settings:

* Name: apobox-vagrant (or whatever you want)
* MySQL Host: 127.0.0.1 (This is the MySQL server's address after you've SSHed into the vagrant box.)
* Username: root
* Password: password (as defined in `provision/mysql_server.sh`.)
* Database: vagrant
* Port: 3306 (default)
* SSH Host: 127.0.0.1
* SSH User: vagrant
* SSH Password: vagrant
* SSH Port: 2222

This setup is handy for backing up your data if you're about to destroy the box, or for making Schema or Seed changes before running the Shell commands in the VM.

## Testing

Unit tests should be created for all new code written in the following categories:

* Model methods
* Behaviors
* Controller actions
* AppController methods
* Components
* Helper methods
* Shells and Tasks
* Libraries in `Lib/'
* Javascript in `webroot/js/`
* **Bundled** plugins

Testing can be done through the browser by visiting https://apobox.dev/test.php but is **not** the recommended method to run tests. The browser test runner has been removed in CakePHP 3.0.

Command line automated test running is the preferred method to run tests and can be run from within the vm (`vagrant ssh`) by running `bin/run-tests` from `/var/www`. Coverage can be viewed by running `open tmp/coverage/html/index.html` from the host machine.

To run all tests without generating code coverage (much faster), from within the vm run `bin/cake test app All`.

To run the PHP codesniffer, from within the vm run `bin/run-codesniffer`.


### Javascript Tests

* Tests can also be written for the browser JavaScript code.
* Javascript should be written in individual "class" files (they will be merged by asset compilation) in `webroot/js/src/`.
* Anything you would normally put in a `document.ready(...)` call should be placed in @TODO.
* Matching test files should be created in `webroot/js/test/`.
* Everything from these folders will be compressed into `webroot/js/main.js`.
* These compiled assets and tests are then included in `View/Pages/test.ctp`.
* You can run your tests in the browser by visiting http://localhost:8080/pages/testjs.
* There is a `grunt` task to auto-run these tests on change as well: `grunt test`


### All grunt Commands

* `app` - Builds the Chrome app
* `dev` - Runs the `server` task and `watch` task
* `css` - Lints and compiles all scss source file
* `email` - Builds email templates
* `js` - Runs the `jspm` task to compile all Javascript
* `widget` - Builds the widget

## Signup Widget

The Signup widget can be used on any site following the instructions in the [Widget Documentation](/widgets/README.md). After making changes to the widget code or related configurations, the widget source code needs rebuilt with the `grunt widget` command.

## Chrome App

A chrome app exists for scale reading and ZPL printing. The app is published (to testers only) by rick@loadsys.com and can be managed on his [Webstore Developer Dashboard](https://chrome.google.com/webstore/developer/dashboard).

Build a new zip with `grunt app` and then upload `/chrome_app/scale_app.zip` on the dashboard.

## License

Copyright (c) 2016 APO Box
