<?php

namespace NSWDPC\DateInputs;

use Codem\Utilities\HTML5\NumberField;
use SilverStripe\Core\Validation\ValidationResult;

/**
 * Month input field, handles validation
 * @author James
 */
class MonthNumberField extends NumberField {

    use DateInputChild;

    /**
     * @inheritdoc
     */
    public function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        parent::__construct($name, $title, $value, $maxLength, $form);
        $this->setAttribute('min', 1);
        $this->setAttribute('max', 12);
        $this->setAttribute('step', 1);
        $this->setAttribute('inputmode', 'numeric');
        $this->setAttribute(
            'placeholder',
            _t(
                'NSWDPC\\DateInputs\\MonthField.VALID_MONTH_DIRECTION',
                "1 (Jan) - 12 (Dec)"
            )
        );

        $this->setDatalist(
            $this->getMonthDataList()
        );
    }

    /**
     * @inheritdoc
     */
    public function Type()
    {
        return 'monthnumber text';
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
        if ($this->value < 1 || $this->value > 12) {
            $validationResult->addFieldError(
                $this->name,
                _t(
                    'NSWDPC\\DateInputs\\MonthField.INVALID_MONTH',
                    "Please enter a valid month between 1 and 12"
                ),
                ValidationResult::TYPE_ERROR
            );
        }
        return $validationResult;
    }

}
