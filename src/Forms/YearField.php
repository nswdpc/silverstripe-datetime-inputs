<?php

namespace NSWDPC\DateInputs;

use Codem\Utilities\HTML5\NumberField;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * Year field handles validation
 * @author James
 */
class YearField extends NumberField {

    use DateInputChild;

    /**
     * @inheritdoc
     */
    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        parent::__construct($name, $title, $value, $maxLength, $form);
        $this->setAttribute('inputmode', 'numeric');
        $this->setAttribute('step', 1);
        $this->setAttribute(
            'placeholder',
            _t(
                'NSWDPC\\DateInputs\\MonthField.VALID_YEAR_DIRECTION',
                "Enter a year"
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function Type()
    {
        return 'year text';
    }

    /**
     * @inheritdoc
     * The parent field handles validation for invalid complete dates
     */
    public function validate(): ValidationResult
    {
        $validationResult = parent::validate();
        // Don't validate empty fields
        if (empty($this->value) || !$validationResult->isValid()) {
            return $validationResult;
        }

        // Check for valid month
        $minYear = $this->getAttribute('min');
        $maxYear = $this->getAttribute('max');
        if($minYear && $maxYear && ($this->value < $minYear || $this->value > $maxYear)) {
            $validationResult->addFieldError(
                $this->name,
                _t(
                    'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
                    "Please enter a year between {minYear} and {maxYear}",
                    [
                        'minYear' => $minYear,
                        'maxYear' => $maxYear
                    ]
                ),
                ValidationResult::TYPE_ERROR
            );
        } else if($minYear && $this->value < $minYear) {
            $validationResult->addFieldError(
                $this->name,
                _t(
                    'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
                    "Please enter a year equal to or after {minYear}",
                    [
                        'minYear' => $minYear
                    ]
                ),
                ValidationResult::TYPE_ERROR
            );
        } else if($maxYear && $this->value > $maxYear) {
            $validationResult->addFieldError(
                $this->name,
                _t(
                    'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
                    "Please enter a year equal to or before {maxYear}",
                    [
                        'maxYear' => $maxYear
                    ]
                ),
                ValidationResult::TYPE_ERROR
            );
        }
        return $validationResult;
    }

}
