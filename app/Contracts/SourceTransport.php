<?php namespace App\Contracts;


interface SourceTransport
{

    /**
     * Fetch the data for a series of URLs.
     *
     * @param array $urls The url(s) to query.
     */
    public function fetch(array $urls = []);

}
