<?php namespace App\Contracts;

interface CacheService
{

    /**
     * Retrieve items from cache table.
     *
     * @param int $sourceId
     * @param int $year
     * @param int $limit
     */
    public function getFromCache(int $sourceId = 0);


    /**
     * Get an array of items, given an array of indexes.
     *
     * @param int   $sourceId
     * @param array $indexes The list of indexes to retrieve.
     *
     * @return array
     */
    public function getIndexes(int $sourceId = 0, array $indexes = []);


    /**
     * Fetch the source item from the cache.
     *
     * @param string $slug The slug of the source.
     *
     * @return
     * @throws SourceNotFoundException
     */
    public function getSource(string $lug = '');


    /**
     * Get the highest index in the cache.
     *
     * @param int $sourceId
     *
     * @return int
     * @throws SourceNotFoundException
     */
    public function maxCacheIndex(int $sourceId = 0);


    /**
     * Store items to cache table.
     * Uses insert to bulk insert.
     *
     * @param array $items
     *
     * @return boolean
     */
    public function saveToCache(array $items = []);


    /**
     * Sets the limit for the query.
     *
     * @param int $limit
     *
     * @return mixed
     */
    public function setQueryLimit(int $limit);


    /**
     * Sets the offset for the query.
     *
     * @param int $offset
     *
     * @return mixed
     */
    public function setQueryOffset(int $offset);


    /**
     * Sets the year for the query.
     *
     * @param int $year
     *
     * @return mixed
     */
    public function setQueryYear(int $year);


    /**
     * Persist source item to the cache.
     *
     * @param array $source The source data to update.
     *
     * @return boolean
     * @throws SourceNotFoundException
     */
    public function updateSource(array $source = []);

}
