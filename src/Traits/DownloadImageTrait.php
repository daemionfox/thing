<?php

namespace App\Traits;

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

trait DownloadImageTrait
{

    protected function downloadImages(array $urls): array
    {
        foreach ($urls as $url) {
            $file = file_get_contents($url);
            $size = getimagesizefromstring($file);

        }
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
            'debugLogger' => 'php://stdout'
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