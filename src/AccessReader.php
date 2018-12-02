<?php
namespace App;

use cli\progress\Bar;
use RuntimeException;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class AccessReader
{
    private $stats = [
        'views' => 0,
        'urls' => 0,
        'traffic' => 0,
        'crawlers' => [
            'Yandex' => 0,
            'Google' => 0,
            'Bing' => 0,
            'Baidy' => 0,
        ],
        'status_codes' => []
    ];

    private $crawlers = [
        'Yandex' => ['yandexbot'],
        'Google' => ['googlebot'],
        'Bing' => ['bindbot'],
        'Baidy' => ['baiduspider'],
    ];

    private $errors = [];
    private $uniqueUrls = [];

    public function process(string $logfile) : void
    {
        $parser = new RegexParser();
        $reader = new FileReader($logfile);
        $pb = new Bar('Parsing access log', $reader->size());

        foreach ($reader->readByLine() as $line) {
            $pb->tick(strlen($line));
            $this->handleLine($line, $parser);
            if (count($this->errors) > 3) {
                \cli\err('There are too many errors');
                break;
            }
        }

        $pb->finish();

        $this->showStats();
        if (!empty($this->errors)) {
            $this->showErrors();
        }
    }

    private function handleLine($lineContent, LogParser $parser) : void
    {
        try {
            $this->updateStats($parser->parse($lineContent));
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    private function updateStats(array $requestInfo) : void
    {
        $url = $this->buildFullUrl($requestInfo['url'], $requestInfo['path']);
        $this->uniqueUrls[$url] = true;

        $this->stats['views']++;
        $this->stats['urls'] = count($this->uniqueUrls);
        $this->stats['traffic'] += $requestInfo['size'];

        if (isset($this->stats['status_codes'][$requestInfo['status']])) {
            $this->stats['status_codes'][$requestInfo['status']]++;
        } else {
            $this->stats['status_codes'][$requestInfo['status']] = 1;
        }

        $crawler = $this->getSearchCrawler($requestInfo['user_agent']);
        if ($crawler === false) {
            return;
        }

        if (isset($this->stats['crawlers'][$crawler])) {
            $this->stats['crawlers'][$crawler]++;
        } else {
            $this->stats['crawlers'][$crawler] = 1;
        }
    }

    private function getSearchCrawler(string $userAgent) : string
    {
        $crawler = false;

        $lowerCaseUserAgent = mb_strtolower($userAgent);
        foreach ($this->crawlers as $engine => $bots) {
            if ($this->isCrawler($lowerCaseUserAgent, $bots)) {
                $crawler = $engine;
                break;
            }
        }

        return $crawler;
    }

    private function isCrawler(string $userAgent, array $crawlerBots) : bool
    {
        $result = false;
        foreach ($crawlerBots as $botName) {
            if (strpos($userAgent, $botName) !== false) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    private function buildFullUrl(string $url, string $path) : string
    {
        $components = parse_url($url);
        if (!isset($components['host'])) {
            throw new RuntimeException('No url given');
        }

        return $components['host'] . $path;
    }

    private function showStats() : void
    {
        \cli\line("-----------------\n");
        \cli\line("Stats: \n" . json_encode($this->stats, JSON_PRETTY_PRINT) . "\n");
    }

    private function showErrors() : void
    {
        \cli\line("-----------------\n");
        \cli\line("Errors: \n" . json_encode($this->errors, JSON_PRETTY_PRINT) . "\n");
    }
}