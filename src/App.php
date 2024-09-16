<?php

namespace Amne\Phpasync;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils as PromiseUtils;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class App
{
    public function __construct(public ClientInterface $client)
    {
    }
    /**
     * @param callable(): mixed $cb
     */
    private function doRequest(string $data, int $delay, callable $cb): Promise
    {
        $req = new Request(method: 'GET', uri: 'https://httpbin.org/delay/'.$delay.'?data='.$data);

        return $this->client->sendAsync($req)->then(function (Response $response) use ($req, $cb) {
            if ($response->getStatusCode() === 200) {
                $resp = json_decode($response->getBody()->getContents(), true);
                echo 'resp --> '.print_r($resp, true)."\n";

                $cb($resp['args']['data']);
            }
        });
    }


    public function testAsync(): bool
    {
        $expected = 'abc-def';
        $stubs = ['def', 'abc'];
        $respStubs = [];
        $populateResponseCallback = function ($data) use (&$respStubs) {
            $respStubs[] = $data;
        };
        $promises = [];

        // first request - send "def"
        $promises[] = $this->doRequest($stubs[0], 5, $populateResponseCallback);
        // second request - send "abc" but this should finish first
        $promises[] = $this->doRequest($stubs[1], 4, $populateResponseCallback);

        PromiseUtils::all($promises)->wait();

        $response = implode('-', $respStubs);

        echo 'Comparing '.$expected.' to '.$response."\n";

        assert($expected === $response, 'Responses received in wrong order');

        return true;
    }
}
