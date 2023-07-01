<?php

namespace App\Repositories;

use App\Common\CommonConst;
use App\Helpers\CommonHelper;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        $this->model = $this->setModel();
    }

    public function setModel()
    {
        return $this->model = app()->make($this->model);
    }

    public function find($id, array $relations = [])
    {
        $entity = $this->model->find($id);

        if (count($relations)) {
            return $entity->load($relations);
        }

        return $entity;
    }

    public function findOrFail($id, $relations = [])
    {
        $entity = $this->model->findOrFail($id);

        if (count($relations)) {
            return $entity->load($relations);
        }

        return $entity;
    }

    public function findByCondition(mixed $condition, array $relations = [], array $relationCounts = [])
    {
        $entities = $this->model->select($this->model->selectable ?? ['*']);

        if (count($relations)) {
            $entities = $entities->with($relations);
        }

        // load relation counts
        if (count($relationCounts)) {
            $entities = $entities->withCount($relationCounts);
        }

        if (count($condition) && method_exists($this, 'search')) {
            foreach ($condition as $key => $value) {
                $entities = $this->search($entities, $key, $value);
            }
        }

        return $entities;
    }

    public function all()
    {
        return $this->model->all();
    }


    public function create(array $attributes)
    {
//        $attributes = $this->stripAllFields($attributes);
        return $this->model->create($attributes);
    }

    public function update($ids, array $attributes)
    {
//        $attributes = $this->stripAllFields($attributes);
        if (is_array($ids)) {
            return $this->model->whereIn('id', $ids)->update($attributes);
        }

        $object = $this->model->findOrFail($ids);
        $object->fill($attributes);
        $object->save();

        return $object;
    }

    public function stripAllFields($fields)
    {
        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                $fields[$key] = $this->stripAllFields($value);
            } else {
                if (is_string($value)) {
                    $fields[$key] = strip_tags($value);
                }
            }
        }

        return $fields;
    }

    public function findAll($columns = ['*'])
    {
        return $this->model::select($columns)->get();
    }

    public function delete(Model $entity)
    {
        $entity->delete();
    }

    public function deleteMulti(array $ids = [])
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function list($condition, $relations = [], $relationCounts = [])
    {
        $condition = CommonHelper::removeNullValue($condition);
        $data = collect($condition);

        // select list column
        $entities = $this->model->select($this->model->selectable ?? ['*']);

        // load relation counts
        if (count($relationCounts)) {
            $entities = $entities->withCount($relationCounts);
        }

        // load relations
        if (count($relations)) {
            $entities = $entities->with($relations);
        }

        // filter list by condition
        if (count($condition) && method_exists($this, 'search')) {
            foreach ($condition as $key => $value) {
                $entities = $this->search($entities, $key, $value);
            }
        }
        // order list
        $orderBy = $data->has('sort') && in_array($data['sort'], $this->model->sortable) ? $data['sort'] : $this->model->getKeyName();
        $entities = $entities->orderBy($orderBy, $data->has('sortType') && $data['sortType'] == 1 ? 'asc' : 'desc');
        // limit result

        $limit = $data->has('limit') ? (int)$data['limit'] : CommonConst::DEFAULT_PER_PAGE;
        if ($limit) {
            return $entities->paginate($limit);
        }

        return $entities->get();
    }

    public function listGroupBy(mixed $data, $select = [], array $relations = [], array $relationCounts = [], bool $isReturnQuery = false)
    {
        $data = collect($data);

        // select list column
        $entities = $this->model->select($select ?? ['*']);
        // load relation counts
        if (count($relationCounts)) {
            $entities = $entities->withCount($relationCounts);
        }

        // load relations
        if (count($relations)) {
            $entities = $entities->with($relations);
        }

        // filter list by condition
        $condition = $data;
        if (count($condition) && method_exists($this, 'search')) {
            foreach ($condition as $key => $value) {
                $entities = $this->search($entities, $key, $value);
            }
        }

        // order list
        $orderBy = $data->has('sort') && in_array($data['sort'], $this->model->sortable) ? $data['sort'] : $this->model->getKeyName();
        $entities = $entities->orderBy($orderBy, $data->has('sortType') && $data['sortType'] == 1 ? 'asc' : 'desc');

        // return query instead of collection or paging
        if ($isReturnQuery) {
            return $entities;
        }

        // limit result
        $limit = $data->has('limit') ? (int)$data['limit'] : CommonConst::DEFAULT_PER_PAGE;
        if ($limit) {
            return $entities->paginate($limit);
        }

        return $entities->get();
    }
}
