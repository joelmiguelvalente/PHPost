<?php

declare(strict_types=1);

/**
 * @package    PHPost
 * @subpackage Install
 * @author     Miguel92
 * @copyright  2026
 */

require_once __DIR__ . '/src/bootstrap.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($tsTitle) ?></title>
<link rel="stylesheet" href="./assets/css/style.css?v=<?= time() ?>">
</head>
<body>

    <div class="container" role="main">
        <header>
            <small>Instalación de</small>
            <h1><?= Config::app('app.name') ?> <span>v<?= Config::app('app.version') ?></span></h1>
        </header>
        <div class="columns">
            <aside role="navigation" aria-label="Pasos de instalación">
                <?php foreach($Step::LABELS as $slug => $page): ?>
                    <div class="item<?= $slug === $Step->current() ? ' active' : '' ?>" aria-current="<?= $slug === $Step->current() ? 'step' : 'false' ?>">
                        <?= $page ?>
                    </div>
                <?php endforeach; ?>
            </aside>
            <main>
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="error-box" role="alert">
                        <h2>⚠️ Error</h2>
                        <p><?= htmlspecialchars($error) ?></p>
                        <a href="?step=bienvenida" class="btn btn-primary">
                            Volver al inicio
                        </a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="error-box" role="alert">
                            <h3>⚠️ Errores detectados</h3>
                            <?php foreach($errors as $error): ?>
                                <p><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <fieldset>
                            <?php require_once __DIR__ . '/pasos/' . $Step->current() . '.php'; ?>
                        </fieldset>
                    </form>
                <?php endif; ?>
            </main>
        </div>
        <footer>
            <p><strong>PHPost</strong> - Copyright &copy; 2022-<?= date('Y') ?></p>
            <p class="footer-credit">Creado por: Miguel92 · <a href="<?= Config::app('app.contact.repository') ?>" target="_blank" rel="noopener noreferrer">GitHub</a></p>
        </footer>
    </div>

</body>
</html>
