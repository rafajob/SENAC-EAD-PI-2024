<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_auth();

$restaurantId = filter_input(INPUT_GET, 'restaurante_id', FILTER_VALIDATE_INT);

if (!$restaurantId) {
    header('Location: restaurantes.php');
    exit();
}

$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pratos | Food in Time</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            color: #172033;
            background: #eef3f8;
            font: 16px/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main {
            width: min(100%, 640px);
            margin: 40px auto;
            padding: 32px;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 40px rgba(26, 45, 78, .12);
        }
        label { display: block; margin-top: 16px; font-weight: 600; }
        input, select, button {
            width: 100%;
            margin-top: 6px;
            padding: 12px;
            border-radius: 8px;
            font: inherit;
        }
        input, select { border: 1px solid #c9d3e0; }
        button {
            margin-top: 24px;
            border: 0;
            color: #fff;
            background: #1769e0;
            font-weight: 700;
            cursor: pointer;
        }
        button:disabled { opacity: .55; cursor: not-allowed; }
        .error { color: #8a1c1c; }
        a { display: block; margin-top: 16px; color: #1769e0; text-align: center; }
    </style>
</head>
<body>
    <main>
        <h1>Selecione os pratos</h1>

        <form method="post" action="confirmacao_pedido.php" id="pratoForm">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="restaurant_id" value="<?= e($restaurantId) ?>">

            <label for="quantidade">Número de pratos</label>
            <input type="number" id="quantidade" min="1" max="20" value="1">

            <div id="selecoes"></div>
            <p id="erro" class="error" role="alert"></p>

            <button type="submit" id="continuar" disabled>Revisar pedido</button>
        </form>

        <a href="restaurantes.php">Escolher outro restaurante</a>
    </main>

    <script>
        const csrfToken = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const restaurantId = <?= json_encode($restaurantId) ?>;
        const quantityInput = document.querySelector('#quantidade');
        const selections = document.querySelector('#selecoes');
        const submitButton = document.querySelector('#continuar');
        const errorBox = document.querySelector('#erro');
        let dishes = [];

        function renderSelections() {
            const quantity = Math.min(20, Math.max(1, Number(quantityInput.value) || 1));
            quantityInput.value = quantity;
            selections.replaceChildren();

            for (let index = 0; index < quantity; index += 1) {
                const label = document.createElement('label');
                label.htmlFor = 'prato-' + index;
                label.textContent = 'Prato ' + (index + 1);

                const select = document.createElement('select');
                select.id = 'prato-' + index;
                select.name = 'prato_ids[]';
                select.required = true;

                for (const dish of dishes) {
                    const option = document.createElement('option');
                    option.value = dish.id_prato;
                    option.textContent = dish.nome_prato;
                    select.append(option);
                }

                selections.append(label, select);
            }
        }

        async function carregarPratos() {
            const body = new URLSearchParams({
                csrf_token: csrfToken,
                restaurante_id: restaurantId
            });
            const response = await fetch('selecao_pratos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Não foi possível carregar os pratos.');
            }

            dishes = data.pratos;

            if (dishes.length === 0) {
                throw new Error('Este restaurante ainda não possui pratos cadastrados.');
            }

            renderSelections();
            submitButton.disabled = false;
        }

        quantityInput.addEventListener('change', renderSelections);

        carregarPratos().catch(error => {
            errorBox.textContent = error.message;
            submitButton.disabled = true;
        });
    </script>
</body>
</html>
