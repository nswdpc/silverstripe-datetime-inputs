<?php

namespace NSWDPC\DateInputs;

use Codem\Utilities\HTML5\NumberField;

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
     * The parent field handles validation for invalid complete dates
     */
    public function validate($validator)
    {
        // Don't validate empty fields
        if (empty($this->value)) {
            return true;
        }

        $result = parent::validate($validator);
        if(!$result) {
            return false;
        }

        return true;

    }

}
