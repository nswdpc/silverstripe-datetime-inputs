<?php

namespace NSWDPC\DateInputs;

use SilverStripe\Forms\Fieldlist;

/**
 * A composite field for date and time input
 * @author James
 */
class DatetimeCompositeField extends DateCompositeField {

    /**
     * @var TimeField
     */
    protected $timeField;

    /**
     * @return string
     */
    protected static function getParserPattern() : string {
        $pattern = parent::getParserPattern();
        $pattern .= " ";
        $pattern .= "(?<time>\d{1,2}\:\d{1,2})";
        return $pattern;
    }

    /**
     * Push time input into composite
     * @inheritdoc
     */
    public function buildDateTimeFields() : Fieldlist {

        $this->children = parent::buildDateTimeFields();

        if(!$this->timeField) {
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
        $this->children->push( $this->timeField );
        $this->children->setContainerField($this);
        return $this->children;
    }

    /**
     * Return the value as a YMD formatted date
     * @return string
     */
    public function dataValue() {
        $value = parent::dataValue();
        $timeValue = $this->timeField->dataValue();
        $value = $value . " " . $timeValue;
        return trim($value);
    }

    /**
     * Date validation message
     */
    protected function getDateValidationErrorMessage($dateValue) : string {
        return _t(
            'DateCompositeField.INVALID_DATE_PROVIDED',
            'The date and time \'{providedDate}\' is not valid. Please check the year, month, day and time values.',
            [
                'providedDate' => $dateValue
            ]
        );
    }
}
