<?php
namespace kodeops\LaravelStrapi;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Http;
use Exception;

class Strapi
{
    public static function request(
        string $collection, 
        array $params = [], 
        $loop_results = false
    )
    {
        if (! env('STRAPI_TOKEN')) {
            throw new Exception("STRAPI_TOKEN must be defined");
        }

        if (! env('STRAPI_URL')) {
            throw new Exception("STRAPI_URL must be defined");
        }

        if (! isset($params['pagination[pageSize]'])) {
            $params['pagination[pageSize]'] = 100;
        }

        $url = env('STRAPI_URL') . "/api/{$collection}?" . http_build_query($params);
        $cache_key = "strapi." . sha1($url);
        $response = Http::withToken(env('STRAPI_TOKEN'))->get($url);
        $cache = Activity::where('description', $cache_key)->orderBy('created_at', 'DESC')->first();

        if ($response->failed()) {
            // If response failed try to use cached
            if (! $cache) {
                throw new Exception("Could not fetch strapi: {$url}");
            }
            return $cache->properties;
        }

        $response = $response->json();

        if (
            ! $loop_results
            OR
            $response['meta']['pagination']['pageCount'] == $response['meta']['pagination']['page']
            OR
            ! $response['meta']['pagination']['pageCount']
        ) {
            self::storeCache($cache, $cache_key, $response);
            return $response;
        }

        $data = $response['data'];
        $loop = 1;
        while (true) {
            $params['pagination[page]'] = $response['meta']['pagination']['page'] + 1;
            $response = self::request($collection, $params, false);
            $data = array_merge($data, $response['data']);
            if ($response['meta']['pagination']['pageCount'] == $response['meta']['pagination']['page']) {
                break;
            }
        }

        $response['data'] = $data;

        self::storeCache($cache, $cache_key, $response);

        return $response;
    }

    public static function storeCache(
        Activity $cache = null, 
        string $cache_key, 
        array $data
    )
    {
        if ($cache) {
            $cache->update(['properties' => $data]);
        } else {
            activity()
                ->withProperties($data)
                ->log($cache_key);
        }
    }
}