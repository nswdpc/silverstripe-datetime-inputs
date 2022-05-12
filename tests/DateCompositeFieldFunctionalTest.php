<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DateCompositeField;
use SilverStripe\Dev\FunctionalTest;

/**
 * FunctionalTest for date field
 */
class DateCompositeFieldFunctionalTest extends FunctionalTest {

    /**
     * @inheritdoc
     */
    protected $usesDatabase = false;

    /**
     * @inheritdoc
     */
    protected static $extra_controllers = [
        DateInputTestController::class
    ];

    protected function getTestPath() {
        return 'DateInputTestController';
    }

    public function testDateSubmission() {
        $page = $this->get( $this->getTestPath() );
        $year = '2028';
        $month = '10';
        $day = '30';
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $this->assertTrue(strpos($postSubmit->getBody(), "TEST_DATEINPUT_OK_{$year}-{$month}-{$day}") !== false);
    }

    public function testInvalidMinMaxYearSubmission() {
        $page = $this->get( $this->getTestPath() );
        $year = '3001';// > 3000
        $month = '10';
        $day = '30';
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $message = _t(
            'NSWDPC\\DateInputs\\YearField.YEAR_OUT_OF_RANGE',
            "Please enter a year between {minYear} and {maxYear}",
            [
                'minYear' => 1990,
                'maxYear' => 3000
            ]
        );

        $this->assertTrue(strpos($postSubmit->getBody(), $message) !== false);
    }

    public function testInvalidDateSubmission() {
        $page = $this->get( $this->getTestPath() );
        $year = '2028';
        $month = '11';
        $day = '31';
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $message = DateCompositeField::getDateValidationErrorMessage("{$year}-{$month}-{$day}");
        $this->assertTrue(strpos($postSubmit->getBody(), $message) !== false);
    }

    public function testInValidDayOfMonthSubmission() {
        $page = $this->get( $this->getTestPath() );
        $year = '2022';
        $month = '3';
        $day = '88';
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $message = _t(
            'NSWDPC\\DateInputs\\DayOfMonthField.INVALID_DAY_OF_MONTH',
            "Please enter a valid day between 1 and 31"
        );
        $this->assertTrue(strpos($postSubmit->getBody(), $message) !== false);
    }

    public function testInvalidMonthNumberSubmission() {
        $page = $this->get( $this->getTestPath() );
        $year = '2022';
        $month = '86';
        $day = '12';
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $message = _t(
            'NSWDPC\\DateInputs\\MonthField.INVALID_MONTH',
            "Please enter a valid month between 1 and 12"
        );
        $this->assertTrue(strpos($postSubmit->getBody(), $message) !== false);
    }

    public function testRequiredDateSubmission() {
        $page = $this->get( $this->getTestPath() );
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate'
        );
        $this->assertTrue(strpos($postSubmit->getBody(), '"Test date" is required') !== false);
    }
}
