<?php

namespace App\Http\Controllers\Install;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EnvWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PDO;
use PDOException;
use Throwable;

class InstallController extends Controller
{
    public function index(): View
    {
        return view('install.index', [
            'requirements' => $this->checkRequirements(),
            'canInstall' => ! in_array(false, $this->checkRequirements(), true),
            'appUrl' => request()->getSchemeAndHttpHost(),
        ]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8',
        ]);

        $database = str_replace('`', '', $data['db_database']);

        try {
            // Sur cPanel/o2switch, l'utilisateur MySQL n'a en général de droits
            // que sur la base qui lui a déjà été assignée (pas de CREATE global) :
            // on essaie d'abord de se connecter directement à cette base.
            new PDO(
                "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$database};charset=utf8mb4",
                $data['db_username'],
                $data['db_password'] ?? '',
            );
        } catch (PDOException $e) {
            try {
                // La base n'existe pas encore (installation locale/VPS avec un
                // utilisateur ayant les droits complets) : on tente de la créer.
                $pdo = new PDO(
                    "mysql:host={$data['db_host']};port={$data['db_port']};charset=utf8mb4",
                    $data['db_username'],
                    $data['db_password'] ?? '',
                );
                $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (PDOException $e2) {
                return back()->withInput()->withErrors([
                    'db_host' => "Connexion à la base de données impossible : {$e2->getMessage()}. Sur un hébergement cPanel, créez d'abord la base et l'utilisateur MySQL depuis votre espace d'hébergement, puis renseignez ces mêmes informations ici.",
                ]);
            }
        }

        $appKey = 'base64:'.base64_encode(random_bytes(32));

        $envValues = [
            'APP_NAME' => $data['app_name'],
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => rtrim($data['app_url'], '/'),
            'APP_KEY' => $appKey,
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
            'SESSION_DRIVER' => 'file',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',
            'FILESYSTEM_DISK' => 'public',
            'MAIL_MAILER' => 'log',
        ];

        EnvWriter::update($envValues);
        $this->applyToCurrentRequest($envValues);

        try {
            Artisan::call('migrate', ['--force' => true]);

            $admin = new User;
            $admin->company_id = null;
            $admin->name = $data['admin_name'];
            $admin->email = $data['admin_email'];
            $admin->password = Hash::make($data['admin_password']);
            $admin->role = UserRole::SUPER_ADMIN;
            $admin->is_active = true;
            $admin->email_verified_at = now();
            $admin->save();

            if (! file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        } catch (Throwable $e) {
            return back()->withInput()->withErrors([
                'db_host' => "L'installation a échoué : {$e->getMessage()}",
            ]);
        }

        file_put_contents(storage_path('installed'), now()->toDateTimeString()."\n");

        return view('install.done', ['appUrl' => rtrim($data['app_url'], '/')]);
    }

    /**
     * EnvWriter n'écrit que le fichier .env sur disque : le process PHP en
     * cours a déjà chargé les variables d'environnement au démarrage
     * (Dotenv), donc env()/config() renverraient encore les anciennes
     * valeurs pour LE RESTE DE CETTE REQUÊTE (migrate, config:cache...) si
     * on ne les propage pas explicitement ici.
     */
    protected function applyToCurrentRequest(array $values): void
    {
        foreach ($values as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        Config::set([
            'app.name' => $values['APP_NAME'],
            'app.env' => $values['APP_ENV'],
            'app.debug' => false,
            'app.url' => $values['APP_URL'],
            'app.key' => $values['APP_KEY'],
            'database.default' => $values['DB_CONNECTION'],
            'database.connections.mysql.host' => $values['DB_HOST'],
            'database.connections.mysql.port' => $values['DB_PORT'],
            'database.connections.mysql.database' => $values['DB_DATABASE'],
            'database.connections.mysql.username' => $values['DB_USERNAME'],
            'database.connections.mysql.password' => $values['DB_PASSWORD'],
            'session.driver' => $values['SESSION_DRIVER'],
            'cache.default' => $values['CACHE_STORE'],
            'queue.default' => $values['QUEUE_CONNECTION'],
            'filesystems.default' => $values['FILESYSTEM_DISK'],
            'mail.default' => $values['MAIL_MAILER'],
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    protected function checkRequirements(): array
    {
        return [
            'PHP 8.2 ou plus récent' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'Extension pdo_mysql' => extension_loaded('pdo_mysql'),
            'Extension mbstring' => extension_loaded('mbstring'),
            'Extension openssl' => extension_loaded('openssl'),
            'Extension fileinfo' => extension_loaded('fileinfo'),
            'Extension gd (photos)' => extension_loaded('gd'),
            'Dossier storage/ inscriptible' => is_writable(storage_path()),
            'Dossier bootstrap/cache/ inscriptible' => is_writable(base_path('bootstrap/cache')),
        ];
    }
}
