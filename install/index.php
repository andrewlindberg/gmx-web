<?php
define('BASE_DIR', dirname(__DIR__));

include __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	echo render('template', [
		'baseUrl' => getBaseUrl()
	]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$step = isset($_GET['step']) ? (int)$_GET['step'] : null;
	switch ($step) {
		case 1: {
			try {
				set_time_limit(0);
				composerInstall(BASE_DIR);
				json([
					'status' => true
				]);
			} catch (Exception $e) {
				json([
					'status' => false,
					'message' => $e->getMessage()
				]);
			}
		} break;

		case 2: {
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
				$filePath = BASE_DIR . DIRECTORY_SEPARATOR . 'config.json';
				$data = json_encode($config, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
				file_put_contents($filePath, $data);
				json([
					'status' => true
				]);
			} catch (Exception $e) {
				json([
					'status' => false,
					'message' => $e->getMessage()
				]);
			}
		} break;

		case 3: {
			try {
				require BASE_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
				$container = require BASE_DIR . DIRECTORY_SEPARATOR . 'phpmig.php';
				runMigrations($container);
				json([
					'status' => true
				]);
			} catch (Exception $e) {
				json([
					'status' => false,
					'message' => $e->getMessage()
				]);
			}
		}

		case 4: {
			try {
				$container = getContainer(BASE_DIR);
				createUser($container, $_POST['email'], $_POST['pass']);
				json([
					'status' => true
				]);
			} catch (Exception $e) {
				json([
					'status' => false,
					'message' => $e->getMessage()
				]);
			}
		}

		case 5: {
			try {
				$container = getContainer(BASE_DIR);

				\GameX\Core\Jobs\JobHelper::createTask('monitoring');
				\GameX\Core\Jobs\JobHelper::createTask('punishments');

				json([
					'status' => true
				]);
			} catch (Exception $e) {
				json([
					'status' => false,
					'message' => $e->getMessage()
				]);
			}
		}
	}
}
