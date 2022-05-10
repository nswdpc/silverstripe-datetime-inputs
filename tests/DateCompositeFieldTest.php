<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DateCompositeField;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use Silverstripe\Forms\FieldList;
use Silverstripe\Forms\Form;
use SilverStripe\Forms\RequiredFields;

/**
 * Test the date field
 */
class DateCompositeFieldTest extends SapphireTest {

    protected $usesDatabase = false;

    public function testDateParser() {

        $inputValue = '2022-1-31';
        $results = DateCompositeField::parseDateTime($inputValue);
        $this->assertEquals(2022, $results['year']);
        $this->assertEquals(1, $results['month']);
        $this->assertEquals(31, $results['day']);

        // invalid full date
        $inputValue = '2022-11-31';
        $results = DateCompositeField::parseDateTime($inputValue);
        $this->assertEquals(2022, $results['year']);
        $this->assertEquals(11, $results['month']);
        $this->assertEquals(31, $results['day']);

        // fail on incomplete date
        $inputValue = '11-31';
        $results = DateCompositeField::parseDateTime($inputValue);
        $this->assertEquals('', $results['year']);
        $this->assertEquals('', $results['month']);
        $this->assertEquals('', $results['day']);
    }

    public function testFieldCreate() {
        $dateValue = "2030-12-14";
        $fieldName = "EventDate";
        $field = DateCompositeField::create(
            $fieldName,
            'Date of the event',
            $dateValue
        );
        $this->assertEquals($fieldName, $field->getName());
        $dateValueReturned = $field->dataValue();
        $this->assertEquals($dateValue, $dateValueReturned);
    }

    public function testFieldChildren() {
        $dateValue = "2030-12-14";
        $fieldName = "EventDate";
        $field = DateCompositeField::create(
            $fieldName,
            'Date of the event',
            $dateValue
        );

        $this->assertEquals($fieldName, $field->getName());

        $children = $field->getChildren();

        $this->assertEquals(3, $children->count());

    }

    public function testFieldViaForm() {

        $dateValue = "2030-12-14";

        $fieldName = "EventDate";

        $dateField = DateCompositeField::create(
            $fieldName,
            'Date of the event'
        );

        $form = Form::create(
            null,
            'TestEventForm',
            FieldList::create( $dateField )
        );

        $form->loadDataFrom( ['EventDate' => $dateValue ] );

        $fields = $form->Fields();
        $formDateField = $fields->dataFieldByName($fieldName);

        $children = $formDateField->getChildren();

        $this->assertEquals(3, $children->count());

    }

    public function testFieldWarning() {

        // an invalid date
        $dateValue = "2030-11-31";
        $fieldName = "Birthday";
        $field = DateCompositeField::create(
            $fieldName,
            'Date of birth',
            $dateValue
        );

        $this->assertNotEmpty(
            $field->getFieldWarning()
        );

    }
}
