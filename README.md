```
 _     _  _____  ______  _______  _____   _____  _______
 |____/  |     | |     \ |______ |     | |_____] |______
 |    \_ |_____| |_____/ |______ |_____| |       ______|
 
```
 

# Laravel Strapi Wrapper

This package is a wrapper to make REST API calls to [Strapi](https://docs.strapi.io/developer-docs/latest/getting-started/introduction.html).

## Making a request

The REST API allows accessing the content-types through API endpoints. Strapi automatically creates API endpoints when a content-type is created. API parameters can be used when querying API endpoints to refine the results.

[REST API Documentation](https://docs.strapi.io/developer-docs/latest/developer-resources/database-apis-reference/rest-api.html)

```
use kodeops\LaravelStrapi\Strapi;

$collection = 'tgam-artist';
$params = [
    'populate' => 'deep',
];
$loop_results = true;

Strapi::request($collection, $params, $loop_results);
```

Update a collection item:

```
use kodeops\LaravelStrapi\Strapi;

$collection = 'tgam-artist';
$params = [
    'data' => [
        'title' => A title for the collection item',
    ],
];

Strapi::update($collection, $params);
```

