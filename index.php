<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

header('Location: ' . (is_authenticated() ? 'boas_vindas.php' : 'login.php'));
exit();
