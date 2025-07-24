<?php

namespace NSWDPC\DateInputs;

/**
 * Default imput helper methods for child fields of DateCompositeField and DatetimeCompositeField
 * @author James
 */
trait DateInputChild
{
    /**
     * The parent field handles data
     */
    public function hasData(): bool
    {
        return false;
    }

    /**
     * This field is required if the parent is required
     * @return bool
     */
    public function Required()
    {
        $isRequired = false;
        if ($container = $this->getContainerFieldList()) {
            $dateTimeCompositeField = $container->getContainerField();
            $isRequired = $dateTimeCompositeField->Required();
        }

        return $isRequired;
    }

    /**
     * Return values for a <datalist>
     */
    protected function createDataListFromRange(int $min, int $max, string $context = ''): array
    {
        $list = [];
        for ($i = $min; $i <= $max; $i++) {
            $list[ $i ] = $i;
        }

        return $list;
    }

    /**
     * Return values for month data list
     */
    public function getMonthDataList(): array
    {
        return $this->createDataListFromRange(1, 12);
    }

    /**
     * Return values for day data list
     */
    public function getDayDataList(): array
    {
        return $this->createDataListFromRange(1, 31);
    }

    /**
     * Return values for hour data list
     */
    public function getHourDataList(): array
    {
        return $this->createDataListFromRange(0, 23);
    }

    /**
     * Return values for minute data list
     */
    public function getMinuteDataList(): array
    {
        return $this->createDataListFromRange(0, 59);
    }

}
