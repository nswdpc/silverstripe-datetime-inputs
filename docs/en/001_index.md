# Documentation

## Field ordering and example hint

The default field ordering is year-month-day(-time) ordering to allow unambiguous data entry.

You can change the field ordering by passing a value as an argument to the field constructor or by calling `setFieldOrder` with a supported order and format example:

```php
$field = DateCompositeField::create(
    'EventDate',
    _t(
        'app.EVENT_DATE',
        'Date of the event'
    ),
    $record->EventDate,
    DateCompositeField::ORDER_DMY,
    "<your day month year format example>"
);
```

```php
$field->setFieldOrder(
    DateCompositeField::ORDER_DMY,
    "<your day month year format example>"
);
```

Supported field orders are ymd, dmy and mdy. An exception will be thrown if an unexpected field order is used.

Note that the input value must always be a date value in the format Y-m-d to avoid ambiguity. Setting the field ordering is visual/HTML source order only.

## Date submission

Data is submitted in array notation format. On the PHP side, the keys are year, month, date (and time if that field is used).

Once the date is validated, the field returns a data value in `Y-m-d` or `Y-m-d H:i` format.

## Validation

The child input fields provide a basic validation using input attributes. The year must be a number, the month a value between 1 and 12, the day a value between 1 and 31.

This can allow someone to add invalid dates such as 2030-11-31. The submitted date is validated during validate() using `\DateTime::getLastErrors()`. This avoids invalid dates such as Nov 31 being stored as Dec 1.

It's entirely possible that the date value provided from your data is sufficiently invalid to remain un-parseable, resulting in unexpected results.

Your application can perform further validation on the data values returned prior to writing them.

## Required fields

When the DateCompositeField is set as a required field in a validator, all child inputs are considered to be required fields.

## Field warning

You can provide some information about invalid date value(s) by using the `setFieldWarning` method.

## Templating

The templates provided are basic and you can replace these in your own theme using the standard Silverstripe naming process.
