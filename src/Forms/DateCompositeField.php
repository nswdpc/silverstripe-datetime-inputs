<?php

namespace NSWDPC\DateInputs;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\Fieldlist;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\View\Requirements;

/**
 * A composite field made up of 3 text inputs: day, month and year
 * Default field ordering is year-month-day
 * @author James
 */
class DateCompositeField extends CompositeField {

    /**
     * @var string
     */
    const ORDER_DMY = 'dmy';

    /**
     * @var string
     */
    const ORDER_YMD = 'ymd';

    /**
     * @var string
     */
    const ORDER_MDY = 'mdy';

    /**
     * @var string
     */
    protected $fieldOrder = self::ORDER_YMD;

    /**
     * @var NumberField
     */
    protected $dayField;

    /**
     * @var NumberField
     */
    protected $monthField;

    /**
     * @var NumberField
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
    protected $fieldWarning = '';

    /**
     * @var bool
     * Set to true when setSubmittedValue is called
     */
    private $isSubmittingValue = false;

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
            $this->setTitle( FormField::name_to_label($name) );
        } else {
            $this->setTitle( $title );
        }

        $this->setName($name);

    }

    /**
     * @return string
     */
    protected static function getParserPattern() : string {
        $pattern = "(?<year>\d*)\-(?<month>\d{1,2})\-(?<day>\d{1,2})";
        return $pattern;
    }

    /**
     * Parse a date time value into parts via named capture groups
     * @param string input value that may or may not be a valid date
     */
    public static function parseDateTime(string $inputValue) : array {
        $pattern = "/" . static::getParserPattern() . "/";
        $result = preg_match($pattern, $inputValue, $matches);
        $data = [];
        foreach(['year','month','day','time'] as $key) {
            $data[$key] = (isset($matches[$key]) ? $matches[$key] : '');
        }
        return $data;
    }

    /**
     * @inheritdoc
     * @param array $value
     */
    public function setSubmittedValue($value, $data = null)
    {
        $this->isSubmittingValue = true;
        parent::setSubmittedValue($value, $data);
    }

    /**
     * @inheritdoc
     * When Form::loadDataFrom() is called, the value is set, child fields need to be set
     * when this occurs
     */
    public function setValue($value, $data = null)
    {
        $this->dateValue = [
            'year' => '',
            'month' => '',
            'day' => '',
            'time' => '',
            'strValue' => ''
        ];

        if(is_array($value)) {
            // submitted value
            $this->isSubmittingValue = true;
            $this->dateValue = array_merge($this->dateValue, $value);
            $this->dateValue['strValue'] = $this->dateValue['year'] . "-" . $this->dateValue['month'] . "-" . $this->dateValue['day'];
            if(!empty($this->dateValue['time'])) {
                $this->dateValue['strValue'] .= " " . $this->dateValue['time'];
            }
        } else if(is_string($value)) {
            $this->dateValue['strValue'] = $value;

            try {
                // string value loaded from data or in field creation
                $parts = self::parseDateTime($this->dateValue['strValue']);
                $this->dateValue['year'] = $parts['year'];
                $this->dateValue['month'] = $parts['month'];
                $this->dateValue['day'] = $parts['day'];
                $this->dateValue['time'] = $parts['time'];
            } catch (\Exception $e) {
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
    public function saveInto(DataObjectInterface $record)
    {
        $dataValue = $this->dataValue();
        //var_dump($this->getName());var_dump($dataValue);exit;
        return parent::saveInto($record);
    }

    /**
     * Set the field order. The child fields are automatically reordered when called
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setFieldOrder(string $order, string $formatExampleValue) : self {
        switch($order) {
            case self::ORDER_DMY:
            case self::ORDER_YMD:
            case self::ORDER_MDY:
                if($this->children && ($order == $this->fieldOrder)) {
                    // nothing to change if the fields exist
                    return $this;
                }
                $this->fieldOrder = $order;
                $this->formatExampleValue = $formatExampleValue;
                // update child fields
                $this->buildDateTimeFields();
                return $this;
                break;
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
    public function getFieldOrder() : string {
        return $this->fieldOrder;
    }

    /**
     * Set the field format example helper text
     */
    public function setFormatExample(string $example) : self {
        $this->formatExample = $example;
        return $this;
    }

    /**
     * Get the field format example helper text
     */
    public function getFormatExample() : string {
        return $this->formatExample;
    }

    /**
     * Set a field warning message, eg for invalid date to allow for correction
     */
    public function setFieldWarning(string $warningMessage) : self {
        $this->fieldWarningMessage = $warningMessage;
        return $this;
    }

    /**
     * Set a field warning message
     */
    public function getFieldWarning() : ?string {
        return $this->fieldWarningMessage;
    }

    /**
     * Set minimum allowed year value
     * @param int|null $minYear
     * @param int|null $maxYear
     */
    public function setMinMaxYear(int $minYear = null, int $maxYear = null) : self {
        if($this->yearField) {
            $this->yearField->setAttribute('min', $minYear)->setAttribute('max', $maxYear);
        }
        return $this;
    }

    /**
     * Get minimum allowed year value
     */
    public function getMinYear() : ?int {
        $val = null;
        if($this->yearField) {
            $val = $this->yearField->getAttribute('min');
            if(!is_null($val)) {
                $val = intval($val);
            }
        }
        return $val;
    }

    /**
     * Get maximum allowed year value
     */
    public function getMaxYear() : ?int {
        $val = null;
        if($this->yearField) {
            $val = $this->yearField->getAttribute('max');
            if(!is_null($val)) {
                $val = intval($val);
            }
        }
        return $val;
    }

    /**
     * Return whether child fields exist
     */
    public function hasFields() : bool {
        return $this->dayField && $this->monthField && $this->yearField;
    }

    /**
     * @inheritdoc
     * Note: removes hasData check
     */
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
    public function dataValue() {
        $year = $this->yearField->dataValue();
        $month = $this->monthField->dataValue();
        $day = $this->dayField->dataValue();
        if($year && $month && $day) {
            $this->value = "{$year}-{$month}-{$day}";
            return $this->value;
        } else {
            return "";
        }
    }

    /**
     * Return a prefixed field name
     */
    public function getPrefixedFieldName(string $suffix) : string {
        $fieldName = $this->getName() . "[{$suffix}]";
        return $fieldName;
    }

    /**
     * Build the fields and set order based on
     * @throws \RuntimeException
     * @inheritdoc
     */
    protected function buildDateTimeFields() : Fieldlist {

        if(!$this->hasFields()) {
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

        switch($this->fieldOrder) {
            // non US locale
            case self::ORDER_DMY:
                $this->children = Fieldlist::create(
                    $this->dayField, $this->monthField, $this->yearField
                );
                break;
            // US locale
            case self::ORDER_MDY:
                $this->children = Fieldlist::create(
                    $this->monthField, $this->dayField, $this->yearField
                );
                break;
            // iso - default
            case self::ORDER_YMD:
            default:
                $this->children = Fieldlist::create(
                    $this->yearField, $this->monthField, $this->dayField
                );
                break;
        }

        if($this->formatExampleValue) {
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
    public function getFieldHolderTemplate()
    {
        $controller = Controller::curr();
        if($controller instanceof LeftAndMain) {
            return "NSWDPC/DateInputs/Admin/DateCompositeField_holder";
        } else {
            return "NSWDPC/DateInputs/DateCompositeField_holder";
        }
    }

    /**
     * @inheritdoc
     */
    public function FieldHolder($properties = [])
    {
        Requirements::css(
            'nswdpc/silverstripe-datetime-inputs:client/static/css/field.css',
            'screen'
        );
        $controller = Controller::curr();
        if($controller instanceof LeftAndMain) {
            Requirements::css(
                'nswdpc/silverstripe-datetime-inputs:client/static/css/admin.css',
                'screen'
            );
        }
        return parent::FieldHolder($properties);
    }

    /**
     * Check DateTime for any invalid datetime errors eg 31st Nov was provided
     * @return bool
     * @throws DateValidationException
     */
    protected function checkProvidedDateTime(string $value) : bool {
        $check = new \DateTime($value);
        $lastErrors = $check->getLastErrors();
        if(!empty($lastErrors)) {
            foreach(['warning_count','error_count'] as $key) {
                if(isset($lastErrors[$key]) && $lastErrors[$key] > 0) {
                    throw new DateValidationException(
                        self::getDateValidationErrorMessage( $value )
                    );
                }
            }
        }
        return true;
    }

    /**
     * Validate this field
     *
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {
        $valid = parent::validate($validator);
        if(!$valid) {
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
        } catch (\Exception $e) {
            $valid = false;
            $validator->validationError(
                $this->name,
                self::getDateValidationErrorMessage( $this->dateValue['strValue'] )
            );
        }

        if(!$valid) {
            $validator->validationError(
                '',// no field
                _t(
                    'DateCompositeField.FIELD_HAS_ERRORS',
                    'The field \'{fieldTitle}\' contains errors',
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
    public static function getDateValidationErrorMessage($dateValue) : string {
        return _t(
            'DateCompositeField.INVALID_DATE_PROVIDED',
            'The date \'{providedDate}\' is not a valid date. Please check the year, month and day values.',
            [
                'providedDate' => $dateValue
            ]
        );
    }

    /**
     * The readonly version of this field
     */
    public function performReadonlyTransformation()
    {
        $field = ReadonlyField::create(
            $this->name,
            $this->title,
            $this->dataValue()
        );
        $field->setDescription( $this->getDescription() );
        $field->setRightTitle( $this->RightTitle() );
        return $field;
    }
}
