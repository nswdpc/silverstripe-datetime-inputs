<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DatetimeCompositeField;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\View\SSViewer;

/**
 * Test controller for Datetime input testing
 * @author James
 */
class DatetimeInputTestController extends Controller implements TestOnly {

    /**
     * @config
     */
    private static $allowed_actions = [
        'DatetimeCompositeTestForm',
        'doTestDate'
    ];

    /**
     * @config
     */
    private static $url_segment = "DatetimeInputTestController";

    protected $template = 'BlankPage';

    /**
     * @config
     */
    private static $url_handlers = [
        '$Action//$ID/$OtherID' => "handleAction",
    ];

    public function __construct()
    {
        parent::__construct();
        if (Controller::has_curr()) {
            $this->setRequest(Controller::curr()->getRequest());
        }
    }

    public function Link($action = null)
    {
        /** @skipUpgrade */
        return Controller::join_links(
            'DatetimeInputTestController',
            $action
        );
    }

    public function DatetimeCompositeTestForm() {
        return $this->Form();
    }

    public function Form() {

        $datetimeCompositeField = DatetimeCompositeField::create(
            'TestDate',
            'Test date'
        );

        $form = Form::create(
            $this,
            "DatetimeCompositeTestForm",
            Fieldlist::create(
                $datetimeCompositeField
            ),
            Fieldlist::create(
                FormAction::create(
                    'doTestDate',
                    'Submit'
                )
            ),
            RequiredFields::create(['TestDate'])
        );
        return $form;
    }

    public function doTestDate($data, $form, $request) {
        $dataValue = $form->Fields()->dataFieldByName('TestDate')->dataValue();
        $form->sessionMessage('TEST_DATEINPUT_OK_' . $dataValue, 'good');
        return $this->redirectBack();
    }

    public function getViewer($action = null)
    {
        return new SSViewer('BlankPage');
    }

}
