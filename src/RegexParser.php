<?php

namespace App;

/**
 * @author Gadel Raymanov <raymanovg@gmail.com>
 */
class RegexParser implements LogParser
{
    const REQUEST_KEY = 'request';
    const STATUS_KEY = 'status';
    const URL_KEY = 'url';
    const USER_AGENT_KEY = 'user_agent';

    private $pattern = '/^.*"(?<%s>.*HTTP\/[\d.]{3}).*"\s?(?<%s>[\s\d-]*)\s+"(?<%s>[\S]*)"\s?"(?<%s>.*)"$/';

    public function __construct()
    {
        $this->pattern = sprintf(
            $this->pattern,
            self::REQUEST_KEY,
            self::STATUS_KEY,
            self::URL_KEY,
            self::USER_AGENT_KEY
        );
    }

    public function parse(string $data) : array
    {
        if (!preg_match($this->pattern, $data, $matches)) {
            throw new \RuntimeException('Cannot parse this data');
        }

        return $this->build($matches);
    }

    protected function getRequestPath(string $request)
    {
        if (!preg_match('/(?<path>\/\w+.*)\s+/', $request, $matches)) {
            throw new \RuntimeException('Cannot retrieve request path');
        }

        return $matches['path'];
    }

    protected function build($matches) : array
    {
        $path = $this->getRequestPath($matches[self::REQUEST_KEY]);
        $statusInfo = str_replace('-', '', $matches[self::STATUS_KEY]);
        [$statusCode, $requestSize] = explode(' ', $statusInfo);

        return [
            'status_code' => (int) $statusCode,
            'request_path' => $path,
            'request_size' => (int) $requestSize,
            'url' => $matches[self::URL_KEY],
            'user_agent' => $matches[self::USER_AGENT_KEY]
        ];
    }
}