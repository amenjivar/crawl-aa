<?php
namespace app\components;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use yii\httpclient\Exception;

/**
 * Class crawler
 * @package app\components
 */
class Crawler extends Component
{
    const IS_CRAWLED = 1;
    const NOT_CRAWLED = 0;

    /**
     * @var string The URL used as point of entry
     */
    private string $entryPointURL;

    /**
     * @var string The current page being crawled
     */
    private string $currentPageURL;

    /**
     * @var array unique list (in key) of images from all pages crawled
     * - array values are; IS_CRAWLED
     */
    private array $images = [];

    /**
     * @var array|array[] unique list (in key) of external and internal links from all pages crawled
     * - array values for external/internal are; IS_CRAWLED or NOT_CRAWLED
     */
    private array $link = ['external' => [], 'internal' => []];

    /**
     * @var array a unique set of pages crawled (in key).
     * - array values contain a list of; httpCode, loadSeconds, wordCount and titleLength
     */
    private array $pages = [];

    /**
     * Starts crawling the specified URL
     * @param string $entryPointURL The page to crawl
     * @param int $limit The maximum number of pages to crawl
     * @return array
     * @throws Exception
     */
    public function run(string $entryPointURL, int $limit = 4): array
    {
        // set the entry point; removing trailing path separator
        $this->entryPointURL = rtrim($entryPointURL, '/');

        // add the entry point as an internal link (not crawled)
        $this->link['internal'][$this->entryPointURL] = self::NOT_CRAWLED;

        // pages are available for crawling
        while(false !== ($url = array_search(0, $this->link['internal']))
            // and crawl limit not reached
            && count($this->pages) <= $limit
        ) {
            // crawl the page
            if (($results = $this->crawlPage($url))) {
                $this->pages[$url] = $results;
            }

            // set internal link as crawled
            $this->link['internal'][$url] = self::IS_CRAWLED;
        }

        return $this->getResults();
    }

    /**
     * Crawl the specified page
     * @param $url
     * @return array|false
     * @throws Exception
     */
    private function crawlPage($url)
    {
        try {
            $response = (
                $request = (new Client([
                    'transport' => CurlTransport::class,
                ]))->createRequest()
                    ->setFormat(Client::FORMAT_CURL)
                    ->setOptions([
                        CURLOPT_USERAGENT => Yii::$app->request->userAgent,
                        CURLOPT_FOLLOWLOCATION => true,
                    ])
                    ->setURL($this->currentPageURL = $url)
            )->send();
        } catch (InvalidConfigException | Exception $e) {
            return false;
        }

        // parse the HTML contents
        list($titleLength, $wordCount) = $this->crawlContents($response->getContent());

        return [
            'httpCode' => $response->getStatusCode(),
            'loadSeconds' => $request->responseTime(),
            'titleLength' => $titleLength,
            'wordCount' => $wordCount,
        ];
    }

    /**
     * Parses the HTML contents to find title length, word count and links/images
     * @param string $content
     * @return int[]
     */
    private function crawlContents(string $content): array
    {
        // create DOMDocument to use with XPath
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        // load the prepared HTML from string
        $dom->loadHTML($this->prepareHTML($content));
        libxml_clear_errors();

        // create DOMXPath to search through elements
        $xpath = new DOMXPath($dom);

        // save image paths
        $this->saveList('saveImage', $xpath->query("//img"));

        // save links (internal and external)
        $this->saveList('saveLink', $xpath->query('//a'));

        return [
            // get the length of the title
            strlen($xpath->query('//title')->item(0)->nodeValue ?? ''),
            str_word_count(strtolower($xpath->query('//body')->item(0)->textContent ?? ''))
        ];
    }

    /**
     * Returns the absolute URL for a URL based on the current page
     * @param $url
     * @return string
     */
    private function getAbsoluteURL($url): string
    {
        switch (substr($url, 0, 1 )) {
            case '.':
                return $this->currentPageURL . '/' . (
                    './' === substr($url, 0, 2)
                        ? substr($url, 2)
                        : $url
                    );
            case '#':
                return $this->currentPageURL;
            case '/':
                return $this->getHost('//' === substr($url, 0, 2 )) . $url;
        }

        return $url;
    }

    /**
     * Returns the average number for values in the specified column of all pages
     * @param string $column
     * @return float|int
     */
    private function getAverageFromPages(string $column)
    {
        return count($arr = array_column($this->pages, $column))
            ? array_sum($arr) / count($arr)
            : 0;
    }

    /**
     * Returns the host based on the entry point
     * @param bool $schemeOnly Whether only the scheme (with colon) is returned
     * @return string
     */
    private function getHost(bool $schemeOnly = false): string
    {
        $parsedURL = parse_url($this->entryPointURL);

        return $parsedURL['scheme'] . ':' . ($schemeOnly ? '' : '//' . $parsedURL['host']);
    }

    /**
     * Returns the results for the completed crawl
     * @return array
     */
    private function getResults(): array
    {
        return [
            'entryPoint' => $this->entryPointURL,
            'pages' => $this->pages,
            'average' => [
                'loadSeconds' => $this->getAverageFromPages('loadSeconds'),
                'wordCount' => $this->getAverageFromPages('wordCount'),
                'titleLength' => $this->getAverageFromPages('titleLength'),
            ],
            'uniqueCount' => [
                'externalLinks' => count($this->link['external']),
                'internalLinks' => count($this->link['internal']),
                'images' => count($this->images),
                'pages' => count($this->pages),
            ],
        ];
    }

    /**
     * Cleanup and prepares the HTML for use with XPath
     * @param $content
     * @return array|string|string[]|null
     */
    private function prepareHTML($content)
    {
        $replacements = [
            // remove script tags
            '#<script(.*?)>(.*?)</script>#is' => '',

            // ensure words are separated with spaces
            '#<span(.*?)>(.*?)</span>#is' => ' $2 ',
            '#<div(.*?)>(.*?)</div>#is' => ' $2 ',
        ];

        return preg_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Iterates through the list of nodes to execute the specified method
     * @param string $method
     * @param DOMNodeList $list
     * @return void
     */
    private function saveList(string $method, DOMNodeList $list)
    {
        for ($i = 0, $len = $list->length; $i < $len; $i++) {
            $this->$method($list->item($i));
        }
    }

    /**
     * Saves an image to the image list
     * @param DomElement $image
     * @return void
     */
    private function saveImage(DomElement $image)
    {
        // if a source is found
        if (($src = $image->getAttribute('data-src'))
            || ($src = $image->getAttribute('src'))
        ) {
            // save the source to the list of images
            $this->images[$src] = self::IS_CRAWLED;
        }
    }

    /**
     * Saves a URL to the link list
     * @param DOMElement $link
     * @return void
     */
    private function saveLink(DomElement $link)
    {
        if (($href = $link->getAttribute('href'))) {
            $href = rtrim($this->getAbsoluteURL($href), '/');

            // determine whether link is internal or external
            $type = strpos($href, $this->getHost()) === 0 ? 'internal' : 'external';

            // add the link to the list (ignoring changes if it already exists)
            $this->link[$type][$href] = $this->link[$type][$href] ?? self::NOT_CRAWLED;
        }
    }
}
