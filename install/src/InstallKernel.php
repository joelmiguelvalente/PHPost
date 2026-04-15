<?php

/**
 * ------------------------------------------------------------
 * InstallKernel
 * ------------------------------------------------------------
 * Orquesta el ciclo de vida completo del instalador:
 * bootstrap → routing → dispatch → render.
 *
 * @package   PHPost\Install
 * @copyright 2026
 */

declare(strict_types=1);

final class InstallKernel
{
    private StepRegistry     $registry;
    private InstallerSession $session;
    private InstallerRequest $request;
    private InstallerLogger  $logger;
    private LockManager      $lock;
    private string           $baseUrl;
    private string           $siteUrl;

    public function __construct()
    {
        $this->bootstrap();
    }

    // ── Punto de entrada ─────────────────────────────────────

    public function run(): void
    {
        // Si ya está instalado, redirigir
        if ($this->lock->isLocked()) {
            header('Location: ../');
            exit;
        }

        $step = $this->request->getStep();

        // Paso por defecto
        if ($step === '') {
            header('Location: index.php?step=bienvenida');
            exit;
        }

        // Paso desconocido
        if (!$this->registry->has($step)) {
            $this->renderError('El paso solicitado no existe.');
            return;
        }

        // Control de acceso por flujo
        if ($step !== 'bienvenida' && !$this->session->canAccess($step, StepRegistry::FLOW)) {
            header('Location: index.php?step=bienvenida');
            exit;
        }

        // Dispatch al handler
        $handler = $this->registry->get($step);
        $result  = $handler->handle($this->request);

        // Redirigir
        if ($result->isRedirect()) {
            $this->session->markCompleted($step);
            if ($step === 'bienvenida') {
                $this->session->acceptLicense();
            }
            header('Location: index.php?step=' . $result->getRedirect());
            exit;
        }

        // Renderizar la vista
        $this->render($step, $result);
    }

    // ── Bootstrap ────────────────────────────────────────────

    private function bootstrap(): void
    {
        define('TS_STORAGE', dirname(__DIR__, 2) . '/storage');

        require_once dirname(__DIR__, 2) . '/config/Config.php';
        require_once dirname(__DIR__, 2) . '/src/Utils/Extras.php';

        // Autoload del instalador
        spl_autoload_register(function (string $class): void {
            $map = [
                'StepHandlerInterface' => __DIR__ . '/Contracts/StepHandlerInterface.php',
                'StepResult'           => __DIR__ . '/StepResult.php',
                'InstallerRequest'     => __DIR__ . '/InstallerRequest.php',
                'InstallerSession'     => __DIR__ . '/InstallerSession.php',
                'InstallerLogger'      => __DIR__ . '/InstallerLogger.php',
                'StepRegistry'         => __DIR__ . '/StepRegistry.php',
                'Validator'            => __DIR__ . '/Validator.php',
                'ConfigWriter'         => __DIR__ . '/ConfigWriter.php',
                'LockManager'          => __DIR__ . '/LockManager.php',
                // Handlers
                'WelcomeHandler'       => __DIR__ . '/Steps/WelcomeHandler.php',
                'PermissionsHandler'   => __DIR__ . '/Steps/PermissionsHandler.php',
                'DatabaseHandler'      => __DIR__ . '/Steps/DatabaseHandler.php',
                'MailerHandler'        => __DIR__ . '/Steps/MailerHandler.php',
                'SiteHandler'          => __DIR__ . '/Steps/SiteHandler.php',
                'AdminHandler'         => __DIR__ . '/Steps/AdminHandler.php',
                'FinalizeHandler'      => __DIR__ . '/Steps/FinalizeHandler.php',
            ];
            if (isset($map[$class])) {
                require_once $map[$class];
            }
        });

        require_once dirname(__DIR__, 1) . '/connection.php';

        // Configurar errores según entorno
        $debug = Config::app('debug.active') ?? false;
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');
        error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);

        // Sesión segura
        $secCfg = Config::app('security.session');
        session_name($secCfg['name'] ?? 'phpost_install');
        if (!headers_sent()) {
            session_start([
                'cookie_secure'   => $secCfg['secure']   ?? false,
                'cookie_httponly' => $secCfg['httponly']  ?? true,
                'cookie_samesite' => $secCfg['samesite']  ?? 'Lax',
            ]);
        }

        // Infraestructura
        $extras         = new Extras();
        $ssl            = $extras->getSSLProtocol(true);
        $local          = dirname($_SERVER['REQUEST_URI'] ?? '/', 2);
        $this->siteUrl  = $ssl . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $local;
        $this->baseUrl  = $this->siteUrl . '/install';
        $this->logger   = new InstallerLogger(__DIR__ . '/../install');
        $this->lock     = new LockManager(dirname(__DIR__, 2));
        $this->session  = new InstallerSession();
        $this->request  = InstallerRequest::fromGlobals();
        $this->registry = new StepRegistry();

        // Registrar handlers
        $configWriter = new ConfigWriter();
        $this->registry->register(new WelcomeHandler());
        $this->registry->register(new PermissionsHandler());
        $this->registry->register(new DatabaseHandler($configWriter, $this->logger));
        $this->registry->register(new MailerHandler($configWriter, $this->logger));
        $this->registry->register(new SiteHandler($this->siteUrl, $extras, $this->logger));

        require_once dirname(__DIR__, 2) . '/src/Utils/PasswordHandler.php';
        $this->registry->register(new AdminHandler($this->siteUrl, new PasswordHandler(), $this->logger));
        $this->registry->register(new FinalizeHandler($this->lock, $this->logger));
    }

    // ── Render ───────────────────────────────────────────────

    private function render(string $step, StepResult $result): void
    {
        $data    = $result->getData();
        $error   = $result->getError();
        $baseUrl = $this->baseUrl;
        $siteUrl = $this->siteUrl;

        require dirname(__DIR__, 1) . '/views/layout.php';
    }

    private function renderError(string $message): void
    {
        http_response_code(400);
        $error = $message;
        require dirname(__DIR__, 1) . '/views/error.php';
    }
}
