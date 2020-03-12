# Products tests

This whole repository is dedicated to tests that go beyond a single plugin and require, instead, all plugins to run.

Due to the complex nature of each test the methodology, infrastructure and approach will change.
Depending on the test you're trying to run, or debug, locally refer to the dedicated section.

## Random activation tests

These tests use a fuzzy (pseudo-random) approach to test the issues that might arise during the plugins update paths.  
To ensure no update path, sequence, order or version of update ever throws a fatal error, the tests will run a number of times ("epochs").

On each run the tests will:
1. run on a random version of WordPress (incl. the "nightly" one)
2. install a random number of plugins, each in random version
3. activate the plugins in a random order
4. deactivate the plugins 

Sticking to a stricter TDD definition steps 1 and 2 and the "arrange" phase, steps 3 and 4 are conflated "act" and "assert" phases.  

Depending on when the failure happened, during the activation phase or the deactivation one, the debug approach is different.

### Downloading the plugins

If you've not already downloaded the plugins, this is the first step to any further debug action.  
Skip this step if you've already done it.

Create a local version of the licenses file, run this in the terminal:

```bash
cp env-licenses-exmaple .env.licenses
```

Fill each license field with a valid license taken from your theeventscalendar.com account.

If you did not do it already, download the last versions of all plugins. From the repository root directory run:

```bash
php dev/setup/dl-plugin-zips.php 5 "$(<.github/workflows/activation-test-plugins)" .env.licenses
```

The script will download the last 5 versions of each plugin in the `dev/test/_plugin_store` directory and will use the `.env.licenses` file to authorize the download of the premium plugins.

### Debugging failed activation

When the tests fail during the activation phase you will see an error message detailing the WordPress and "activation path" used in the tests.  

The "activation path" means that installing the plugins with those specific versions and activating them in that specific order, will trigger a fatal error.

As an example, let's say the activation failed w/ the following scenario:
* WordPress version `5.3.2`
* `image-widget` version `4.4.5`
* `the-events-calendar` version `5.0.2.1`
* `events-community-tickets` version `4.7.1`

The test infrastructure comes packed with a custom PHP interactive shell that you can use to run any function the tests use.  
Run `sh dev/php-shell.sh` to start it; any command shown below is actually a command you would run in this shell.

The following command will spin up the `wordpress_debug` container, install a clean version of WordPress in it, and prepare it for the tests:

```php
prepare_wordpress('5.3.2','wordpress_debug');
```

Next, let's install the specific version of each plugin using the PHP interactive shell:

```php
install_plugin('image-wigdet','4.4.5');
install_plugin('the-events-calendar','5.0.2.1');
install_plugin('events-community-tickets','4.7.1');
```

The `dev/test/_plugin_store` directory is set as plugins directory of the `wordpress_debug` container, so the installed plugins will appear, unzipped, in the `dev/test/_plugin_store` directory.  

The unzipped version of each plugin is the one the `wordpress_container` is using: any modification you make to the plugin code, while trying to debug, will be reflected in the container.  

Activate the plugins one by one to reproduce the issue in the PHP interactive shell:

```php
wp_cli(['plugin','activate','image-widget','--debug']);
wp_cli(['plugin','activate','the-events-calendar','--debug']);
wp_cli(['plugin','activate','events-community-tickets','--debug']);
```

When the issue pops up, update the code and test the fix again.

### Debugging failed deactivation

When the tests fail during the deactivation phase you will see an error message detailing the WordPress and "deactivation context" used in the tests.  

The "deactivation context" means that installing the plugins in those specific versions, activating them in that specific order, will trigger a fatal error.

Follow through the steps detailed in the ["Debugging failed activation" section](#debugging-failed-activation) and fire up the interactive shell (using `sh dev/php-shell.sh`).

Now use the `wp_cli` function to reproduce the issue:

```php
wp_cli(['plugin','deactivate','--all']);
```

When the issue pops up, update the code and test the fix again.

### Opening a PR to fix the activation/deactivation issue

When you find the fix for an issue open a PR on the repository where the issue lives and reference the line, in the build, where the issue shows up.  

The link should look something like this: `https://github.com/moderntribe/products-test-automation/runs/500225977#step:7:1232`.
