<?php

namespace App\Command;

use App\Entity\Vendor;
use App\Entity\VendorCategory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VendorCategoryCleanupCommand extends Command
{
    protected EntityManagerInterface $doctrine;
    protected ParameterBagInterface $parameterBag;
    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $doctrine, ParameterBagInterface $parameterBag, LoggerInterface $logger, string $name = null)
    {
        $this->doctrine = $doctrine;
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
        parent::__construct($name);
    }



    public function configure()
    {
        $this->setName('vendorcat:cleanup')
            ->setDescription('Cleanup duplicate vendor categories')
            ->addOption(
                'dryrun',
                '-d',
                InputOption::VALUE_NONE,
                'Make no database changes'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryrun = $input->getOption('dryrun');
        $vendors = $this->doctrine->getRepository(Vendor::class)->findAll();

        /**
         * @var Vendor $vendor
         */
        foreach ($vendors as $vendor) {
            $categories = $vendor->getVendorCategories();
            $catCache = [];
            $saveCnt = 0;
            $delCnt = 0;
            $output->write("Deduping categories for: {$vendor->getName()} ");
            /**
             * @var VendorCategory $category
             */
            foreach ($categories as $category) {
                if (!isset($catCache[$category->getCategory()])) {
                    $catCache[$category->getCategory()] = $category->getId();
                    $saveCnt++;
                } else {
                    if (!$dryrun) {
                        $this->doctrine->remove($category);
                    }
                    $delCnt++;
                }
            }
            $output->writeln("{$saveCnt} saved / {$delCnt} removed.");
        }
        if (!$dryrun) {
            $output->writeln("Committing changes to database");
            $this->doctrine->flush();
        }
        return 0;
    }

}