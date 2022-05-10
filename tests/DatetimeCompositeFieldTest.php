<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DatetimeCompositeField;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use Silverstripe\Forms\FieldList;
use Silverstripe\Forms\Form;

/**
 * Test the datetime field
 */
class DatetimeCompositeFieldTest extends SapphireTest {

    protected $usesDatabase = false;

    public function testDateParser() {

        $inputValue = '2022-1-31 11:45';
        $results = DatetimeCompositeField::parseDateTime($inputValue);
        $this->assertEquals(2022, $results['year']);
        $this->assertEquals(1, $results['month']);
        $this->assertEquals(31, $results['day']);
        $this->assertEquals('11:45', $results['time']);

        // invalid full date
        $inputValue = '2022-11-31 11:45';
        $results = DatetimeCompositeField::parseDateTime($inputValue);
        $this->assertEquals(2022, $results['year']);
        $this->assertEquals(11, $results['month']);
        $this->assertEquals(31, $results['day']);
        $this->assertEquals('11:45', $results['time']);

        // fail on incomplete date
        $inputValue = '11-31 11:45';
        $results = DatetimeCompositeField::parseDateTime($inputValue);
        $this->assertEquals('', $results['year']);
        $this->assertEquals('', $results['month']);
        $this->assertEquals('', $results['day']);
        $this->assertEquals('', $results['time']);
    }

    public function testFieldCreate() {
        $dateValue = "2030-12-14 11:45";
        $fieldName = "EventDatetime";
        $field = DatetimeCompositeField::create(
            $fieldName,
            'Date of the event',
            $dateValue
        );
        $this->assertEquals($fieldName, $field->getName());
        $dateValueReturned = $field->dataValue();
        $this->assertEquals($dateValue, $dateValueReturned);
    }

    public function testFieldChildren() {
        $dateValue = "2030-12-14 23:59";
        $fieldName = "EventDatetime";
        $field = DatetimeCompositeField::create(
            $fieldName,
            'Date of the event',
            $dateValue
        );

        $this->assertEquals($fieldName, $field->getName());

        $children = $field->getChildren();

        $this->assertEquals(4, $children->count());

    }

    public function testFieldViaForm() {

        $dateValue = "2030-12-14 04:45";

        $fieldName = "EventDatetime";

        $dateField = DatetimeCompositeField::create(
            $fieldName,
            'Date of the event'
        );

        $form = Form::create(
            null,
            'TestEventForm',
            FieldList::create( $dateField )
        );

        $form->loadDataFrom( ['EventDatetime' => $dateValue ] );

        $fields = $form->Fields();
        $formDateField = $fields->dataFieldByName($fieldName);

        $children = $formDateField->getChildren();

        $this->assertEquals(4, $children->count());

    }



    public function testFieldWarning() {

        // an invalid time on the date
        $dateValue = "2030-11-30 25:69";
        $fieldName = "Birthday";
        $field = DatetimeCompositeField::create(
            $fieldName,
            'Date and time of birth',
            $dateValue
        );

        $this->assertNotEmpty(
            $field->getFieldWarning()
        );

    }
}