<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>WM Suplementos — Em manutenção</title>
<link rel="icon" type="image/png" href="/assets/img/logo.png">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body {
        height: 100%;
        background: #0a0a0a;
    }
    body {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }
    .logo-wrap {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5vw;
    }
    .logo-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle, rgba(220,20,20,0.35) 0%, rgba(220,20,20,0) 70%);
        filter: blur(20px);
    }
    .logo-wrap img {
        position: relative;
        width: min(90vw, 480px);
        height: auto;
    }
</style>
</head>
<body>
    <div class="logo-wrap">
        <img src="/assets/img/logo-wm-novo.png" alt="WM Suplementos">
    </div>
</body>
</html>
