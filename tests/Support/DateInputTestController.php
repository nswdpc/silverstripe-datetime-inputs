<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DateCompositeField;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
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
    private static array $allowed_actions = [
        'DateCompositeTestForm',
        'doTestDate'
    ];

    /**
     * @config
     */
    private static string $url_segment = "DateInputTestController";

    protected $template = 'BlankPage';

    /**
     * @config
     */
    private static array $url_handlers = [
        '$Action//$ID/$OtherID' => "handleAction",
    ];

    public function __construct()
    {
        parent::__construct();
        if (\SilverStripe\Control\Controller::curr() instanceof \SilverStripe\Control\Controller) {
            $this->setRequest(Controller::curr()->getRequest());
        }
    }

    #[\Override]
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
        return Form::create(
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
            \SilverStripe\Forms\Validation\RequiredFieldsValidator::create(['TestDate'])
        );
    }

    public function doTestDate($data, $form, $request): \SilverStripe\Control\HTTPResponse
    {
        $dataValue = $form->Fields()->dataFieldByName('TestDate')->dataValue();
        $form->sessionMessage('TEST_DATEINPUT_OK_' . $dataValue, 'good');
        return $this->redirectBack();
    }

    #[\Override]
    public function getViewer($action = null)
    {
        return SSViewer::create('BlankPage');
    }

}
