<?php

namespace Modules\AppFiles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\AppFiles\Models\Files;
use Modules\AppFiles\Services\UploadFileService;

class AppFilesController extends Controller
{
    protected $uploadFileService;

    public function __construct(UploadFileService $uploadFileService)
    {
        $this->table = "files";
        $this->uploadFileService = $uploadFileService;

        // Allowed file types
        $this->allowedFileTypes = get_option("file_allowed_file_types", "jpeg,gif,png,jpg,webp,mp4,csv,pdf,mp3,wmv,json");
        $this->maxFileSize = (int)\Access::permission('appfiles.max_size') * 1024;
    }

    public function index(Request $request)
    {   
        $total = Files::where("team_id", $request->team_id)->count();
        return view('appfiles::index',[
            "total" => $total
        ]);
    }

    public function list(Request $request)
    {
        $data = Files::getList($request);

        if ($data['files']->isEmpty() && $data['current_page'] > 1) {
            return response()->json([
                "status" => 0
            ]);
        }

        switch ($request->segment(3)) {
            case 'popup_list':
                $view = 'appfiles::popup_list';
                break;
            case 'mini_list':
                $view = 'appfiles::mini_list';
                break;
            default:
                $view = 'appfiles::list';
                break;
        }

        return response()->json([
            "status" => 1,
            "data" => view($view, $data)->render()
        ]);
    }

    public function upload_files(Request $request)
    {
        $folder = Files::where([
            "is_folder" => 1,
            "id_secure" => $request->folder_id,
            "team_id" => $request->team_id
        ])->first();

        if (!$request->hasFile('files')) {
            return response()->json([
                "status" => 0,
                "message" => __("Please select at least one file to upload."),
            ]);
        }

        $allowedFileTypes = is_array($this->allowedFileTypes)
            ? $this->allowedFileTypes
            : explode(',', $this->allowedFileTypes);
        $maxFileSizeKB = $this->maxFileSize;

        $errors = [];
        $validFiles = [];

        foreach ($request->file('files') as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            $allowedCsvTypes = [
                "text/csv",
                "application/csv",
                "text/plain",
                "application/octet-stream",
                "application/vnd.ms-excel",
                "text/comma-separated-values",
            ];
            $isCsv = $ext === 'csv';

            $validator = Validator::make(
                ['file' => $file],
                [
                    'file' => [
                        'required',
                        'file',
                        $isCsv ? 'mimetypes:' . implode(',', $allowedCsvTypes) : ('mimes:' . implode(',', $allowedFileTypes)),
                        'max:' . $maxFileSizeKB
                    ]
                ],
                [
                    'file.mimes' => __('Invalid file type. Allowed: ') . implode(', ', $allowedFileTypes),
                    'file.mimetypes' => __('Invalid CSV file type.'),
                    'file.required' => __('Please upload at least one file.'),
                    'file.max' => __('Max file size is :size MB.', ['size' => $maxFileSizeKB / 1024])
                ]
            );

            if ($validator->fails()) {
                $errors[$file->getClientOriginalName()] = $validator->errors()->first('file');
            } else {
                $validFiles[] = $file;
            }
        }

        if (count($validFiles) === 0) {
            return response()->json([
                "status" => 0,
                "message" => __("All files failed to upload."),
                "errors" => $errors
            ]);
        }

        $request->files->set('files', $validFiles);

        try {
            \UploadFile::handleFileUpload($request, $folder);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 0,
                "message" => $e->getMessage()
            ]);
        }

        if (count($errors)) {
            return response()->json([
                "status" => 2,
                "message" => __("Some files failed to upload."),
                "errors" => $errors
            ]);
        }

        return response()->json([
            "status" => 1,
            "message" => __("Upload successful.")
        ]);
    }

    public function upload_from_url(Request $request){
        $folder_id = $request->id;

        return response()->json([
            "status" => 1,
            "data" => view('appfiles::upload_from_url',[
                "folder_id" => $folder_id
            ])->render()
        ]);
    }

    public function save_file(Request $request)
    {
        // Validate the input
        $request->validate([
            'file_url' => 'required|url',
        ]);

        $file_url = $request->file_url;
        $folder_id = $request->folder_id;
        $folder = Files::where(["is_folder" => 1, "id_secure" => $folder_id, "team_id" => $request->team_id])->first();

        try {
            $filePath = \UploadFile::saveFileFromUrl([
                'file_url' => $file_url, 
                'folder_id' => $folder ? $folder->id : 0,
                'from' => 'url'
            ], $folder);

            return response()->json([
                'status' => 1,
                'message' => 'File saved successfully',
                'file_path' => $filePath
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function save_files(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|url',
            'folder_id' => 'nullable|string',
        ]);

        $folder = Files::where([
            'is_folder' => 1,
            'id_secure' => $request->folder_id,
            'team_id' => $request->team_id
        ])->first();

        $folder_id = $folder?->id ?? 0;
        $saved = 0;

        try {
            foreach ($request->input('files', []) as $fileUrl) {
                \UploadFile::saveFileFromUrl([
                    'file_url'  => $fileUrl,
                    'folder_id' => $folder_id,
                    'from'      => 'search'
                ], $folder);

                $saved++;
            }

            return response()->json([
                'status' => 1,
                'message' => "Saved {$saved} files successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function save_file_from_cloud(Request $request){

        // Validate the input
        $request->validate([
            'files' => 'required',
        ]);

        $files = $request->input('files');
        $folder_id = $request->folder_id;
        $folder = Files::where(["is_folder" => 1, "id_secure" => $folder_id, "team_id" => $request->team_id])->first();
        $folder_id =$folder ? $folder->id : 0;

        try {
            $result = \UploadFile::saveMultipleFilesFromUrls($files, $folder_id);

            return response()->json([
                'status' => 1,
                'message' => 'File saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function popup_files(Request $request){
        $id = $request->id;
        $filter = $request->filter;

        if($filter){
            try {
                $filter = unserialize($filter);
            } catch (\Exception $e) {
                $filter = null;
            }
        }

        return response()->json([
            "status" => 1,
            "data" => view('appfiles::popup_files',[
                "id" => $id,
                "filter" => $filter
            ])->render()
        ]);
    }

    public function update_folder(Request $request){
        $id = $request->id;
        $Files = new Files();
        $result = Files::where("id_secure", $id)->first();
        $folders = $Files->getFoldersAndSubfolders(0);

        return response()->json([
            "status" => 1,
            "data" => view('appfiles::update_folder',[
                "result" => $result,
                "folders" => $folders
            ])->render()
        ]);
    }

    public function save_folder(Request $request)
    {
        $parent_folder = Files::where([
            'is_folder' => 1,
            'id_secure' => $request->parent
        ])->first();

        $parent_id = $parent_folder ? $parent_folder->id : 0;

        $item = Files::where('id_secure', $request->id_secure)->first();

        $rules = [
            'name' => ['required', Rule::unique('files')->ignore($item?->id)]
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first()
            ]);
        }

        $values = [
            'team_id' => $request->team_id,
            'name' => $request->name,
            'pid' => $parent_id,
            'is_folder' => 1,
        ];

        if ($item) {
            $item->update($values);
        } else {
            $values['id_secure'] = rand_string();
            $values['created'] = now()->timestamp;
            Files::create($values);
        }

        return response()->json(['status' => 1, 'message' => 'Folder saved successfully.']);
    }

    public function destroy(Request $request)
    {
        $result = \UploadFile::destroy($request);
        return $result;
    }

    public function settings(Request $request)
    {   
        return view('appfiles::settings');
    }
}
