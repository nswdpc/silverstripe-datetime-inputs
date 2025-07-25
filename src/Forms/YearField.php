<?php

namespace NSWDPC\DateInputs;

use Codem\Utilities\HTML5\NumberField;

/**
 * Year field handles validation
 * @author James
 */
class YearField extends NumberField
{
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
    #[\Override]
    public function Type()
    {
        return 'year text';
    }

    /**
     * @inheritdoc
     * The parent field handles validation for invalid complete dates
     */
    #[\Override]
    public function validate($validator)
    {
        // Don't validate empty fields
        if (empty($this->value)) {
            return true;
        }

        $result = parent::validate($validator);
        if (!$result) {
            return false;
        }

        // Check for valid month
        $valid = true;
        $minYear = $this->getAttribute('min');
        $maxYear = $this->getAttribute('max');
        if ($minYear && $maxYear) {
            if ($this->value < $minYear || $this->value > $maxYear) {
                $validator->validationError(
                    $this->name,
                    _t(
                        'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
                        "Please enter a year between {minYear} and {maxYear}",
                        [
                            'minYear' => $minYear,
                            'maxYear' => $maxYear
                        ]
                    )
                );
                $valid = false;
            }
        } elseif ($minYear) {
            if ($this->value < $minYear) {
                $validator->validationError(
                    $this->name,
                    _t(
                        'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
                        "Please enter a year equal to or after {minYear}",
                        [
                            'minYear' => $minYear
                        ]
                    )
                );
                $valid = false;
            }
        } elseif ($maxYear) {
            if ($this->value > $maxYear) {
                $validator->validationError(
                    $this->name,
                    _t(
                        'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
                        "Please enter a year equal to or before {maxYear}",
                        [
                            'maxYear' => $maxYear
                        ]
                    )
                );
                $valid = false;
            }
        }

        return $valid;
    }

}
