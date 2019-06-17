<?php namespace App\Services;

use App\Contracts\SourceTransport;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as ExternalRequest;

class GuzzleTransport implements SourceTransport
{

    /*
     * The max number of concurrent parallel calls.
     */
    protected $concurrency;


    /*
     * The HTTP client.
     */
    protected $httpClient;


    /**
     * GuzzleCaller constructor.
     */
    public function __construct()
    {
        $this->concurrency = env('GUZZLE_CONCURRENCY', 10);
        $this->httpClient  = new Client();
    }


    /**
     * Fetch the data from the URL in question passing the filters as a querystring.
     *
     * @param array $urls Array of url to query.
     *
     * @return array
     */
    public function fetch(array $urls = [])
    {
        // Determine url count.
        $multiCall = ( count($urls) > 1 );

        // Returned item(s) store.
        $items = [];

        // Ensure any network or 3rd party lib failures do not halt the response.
        try {

            // Generate the requests.
            $requests = function ($urls) {
                foreach ( $urls as $url ) {
                    yield new ExternalRequest("GET", $url);
                }
            };

            // Run concurrent requests.
            $pool = new Pool($this->httpClient, $requests($urls), [
                'concurrency' => $this->concurrency,
                'fulfilled'   => function ($response, $index) use (&$items, $multiCall) {
                    if ( $response->getStatusCode() == 200 ) {

                        // Parse Body.
                        $body     = $response->getBody() ?? null;
                        $itemJSON = $body->getContents();
                        $item     = json_decode($itemJSON ?? [], true);

                        // Make sure we have a decoded object.
                        if ( !is_null($item) ) {

                            // Check if single fetch or batch.
                            if ( $multiCall ) {
                                array_push($items, $item);
                            } else {
                                $items = $item;
                            }

                        } else {
                            // Alert admin & log, remote json object cannot be parsed, problem?
                            // Out of scope for this assessment.
                        }

                    } else {
                        // Alert admin & log, remote fetching is failing.
                        // Out of scope for this assessment.
                    }
                }
            ]);

            // Initiate the transfers and create a promise.
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();

        } catch ( \Exception $e ) {
            // Alert admin & log, remote fetching is failing.
            // Out of scope for this assessment.
        }

        // Return the collection.
        return $items;

    }

}

