<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DateCompositeField;
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
class DateInputTestController extends Controller implements TestOnly
{
    /**
     * @config
     */
    private static $allowed_actions = [
        'DateCompositeTestForm',
        'doTestDate'
    ];

    /**
     * @config
     */
    private static $url_segment = "DateInputTestController";

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
            'DateInputTestController',
            $action
        );
    }

    public function DateCompositeTestForm()
    {
        return $this->Form();
    }

    public function Form()
    {

        $dateCompositeField = DateCompositeField::create(
            'TestDate',
            'Test date'
        );

        $min = 1990;
        $max = 3000;
        $dateCompositeField->setMinMaxYear($min, $max);

        $form = Form::create(
            $this,
            "DateCompositeTestForm",
            Fieldlist::create(
                $dateCompositeField
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

    public function doTestDate($data, $form, $request)
    {
        $dataValue = $form->Fields()->dataFieldByName('TestDate')->dataValue();
        $form->sessionMessage('TEST_DATEINPUT_OK_' . $dataValue, 'good');
        return $this->redirectBack();
    }

    public function getViewer($action = null)
    {
        return new SSViewer('BlankPage');
    }

}
