<?php

namespace App\Command;

use App\Entity\Vendor;
use App\Entity\VendorImage;
use App\Exceptions\DownloadException;
use Doctrine\ORM\EntityManagerInterface;
use finfo;
use GuzzleHttp\Exception\TransferException;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use VStelmakh\UrlHighlight\UrlHighlight;

class VendorImageDownloadCommand extends Command
{

    private int $minWidth = 640;
    private int $minHeight = 480;
    private EntityManagerInterface $doctrine;
    private ParameterBagInterface $parameterBag;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $doctrine, ParameterBagInterface $parameterBag, LoggerInterface $logger, string $name = null)
    {
        $this->doctrine = $doctrine;
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName('vendor:collectimages')
            ->setDescription('Download images from the vendor\'s image block')
            ->addArgument(
                'vendorid',
                InputArgument::REQUIRED,
                'Vendor ID from the database',
                null
            )
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
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);

        $vendorID = $input->getArgument('vendorid');
        $this->minWidth = $input->getOption('width');
        $this->minHeight = $input->getOption('height');


        /**
         * @var Vendor $vendor
         */
        $vendor = $this->doctrine->getRepository(Vendor::class)->find($vendorID);
        $output->writeln("Starting image download for {$vendor->getName()}");
        $imageBlock = $vendor->getImageBlock();
        $urlHighlight = new UrlHighlight();
        $urls = $urlHighlight->getUrls($imageBlock);
        $vendor->setImageURLS(join("\n", $urls));
        $output->writeln("   - Found " . count($urls) . " urls");
        $path = $this->parameterBag->get("imagepath");
        $vendorSHA = sha1(strtoupper($vendor->getName()));
        $imagePath = "{$path}/{$vendorSHA}";
        $output->writeln("   - Image path is at {$imagePath}");
        if (!is_dir($imagePath)) {
            $output->writeln("   - Creating image directory");
            mkdir($imagePath, 0755);
        }
        $images = $this->pullImages($urls);
        $output->writeln("   - Found " . count($images) . " potential images");
        $files = $this->downloadImages($output, $images, $imagePath);
        $output->writeln("   - Found " . count($files) . " actual images");

        foreach ($files as $f) {

            $image = (new VendorImage())->setImagePath($f)->setVendor($vendor);
            $this->doctrine->persist($image);

        }

        $this->doctrine->persist($vendor);
        $this->doctrine->flush();

        $output->writeln("Download complete.");
        $output->writeln("");
        return 0;
    }


    /**
     * @param array $urls
     * @param $path
     * @return array
     * @throws DownloadException
     */
    protected function downloadImages(OutputInterface $output, array $urls, $path = null): array
    {
        if (empty($path)) {
            throw new DownloadException("File path not provided");
        }

        if (!is_dir($path)) {
            throw new DownloadException("Download path does not exist: {$path}");
        }
        $results = [];
        foreach ($urls as $url) {
            try {
                $output->write("   * Checking $url");

                if (strtolower(substr($url,0,5)) === "data:" && stristr($url, 'base64')) {
                    $file = base64_decode($url);
                } else {
                    try {
                        $guzzle = new \GuzzleHttp\Client();
                        $response = $guzzle->get($url, ['stream' => true, 'timeout' => '2']);
                        $file = $response->getBody()->getContents();
                    } catch (TransferException) {
                        continue;
                    }
                }

//                $file = file_get_contents($url);
                list($ext,) = explode("/", (new finfo(FILEINFO_EXTENSION))->buffer($file));
                $uuid = Uuid::uuid4();

                $size = getimagesizefromstring($file);

                $imgWidth = $size[0];
                $imgHeight = $size[1];

                if ($imgWidth < $this->minWidth || $imgHeight < $this->minHeight) {
                    $output->writeln(" too small");
                    continue;
                }
                $filename = "{$uuid}.{$ext}";
                $imagePath = "{$path}/{$filename}";
                $image = $this->doctrine->getRepository(VendorImage::class)->findOneBy(['imagePath' => $imagePath]);
                if (!empty($image) && !file_exists($imagePath)) {
                    $output->writeln(" file already downloaded");
                    continue; // Image already exists and is downloaded...
                }

                if (!file_exists($imagePath)) {
                    $output->writeln(" downloading");
                    file_put_contents($imagePath, $file);
                }

                file_put_contents($imagePath, $file);
                $results[] = $imagePath;
            } catch (\ErrorException $ee) {
                $foo = 'bar';
                // Continue on
            }


        }

        return $results;
    }

    /**
     * @param array $urls
     * @return array
     * @throws \HeadlessChromium\Exception\CommunicationException
     * @throws \HeadlessChromium\Exception\CommunicationException\CannotReadResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\InvalidResponse
     * @throws \HeadlessChromium\Exception\CommunicationException\ResponseHasError
     * @throws \HeadlessChromium\Exception\NavigationExpired
     * @throws \HeadlessChromium\Exception\NoResponseAvailable
     * @throws \HeadlessChromium\Exception\OperationTimedOut
     */
    protected function pullImages(array $urls): array
    {
        $factory = new BrowserFactory('/usr/bin/chromium');
        $browser = $factory->createBrowser([
            'headless' => true,
            'noSandbox' => true,
            'debugLogger' => $this->logger
        ]);
        $page = $browser->createPage();
        $images = [];

        foreach ($urls as $url) {
            try {
                $page->navigate($url)->waitForNavigation(Page::NETWORK_IDLE, 45000);
                $network = $page->getNetworkResponses();
                foreach ($network as $n) {
                    if (stristr(strtolower($n->mimeType), 'image') !== false) {
                        $images[] = $n->url;
                    }
                }
            } catch ( \Exception $e) {
                // Do nothing, just get the next image
            }
        }

        $browser->close();
        return $images;


    }

}