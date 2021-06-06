<?php


namespace App\Command;


use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class ShowNumberOfProductsPerCategoryCommand
 * @package App\Command
 * Description: displays table with number of products per category
 */
class CountProductsPerCategoryCommand extends Command
{
    protected static $defaultName = 'app:count-products-per-category';
    private $logger;
    private $em;

    private $categories = ['Baby', 'Automotive', 'Beauty', 'Books', 'Clothing', 'Computers', 'Electronics', 'Games', 'Garden', 'Grocery', 'Health', 'Home', 'Industrial', 'Jewelery', 'Kids', 'Movies', 'Music', 'Outdoors', 'Shoes', 'Sports', 'Tools', 'Toys', ''];





    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * used for measuring script execution time
         */
        $start_time = microtime(true);

        $entityManager = $this->em;


        $rows = [];
        foreach ($this->categories as $category) {
            $count = $entityManager->getRepository(Product::class)->countByCategory($category);
            $price = $entityManager->getRepository(Product::class)->sumAllProductValuesPerCategoryInUSD($category);
            $rowArray = $category === "" ? ['no category identified', $count, $price] : [$category, $count, $price];
            $rows[] = $rowArray;
        }

        $table = new Table($output);
        $table->setHeaders(['category_name', 'Number_of_products', 'total_price_usd']);
        $table->setRows($rows);
        $table->render();

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        /**
         * "success" output:
         */
        $outputSuccessStyle = new OutputFormatterStyle('green', '#ff0', ['bold', 'blink']);
        $output->getFormatter()->setStyle('success', $outputSuccessStyle);
        $output->writeln(
            "<success>Data sucessfully provided. Execution time: $execution_time seconds.</>",
        );
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Returns the information about the number of products for a categories given.')
            ->setHelp('Categories are hardcoded at this stage, so make sure all of them are up-to-date.');
    }

}