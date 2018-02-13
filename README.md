# [Vimeography](http://vimeography.com) [![Build Status](https://secure.travis-ci.org/davekiss/vimeography.png?branch=master)](http://travis-ci.org/davekiss/vimeography) [![Coverage Status](https://coveralls.io/repos/davekiss/vimeography/badge.png)](https://coveralls.io/r/davekiss/vimeography) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/davekiss/vimeography/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/davekiss/vimeography/?branch=master) #

### Welcome to our GitHub Repository

Vimeography was born out of a desire to create a good-looking gallery layout from a collection of Vimeo videos without all of the fuss. This WordPress plugin makes it dead-simple to create a video gallery that just works&trade;

More information can be found at [vimeography.com](http://vimeography.com/).

## Installation

For detailed setup instructions, visit the official [Documentation](http://vimeography.com/help/guide) page.

1. You can clone the GitHub repository: `https://github.com/davekiss/vimeography.git`
2. Or download it directly as a ZIP file: `https://github.com/davekiss/vimeography/archive/master.zip`

This will download the latest developer copy of Vimeography.

Then, be sure to define a constant called `VIMEOGRAPHY_DEV` in your `wp-config.php` file. This will ensure that the code bundle of your Vimeography themes will be loaded from the Webpack dev server rather than the theme's `dist` folder.

`define('VIMEOGRAPHY_DEV', true);`

Lastly, you can install the theme dependencies and start up the webpack dev server like so:

```
  cd wp-content/plugins/vimeography/vimeography-harvestone
  yarn install

  # After a few minutes, the install will complete and you can start the server.
  yarn start
```

## Building Themes for Production

```
  cd wp-content/plugins/vimeography/vimeography-harvestone
  yarn build
```

## Tests

1. Install PHPUnit
2. Install WP tests and database: `sh bin/install-wp-tests.sh wordpress_test root '' localhost latest`
3. `cd vimeography && phpunit`

## Bugs
If you find an issue, let us know [here](https://github.com/davekiss/vimeography/issues?state=open)!

## Support
This is a developer's portal for Vimeography and should _not_ be used for support. Please visit the [contact page](https://vimeography.com/contact).

## Contributions
Anyone is welcome to contribute to Vimeography. Please read the [guidelines for contributing](https://github.com/davekiss/vimeography/blob/master/CONTRIBUTING.md) to this repository.

There are various ways you can contribute:

1. Raise an [Issue](https://github.com/davekiss/vimeography/issues) on GitHub
2. Send us a Pull Request with your bug fixes and/or new features
3. Translate Vimeography into different languages
4. Provide feedback and suggestions on [enhancements](https://github.com/davekiss/vimeography/issues?direction=desc&labels=Enhancement&page=1&sort=created&state=open)

### Supported by BrowserStack
Thanks to [BrowserStack](https://browserstack.com/) for their support of this open-source project.

<img src="https://cdn.rawgit.com/davekiss/vimeography/master/browserstack-logo.svg" width="150">