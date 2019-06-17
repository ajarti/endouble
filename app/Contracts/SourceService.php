<?php namespace App\Contracts;

interface SourceService
{

    /*
     * Check if our latest index is the caches latest index.
     */
    public function updateCache();

}
