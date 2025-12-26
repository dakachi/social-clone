<?php

namespace Modules\AdminSystemInformation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use DB;

class AdminSystemInformationController extends Controller
{
    public function index()
    {
        // MySQL settings
        $mysqlConfig = DB::select("
            SHOW VARIABLES
            WHERE Variable_name IN ('max_connections','max_user_connections','wait_timeout','max_allowed_packet')
        ");
        $mysqlSettings = collect($mysqlConfig)->pluck('Value', 'Variable_name');

        // Tool checks that DON'T require shell_exec
        $ffmpegStatus = $this->hasBinary('ffmpeg', [
            env('FFMPEG_PATH'), '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/bin/ffmpeg'
        ]);
        $nodeStatus = $this->hasBinary('node', [
            env('NODE_PATH'), '/usr/bin/node', '/usr/local/bin/node', '/bin/node'
        ]);

        $data = [
            'phpSettings' => [
                'max_input_time'       => ini_get('max_input_time'),
                'file_uploads'         => ini_get('file_uploads'),
                'max_execution_time'   => ini_get('max_execution_time'),
                'SMTP'                 => ini_get('SMTP'),
                'smtp_port'            => ini_get('smtp_port'),
                'upload_max_filesize'  => ini_get('upload_max_filesize'),
                'phpversion'           => phpversion(),
                'allow_url_fopen'      => ini_get('allow_url_fopen'),
                'allow_url_include'    => ini_get('allow_url_include'),
                'memory_limit'         => ini_get('memory_limit'),
                'post_max_size'        => ini_get('post_max_size'),
            ],
            'mysqlSettings' => $mysqlSettings,
            'extensions'    => [
                'pdo_mysql' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled',
                'intl'      => extension_loaded('intl') ? 'Enabled' : 'Disabled',
                'openssl'   => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
                'zip'       => extension_loaded('zip') ? 'Enabled' : 'Disabled',
                'zlib_output_compression' => ini_get('zlib.output_compression') ? 'Enabled' : 'Disabled',
            ],
            'imageSupport' => [
                'jpeg' => function_exists('imagejpeg') ? 'Supported' : 'Not Supported',
                'png'  => function_exists('imagepng') ? 'Supported' : 'Not Supported',
                'webp' => function_exists('imagewebp') ? 'Supported' : 'Not Supported',
            ],
            'tools' => [
                'ffmpeg' => $ffmpegStatus,
                'nodeJs' => $nodeStatus,
            ],
            'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'Not Available',
        ];

        return view('adminsysteminformation::index', $data);
    }

    /**
     * Check if a binary exists without relying on shell_exec.
     * Returns: 'Installed' | 'Not Installed' | 'Unknown (shell_exec disabled)'
     */
    private function hasBinary(string $command, array $commonPaths = []): string
    {
        // 1) Environment variable path
        foreach ($commonPaths as $p) {
            if (!$p) continue;
            if (@is_file($p) || @is_link($p)) {
                return 'Installed';
            }
        }

        // 2) If shell_exec is available, confirm via command -v / which
        if (function_exists('shell_exec')) {
            $out = @\shell_exec("command -v " . escapeshellcmd($command) . " 2>/dev/null")
               ?: @\shell_exec("which " . escapeshellcmd($command) . " 2>/dev/null");
            if ($out && trim($out) !== '') {
                return 'Installed';
            }
            return 'Not Installed';
        }

        // 3) Unknown if we can't probe
        return 'Unknown (shell_exec disabled)';
    }

    public function save(Request $request)
    {
        foreach ($request->all() as $name => $value) {
            if (is_string($value) || $value === "") {
                DB::table('options')->updateOrInsert(
                    ['name' => $name],
                    ['value' => $value]
                );
            }
        }

        ms([
            "status" => 1,
            "message" => __("Succeed")
        ]);
    }

    public function pusher()
    {
        return view('adminsettings::pusher');
    }
}
