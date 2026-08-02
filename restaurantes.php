<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_auth();

$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurantes | Food in Time</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            color: #172033;
            background: #eef3f8;
            font: 16px/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main {
            width: min(100%, 520px);
            padding: 32px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(26, 45, 78, .12);
        }
        select, button, a {
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            border-radius: 8px;
            font: inherit;
        }
        select { border: 1px solid #c9d3e0; }
        button {
            border: 0;
            color: #fff;
            background: #1769e0;
            font-weight: 700;
            cursor: pointer;
        }
        button:disabled { opacity: .55; cursor: not-allowed; }
        a { display: block; color: #1769e0; text-align: center; }
        .error { color: #8a1c1c; }
    </style>
</head>
<body>
    <main>
        <h1>Escolha o restaurante</h1>
        <p>Os pratos disponíveis serão carregados na próxima etapa.</p>

        <label for="restaurante">Restaurante</label>
        <select id="restaurante" disabled>
            <option>Carregando...</option>
        </select>

        <button id="continuar" type="button" disabled>Continuar</button>
        <p id="erro" class="error" role="alert"></p>
        <a href="boas_vindas.php">Voltar</a>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const select = document.querySelector('#restaurante');
        const button = document.querySelector('#continuar');
        const errorBox = document.querySelector('#erro');

        async function carregarRestaurantes() {
            const body = new URLSearchParams({ csrf_token: csrfToken });
            const response = await fetch('carregar_restaurantes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Não foi possível carregar os restaurantes.');
            }

            select.replaceChildren();

            for (const restaurante of data.restaurantes) {
                const option = document.createElement('option');
                option.value = restaurante.id_restaurante;
                option.textContent = restaurante.nome_restaurante;
                select.append(option);
            }

            const hasOptions = data.restaurantes.length > 0;
            select.disabled = !hasOptions;
            button.disabled = !hasOptions;
        }

        button.addEventListener('click', () => {
            const params = new URLSearchParams({ restaurante_id: select.value });
            window.location.href = 'pratos.php?' + params.toString();
        });

        carregarRestaurantes().catch(error => {
            select.replaceChildren(new Option('Indisponível', ''));
            errorBox.textContent = error.message;
        });
    </script>
</body>
</html>
