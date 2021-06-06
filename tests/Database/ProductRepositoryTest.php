<?php


use PHPUnit\Framework\TestCase;

class ProductRepositoryTest extends TestCase
{
    private string $jsonInputPath = "./../../data(1).json";


    public function testIfFileExists()
    {
        $this->assertFileExists(__DIR__ . '/../../data(1).json');;
    }

    public function testIfFileIsReadable()
    {
        $this->assertFileIsReadable(__DIR__ . '/../../data(1).json');;
    }

    public function testIfIfIsConvertableToArray()
    {
        $this->assertIsArray(json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true));
    }

    public function testIfArrayIsNotEmpty()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        $this->assertTrue(count($arrayFromJson) > 0);
    }

    /**
     * ID is mandatory field
     */
    public function testIfIdIsSet()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        foreach ($arrayFromJson as $array) {
            $this->assertTrue(strlen($array['ID']) > 0, 'Failure occured - ID not set');
        }
    }


    /**
     * title is mandatory field
     */
    public function testIfTitleIsSet()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        foreach ($arrayFromJson as $array) {
            try {
                $this->assertTrue(strlen($array['title']) > 0, 'No title set in array with ID=' . $array['ID']);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * description is mandatory field
     */
    public function testIfDescriptionIsSet()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        foreach ($arrayFromJson as $array) {
            try {
                $this->assertTrue(strlen($array['description']) > 0, 'No description set in array with ID=' . $array['ID']);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * price is mandatory field
     */
    public function testIfPriceIsSet()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        foreach ($arrayFromJson as $array) {
            try {
                $this->assertTrue(strlen($array['price']) > 0, 'No price set in array with ID=' . $array['ID']);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * I considered date as a mandatory field
     */
    public function testIfDateIsSet()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        foreach ($arrayFromJson as $array) {
            try {
                $this->assertTrue(strlen($array['date']) > 0, 'No date set in array with ID=' . $array['ID']);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * gtin is mandatory field
     */
    public function testIfGtinIsSet()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        foreach ($arrayFromJson as $array) {
            try {
                $this->assertTrue(strlen($array['gtin']) > 0, 'No gtin set in array with ID=' . $array['ID']);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    /**
     * gtin is mandatory field. Gtin should be at least 8 digits long and not longer than 14
     */
    public function testIfGtinLengthIsCorrect()
    {
        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);
        $faultyItemsArr = [];

        foreach ($arrayFromJson as $array) {
            try {
                if (strlen($array['gtin']) > 14 || strlen($array['gtin']) < 8) {
                    $faultyItemsArr[] = $array['ID'];
                }
                $this->assertTrue( strlen($array['gtin'] > 14), 'Gtin length is longer than 14 for product with ID:' . $array['ID']);
                $this->assertTrue( strlen($array['gtin'] <8), 'Gtin length is shorter than 8 for product with ID:' . $array['ID']);

            } catch (\Exception $e) {
                error_log($e->getMessage());
            }

            /**
             * additionally: logging to the console number of items with not proper gtin lenght
             */
            $this->expectOutputString("There is a problem with gtin length on item with ID: ".count($faultyItemsArr).".");
        }
    }


    /**
     * gtin is mandatory field. Gtin should be at least 8 digits long and not longer than 14
     */
    public function testIfThereAreNoDuplicates()
    {
        /**
         * @return bool
         * @Description: a helper function that checks if duplicate exists for a given array
         */
        function checkIfDupesExist(array $array):bool {
                $helperArray = [];

            foreach($array as $innerArray) {
                $helperArray[] = $innerArray['ID'];
            }
            $arrayUniqueCount = count(array_unique($helperArray));
            $arrayCount = count($helperArray);

            if ($arrayCount === $arrayUniqueCount) {
                return true;
            } else {
                return false;
            }
        }

        $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);

        $this->assertIsArray($arrayFromJson, '$arrayFromJson is not an array.');
        $this->assertTrue(checkIfDupesExist($arrayFromJson), 'Array is not-unique.');


    }

}
