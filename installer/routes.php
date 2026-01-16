<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

$this->router->get('/', function (Request $request) {
    return view('install');
});

$this->router->post('/', function (Request $request) {
    // Start output buffering to prevent any output before JSON
    ob_start();
    
    try {
        $data = json_decode($request->getContent(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in request: ' . json_last_error_msg());
        }

        $errors = [];
    // Purchase code is now optional - removed validation requirement
    if (empty($data['site_name'])) {
        $errors['site_name'] = 'Site name is required'; 
    }
    if (empty($data['timezone'])) {
        $errors['timezone'] = 'Full name is required';
    }
    if (empty($data['fullname'])) {
        $errors['fullname'] = 'Full name is required';
    }
    if (empty($data['admin_email'])) {
        $errors['admin_email'] = 'Email is required';
    } elseif (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors['admin_email'] = 'Invalid email format';
    }
    if (empty($data['admin_username'])) {
        $errors['admin_username'] = 'Username is required';
    }
    if (empty($data['admin_password'])) {
        $errors['admin_password'] = 'Password is required';
    }
    if (($data['admin_password'] ?? '') !== ($data['admin_password_confirm'] ?? '')) {
        $errors['admin_password_confirm'] = 'Password confirmation does not match';
    }
    if (empty($data['database_host'])) {
        $errors['database_host'] = 'Database host is required';
    }
    if (empty($data['database_name'])) {
        $errors['database_name'] = 'Database name is required';
    }
    if (empty($data['database_username'])) {
        $errors['database_username'] = 'Database username is required';
    }
        if (!empty($errors)) {
            ob_end_clean();
            return new JsonResponse([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $errors
            ]);
        }

        $domain = $request->header('host', '');
        if (empty($domain)) {
            $url = url('/');
            $parsed = parse_url($url);
            $domain = $parsed['host'] ?? 'localhost';
        }
        $domain = preg_replace('/^www\./', '', $domain);
        $purchaseCode = !empty($data['purchase_code']) ? trim(preg_replace('/\s+/', '', $data['purchase_code'])) : '';
        
        // Skip license verification - purchase code is optional
        $verifyResult = [
            'status' => 1,
            'product_id' => 'main',
            'version' => '1.0.0',
            'install_path' => '',
            'download_url' => null
        ];
        
        // Only attempt download if purchase code is provided
        $downloadUrl = null;
        $installPath = '';
        if (!empty($purchaseCode)) {
            $verifyUrl = 'https://stackposts.com/api/marketplace/install';
            try {
                $response = Http::withoutVerifying()
                    ->timeout(15)
                    ->post($verifyUrl, [
                        'purchase_code' => $purchaseCode,
                        'domain'        => $domain,
                        'website'       => url('/'),
                        'is_main'       => 1
                    ]);

                $verifyResult = $response->json();
                if ($response->ok() && ($verifyResult['status'] ?? 0) == 1) {
                    $downloadUrl = $verifyResult['download_url'] ?? null;
                    $installPath = base_path($verifyResult['install_path'] ?? '');
                }
            } catch (\Exception $e) {
                // Silently continue without purchase code verification
                error_log('Purchase code verification skipped: ' . $e->getMessage());
            }
        }
        
        if ($downloadUrl && $installPath) {
            if (!is_dir($installPath)) {
                File::makeDirectory($installPath, 0775, true);
            }

            if (!is_dir(storage_path('app'))) {
                \File::makeDirectory( storage_path('app'), 0775, true);
            }
            $tmpZip = storage_path('app/installer_' . uniqid() . '.zip');
            try {
                $fileResponse = Http::withoutVerifying()->timeout(60)->get($downloadUrl);
                if (!$fileResponse->ok()) {
                    throw new \Exception('Download failed with status code: ' . $fileResponse->status());
                }
                file_put_contents($tmpZip, $fileResponse->body());
            } catch (\Exception $e) {
                // Continue installation even if download fails
                error_log('Download failed (continuing anyway): ' . $e->getMessage());
            }

            if (file_exists($tmpZip)) {
                $zip = new \ZipArchive();
                if ($zip->open($tmpZip) === TRUE) {
                    $zip->extractTo($installPath);
                    $zip->close();
                    File::delete($tmpZip);
                } else {
                    File::delete($tmpZip);
                }
            }
        }

        try {
            $dsn = "mysql:host={$data['database_host']};dbname={$data['database_name']};charset=utf8";
            $pdo = new \PDO($dsn, $data['database_username'], $data['database_password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            ob_end_clean();
            return new JsonResponse([
                'success' => false,
                'message' => 'Cannot connect to database: ' . $e->getMessage(),
                'errors'  => ['database_host' => 'Database connection failed.']
            ]);
        }

        $site_url = str_replace("installer", "", url('/'));
        $site_url = str_replace("installer/", "", $site_url);
        $newVars = [
            'SITE_TITLE'    => $data['site_name'],
            'APP_NAME'      => $data['site_name'],
            'APP_URL'       => $site_url,
            'APP_TIMEZONE'  => $data['timezone'] ?? 'UTC',
            'APP_INSTALLED' => 'true',
            'DB_HOST'       => $data['database_host'],
            'DB_DATABASE'   => $data['database_name'],
            'DB_USERNAME'   => $data['database_username'],
            'DB_PASSWORD'   => $data['database_password'],
        ];
        updateEnvVars(base_path('.env'), $newVars);

        global $container;
        $appConfig = $container->make('config');

        $appConfig->set('database.default', 'mysql');

        // Use direct values instead of env() to avoid Dotenv dependency issues
        $dbPort = $data['database_port'] ?? '3306';
        $dbSocket = $data['database_socket'] ?? '';
        
        $appConfig->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => $data['database_host'],
            'port' => $dbPort,
            'database' => $data['database_name'],
            'username' => $data['database_username'],
            'password' => $data['database_password'],
            'unix_socket' => $dbSocket,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? [] : [],
        ]);

        try {
            $cachedConfigFile = base_path('bootstrap/cache/config.php');
            if (File::exists($cachedConfigFile)) {
                File::delete($cachedConfigFile);
            }
        } catch (\Exception $e) {
            error_log('Config clear failed: ' . $e->getMessage());
        }

        try {
            $migrator = $container->make('migrator');
            $schema = $container->make('db.schema');
            try {
                $schema->create('migrations', function ($table) {
                    $table->increments('id');
                    $table->string('migration');
                    $table->integer('batch');
                });
            } catch (\PDOException $e) {
                if ($e->getCode() !== '42S01') {
                    throw $e;
                }
                error_log('Migration warning: Table `migrations` already exists - ' . $e->getMessage());
            }

            $migrator->run(base_path('database/migrations'), ['--force' => true]);

        } catch (\Exception $e) {
            $errorMessage = 'Migrate failed: ' . $e->getMessage();

            if ($e instanceof \PDOException && $e->getCode() === '42S01') {
                error_log('Migration warning: A table already exists in the database - ' . $e->getMessage());
            } else {
                ob_end_clean();
                return new JsonResponse([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors'  => ['migrate' => 'Migrate database failed!']
                ]);
            }
        }

        $result = createAdminUser($pdo, $data);
        if (!$result['success']) {
            ob_end_clean();
            return new JsonResponse($result);
        }

        // Only insert purchase addon if purchase code is provided
        if (!empty($purchaseCode)) {
            try {
                insertPurchaseAddon($pdo, [
                    'product_id'    => $verifyResult['product_id'] ?? 'main',
                    'version'       => $verifyResult['version'] ?? '1.0.0',
                    'module_name'   => 'main',
                    'purchase_code' => $purchaseCode,
                    'install_path'  => $verifyResult['install_path'] ?? '',
                ]);
            } catch (\Exception $e) {
                // Continue installation even if addon insertion fails
                error_log('Purchase addon insertion failed (continuing anyway): ' . $e->getMessage());
            }
        }
        

        // Clear any output that might have been generated
        ob_end_clean();
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Installation successful!'
        ]);
        
    } catch (\Exception $e) {
        // Clear any output
        ob_end_clean();
        
        // Log the error for debugging
        error_log('Installer error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Installation failed: ' . $e->getMessage(),
            'errors' => ['general' => $e->getMessage()]
        ], 500);
        
    } catch (\Throwable $e) {
        // Clear any output
        ob_end_clean();
        
        // Log the error for debugging
        error_log('Installer fatal error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        
        return new JsonResponse([
            'success' => false,
            'message' => 'Installation failed: ' . $e->getMessage(),
            'errors' => ['general' => $e->getMessage()]
        ], 500);
    }
});