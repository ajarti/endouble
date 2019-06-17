<?php namespace App\Services;

use App\Contracts\CacheService;
use App\Contracts\SourceService;
use App\Contracts\SourceTransport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComicsService implements SourceService
{

    protected $baseUrl;
    protected $basePath;
    protected $slug;
    protected $cache;
    protected $indexPath;
    protected $source;
    protected $chunkSize;

    /*
     * The Service for calling the source API.
     */
    protected $sourceCaller;


    /**
     * ComicsService constructor.
     */
    public function __construct()
    {
        // Increase time for cache warming.
        set_time_limit(env('XKCD_TIMEOUT', 300));

        // Set the source config.
        $this->slug      = env('XKCD_SLUG', null);
        $this->baseUrl   = env('XKCD_BASE_URL', null);
        $this->basePath  = env('XKCD_BASE_PATH', null);
        $this->indexPath = env('XKCD_INDEX_PATH', null);
        $this->chunkSize = env('XKCD_CHUNK_SIZE', 20);

        // Resolve the current Source Transport & Cache.
        $this->sourceCaller = resolve(SourceTransport::class);
        $this->cache        = resolve(CacheService::class);

        // Get the current source details and store for reuse.
        $this->source = $this->cache->getSource($this->slug);
    }


    /**
     * Update the cache with the latest remote sources if applicable.
     *
     * @param int $currentIndex
     * @param int $remoteIndex
     */
    private function updateLatestItems(int $currentIndex = 0, int $remoteIndex = 0)
    {

        // Latest Index.
        $latestMaxIndex = $currentIndex;

        // Get the difference in indexes.
        $missingIndexes = $remoteIndex - $currentIndex;

        // Batch URLS to limit size of calls to XKCD, and only fetch indexes we don't have.
        $offsets       = range($currentIndex + 1, $remoteIndex);
        $dbItemIndexes = $this->cache->getIndexes($this->source->id, $offsets);
        $offsets       = array_diff($offsets, $dbItemIndexes->toArray());
        $urlsToFetch   = [];
        foreach ( $offsets as $offset ) {
            array_push($urlsToFetch, $this->baseUrl . str_replace('{index}', $offset, $this->basePath));
        }
        $urlGroups = collect($urlsToFetch)->chunk($this->chunkSize);
        foreach ( $urlGroups as $urls ) {

            // Fetch the items.
            $latestItems = collect($this->sourceCaller->fetch($urls->toArray()));

            // Update the cache.
            if ( is_a($latestItems, Collection::class) && !$latestItems->isEmpty() ) {

                // Make sure we don't have a single item.
                // it's properties will be the array, not an
                // array or items. So flatten.
                if ( !is_array($latestItems->first()) ) {
                    $latestItems = collect([$latestItems]);
                }

                // Check the item is not already cached by getting a list of indexes and querying cache.
                $newItemIndexes = $latestItems->pluck('num');

                if ( !$newItemIndexes->isEmpty() ) {
                    $dbItemIndexes = $this->cache->getIndexes($this->source->id, $newItemIndexes->toArray());
                    $items         = $latestItems->whereNotIn('num', $dbItemIndexes);
                    $itemsToInsert = $items->map(function ($item) use (&$latestMaxIndex) {
                        $date           = str_pad($item['year'], 4, '0', STR_PAD_LEFT) . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($item['day'], 2, '0', STR_PAD_LEFT);
                        $dated_at       = Carbon::createFromFormat('Y-m-d', $date);
                        $index          = ( isset($item['num']) ) ? $item['num'] : 0;
                        $latestMaxIndex = ( $index > $latestMaxIndex ) ? $index : $latestMaxIndex;
                        return [
                            'source_id'  => $this->source->id,
                            'item_index' => $index,
                            'item'       => json_encode($item),
                            'dated_at'   => $dated_at
                        ];
                    });

                    // Inject missing items into cache.
                    if ( !$itemsToInsert->isEmpty() ) {
                        $this->cache->saveToCache($itemsToInsert->toArray());
                    }
                }
            }
        }

        // Update the current source index.
        // Check there was not just a fill operation, i.e. missing indexes were updated when the latest_index was set low.
        // In this case the $latestMaxIndex will be the last updated index, not the highest index in the cache.
        $maxCacheIndex  = $this->cache->maxCacheIndex($this->source->id);
        $latestMaxIndex = ( $latestMaxIndex > $maxCacheIndex ) ? $latestMaxIndex : $maxCacheIndex;
        if ( $latestMaxIndex > $currentIndex ) {
            if (
            !$this->cache->updateSource([
                'id'           => $this->source->id,
                'latest_index' => $latestMaxIndex
            ])
            ) {
                // Alert admin & log, source latest_index saving is failing.
                // Out of scope for this assessment.
            }
        }

    }


    /**
     * Fetches the latest max index for the remote source.
     *
     * @return int
     */
    public function getLatestRemoteIndex()
    {
        // Set a baseline index of 0.
        $latestRemoteIndex = 0;

        // Get the latest source index and ensure a failed fetch does not stop the flow.
        // (A mature API should require a error code returned to the user or just the current cached values)
        try {
            $url          = $this->baseUrl . $this->indexPath;
            $sourceLatest = $this->sourceCaller->fetch([$url]);
            if ( is_array($sourceLatest)
                && isset($sourceLatest['num'])
                && is_numeric($sourceLatest['num'])
            ) {
                $latestRemoteIndex = (int)$sourceLatest['num'];
            }
        } catch ( \Exception $e ) {
            // Alert admin & log, remote fetching is failing.
            // Out of scope for this assessment.
        }

        // Return Latest index.
        return $latestRemoteIndex;
    }


    /**
     * Check to see if the index we have is the latest.
     * If not update the cache.
     *
     * Could also use a scheduled update to reduce the number of queries
     * But this would effect the cache freshness.
     *
     */
    public function updateCache()
    {
        // Get the current latest index.
        $currentIndex = $this->source->latest_index;

        // Get the remote source's latest index.
        $remoteIndex = $this->getLatestRemoteIndex();

        // Check if the current index is < the latest flight number(index)
        // and update accordingly.
        if ( $currentIndex < $remoteIndex ) {
            $latestItems = $this->updateLatestItems($currentIndex, $remoteIndex);
        }

    }

}
