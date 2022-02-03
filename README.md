# Gravity Forms Encryption
---

Encrypt fields of Gravity Forms entries. It uses LibSodium as it's encryption library.

## Overview
---

[TOC]

## Install
---

### For development

To contribute to this project, you will need to download composer: [Composer](https://getcomposer.org/).

Clone the repository to your desired folder and install the dependencies through composer. 

``` bash
$ composer install
```

### For production

Run the `package.sh` script to create an optimized build. This also requires Composer.

## Usage
---

The plugin generates a `.GRAVITY_FORMS_KEY` file outside of the public root. This file contains the {en,de}cryption key and is generated on plugin activiation if the file is not found. 

The plugin does not encrypt old values. Only new entries will be encrypted.

If you lose the encryption key there is no way to get your data back. Use this plugin at your own risk.

## Changelog
---

Please see [readme.txt](readme.txt) for more information on what has changed recently.

## Testing
---

Please make sure you have installed the dev dependencies and run:

``` bash
$ composer test
$ composer phplint
$ composer phpcompatibility
```

This project also uses Psalm for code analysis:
``` bash
$ composer psalm
```

## Security
---

If you discover any security related issues, please email sander@dekroon.xyz instead of using the issue tracker.
