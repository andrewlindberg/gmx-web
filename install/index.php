<?php
define('BASE_DIR', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

include __DIR__ . DS . 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	echo render('template', [
		'baseUrl' => getBaseUrl()
	]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$step = isset($_GET['step']) ? $_GET['step'] : null;
	switch ($step) {
        case 'checks': {
            if (!checkPhpVersion()) {
                json([
                    'success' => false,
                    'message' => 'PHP must be 5.6.0 or higher'
                ]);
            } elseif (!is_writable(BASE_DIR . DS . 'runtime' . DS . 'cache')) {
                json([
                    'success' => false,
                    'message' => sprintf('Folder %s is not writable', BASE_DIR . DS . 'runtime' . DS . 'cache')
                ]);
            } elseif (!is_writable(BASE_DIR . DS . 'runtime' . DS . 'logs')) {
                json([
                    'success' => false,
                    'message' => sprintf('Folder %s is not writable', BASE_DIR . DS . 'runtime' . DS . 'logs')
                ]);
            } elseif (!is_writable(BASE_DIR . DS . 'runtime' . DS . 'twig_cache')) {
                json([
                    'success' => false,
                    'message' => sprintf('Folder %s is not writable', BASE_DIR . DS . 'runtime' . DS . 'twig_cache')
                ]);
            } else {
                json([
                    'success' => true
                ]);
            }
        } break;

		case 'composer': {
			try {
				set_time_limit(0);
				composerInstall(BASE_DIR);
				json([
					'success' => true
				]);
			} catch (Exception $e) {
				json([
					'success' => false,
					'message' => $e->getMessage()
				]);
			}
		} break;

		case 'config': {
			try {
				if (!checkDbConnection($_POST['db'])) {
					throw new Exception('Can\'t connect to database');
				}
				$config = getBaseConfig();
				$config['db']['host'] = $_POST['db']['host'];
				$config['db']['port'] = $_POST['db']['port'];
				$config['db']['username'] = $_POST['db']['user'];
				$config['db']['password'] = $_POST['db']['pass'];
				$config['db']['database'] = $_POST['db']['name'];
				$config['db']['prefix'] = $_POST['db']['prefix'];
				$filePath = BASE_DIR . DS . 'config.json';
				$data = json_encode($config, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
				file_put_contents($filePath, $data);
				json([
					'success' => true
				]);
			} catch (Exception $e) {
				json([
					'success' => false,
					'message' => $e->getMessage()
				]);
			}
		} break;

		case 'migrations': {
			try {
				$container = getContainer(BASE_DIR, true);
				runMigrations($container);
				json([
					'success' => true
				]);
			} catch (Exception $e) {
				json([
					'success' => false,
					'message' => $e->getMessage()
				]);
			}
		}

		case 'admin': {
			try {
                $container = getContainer(BASE_DIR, true);
				createUser($container, $_POST['login'], $_POST['email'], $_POST['pass']);
				json([
					'success' => true
				]);
			} catch (Exception $e) {
				json([
					'success' => false,
					'message' => $e->getMessage()
				]);
			}
		}

		case 'tasks': {
			try {
                $container = getContainer(BASE_DIR, false);

				\GameX\Core\Jobs\JobHelper::createTask('monitoring');
				\GameX\Core\Jobs\JobHelper::createTask('punishments');

				json([
					'success' => true
				]);
			} catch (Exception $e) {
				json([
					'success' => false,
					'message' => $e->getMessage()
				]);
			}
		}

		default: {
			json([
				'success' => false,
				'message' => 'Unknown step ' . $step
			]);
		}
	}
}
