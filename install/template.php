<?php
/**
 * @var string $baseUrl
 */
?>
<!doctype html>
<html lang="en">
<head>
    <title>GameX Install</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="<?= $baseUrl; ?>/assets/css/uikit.css" />
    <script src="<?= $baseUrl; ?>/assets/js/uikit.js"></script>
	<script src="<?= $baseUrl; ?>/assets/js/jquery-3.3.1.min.js"></script>
</head>
<body>
<div class="uk-container uk-container-small uk-margin-medium">
    <form id="installForm">
        <div class="uk-child-width-1-2@m uk-grid" uk-grid="">
            <div class="uk-first-column">
                <div class="uk-card uk-card-default uk-margin uk-card-large">
                    <div class="uk-card-header">
                        <h3 class="uk-card-title">Database</h3>
                    </div>
                    <div class="uk-card-body">
                        <div class="form-group">
                            <label>Engine:</label>
                            <select class="uk-select" id="formDatabaseEngine">
                                <option value="mysql" selected>MySQL</option>
                                <option value="postgresql">PostgreSQL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Database Host:</label>
                            <input type="text" class="uk-input" id="formDatabaseHost" value="127.0.0.1">
                        </div>
                        <div class="form-group">
                            <label>Database Port:</label>
                            <input type="text" class="uk-input" id="formDatabasePort" value="3306">
                        </div>
                        <div class="form-group">
                            <label>Database User:</label>
                            <input type="text" class="uk-input" id="formDatabaseUser" value="root">
                        </div>
                        <div class="form-group">
                            <label>Database Password:</label>
                            <input type="password" class="uk-input" id="formDatabasePass" value="">
                        </div>
                        <div class="form-group">
                            <label>Database:</label>
                            <input type="text" class="uk-input" id="formDatabaseName" value="test">
                        </div>
                        <div class="form-group">
                            <label>Database Prefix:</label>
                            <input type="text" class="uk-input" id="formDatabasePrefix" value="gmx_">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="uk-card uk-card-default uk-margin uk-card-large">
                    <div class="uk-card-header">
                        <h3 class="uk-card-title">Admin</h3>
                    </div>
                    <div class="uk-card-body">
                        <div>
                            <div class="form-group">
                                <label>Admin Username:</label>
                                <input type="text" class="uk-input" id="formAdminLogin" value="admin">
                            </div>
                            <div class="form-group">
                                <label>Admin Email:</label>
                                <input type="email" class="uk-input" id="formAdminEmail" value="admin@example.com">
                            </div>
                            <div class="form-group">
                                <label>Admin Password:</label>
                                <input type="password" class="uk-input" id="formAdminPass" value="">
                            </div>
                        </div>
                    </div>
                </div>
	            <div class="uk-card uk-card-default uk-margin uk-card-large">
		            <div class="uk-card-header">
			            <h3 class="uk-card-title">Additional</h3>
		            </div>
		            <div class="uk-card-body" style="padding-top: 35px">
			            <div>
				            <div class="form-group">
					            <label>
					                <input type="checkbox" class="uk-checkbox" id="formComposer">
						            Install dependencies
					            </label>
				            </div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-center uk-flex-wrap uk-margin">
            <div class="uk-width-1-1@m uk-margin">
                <button type="submit" class="uk-button uk-button-secondary uk-button-large" id="formSubmitButton">Install</button>
            </div>

        </div>
    </form>

</div>
<script>
    var statusList;
	function result(message, nextCall) {
		var el= $('<li/>');
		el.addClass('list-group-item').text(message + '...');
		statusList.append(el);
		return function (data) {
			if (data.status) {
				el.text(message);
                el.addClass('uk-text-success');
				nextCall();
			} else {
				el.text(message + ': ' + data.message);
                el.addClass('uk-text-danger');
                $('#close-btn').prop('disabled', false);
                $('#formSubmitButton').prop('disabled', false);
			}
		};
	}

	function fail(nextFunc) {
        return function () {
            nextFunc({
                success: false,
                message: 'Server error'
            });
            $('#formSubmitButton').prop('disabled', false);
        };
    }

    function installChecks() {
        var nextFunc = result('Checks requirements', installComposer);
        var data = {
            db: {
                engine: $('#formDatabaseEngine').val(),
                host: $('#formDatabaseHost').val(),
                port: $('#formDatabasePort').val(),
                user: $('#formDatabaseUser').val(),
                pass: $('#formDatabasePass').val(),
                name: $('#formDatabaseName').val(),
                prefix: $('#formDatabasePrefix').val()
            },
            admin: {
                login: $('#formAdminLogin').val(),
                email: $('#formAdminEmail').val(),
                pass: $('#formAdminPass').val()
            }
        };
        $.post('?step=checks', data)
            .done(nextFunc)
            .fail(fail(nextFunc));
    }

	function installComposer() {
		if ($('#formComposer').prop('checked')) {
			var nextFunc = result('Download dependencies', installConfig);
			$.post('?step=composer')
				.done(nextFunc)
				.fail(fail(nextFunc));
		} else {
			installConfig();
		}
	}

	function installConfig() {
		var data = {
			db: {
                engine: $('#formDatabaseEngine').val(),
				host: $('#formDatabaseHost').val(),
				port: $('#formDatabasePort').val(),
				user: $('#formDatabaseUser').val(),
				pass: $('#formDatabasePass').val(),
				name: $('#formDatabaseName').val(),
				prefix: $('#formDatabasePrefix').val()
			}
		};
        var nextFunc = result('Create config file', installMigrations);
		$.post('?step=config', data)
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installMigrations() {
        var nextFunc = result('Create tables', installAdmin);
		$.post('?step=migrations')
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installAdmin() {
		var data = {
			login: $('#formAdminLogin').val(),
			email: $('#formAdminEmail').val(),
			pass: $('#formAdminPass').val()
		};
        var nextFunc = result('Create administrator', finish);
		$.post('?step=admin', data)
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function finish() {
        $('#formSubmitButton').prop('disabled', false);
        alert("Successfully installed");
        location.href = '<?= $baseUrl; ?>/';
	}

    UIkit.util.on('#installForm', 'submit', function (e) {
        e.preventDefault();
        e.target.blur();
        UIkit.modal.dialog('<ul class="uk-modal-body uk-list uk-width-1-1@m uk-list-divider" id="status"></ul><br><button id="close-btn" class="uk-button uk-button-secondary uk-modal-close" disabled>Close</button>', {
            container: true,
            bgClose: false,
            escClose: false
        });
        statusList = $('#status');
        statusList.empty();
        $('#formSubmitButton').prop('disabled', true);
        setTimeout(installChecks, 1000);
    });

</script>
</body>
</html>
