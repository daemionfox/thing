<?php

namespace App\Command;

use App\Entity\Vendor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VendorCollectAllImagesCommand extends \Symfony\Component\Console\Command\Command
{
    private int $minWidth = 640;
    private int $minHeight = 480;
    private EntityManagerInterface $doctrine;
    private ParameterBagInterface $parameterBag;

    public function __construct(EntityManagerInterface $doctrine, ParameterBagInterface $parameterBag, string $name = null)
    {
        $this->doctrine = $doctrine;
        $this->parameterBag = $parameterBag;
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName('vendor:collectall')
            ->setDescription('Download images from the vendor\'s image block')
            ->addOption(
                'width',
                '-w',
                InputOption::VALUE_OPTIONAL,
                'Minimum Width of an image',
                '640'
            )
            ->addOption(
                'height',
                '-t',
                InputOption::VALUE_OPTIONAL,
                'Minimum Height of an image',
                '480'
            )
            ->addOption(
                'progress',
                '-p',
                InputOption::VALUE_OPTIONAL,
                'progress bar tag'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);


        $progress = $input->getOption('progress');

        $this->minWidth = $input->getOption('width');
        $this->minHeight = $input->getOption('height');

        $vendors = $this->doctrine->getRepository(Vendor::class)->findAll();


        $progressCache = [
            'current' => 0,
            'total' => count($vendors)
        ];

        if (!empty($progress)) {
            file_put_contents(__DIR__ . "/../../cache/{$progress}.json", json_encode($progressCache));
        }

        $imageOutput = new NullOutput();

        /**
         * @var Vendor $vendor
         */
        foreach ($vendors as $vendor) {
            $id = $vendor->getId();

            $imageInput = new ArrayInput([
                'vendorid' => $id,
                '--height' => $this->minHeight,
                '--width' => $this->minWidth
            ]);

            $output->write("<info>Fetching images for " . $vendor->getName() . "...</info> ");
            $collect = $this->getApplication()->find('vendor:collectimages');
            $collect->run($imageInput, $imageOutput

            );
            $output->writeln(" done.");
            $progressCache['current']++;
            if (!empty($progress)) {
                file_put_contents(__DIR__ . "/../../cache/{$progress}.json", json_encode($progressCache));
            }

            sleep(5);

        }

        return 0;
    }



    }