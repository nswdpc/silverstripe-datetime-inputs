<?php

namespace NSWDPC\DateInputs;

use Codem\Utilities\HTML5\TimeField as BaseTimeField;

/**
 * Time field handles validation, child of DatetimeCompositeField
 * @author James
 */
class TimeField extends BaseTimeField {

    use DateInputChild;

}
