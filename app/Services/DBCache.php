<?php namespace App\Services;

use App\Contracts\CacheService;
use App\Exceptions\SourceNotFoundException;
use App\Models\Cache;
use App\Models\Source;
use Illuminate\Database\Eloquent\Collection;

class DBCache implements CacheService
{

    /**
     * Query limit.
     *
     * @var int
     */
    protected $limit;

    /**
     * Query offset.
     *
     * @var int
     */
    protected $offset;

    /**
     * Query year.
     *
     * @var int|null
     */
    protected $targetYear;


    /**
     * DBCache constructor.
     */
    public function __construct()
    {
        // Clear filters on creation.
        $this->resetFilters();
    }


    /**
     * Retrieve items from cache table.
     *
     * @param int $sourceId
     *
     * @return Collection
     */
    public function getFromCache(int $sourceId = 0)
    {
        // Start the query.
        $query = Cache::whereSourceId($sourceId);

        // Check if we should filter by year.
        if ( !is_null($this->targetYear) && is_numeric($this->targetYear) ) {
            $query->whereYear('dated_at', '=', $this->targetYear);
        }

        // Check if we should limit.
        if ( !is_null($this->limit) && is_numeric($this->limit) ) {
            $query->limit($this->limit);
        }

        // Check if an offset is required.
        if ( !is_null($this->offset) && is_numeric($this->offset) ) {
            $query->offset($this->offset);
        }

        // Order by.
        $query->orderBy('item_index');

        // Return the results.
        return $query->get();

    }


    /**
     * Get an array of items, given an array of indexes.
     *
     * @param int   $sourceId
     * @param array $indexes The list of indexes to retrieve.
     *
     * @return array
     * @throws SourceNotFoundException
     */
    public function getIndexes(int $sourceId = 0, array $indexes = [])
    {
        // Make sure the source is a valid one.
        $source = Source::find($sourceId);
        if ( is_a($source, Source::class) ) {
            return Cache::whereSourceId($sourceId)->whereIn('item_index', $indexes)->get()->pluck('item_index');
        } else {
            throw new SourceNotFoundException('Cache index Source not found.');
            // Alert admin & log, invalid source.
            // Out of scope for this assessment.
        }
    }


    /**
     * Fetch the source item from the cache.
     *
     * @param string $slug The slug of the source.
     *
     * @return
     * @throws SourceNotFoundException
     */
    public function getSource(string $slug = 'bad-slug')
    {
        $source = Source::whereSlug($slug)->first();
        if ( !is_a($source, Source::class) ) {
            throw new SourceNotFoundException('Source not found.');
        } else {
            return $source;
        }
    }


    /**
     * Get the highest index in the cache.
     *
     * @param int $sourceId
     *
     * @return int
     * @throws SourceNotFoundException
     */
    public function maxCacheIndex(int $sourceId = 0)
    {
        // Make sure we have a valid source.
        if ( is_a(Source::whereId($sourceId)->first(), Source::class) ) {

            // Return the max index or 0.
            $maxIndex = Cache::whereSourceId($sourceId)->max('item_index');
            return ( !is_null($maxIndex) ) ? $maxIndex : 0;
        } else {
            throw new SourceNotFoundException('Source not found.');
        }

    }


    /**
     * Reset the query filters.
     */
    public function resetFilters()
    {
        $this->offset     = 0;
        $this->limit      = 25;
        $this->targetYear = null;
    }


    /**
     * Store items to cache table.
     * Uses insert to bulk insert.
     *
     * @param array $items
     *
     */
    public function saveToCache(array $items = [])
    {
        try {
            Cache::insert($items);
        } catch ( \Exception $e ) {
            // Alert admin & log, cache saving is failing.
            // Out of scope for this assessment.
        }
    }


    /**
     * Sets the limit for the query.
     *
     * @param int $limit
     *
     * @return mixed
     */
    public function setQueryLimit(int $limit)
    {
        $this->limit = (int) $limit;
    }


    /**
     * Sets the offset for the query.
     *
     * @param int $offset
     *
     * @return mixed
     */
    public function setQueryOffset(int $offset)
    {
        $this->offset = (int) $offset;
    }


    /**
     * Sets the year for the query.
     *
     * @param int $year
     *
     * @return mixed
     */
    public function setQueryYear(int $year)
    {
        $this->targetYear = (int) $year;
    }


    /**
     * Persist source item to the cache.
     *
     * @param array $source The source data to update.
     *
     * @return boolean
     * @throws SourceNotFoundException
     */
    public function updateSource(array $source = [])
    {
        if ( !empty($source) && isset($source['id']) ) {
            $currentSource = Source::whereId($source['id'])->first();
            if ( is_a($currentSource, Source::class) ) {
                $currentSource->fill($source);
                return $currentSource->save();
            } else {
                throw new SourceNotFoundException('Source not found.');
            }
        }

        // Something failed.
        return false;
    }


}
