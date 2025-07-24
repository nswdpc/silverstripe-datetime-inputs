<?php

namespace NSWDPC\DateInputs\Tests;

use SilverStripe\Dev\FunctionalTest;

/**
 * FunctionalTest for date field
 */
class DatetimeCompositeFieldFunctionalTest extends FunctionalTest
{
    /**
     * @inheritdoc
     */
    protected $usesDatabase = false;

    /**
     * @inheritdoc
     */
    protected static $extra_controllers = [
        DatetimeInputTestController::class
    ];

    protected function getTestPath(): string
    {
        return 'DatetimeInputTestController';
    }

    public function testDateSubmission(): void
    {
        $this->get($this->getTestPath());
        $year = '2028';
        $month = '10';
        $day = '30';
        $time = '11:45';
        $postSubmit = $this->submitForm(
            'Form_DatetimeCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day,
                'TestDate[time]' => $time
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $this->assertTrue(str_contains($postSubmit->getBody(), "TEST_DATEINPUT_OK_{$year}-{$month}-{$day} {$time}"));
    }

    public function testInvalidTimeSubmission(): void
    {
        $this->get($this->getTestPath());
        $year = '2028';
        $month = '11';
        $day = '30';
        $time = '26:92';

        $postSubmit = $this->submitForm(
            'Form_DatetimeCompositeTestForm',
            'action_doTestDate',
            [
                'TestDate[year]' => $year,
                'TestDate[month]' => $month,
                'TestDate[day]' => $day,
                'TestDate[time]' => $time
            ]
        );
        $this->assertEquals(200, $postSubmit->getStatusCode());
        $message = "Please enter a valid date";//TODO _t()
        $this->assertTrue(str_contains($postSubmit->getBody(), $message));
    }

    public function testRequiredDateSubmission(): void
    {
        $this->get($this->getTestPath());
        $postSubmit = $this->submitForm(
            'Form_DatetimeCompositeTestForm',
            'action_doTestDate'
        );
        $message = '"Test date" is required';
        $this->assertTrue(str_contains($postSubmit->getBody(), htmlspecialchars($message)));
    }

}
