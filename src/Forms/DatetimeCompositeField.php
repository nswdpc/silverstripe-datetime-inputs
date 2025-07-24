<?php

namespace NSWDPC\DateInputs;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * A composite field for date and time input
 * @author James
 */
class DatetimeCompositeField extends DateCompositeField
{
    /**
     * @var \NSWDPC\DateInputs\TimeField
     */
    protected $timeField;

    #[\Override]
    protected static function getParserPattern(): string
    {
        $pattern = parent::getParserPattern();
        $pattern .= " ";
        return $pattern . "(?<time>\d{1,2}\:\d{1,2})";
    }

    /**
     * Push time input into composite
     * @inheritdoc
     */
    #[\Override]
    public function buildDateTimeFields(): FieldList
    {

        $this->children = parent::buildDateTimeFields();

        if (!$this->timeField) {
            $this->timeField = TimeField::create(
                $this->getPrefixedFieldName('time'),
                _t('DatetimeCompositeField.TIME_TITLE', 'Time'),
                $this->dateValue['time']
            )->setDescription(
                _t('DatetimeCompositeField.TIME_EXAMPLE', '3pm = 15:00')
            );
        } else {
            // when a value is available, ensure child field values are set
            $this->timeField->setValue($this->dateValue['time']);
        }

        // ensure time field is added to the child fields
        $this->children->push($this->timeField);
        $this->children->setContainerField($this);
        return $this->children;
    }

    /**
     * Return the value as a YMD formatted date
     */
    #[\Override]
    public function dataValue(): string
    {
        $value = parent::dataValue();
        $timeValue = $this->timeField->dataValue();
        $value = $value . " " . $timeValue;
        return trim($value);
    }

    /**
     * Return formatted representation of the current field value
     */
    #[\Override]
    public function getFormattedValue(): ?string
    {
        $value = $this->Value();
        if ($value) {
            $dbField = DBField::create_field(DBDatetime::class, $value);
            $value = $dbField->FormatFromSettings();
        }

        return $value;
    }

    /**
     * Date and time validation message
     */
    #[\Override]
    public static function getDateValidationErrorMessage($dateValue): string
    {
        return _t(
            'DateCompositeField.INVALID_DATE_TIME_PROVIDED',
            "The date and time '{providedDate}' is not valid. Please check the year, month, day and time values.",
            [
                'providedDate' => $dateValue
            ]
        );
    }

    /**
     * Hide placeholders
     */
    #[\Override]
    public function hidePlaceholders(): self
    {
        if ($this->hasFields()) {
            parent::hidePlaceholders();
            $this->timeField->setAttribute('placeholder', null);
        }

        return $this;
    }
}
