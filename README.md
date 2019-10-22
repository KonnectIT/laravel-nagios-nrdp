# Send NRDP statuses to your Nagios Monitoring Server

This package is sending messages to your Nagios Server via NRDP. This means that
you also have to install the NRDP server service to your Nagios Monitoring Server.
The package for the server service can be found here:

https://github.com/NagiosEnterprises/nrdp

## Installation

You can install the package via composer:

```bash
composer require konnectit/laravel-nagios-nrdp
```

## Usage

### Host check
```php
use KonnectIT\LaravelNagiosNrdp\HostStates;

\NagiosNrdp::state(HostStates::HOST_OK)->send('OK');
```

### Service check
```php
use KonnectIT\LaravelNagiosNrdp\ServiceStates;

\NagiosNrdp::service('PHP extensions')->state(ServiceStates::SERVICE_OK)->send('All services OK');
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email e.heij@konnectit.nl instead of using the issue tracker.

## Credits

- [Edwin Heij](https://github.com/bahjaat)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
