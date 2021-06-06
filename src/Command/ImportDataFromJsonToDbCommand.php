<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;


/**
 * Class ImportFilteredDataFromJsonToDbCommand
 * @package App\Command
 * Checks whether all required fields are present in json file and if yes, load row to database
 */
class ImportDataFromJsonToDbCommand extends Command
{
    protected static $defaultName = 'app:import-json-data-to-db';
    private $logger;
    private $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;

        parent::__construct();
    }


    /**
     * @param $array
     * @return array
     * @description filters out the products that doesn't meet minimum criteria and returns new array of products.
     */
    private function getFilteredArray($array)
    {
        try {
            $filteredArray = [];
            foreach ($array as $item) {
                if (
                    /**
                     * minimum criteria:
                     */
                    isset($item['ID']) &&
                    strlen($item['title']) > 0 &&
                    strlen($item['description']) > 0 &&
                    strlen($item['price']) > 0 &&
                    strlen($item['date']) > 0 &&
                    strlen($item['gtin']) > 0
                ) {
                    $filteredArray[] = $item;
                }
            }
            return $filteredArray;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * @param array $productItem
     * @return Product
     * @throws \Exception
     * @description: Takes the array and use it's values to create a Product object.
     */
    private function prepareProductObjectToPersist(array $productItem)
    {
        try {
            $product = new Product();
            $product->setTitle($productItem['title']);
            $product->setDescription($productItem['description']);
            $product->setGtin($productItem['gtin']);
            $product->setMpn($productItem['mpn']);
            $product->setPrice($productItem['price']);
            $product->setShortcode($productItem['shortcode']);
            $product->setCategory($productItem['category']);
            $product->setSub(serialize($productItem['sub']));
            $dateTime = new \DateTime($productItem['date']);
            $product->setDate($dateTime);
            $product->setProductId(intval($productItem['ID']));

            return $product;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start_time = microtime(true);

        /**
         * command starts with a question
         */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure you want to import the data to the db?', false, '/^(y|j)/i');


        /**
         * when question asked converts to true, then:
         */
        if ($helper->ask($input, $output, $question)) {

            /**
             * generates array from jsonFile hardcoded [could potentially be any path, but I hardcoded it here to keep it simple]
             */
            $arrayFromJson = json_decode(file_get_contents(__DIR__ . '/../../data(1).json'), true);

            /**
             * filters out products that does not meet criteria
             */
            $filteredArrayFromJson = $this->getFilteredArray($arrayFromJson);


            if (
                /**
                 * some basic validation:
                 */
                count($filteredArrayFromJson) < 1 ||
                is_array($filteredArrayFromJson) === false ||
                !isset($filteredArrayFromJson)
            ) {
                /**
                 * scenario A: criteria hasn't been met [FAILURE:(]:
                 */
                $outputFailureStyle = new OutputFormatterStyle('red', 'black', ['bold', 'blink']);

                $output->getFormatter()->setStyle('failure', $outputFailureStyle);
                $output->writeln(
                    '<failure>Either array is empty or the data format is incorrect.</>',
                );

                return Command::FAILURE;
            }

            /**
             * scenario B: criteria has been met [SUCCESS:)]
             */
            $entityManager = $this->em;
            try {
                foreach ($filteredArrayFromJson as $productItem) {
                    $product = $this->prepareProductObjectToPersist($productItem);
                    if(
                        !isset($productItem['ID'])
                    ) {
                        continue;
                    } else {
                        $entityManager->persist($product);
                    }
                }


            } catch (\Exception $e) {
                error_log($e->getMessage());
            }

            /**
             * flush here:
             */
            $entityManager->flush();

            /**
             * "success" output:
             */
            $outputSuccessStyle = new OutputFormatterStyle('green', '#ff0', ['bold', 'blink']);
            $output->getFormatter()->setStyle('success', $outputSuccessStyle);


            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time);
            $processedRowsNumber = count($filteredArrayFromJson);
            $memoryUsage = memory_get_usage();


            $output->writeln(
                "<success>
                            Successfully imported. Execution time: $execution_time seconds,
                            Number of processed rows: $processedRowsNumber,
                            Memory used: $memoryUsage bytes.
                            </>"
            );
            return Command::SUCCESS;
        }
        return Command::INVALID;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import data from json file to db.')
            ->setHelp('This command removes the json rows that are not meeting the product minimum requirements. Then the correct rows are inserted to mysql db.');
    }
}