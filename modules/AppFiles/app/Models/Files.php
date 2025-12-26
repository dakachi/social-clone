<?php

namespace Modules\AppFiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use DB;

class Files extends Model
{
    //
    protected $table = 'files';
    protected $fillable = [];
    protected $guarded = [];  

    public $timestamps = false;

    public static function getList(Request $request)
    {
        $search = $request->keyword;
        $status = $request->status;
        $folder_id = $request->folder_id;
        $current_page = $request->page + 1;
        $file_type = $request->file_type;
        $team_id = $request->team_id;
        $per_page = 30;

        $folder = self::where([
            'is_folder' => 1,
            'id_secure' => $folder_id,
            'team_id' => $team_id
        ])->first();

        $wheres = [
            'team_id' => $team_id,
            'is_folder' => 0
        ];

        $file_types = ['image', 'video', 'doc', 'pdf', 'csv', 'audio', 'other'];
        if (in_array($file_type, $file_types)) {
            $wheres['detect'] = $file_type;
        }

        $parent_folders = [];
        if ($folder) {
            $wheres['pid'] = $folder->id;
            $folder_id = $folder->id;
            $parent_folders = self::getParentFolders($folder->id);
        } else {
            $wheres['pid'] = 0;
            $folder_id = 0;
        }

        Paginator::currentPageResolver(function () use ($current_page) {
            return $current_page;
        });

        $query = self::where($wheres);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('detect', 'like', "%{$search}%");
            });
        }

        $files = $query->orderByDesc('created')->paginate($per_page);
        $folders = $current_page == 1 ? self::folders($folder_id) : false;

        return [
            'files' => $files,
            'folders' => $folders,
            'folder' => $folder,
            'current_page' => $current_page,
            'parent_folders' => $parent_folders
        ];
    }

    protected static function folders($folder_id = 0)
    {
        $queryFiles = DB::table('files as f1')
            ->leftJoin('files as f2', function ($join) {
                $join->on('f2.pid', '=', 'f1.id')
                     ->where('f2.is_folder', 0);
            })
            ->select('f1.id', 'f1.name', 'f1.id_secure', 
                    DB::raw('COUNT(f2.id) as file_count'),
                    DB::raw('COALESCE(SUM(f2.size), 0) as total_size'))
            ->where('f1.pid', (int) $folder_id)
            ->where('f1.is_folder', 1)
            ->where('f1.team_id', request()->team_id)
            ->groupBy('f1.id', 'f1.name', 'f1.id_secure');

        $queryFolders = DB::table('files as f1')
            ->leftJoin('files as f2', function ($join) {
                $join->on('f2.pid', '=', 'f1.id')
                     ->where('f2.is_folder', 1);
            })
            ->select('f1.id', 'f1.name', 'f1.id_secure', 
                    DB::raw('COUNT(f2.pid) as folder_count'))
            ->where('f1.pid', (int) $folder_id)
            ->where('f1.is_folder', 1)
            ->where('f1.team_id', request()->team_id)
            ->groupBy('f1.id', 'f1.name', 'f1.id_secure');

        $files = $queryFiles->get();
        $folders = $queryFolders->get();

        $combined = $files->map(function ($item) use ($folders) {
            $folder = $folders->firstWhere('id', $item->id);
            $item->folder_count = $folder ? $folder->folder_count : 0;
            $item->total_size = Files::calculateTotalSize($item->id);

            return $item;
        });

        return $combined;
    }

    public function getFoldersAndSubfolders($parentId = 0) {
        $folders = Files::with(['all_subfolders' => function($query) {
            $query->where('is_folder', 1);
        }])->where('pid', $parentId)->where("team_id", request()->team_id)->where('is_folder', 1)->get();

        $result = [];
        foreach ($folders as $folder) {
            $folderData = [
                'id' => $folder->id,
                'id_secure' => $folder->id_secure,
                'name' => $folder->name,
                'subfolders' => $this->getSubfoldersArray($folder->all_subfolders)
            ];
            $result[] = $folderData;
        }
        return $result;
    }

    public function getSubfoldersArray($subfolders)
    {
        $result = [];
        foreach ($subfolders as $subfolder) {
            $subfolderData = [
                'id' => $subfolder->id,
                'id_secure' => $subfolder->id_secure,
                'name' => $subfolder->name,
                'subfolders' => $this->getSubfoldersArray($subfolder->all_subfolders)
            ];
            $result[] = $subfolderData;
        }
        return $result;
    }

    public static function getParentFolders($folderId)
    {
        $parents = [];
        $currentFolder = Files::find($folderId);

        while ($currentFolder && $currentFolder->parent) {
            $currentFolder = $currentFolder->parent;
            $parents[] = $currentFolder;
        }

        return array_reverse($parents);
    }
    

    public function subfolders()
    {
        return $this->hasMany(Files::class, 'pid')->where(['is_folder' => 1, "team_id" => request()->team_id]);
    }

    public function all_subfolders()
    {
        return $this->subfolders()->with('all_subfolders');
    }

    public function parent()
    {
        return $this->belongsTo(Files::class, 'pid');
    }

    protected static function calculateTotalSize($folder_id)
    {
        $ids = [$folder_id];
        $newIds = [$folder_id];

        do {
            $childFolders = DB::table('files')
                ->whereIn('pid', $newIds)
                ->where('is_folder', 1)
                ->pluck('id')
                ->toArray();

            $newIds = array_diff($childFolders, $ids);
            $ids = array_merge($ids, $newIds);
        } while (count($newIds) > 0);

        $totalSize = DB::table('files')
            ->whereIn('pid', $ids)
            ->where('is_folder', 0)
            ->sum('size');

        return (int) $totalSize;
    }
    
}
