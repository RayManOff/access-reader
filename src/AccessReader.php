<?php
namespace App;

use cli\progress\Bar;
use RuntimeException;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class AccessReader
{
    protected $stats = [
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

    protected $crawlers = [
        'Yandex' => ['YandexBot'],
        'Google' => ['Googlebot'],
        'Bing' => ['bindbot'],
        'Baidy' => ['Baiduspider'],
    ];

    protected $errors = [];
    protected $uniqueUrls = [];

    public function process(string $logfile)
    {
        $parser = new RegexParser();
        $reader = new FileReader($logfile);
        $pb = new Bar('Parsing', $reader->size());

        foreach ($reader->readByLine() as $line) {
            $pb->tick(strlen($line));

            try {
                $this->handleLine($line, $parser);
            } catch (\Exception $e) {
                error_log($e->getMessage());
                break;
            }
        }

        $pb->finish();

        $this->showStats();
        $this->showErrors();
    }

    private function handleLine($lineContent, LogParser $parser)
    {
        try {
            $this->updateStats($parser->parse($lineContent));
        } catch (\Exception $e) {
            if (count($this->errors) > 0) {
                throw new RuntimeException('Stop parsing. There are too many error');
            }
            $this->errors[] = $e->getMessage();
        }
    }

    protected function updateStats(array $requestInfo)
    {
        $url = $this->buildFullUrl($requestInfo['url'], $requestInfo['request_path']);
        $this->uniqueUrls[$url] = true;

        $this->stats['views']++;
        $this->stats['urls'] = count($this->uniqueUrls);
        $this->stats['traffic'] += $requestInfo['request_size'];

        if (isset($this->stats['status_codes'][$requestInfo['status_code']])) {
            $this->stats['status_codes'][$requestInfo['status_code']]++;
        } else {
            $this->stats['status_codes'][$requestInfo['status_code']] = 1;
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

    protected function getSearchCrawler(string $userAgent) {
        $crawler = false;
        foreach ($this->crawlers as $engine => $bots) {
            foreach ($bots as $botName) {
                if (strpos($userAgent, $botName) !== false) {
                    $crawler = $engine;
                    break;
                }
            }
        }

        return $crawler;
    }

    protected function buildFullUrl(string $url, string $path) : string
    {
        $components = parse_url($url);
        if (!isset($components['host'])) {
            throw new RuntimeException('No url given');
        }

        return $components['host'] . $path;
    }

    protected function showStats()
    {
        echo "-----------------\n";
        echo "Stats: \n" . json_encode($this->stats, JSON_PRETTY_PRINT) . "\n";
        echo "-----------------\n";
    }

    protected function showErrors()
    {
        echo "-----------------\n";
        echo "Errors: \n" . json_encode($this->errors, JSON_PRETTY_PRINT) . "\n";
        echo "-----------------\n";
    }
}