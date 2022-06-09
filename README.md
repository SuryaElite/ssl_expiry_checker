# SSL Expiry Checking Script
SSL Certificate Expiry date checker and notifying through email before expiry.

## Installation / Requirement / Usages

### Requirement

- Composer - Download and install Composer by following the [official instructions](https://getcomposer.org/download/).
- PHP 5.5.9 or above

### Installation

- Clone the repo & fire composer install

```sh
$ git clone git@github.com:suryaelite/ssl_expiry_checker.git
$ cd ssl_expiry_checker
$ composer install
```

- Copy DomainNamesList.sample.php to DomainNamesList.php and add the domain names, without https://
- Copy Email.sample.php to Email.php

```sh
$ cp src/Config/DomainNamesList.sample.php DomainNamesList.php
$ cp src/Config/Email.sample.php src/Config/Email.php
```

### Usages

Scripts resides in ```src/App/*```

To run the scripts
```sh
$ php src/App/SslExpiryChecker.php
```

## Scripts List
| Script | Purpose |
| ------ | ------ |
| SslExpiryChecker | Check the domain names for the expiry and notify |



## Coding Style

Please follow the [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standard and the [PSR-4](http://www.php-fig.org/psr/psr-4/) autoloading standard.

Thank you!
