<?php

namespace App\Http\Controllers;

use App\Contracts\CacheService;
use App\Http\Requests\SourceRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SourceController extends Controller
{


    /**
     * The Source service.
     */
    protected $cache;

    /**
     * The Cache service.
     */
    protected $source;

    /**
     * The Source transformer.
     */
    protected $transformer;


    /**
     * SourceController constructor.
     */
    public function __construct()
    {
        // Resolve the Cache.
        $this->cache = resolve(CacheService::class);
    }


    /**
     * Update the cache from the live source and execute the query
     * against the cache.
     *
     * @param SourceRequest $request
     * @param string        $slug
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function proxySource(SourceRequest $request, $slug = '')
    {

        // Get the current Source, pre-validated in SourceRequest.
        $this->source = $this->cache->getSource($slug);

        // Check the required service is registered.
        try {
            $source = resolve(strtolower($slug) . 'Service');
        } catch ( \Exception $e ) {
            return response()->json(['error' => $slug . ' is an invalid source.'], 400);
            // Alert admin & log, Source Service exists, but not registered in AppServiceProvider.
            // Out of scope for this assessment.
        }

        // Check the required transformer is available.
        $transformerClass = '\\App\\Http\\Resources\\' . $slug . 'Transformer';
        if ( class_exists($transformerClass) ) {
            $this->transformer = new $transformerClass(Model::class);
        } else {
            return response()->json(['error' => $slug . ' requires a source transformer.'], 400);
            // Alert admin & log, Source Transformer does not exist.
            // Out of scope for this assessment.
        }

        // Check the cache against the last item on the remote API.
        // If need be update the local cache items.
        // This is a live check that would trigger on each request.
        // It could be replaced by a schedule that triggers cache updates every X
        // But this would be at the expense of the freshness of the results.
        $source->updateCache();

        // Return the requested items using query params.
        $this->cache->resetFilters();

        // Offset.
        if ( $request->has('offset') ) {
            $this->cache->setQueryOffset($request->get('offset', 0));
        }

        // Limit.
        if ( $request->has('limit') ) {
            $this->cache->setQueryLimit($request->get('limit', env('SOURCE_LIMIT', 10)));
        }

        // Year.
        if ( $request->has('year') ) {
            $this->cache->setQueryYear($request->get('year'));
        }

        // Transform response.
        return $this->transformer::collection($this->cache->getFromCache($this->source->id))->additional([
            'meta' => [
                'request'   => [
                    'sourceId' => $slug,
                    'year'     => $request->get('year', ''),
                    'limit'    => $request->get('limit', ''),
                    'offset'   => $request->get('offset', '')
                ],
                'timestamp' => Carbon::now()
            ]
        ]);

    }

}
