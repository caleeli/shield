<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ResourceTrait
{

    private function getModel($modelName)
    {
        $model = 'App\\Models\\' . ucfirst(Str::camel($modelName));
        if (!class_exists($model)) {
            $model = 'App\\Models\\' . ucfirst(Str::camel(Str::singular($modelName)));
        }
        if (!class_exists($model)) {
            $model = 'App\\Models\\' . ucfirst(Str::camel(substr($modelName, 0, -2)));
        }
        if (!class_exists($model)) {
            throw new Exception('Model not found for '. $modelName);
        }
        return $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  string  $module
     * @param  string  $modelName
     * @return \Illuminate\Http\Response
     */
    private function indexFromQuery($query, Request $request)
    {
        if (!($query instanceof Builder)) {
            return $query;
        }
        // implement fields
        $fields = $request->input('fields');
        if ($fields) {
            $fields = explode(',', $fields);
            $query->select($fields);
        }
        // implement include
        $include = $request->input('include');
        if ($include) {
            $include = explode(',', $include);
            // convert to camel case
            $include = array_map(function ($value) {
                return Str::camel($value);
            }, $include);
            $query->with($include);
        }
        // implement sort following JSON API spec
        // https://jsonapi.org/format/#fetching-sorting
        $sort = $request->input('sort');
        if ($sort) {
            $sort = explode(',', $sort);
            // Check if has sort sign
            if (count($sort) >= 1) {
                foreach ($sort as $key => $value) {
                    if (substr($value, 0, 1) === '-') {
                        $query->orderBy(substr($value, 1), 'desc');
                    } elseif (substr($value, 0, 1) === '+') {
                        $query->orderBy(substr($value, 1), 'asc');
                    } else {
                        $query->orderBy($value);
                    }
                }
            }
        }
        // add filter
        $query = $this->filter($request, $query);
        // implement pagination
        $perPage = $request->input('per_page', 10);
        return $query->paginate($perPage);
    }

    /**
     * Implement filter following JSON API spec
     * https://jsonapi.org/format/#fetching-filtering
     *
     * filter=filterName(jsonEncodedParam1,jsonEncodedParam2,...)
     *
     * Examples:
     *  - /api/v1/posts?filter[]=byName("name")
     *  - /api/v1/posts?filter[]=byName("name")&filter[]=byAuthor("author")
     *  - /api/v1/posts?filter[]=byRank(21,41)
     */
    private function filter(Request $request, Builder $query): Builder
    {
        // get array of filters from query string
        $filters = $request->input('filter');
        if (empty($filters)) {
            return $query;
        }
        // parse each filter
        foreach ($filters as $filter) {
            // get filter name
            $filterName = substr($filter, 0, strpos($filter, '('));
            // get filter params
            $filterParams = substr($filter, strpos($filter, '(') + 1, -1);
            // decode filter params
            $filterParams = json_decode('['.$filterParams.']', true);
            // check if scope exists
            $scopeName = 'scope' . ucfirst($filterName);
            if (method_exists($query->getModel(), $scopeName)) {
                $query = $query->$filterName(...$filterParams);
            } else {
                // call filter method
                $query = $query->$filterName(...$filterParams);
            }
        }
        return $query;
    }
}
