<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Spatie\Crawler\Crawler;

beforeEach(function () {
    $this->mockHandler = new \GuzzleHttp\Handler\MockHandler([
        new Response(200, [], "User-agent: *\nDisallow: /admin"),
        new Response(200, [], '<html><body>Home</body></html>'),
    ]);

    $this->crawledUrls = [];
    $this->history = Middleware::history($this->crawledUrls);

    $this->handlerStack = HandlerStack::create($this->mockHandler);
    $this->handlerStack->push($this->history);
});

it('should send the correct user agent header when fetching robots.txt', function () {
    $client = new Client(['handler' => $this->handlerStack]);
    $crawler = new Crawler($client);
    $crawler->respectRobots()->startCrawling('http://example.com');

    expect($this->crawledUrls)->toHaveCount(2);
    expect((string) $this->crawledUrls[0]['request']->getUri())->toBe('http://example.com/robots.txt');
    expect($this->crawledUrls[0]['request']->getHeader('User-Agent'))->toBe(['GuzzleHttp/7']);
});

it('should send the custom user agent header when fetching robots.txt', function () {
    $client = new Client(['handler' => $this->handlerStack]);
    $crawler = new Crawler($client);
    $crawler->respectRobots()->setUserAgent('CustomBot/2.0')->startCrawling('http://example.com');

    expect($this->crawledUrls)->toHaveCount(2);
    expect((string) $this->crawledUrls[0]['request']->getUri())->toBe('http://example.com/robots.txt');
    expect($this->crawledUrls[0]['request']->getHeader('User-Agent'))->toBe(['CustomBot/2.0']);
});
