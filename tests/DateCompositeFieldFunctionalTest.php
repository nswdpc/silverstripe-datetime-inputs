<?php

namespace NSWDPC\DateInputs\Tests;

use NSWDPC\DateInputs\DateCompositeField;
use SilverStripe\Dev\FunctionalTest;

/**
 * FunctionalTest for date field
 */
class DateCompositeFieldFunctionalTest extends FunctionalTest
{
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

    protected function getTestPath(): string
    {
        return 'DateInputTestController';
    }

    public function testDateSubmission(): void
    {
        $this->get($this->getTestPath());
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
        $this->assertTrue(str_contains($postSubmit->getBody(), "TEST_DATEINPUT_OK_{$year}-{$month}-{$day}"));
    }

    public function testInvalidMinMaxYearSubmission(): void
    {
        $this->get($this->getTestPath());
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

        $this->assertTrue(str_contains($postSubmit->getBody(), $message));
    }

    public function testInvalidDateSubmission(): void
    {
        $this->get($this->getTestPath());
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
        $this->assertTrue(str_contains($postSubmit->getBody(), htmlspecialchars($message)));
    }

    public function testInValidDayOfMonthSubmission(): void
    {
        $this->get($this->getTestPath());
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
        $this->assertTrue(str_contains($postSubmit->getBody(), $message));
    }

    public function testInvalidMonthNumberSubmission(): void
    {
        $this->get($this->getTestPath());
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
        $this->assertTrue(str_contains($postSubmit->getBody(), $message));
    }

    public function testRequiredDateSubmission(): void
    {
        $this->get($this->getTestPath());
        $postSubmit = $this->submitForm(
            'Form_DateCompositeTestForm',
            'action_doTestDate'
        );
        $message = '"Test date" is required';
        $this->assertTrue(str_contains($postSubmit->getBody(), htmlspecialchars($message)));
    }
}
