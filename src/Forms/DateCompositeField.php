<?php

namespace NSWDPC\DateInputs;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\View\Requirements;

/**
 * A composite field made up of 3 text inputs: day, month and year
 * Default field ordering is year-month-day
 * @author James
 */
class DateCompositeField extends CompositeField
{
    /**
     * @var string
     */
    public const ORDER_DMY = 'dmy';

    /**
     * @var string
     */
    public const ORDER_YMD = 'ymd';

    /**
     * @var string
     */
    public const ORDER_MDY = 'mdy';

    /**
     * @var string
     */
    protected $fieldOrder = self::ORDER_YMD;

    /**
     * @var \Codem\Utilities\HTML5\NumberField
     */
    protected $dayField;

    /**
     * @var \Codem\Utilities\HTML5\NumberField
     */
    protected $monthField;

    /**
     * @var \Codem\Utilities\HTML5\NumberField
     */
    protected $yearField;

    /**
     * @var string custom HTML tag to render with, e.g. to produce a <fieldset>.
     */
    protected $tag = 'fieldset';

    /**
     * @var string
     */
    protected $formatExample = '';

    /**
     * @var string
     */
    protected $formatExampleValue = '';

    /**
     * @var string
     */
    protected $fieldWarningMessage = '';

    /**
     * @var array
     * Store the submitted value, which may not be the derived data value
     * This allows validation on the submitted value. Example Nov 31st
     */
    protected $dateValue = [];

    /**
     * Override constructor to remove custom child fields
     * Set name at time of construction
     * @param string $name
     * @param string|null $title
     * @param string|null|array $value
     * @param string $fieldOrder
     */
    public function __construct($name, $title = null, $value = null, $fieldOrder = self::ORDER_YMD, string $formatExampleValue = '')
    {

        // field initially needs a name
        $this->setName($name);

        // store value
        $this->setValue($value);

        // ensure a field order is set by default
        $this->setFieldOrder($fieldOrder, $formatExampleValue);

        parent::__construct($this->children);

        // Ensure title and name are stored correctly
        if (is_null($title)) {
            $this->setTitle(FormField::name_to_label($name));
        } else {
            $this->setTitle($title);
        }

        $this->setName($name);

    }

    /**
     * Helper method to get the key's value as a string from the given array
     * is the key is not set the value returned is an empty string
     */
    public static function getStringValueFromArray(array $dateValue, string $key): string
    {
        return isset($dateValue[$key]) ? trim(strval($dateValue[$key])) : '';
    }

    /**
     * Helper method to format the $dateValue provided and return it as the $format requested
     * $format should be a format understood by \DateTime
     * The $dateValue must have a year, month and day value, with those keys. Optional time value supported.
     * @throws \InvalidArgumentException
     */
    public static function formatDateValue(array $dateValue, string $format = "Y-m-d"): string
    {
        $date = [];
        $date[] = static::getStringValueFromArray($dateValue, 'year');
        $date[] = static::getStringValueFromArray($dateValue, 'month');
        $date[] = static::getStringValueFromArray($dateValue, 'day');
        $date = array_filter($date);// remove empties
        if (count($date) != 3) {
            throw new \InvalidArgumentException("Invalid dateValue passed to formatDateValue - requires a year, month and day value as strings");
        }

        $dateStr = implode("-", $date);
        $timeStr = static::getStringValueFromArray($dateValue, 'time');
        if ($timeStr !== '') {
            $dateStr .= " " . $timeStr;
        }

        try {
            $dt = new \DateTime($dateStr);
            return $dt->format($format);
        } catch (\Exception) {
            // invalid format or date string
        }

        throw new \InvalidArgumentException("Invalid dateValue or format ({$format}) passed to formatDateValue");
    }

    protected static function getParserPattern(): string
    {
        return "(?<year>\d*)\-(?<month>\d{1,2})\-(?<day>\d{1,2})";
    }

    /**
     * Parse a date time value into parts via named capture groups
     * @param string $inputValue input value that may or may not be a valid date
     */
    public static function parseDateTime(string $inputValue): array
    {
        $pattern = "/" . static::getParserPattern() . "/";
        preg_match($pattern, $inputValue, $matches);
        $data = [];
        foreach (['year','month','day','time'] as $key) {
            $data[$key] = ($matches[$key] ?? '');
        }

        return $data;
    }

    /**
     * @inheritdoc
     * @param array $value
     */
    #[\Override]
    public function setSubmittedValue($value, $data = null)
    {
        return parent::setSubmittedValue($value, $data);
    }

    /**
     * @inheritdoc
     * When Form::loadDataFrom() is called, the value is set, child fields need to be set
     * when this occurs
     */
    #[\Override]
    public function setValue($value, $data = null)
    {
        $this->dateValue = [
            'year' => '',
            'month' => '',
            'day' => '',
            'time' => '',
            'strValue' => ''
        ];

        if (is_array($value)) {
            // check if value contains data
            $value = array_filter(
                $value,
                function ($v, $k): bool {
                    $v = is_string($v) ? trim($v) : '';
                    return $v !== '';
                },
                ARRAY_FILTER_USE_BOTH
            );
            if ($value !== []) {
                $this->dateValue = array_merge($this->dateValue, $value);
                $this->dateValue['strValue'] = $this->dateValue['year'] . "-" . $this->dateValue['month'] . "-" . $this->dateValue['day'];
                if (!empty($this->dateValue['time'])) {
                    $this->dateValue['strValue'] .= " " . $this->dateValue['time'];
                }
            }
        } elseif (is_string($value)) {
            $this->dateValue['strValue'] = $value;
            try {
                // string value loaded from data or in field creation
                $parts = self::parseDateTime($this->dateValue['strValue']);
                $this->dateValue['year'] = $parts['year'];
                $this->dateValue['month'] = $parts['month'];
                $this->dateValue['day'] = $parts['day'];
                $this->dateValue['time'] = $parts['time'];
            } catch (\Exception) {
                // invalid date, empty parts
            }
        }

        parent::setValue($value, $data);

        // update child fields
        $this->buildDateTimeFields();

        return $this;
    }

    /**
     * Save the data value into the record
     * @inheritdoc
     */
    #[\Override]
    public function saveInto(DataObjectInterface $record)
    {
        return parent::saveInto($record);
    }

    /**
     * Set the field order. The child fields are automatically reordered when called
     * @throws \InvalidArgumentException
     */
    public function setFieldOrder(string $order, string $formatExampleValue): self
    {
        switch ($order) {
            case self::ORDER_DMY:
            case self::ORDER_YMD:
            case self::ORDER_MDY:
                if ($this->children && ($order == $this->fieldOrder)) {
                    // nothing to change if the fields exist
                    return $this;
                }

                $this->fieldOrder = $order;
                $this->formatExampleValue = $formatExampleValue;
                // update child fields
                $this->buildDateTimeFields();
                return $this;
            default:
                throw new \InvalidArgumentException(
                    _t(
                        'DateCompositeField.INVALID_FIELD_ORDER',
                        'Invalid field order: {order}',
                        [
                            'order' => $order
                        ]
                    )
                );
        }
    }

    /**
     * Get the current field order
     */
    public function getFieldOrder(): string
    {
        return $this->fieldOrder;
    }

    /**
     * Set the field format example helper text
     */
    public function setFormatExample(string $example): self
    {
        $this->formatExample = $example;
        return $this;
    }

    /**
     * Get the field format example helper text
     */
    public function getFormatExample(): string
    {
        return $this->formatExample;
    }

    /**
     * Set a field warning message, eg for invalid date to allow for correction
     */
    public function setFieldWarning(string $warningMessage): self
    {
        $this->fieldWarningMessage = $warningMessage;
        return $this;
    }

    /**
     * Set a field warning message
     */
    public function getFieldWarning(): ?string
    {
        return $this->fieldWarningMessage;
    }

    /**
     * Set minimum allowed year value
     * @param int|null $minYear
     * @param int|null $maxYear
     */
    public function setMinMaxYear(int $minYear = null, int $maxYear = null): self
    {
        if ($this->yearField) {
            $this->yearField->setAttribute('min', $minYear)->setAttribute('max', $maxYear);
        }

        return $this;
    }

    /**
     * Get minimum allowed year value
     */
    public function getMinYear(): ?int
    {
        $val = null;
        if ($this->yearField) {
            $val = $this->yearField->getAttribute('min');
            if (!is_null($val)) {
                $val = intval($val);
            }
        }

        return $val;
    }

    /**
     * Get maximum allowed year value
     */
    public function getMaxYear(): ?int
    {
        $val = null;
        if ($this->yearField) {
            $val = $this->yearField->getAttribute('max');
            if (!is_null($val)) {
                $val = intval($val);
            }
        }

        return $val;
    }

    /**
     * @inheritdoc
     * Set child fields to be disabled as well
     */
    #[\Override]
    public function setDisabled($disabled)
    {
        foreach ($this->children as $child) {
            $child->setDisabled($disabled);
        }

        return parent::setDisabled($disabled);
    }

    /**
     * @inheritdoc
     * Set child fields to be readonly as well
     */
    #[\Override]
    public function setReadonly($readonly)
    {
        foreach ($this->children as $child) {
            $child->setReadonly($readonly);
        }

        return parent::setReadonly($readonly);
    }

    /**
     * Return whether child fields exist
     */
    public function hasFields(): bool
    {
        return $this->dayField && $this->monthField && $this->yearField;
    }

    /**
     * @inheritdoc
     * Note: removes hasData check
     */
    #[\Override]
    public function hasData()
    {
        return true;
    }

    /**
     * Return the value provided as a YMD formatted date string
     * The parts are taken from the child input field data values
     * The value may or may not be a valid date
     * @return string
     */
    #[\Override]
    public function dataValue()
    {
        $year = $this->yearField->dataValue();
        $month = $this->monthField->dataValue();
        $day = $this->dayField->dataValue();
        if ($year && $month && $day) {
            $this->value = "{$year}-{$month}-{$day}";
            return $this->value;
        } else {
            return "";
        }
    }

    /**
     * Return a prefixed field name
     */
    public function getPrefixedFieldName(string $suffix): string
    {
        return $this->getName() . "[{$suffix}]";
    }

    /**
     * Build the fields and set order based on
     * @throws \RuntimeException
     * @inheritdoc
     */
    protected function buildDateTimeFields(): Fieldlist
    {

        if (!$this->hasFields()) {
            // create fields
            $this->dayField = DayOfMonthField::create(
                $this->getPrefixedFieldName('day'),
                _t('DateCompositeField.DAY_TITLE', 'Day'),
                $this->dateValue['day']
            )->setDescription(
                _t('DateCompositeField.DAY_EXAMPLE', 'Range: 1-31')
            );

            $this->monthField = MonthNumberField::create(
                $this->getPrefixedFieldName('month'),
                _t('DateCompositeField.MONTH_TITLE', 'Month'),
                $this->dateValue['month']
            )->setDescription(
                _t('DateCompositeField.MONTH_EXAMPLE', 'Range: 1-12')
            );

            $this->yearField = YearField::create(
                $this->getPrefixedFieldName('year'),
                _t('DateCompositeField.YEAR_TITLE', 'Year'),
                $this->dateValue['year']
            )->setDescription(
                _t('DateCompositeField.YEAR_EXAMPLE', 'The full year')
            );

        } else {
            // when a value is available, ensure child field values are set
            $this->dayField->setValue($this->dateValue['day']);
            $this->monthField->setValue($this->dateValue['month']);
            $this->yearField->setValue($this->dateValue['year']);
        }

        // Logger::log("Set year {$this->dateValue['year']}");
        // Logger::log("Set month {$this->dateValue['month']}");
        // Logger::log("Set day {$this->dateValue['day']}");

        $this->children = match ($this->fieldOrder) {
            self::ORDER_DMY => Fieldlist::create(
                $this->dayField,
                $this->monthField,
                $this->yearField
            ),
            self::ORDER_MDY => Fieldlist::create(
                $this->monthField,
                $this->dayField,
                $this->yearField
            ),
            default => Fieldlist::create(
                $this->yearField,
                $this->monthField,
                $this->dayField
            ),
        };

        if ($this->formatExampleValue) {
            $this->setFormatExample(
                _t(
                    'DateCompositeField.FORMAT_EXAMPLE_HELPER',
                    'For example: {example}',
                    [
                        'example' => $this->formatExampleValue
                    ]
                )
            );
        }

        $this->children->setContainerField($this);
        return $this->children;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getFieldHolderTemplate()
    {
        $controller = Controller::curr();
        if (class_exists(LeftAndMain::class) && $controller instanceof LeftAndMain) {
            return "NSWDPC/DateInputs/Admin/DateCompositeField_holder";
        } else {
            return "NSWDPC/DateInputs/DateCompositeField_holder";
        }
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function FieldHolder($properties = [])
    {
        Requirements::css(
            'nswdpc/silverstripe-datetime-inputs:client/static/css/field.css',
            'screen'
        );
        $controller = Controller::curr();
        if (class_exists(LeftAndMain::class) && $controller instanceof LeftAndMain) {
            Requirements::css(
                'nswdpc/silverstripe-datetime-inputs:client/static/css/admin.css',
                'screen'
            );
        }

        return parent::FieldHolder($properties);
    }

    /**
     * Check DateTime for any invalid datetime errors eg 31st Nov was provided
     * @throws DateValidationException
     */
    protected function checkProvidedDateTime(string $value): bool
    {
        if ($value === '') {
            // empty value provided
            return true;
        }

        $check = new \DateTime($value);
        $lastErrors = $check->getLastErrors();
        if (!empty($lastErrors)) {
            foreach (['warning_count','error_count'] as $key) {
                if (isset($lastErrors[$key]) && $lastErrors[$key] > 0) {
                    throw new DateValidationException(
                        self::getDateValidationErrorMessage($value)
                    );
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function validate($validator)
    {
        $valid = parent::validate($validator);
        if (!$valid) {
            // parent validation failed
            return false;
        }

        try {
            $valid = true;
            // perform full date validation on the value
            $this->checkProvidedDateTime($this->dateValue['strValue']);
        } catch (DateValidationException $e) {
            $valid = false;
            $validator->validationError(
                $this->name,
                $e->getMessage()
            );
            /* @phpstan-ignore catch.neverThrown */
        } catch (\Exception) {
            $valid = false;
            $validator->validationError(
                $this->name,
                self::getDateValidationErrorMessage($this->dateValue['strValue'])
            );
        }

        if (!$valid) {
            $validator->validationError(
                '',// no field
                _t(
                    'DateCompositeField.FIELD_HAS_ERRORS',
                    "The field '{fieldTitle}' contains errors",
                    [
                        'fieldTitle' => $this->Title()
                    ]
                )
            );
        }

        return $valid;
    }

    /**
     * Date validation message
     */
    public static function getDateValidationErrorMessage($dateValue): string
    {
        return _t(
            'DateCompositeField.INVALID_DATE_PROVIDED',
            "The date '{providedDate}' is not a valid date. Please check the year, month and day values.",
            [
                'providedDate' => $dateValue
            ]
        );
    }

    /**
     * Return formatted representation of the current field value
     */
    public function getFormattedValue(): ?string
    {
        $value = $this->Value();
        if ($value) {
            $dbField = DBField::create_field(DBDate::class, $value);
            $value = $dbField->FormatFromSettings();
        }

        return $value;
    }

    /**
     * The readonly version of this field
     */
    #[\Override]
    public function performReadonlyTransformation()
    {
        $value = $this->getFormattedValue();
        $field = ReadonlyField::create(
            $this->name,
            $this->title,
            $value
        );
        $field->setDescription($this->getDescription());
        $field->setRightTitle($this->RightTitle());
        /* @phpstan-ignore return.type */
        return $field;
    }

    /**
     * Compat method to support fields using set/get html5
     */
    public function setHTML5($is): static
    {
        // NOOP
        return $this;
    }

    /**
     * Compat method to support fields using set/get html5
     */
    public function getHTML5(): bool
    {
        return true;
    }

    /**
     * Set whether the field represents a date of birth
     */
    public function setIsDateOfBirth($is): static
    {
        if ($this->hasFields()) {
            if ($is) {
                $this->yearField->setAttribute('autocomplete', 'bday-year');
                $this->monthField->setAttribute('autocomplete', 'bday-month');
                $this->dayField->setAttribute('autocomplete', 'bday-day');
            } else {
                $this->yearField->setAttribute('autocomplete', '');
                $this->monthField->setAttribute('autocomplete', '');
                $this->dayField->setAttribute('autocomplete', '');
            }
        }

        return $this;
    }

    /**
     * Hide placeholders
     */
    public function hidePlaceholders(): self
    {
        if ($this->hasFields()) {
            $this->yearField->setAttribute('placeholder', null);
            $this->monthField->setAttribute('placeholder', null);
            $this->dayField->setAttribute('placeholder', null);
        }

        return $this;
    }
}
