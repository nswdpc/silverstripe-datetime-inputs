# Date and Datetime composite inputs for Silverstripe

Collect a date or datetime input via single composite field made up of relevant day, month, year, hour and minute inputs. The parent field is a standard composite field with child fields submitting data within the form submission for validation.

Child input fields are standard numeric inputs. The time field is a [time input](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/time).

All child fields are accessible via keyboard navigation.

## Installation

```shell
composer require nswdpc/silverstripe-datetime-inputs
```

## Usage

```php

$dateValue = "2028-01-30";

// Date only
$field = DateCompositeField::create(
    'EventDate',
    _t(
        'app.EVENT_DATE',
        'Date of the event'
    ),
    $dateValue
)->setDescription(
    _t(
        'app.EVENT_DATE_TIME_DESCRIPTION',
        'Provide the date of the event'
    )
);

// Date and Time
$datetimeValue = "2028-01-30 11:45";

$field = DatetimeCompositeField::create(
    'EventDateTime',
    _t(
        'app.EVENT_DATE_TIME',
        'Date and time of the event'
    ),
    $datetimeValue
)->setDescription(
    _t(
        'app.EVENT_DATE_TIME_DESCRIPTION',
        'Provide the date and time of the event'
    )
);
```

[Further documentation](./docs/en/001_index.md)

## License

[BSD-3-Clause](./LICENSE.md)

## Maintainers

+ [dpcdigital@NSWDPC:~$](https://dpc.nsw.gov.au)

## Bugtracker

We welcome bug reports, pull requests and feature requests on the Github Issue tracker for this project.

Please review the [code of conduct](./code-of-conduct.md) prior to opening a new issue.

## Security

If you have found a security issue with this module, please email digital[@]dpc.nsw.gov.au in the first instance, detailing your findings.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.

Please review the [code of conduct](./code-of-conduct.md) prior to completing a pull request.
