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
    $data = json_decode($request->getContent(), true);

    $errors = [];
    if (empty($data['purchase_code'])) {
        $errors['purchase_code'] = 'Purchase code is required';
    }
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
    $purchaseCode = trim(preg_replace('/\s+/', '', $data['purchase_code']));
    $verifyUrl = 'https://stackposts.com/api/marketplace/install';

    $response = Http::withoutVerifying()
        ->timeout(15)
        ->post($verifyUrl, [
            'purchase_code' => $purchaseCode,
            'domain'        => $domain,
            'website'       => url('/'),
            'is_main'       => 1
        ]);

    $verifyResult = $response->json();

    if (!$response->ok() || ($verifyResult['status'] ?? 0) != 1) {
        return new JsonResponse([
            'success' => false,
            'message' => $verifyResult['message'] ?? 'Purchase verification failed!',
            'errors'  => ['purchase_code' => $verifyResult['message'] ?? 'Purchase code invalid!']
        ]);
    }

    $downloadUrl = $verifyResult['download_url'] ?? null;
    $installPath = base_path($verifyResult['install_path'] ?? '');
    if ($downloadUrl) {
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
            return new JsonResponse([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage(),
                'errors'  => ['download_url' => 'Download failed!']
            ]);
        }


        $zip = new \ZipArchive();
        if ($zip->open($tmpZip) === TRUE) {
            $zip->extractTo($installPath);
            $zip->close();
            File::delete($tmpZip);
        } else {
            File::delete($tmpZip);
            return new JsonResponse([
                'success' => false,
                'message' => 'Unable to unzip installer file!',
                'errors'  => ['download_url' => 'Extract installer failed!']
            ]);
        }
    }

    try {
        $dsn = "mysql:host={$data['database_host']};dbname={$data['database_name']};charset=utf8";
        $pdo = new \PDO($dsn, $data['database_username'], $data['database_password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
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

    $appConfig->set('database.connections.mysql', [
        'driver' => 'mysql',
        'host' => $data['database_host'],
        'port' => env('DB_PORT', '3306'),
        'database' => $data['database_name'],
        'username' => $data['database_username'],
        'password' => $data['database_password'],
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
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
            return new JsonResponse([
                'success' => false,
                'message' => $errorMessage,
                'errors'  => ['migrate' => 'Migrate database failed!']
            ]);
        }
    }

    $result = createAdminUser($pdo, $data);
    if (!$result['success']) {
        return new JsonResponse($result);
    }

    insertPurchaseAddon($pdo, [
        'product_id'    => $verifyResult['product_id'],
        'version'       => $verifyResult['version'],
        'module_name'   => 'main',
        'purchase_code' => $data['purchase_code'],
        'version'       => $verifyResult['version'] ?? '1.0',
        'install_path'  => $verifyResult['install_path'] ?? '',
    ]);
        

    return new JsonResponse([
        'success' => true,
        'message' => 'Installation successful!'
    ]);

});