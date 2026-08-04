<?php

namespace NSWDPC\DateInputs;

use Codem\Utilities\HTML5\NumberField;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * Month input field, handles validation
 * @author James
 */
class DayOfMonthField extends NumberField
{
    use DateInputChild;

    /**
     * @inheritdoc
     */
    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        parent::__construct($name, $title, $value, $maxLength, $form);
        $this->setAttribute('min', 1);
        $this->setAttribute('max', 31);
        $this->setAttribute('step', 1);
        $this->setAttribute('inputmode', 'numeric');
        $this->setAttribute('autocomplete', 'off');
        $this->setAttribute(
            'placeholder',
            _t(
                'NSWDPC\\DateInputs\\DayOfMonthField.VALID_DAY_DIRECTION',
                "1 - 31"
            )
        );

        $this->setDatalist(
            $this->getDayDataList()
        );
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function Type()
    {
        return 'dayofmonth text';
    }

    /**
     * @inheritdoc
     * The parent field handles validation for invalid complete dates
     */
    #[\Override]
    public function validate(): ValidationResult
    {
        $validationResult = parent::validate();
        // Don't validate empty fields
        if (empty($this->value) || !$validationResult->isValid()) {
            return $validationResult;
        }

        // Check for valid month
        if ($this->value < 1 || $this->value > 31) {
            $validationResult->addFieldError(
                $this->name,
                _t(
                    'NSWDPC\\DateInputs\\DayOfMonthField.INVALID_DAY_OF_MONTH',
                    "Please enter a valid day between 1 and 31"
                ),
                ValidationResult::TYPE_ERROR
            );
        }

        return $validationResult;
    }

}
