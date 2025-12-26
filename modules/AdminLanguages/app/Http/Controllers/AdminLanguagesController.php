<?php

namespace Modules\AdminLanguages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AdminLanguages\Models\Languages;
use Modules\AdminLanguages\Models\LanguageItems;
use Illuminate\Validation\Rule;

class AdminLanguagesController extends Controller
{
    public $table;
    public $modules;
    public $Datatable;
    public $DatatableTranslations;
    public function __construct()
    {
        $this->Datatable = [
            "element" => "DataTable",
            "columns" => false,
            "order" => ['created', 'desc'],
            "lengthMenu" => [10, 25, 50, 100, 150, 200],
            "search_field" => ["name", "code", "dir", "icon"],
            "columns" => [
                [ 
                    "data" => 'id_secure',
                    'name' => 'id_secure',
                    'className' => 'w-40'
                ],
                [ 
                    "data" => 'name',
                    "name" => "name",
                    'title' => __('Name'),
                ],
                [ 
                    "data" => 'code',
                    "name" => "code",
                    'title' => __('Code'),
                ],
                [ 
                    "data" => 'icon',
                    "name" => "icon",
                    'title' => __('Icon'),
                ],
                [ 
                    "data" => 'dir',
                    "name" => "dir",
                    'title' => __('Text direction'),
                ],
                [ 
                    "data" => 'is_default',
                    "name" => "is_default",
                    'title' => __('Default'),
                ],
                [ 
                    "data" => 'auto_translate',
                    "name" => "auto_translate",
                    'title' => __('Auto Translate'),
                ],
                [ 
                    "data" => 'status', 
                    "name" => "status", 
                    'title' => __('Status'),
                ],
                [ 
                    'data' => 'changed', 
                    'title' => __('Actions'),
                    'className' => 'text-center'
                ],
            ],
            'status_filter' => [
                ['value' => '-1', 'label' => __('All')],
                ['value' => '1', 'name' => 'enable', 'icon' => 'fa-light fa-eye', 'color' => 'success', 'label' => __('Enable')],
                ['value' => '0', 'name' => 'disable', 'icon' => 'fa-light fa-eye-slash', 'color' => 'light', 'label' => __('Disable')],
            ],
            'actions' => [
                [
                    'url'           => module_url("status/enable"),
                    'icon'          => 'fa-light fa-eye',
                    'label'         => __('Enable'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'url'           => module_url("status/disable"),
                    'icon'          => 'fa-light fa-eye-slash',
                    'label'         => __('Disable'),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
                [
                    'divider'       => true
                ],
                [
                    'url'           => module_url("destroy"),
                    'icon'          => 'fa-light fa-trash-can-list',
                    'label'         => __('Delete'),
                    'confirm'       => __("Are you sure you want to delete this item?"),
                    'call_success'  => "Main.DataTable_Reload('#DataTable')"
                ],
            ]               
        ];

        $this->DatatableTranslations = [
            "element" => "DataTable",
            "columns" => false,
            "order" => ['id', 'desc'],
            "lengthMenu" => [50, 100, 150, 200],
            "search_field" => ["name", "value"],
            "columns" => [
                [ 
                    'data' => 'name',
                    'name' => 'name',
                    'title' => __('Name'),
                ],
                [ 
                    'data' => 'value',
                    'name' => 'value',
                    'title' => __('Value'),
                    'className' => 'w-600'
                ],
                [ 
                    'data' => 'id',
                    'name' => 'id',
                    'title' => __('RecoredID'),
                ],
                [ 
                    'title' => __('Auto Translate'),
                    'className' => 'text-center w-110 fs-10',
                ],
            ],
        ];
    }

    public function index()
    {
        $total = Languages::count();
        return view(module("key").'::index', [
            'total' => $total,
            'Datatable' => $this->Datatable,
        ]);
    }

    public function list(Request $request)
    {   
        $joins = [];
        $whereConditions = [];
        $dataTableService = \DataTable::make(Languages::class, $this->Datatable, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function update(Request $request, $id = ""){
        $result = Languages::where("id_secure", $id)->first();
        return view(module("key").'::update', [
            "result" => $result
        ]);
    }

    public function save(Request $request)
    {
        // Validation rules
        $rules = [
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:10',
            'icon'           => 'required|string|max:255',
            'dir'            => 'required|in:ltr,rtl',
            'is_default'     => 'required|boolean',
            'auto_translate' => 'required|boolean',
            'status'         => 'required|boolean',
        ];

        // Check if this is an insert, and count existing languages
        $isInsert = !$request->filled('id');
        $languagesCount = Languages::count();

        // If this is the only language in the system, force it to be the default
        if ($languagesCount === 0 || ($languagesCount === 1 && $isInsert)) {
            $request->merge(['is_default' => 1]);
        }

        // Prepare the data payload
        $data = [
            'id_secure'         => rand_string(),
            'name'              => $request->input('name'),
            'code'              => $request->input('code'),
            'icon'              => $request->input('icon'),
            'dir'               => $request->input('dir'),
            'is_default'        => $request->input('is_default'),
            'auto_translate'    => $request->input('auto_translate'),
            'status'            => (int) $request->input('status'),
            'changed'           => time(),
            'created'           => time(),
        ];

        // If this is an update, keep the same id_secure
        if ($request->has('id')) {
            $data['id_secure'] = $request->input('id');
        }

        // Fields that should not be updated after creation
        $insertOnlyFields = ['id_secure', 'created'];

        // Initialize custom unique rules
        $customRules = [];
        if ($request->filled('id')) {
            $id = Languages::where('id_secure', $request->input('id'))->value('id');
            $customRules['code'] = Rule::unique('languages', 'code')->ignore($id);
        } else {
            $rules['code'] .= '|unique:languages,code';
        }

        // If is_default = 1, unset is_default on all other languages
        if ($request->input('is_default') == 1) {
            Languages::where('code', '!=', $request->input('code'))->update(['is_default' => 0]);
        }

        // Save the language data
        $response = \DBHelper::saveData(
            Languages::class,
            $rules,
            $data,
            $insertOnlyFields,
            [],          // No custom error messages
            $customRules,
            'id_secure'
        );

        // Only create language files on insert
        if(isset($response['id'])){
            if ($isInsert) {
                \Language::createLanguageFiles($request->input('code'), (int)$request->input('auto_translate'));
            }else{
                $this->updateLanguages($response['id']);
            }
        }

        return response()->json($response);
    }

    public function import(Request $request)
    {
        $customMessages = [
            'files.*.mimes' => __('The uploaded file must be in JSON format.'),
            'files.required' => __('At least one file is required.'),
            'files.array' => __('Files must be an array.'),
        ];

        $validator = \Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|mimes:json',
        ], $customMessages);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $files = $request->file('files');

            foreach ($files as $file) {
                if ($file->isValid()) {
                    $filePath = $file->getPathname();
                    \Language::import($filePath);
                } else {
                    throw new \Exception("One or more files are invalid.");
                }
            }

            return response()->json([
                'status' => 1,
                'message' => __('Import successful')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function export($id)
    {
        $language = Languages::where('id_secure', $id)->firstOrFail();
        $code = $language->code;

        try {
            $exportData = \Language::export($code);

            $fileName = "language_{$code}_" . date('Ymd_His') . '.json';
            $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            return response($jsonContent)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Xuất dữ liệu thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateLanguages($id)
    {
        try {
            $language = Languages::where('id_secure', $id)->firstOrFail();

            $locale = $language->code;
            $autoTranslate = $language->auto_translate;

            \Language::updateLanguageTranslations($locale, $autoTranslate);

            return response()->json([
                'status' => 1,
                'message' => __('Languages updated successfully.')
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => 0,
                'message' => __("Auto translation failed: ") . $e->getMessage()
            ]);
        }
        
    }

    public function autoTranslate(Request $request, $id)
    {
        try {
            $language = Languages::where('id_secure', $id)->firstOrFail();

            $locale = $language->code;

            \Language::createLanguageFiles($locale, true);

            return response()->json([
                'status' => 1,
                'message' => __("Auto translation completed successfully.")
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => __("Auto translation failed: ") . $e->getMessage()
            ]);
        }
    }

    public function status(Request $request, $status = "enable")
    {
        $status_update = $status;
        if(isset($this->Datatable['status_filter'])){
            foreach ($this->Datatable['status_filter'] as $value) {
                if (isset($value['name']) && $value['value'] != -1 && $value['name'] == $status) {
                    $status_update = $value['value'];
                    break;
                }
            }
        }

        $response = \DBHelper::updateField(Languages::class, $request->input('id'), 'status', $status_update);
        return response()->json($response);
    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr($request->input('id'));

        if (empty($id_arr)) {
            return response()->json([
                "status" => 0,
                "message" => __("Please select at least one item")
            ]);
        }

        $languages = Languages::whereIn('id_secure', $id_arr)->get();

        foreach ($languages as $language) {
            $code = $language->code;

            LanguageItems::where('code', $code)->delete();

            $langFilePath = resource_path("lang/{$code}.json");
            if (file_exists($langFilePath)) {
                @unlink($langFilePath);
            }
        }

        Languages::whereIn('id_secure', $id_arr)->delete();

        return response()->json([
            "status" => 1,
            "message" => __("Deleted successfully")
        ]);
    }


    /*
    * LANGUAGE ITEMS
     */
    public function editTranslations(Request $request, $id = false){
        $language = Languages::where('id_secure', $id)->firstOrFail();
        $total = LanguageItems::where("code", $language->code)->count();
        return view(module("key").'::edit_translations', [
            'total' => $total,
            'language' => $language,
            'Datatable' => $this->DatatableTranslations,
        ]);
    }

    public function translationsList(Request $request, $id = false)
    {   
        $language = Languages::where('id_secure', $id)->firstOrFail();
        $joins = [];
        $whereConditions = [
            "code" => $language->code
        ];
        $dataTableService = \DataTable::make(LanguageItems::class, $this->DatatableTranslations, $whereConditions, $joins);
        $data = $dataTableService->getData($request);
        return response()->json($data);
    }

    public function updateTranslation(Request $request, $id = false)
    {
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $response = \Language::updateTranslation($id, $request->value);

        return $response;
    }

    public function autoTranslation(Request $request, $id = false)
    {
        $response = \Language::translateWordById($id);

        return $response;
    }
}
