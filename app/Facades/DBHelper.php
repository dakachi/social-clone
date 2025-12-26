<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;

class DBHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dbhelper';
    }

    protected static function saveData($table, array $rules, array $data, array $insertOnlyFields = [], array $customMessages = [], array $customRules = [], $uniqueField = 'id_secure')
    {
        // Merge custom rules into the provided rules.
        foreach ($customRules as $field => $rule) {
            if (!isset($rules[$field])) {
                $rules[$field] = [];
            }

            if (is_string($rules[$field])) {
                $rules[$field] = explode('|', $rules[$field]);
            }

            if (is_array($rule)) {
                $rules[$field] = array_merge($rules[$field], $rule);
            } else {
                $rules[$field][] = $rule;
            }
        }

        // Validate the provided data against the specified rules and custom messages.
        $validator = Validator::make($data, $rules, $customMessages);
        if ($validator->fails()) {
            // Return an error response if validation fails.
            return [
                'status'  => 0,
                'message' => $validator->errors()->first()
            ];
        }

        
        // Determine whether $table is a table name or an Eloquent Model.
        $isModel = false;
        $query = null;
        if (is_string($table) && class_exists($table) && is_subclass_of($table, Model::class)) {
            $isModel = true;
            $query = $table::query();
        } elseif ($table instanceof Model) {
            $isModel = true;
            $query = $table->newQuery();
        }

        $castedFields = [];
        if ($isModel) {
            $modelInstance = is_string($table) ? new $table : $table;
            $castedFields = $modelInstance->getCasts();
        }

        // Convert any array value in $data into JSON.
        foreach ($data as $key => $value) {
            if ((is_array($value) || is_object($value))) {
                // Bỏ qua nếu là field đã khai báo trong casts (Eloquent tự xử lý)
                if ($isModel && array_key_exists($key, $castedFields)) {
                    continue;
                }
                $data[$key] = json_encode($value);
            }
        }

        // Retrieve an existing record based on the unique field, if provided.
        $item = null;
        if (!empty($uniqueField) && isset($data[$uniqueField])) {
            if ($isModel) {
                $item = $query->where($uniqueField, $data[$uniqueField])->first();
            } else {
                $item = DB::table($table)
                          ->where($uniqueField, $data[$uniqueField])
                          ->first();
            }
        }

        $id = null;
        if ($item) {
            foreach ($insertOnlyFields as $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }
            }
            if ($isModel) {
                $item->update($data);
                $id = $item->id; // Capture the updated record's ID
            } else {
                DB::table($table)->where('id', $item->id)->update($data);
                $id = $item->id; // Capture the updated record's ID
            }
        } else {
            if (!empty($uniqueField) && !isset($data[$uniqueField])) {
                $data[$uniqueField] = rand_string(); // Ensure your helper function rand_string() exists.
            }
            if ($isModel) {
                $newItem = $table::create($data);
                $id = $newItem->id; // Capture the inserted record's ID
            } else {
                $id = DB::table($table)->insertGetId($data); // Capture the inserted record's ID
            }
        }

        return [
            'status'  => 1,
            'id'      => $id,
            'message' => 'Succeed'
        ];
    }

    /**
     * Update a specific field for a set of records in an Eloquent model or table.
     *
     * This method allows you to update the 'status' field or any other field. When updating the
     * 'status' field, the value 'enable' will be mapped to 1; any other value defaults to 0.
     *
     * @param  mixed   $modelOrTable    Either an Eloquent model (class name or instance) or a table name.
     * @param  mixed   $ids             A single id or an array of ids for which the field should change.
     * @param  string  $field           The field to update (default is 'status').
     * @param  mixed   $value           The new value for the field. For 'status' field, 'enable' maps to 1, otherwise 0.
     * @param  string  $idField         The field used to filter the records (default 'id_secure').
     * @param  array   $extraConditions Additional query conditions as key-value pairs.
     * @return Array
     */
    protected static function updateField($modelOrTable, $ids, $field, $value, array $extraConditions = [], $idField = 'id_secure', )
    {
        // Ensure that IDs are provided.
        if (empty($ids)) {
            return [
                "status"  => 0,
                "message" => __("Please select at least one item"),
            ];
        }
        
        // Make sure $ids is an array.
        if (is_string($ids)) {
            $ids = [$ids];
        }
        
        // Filter out any id equal to 0.
        $id_arr = [];
        foreach ($ids as $id) {
            if ($id != 0) {
                $id_arr[] = $id;
            }
        }
        
        // If updating the 'status' field, map 'enable' to 1; otherwise, default to 0.
        // For other fields, directly update using the provided value.
        if ($field === 'status' && ($value == 'enable' || $value == "disable")) {
            $newValue = ($value === 'enable') ? 1 : 0;
        } else {
            $newValue = $value;
        }
        
        // Determine whether $modelOrTable is an Eloquent model or a table name.
        if (is_string($modelOrTable) && class_exists($modelOrTable) && is_subclass_of($modelOrTable, Model::class)) {
            // Treat $modelOrTable as a model class name.
            $query = $modelOrTable::query();
        } elseif ($modelOrTable instanceof Model) {
            // Treat $modelOrTable as an instance of a model.
            $query = $modelOrTable->newQuery();
        } else {
            // Otherwise, treat it as a table name.
            $query = DB::table($modelOrTable);
        }
        
        // Apply any extra conditions provided.
        foreach ($extraConditions as $key => $conditionValue) {
            $query->where($key, $conditionValue);
        }
        
        // Update the specific field for the records matching the filtered IDs.
        $query->whereIn($idField, $id_arr)->update([$field => $newValue]);
        
        return [
            'status' => 1,
            'message' => 'Succeed'
        ];
    }

    /**
     * Delete records from an Eloquent model or table based on given identifiers.
     *
     * This method retrieves the list of IDs (or a single ID) to delete, filters out any zero
     * values, applies any additional conditions, and then performs deletion. When using an Eloquent model,
     * each record is deleted individually to ensure model events are triggered.
     *
     * @param  mixed  $modelOrTable    Either an Eloquent model (class name or instance) or a table name.
     * @param  mixed  $ids             A single id or an array of ids to be deleted.
     * @param  string $idField         The field used to filter the records (default 'id_secure').
     * @param  array  $extraConditions Additional query conditions as key-value pairs.
     * @return Array
     */
    protected static function destroy($modelOrTable, $ids, array $extraConditions = [], $idField = 'id_secure')
    {
        // Ensure that IDs are provided.
        if (empty($ids)) {
            return [
                "status"  => 0,
                "message" => __("Please select at least one item"),
            ];
        }

        // Convert a single string ID to an array.
        if (is_string($ids)) {
            $ids = [$ids];
        }

        // Filter out IDs that are equal to 0.
        $id_arr = array_filter($ids, function ($value) {
            return $value != 0;
        });

        // Determine whether $modelOrTable is an Eloquent model or a table name.
        $isEloquent = false;
        $deletedIds = []; // Array to store deleted IDs
        if (is_string($modelOrTable) && class_exists($modelOrTable) && is_subclass_of($modelOrTable, Model::class)) {
            $isEloquent = true;
            $query = $modelOrTable::query();
        } elseif ($modelOrTable instanceof Model) {
            $isEloquent = true;
            $query = $modelOrTable->newQuery();
        } else {
            $query = DB::table($modelOrTable);
        }

        // Apply any extra conditions provided.
        foreach ($extraConditions as $key => $value) {
            $query->where($key, $value);
        }

        // Retrieve all `id` values of the records to be deleted.
        if ($isEloquent) {
            // For Eloquent, retrieve the `id` values before deletion.
            $items = $query->whereIn($idField, $id_arr)->get(['id']);
            foreach ($items as $item) {
                $deletedIds[] = $item->id; // Capture the `id` of the record
                $item->delete(); // Trigger model events during deletion
            }
        } else {
            // For tables, use `pluck` to get the `id` values before deletion.
            $deletedIds = $query->whereIn($idField, $id_arr)->pluck('id')->toArray();
            $query->whereIn($idField, $id_arr)->delete();
        }

        // Return a success response with all deleted `id` values.
        return [
            "status"      => 1,
            "ids" => $deletedIds, // Return all deleted IDs
            "message"     => __("Succeed")
        ];
    }

}
