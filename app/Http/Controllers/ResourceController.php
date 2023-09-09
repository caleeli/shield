<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelNotFoundException;
use App\Http\Requests\JsonApiRequest;
use App\Http\Resources\JsonApiResource;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    use ResourceTrait;

    /**
     * Display a listing of the resource.
     *
     * @param  string  $module
     * @param  string  $modelName
     * @return \Illuminate\Http\Response
     */
    public function index($modelName, Request $request)
    {
        $model = $this->getModel($modelName);
        $query = (new $model)->query();
        return $this->indexFromQuery($query, $request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  JsonApiRequest  $request
     * @return JsonApiResource
     */
    public function store($modelName, Request $request)
    {
        $model = $this->getModel($modelName);
        $data = $request->all();
        $resource = $model::create($data);
        $resource->refresh();
        return $resource->toArray();
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($modelName, $id)
    {
        $model = $this->getModel($modelName);
        $resource = $model::find($id);
        if (!$resource) {
            throw new Exception('Resource not found', 404);
        }
        return $resource->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  string $modelName
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @param  Request  $request
     */
    public function update($modelName, $id, Request $request)
    {
        $model = $this->getModel($modelName);
        $resource = $model::find($id);
        if (!$resource) {
            throw new Exception('Resource not found', 404);
        }
        $data = $request->all();
        $resource->update($data);
        return response()->json($resource, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy($modelName, $id)
    {
        $model = $this->getModel($modelName);
        $resource = $model::find($id);
        if (!$resource) {
            throw new Exception('Resource not found');
        }
        $resource->delete();
        return response()->json(null, 204);
    }

    public function indexChildren($modelName, $id, $children, Request $request)
    {
        $model = $this->getModel($modelName);
        $query = $model::find($id)->$children();
        return $this->indexFromQuery($query, $request);
    }

    public function storeChildren($modelName, $id, $children, Request $request)
    {
        $model = $this->getModel($modelName);
        $owner = $model::find($id)->$children();
        $data = $request->all();
        $resource = $owner->create($data);
        $resource->refresh();
        return $resource->toArray();
    }

    public function destroyChildren($modelName, $id, $children, $childId)
    {
        $model = $this->getModel($modelName);
        $owner = $model::find($id)->$children();
        $resource = $owner->find($childId);
        if (!$resource) {
            throw new Exception('Resource not found');
        }
        $resource->delete();
        return response()->json(null, 204);
    }
}
