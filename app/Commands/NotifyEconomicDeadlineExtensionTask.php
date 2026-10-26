<?php

namespace App\Commands;

use App\Models\Concurso;
use App\Services\EmailService;
use Carbon\Carbon;

class NotifyEconomicDeadlineExtensionTask
{
    public function execute(array $args = [])
    {
        try {
            $options = $this->parseArgs($args);

            if ($options['help']) {
                echo $this->usage();
                return ['success' => true, 'help' => true];
            }

            if (!$options['concurso-id']) {
                throw new \RuntimeException('Debe indicar --concurso-id.');
            }

            $concurso = Concurso::with(['cliente.customer_company', 'oferentes.company.users', 'oferentes.invitation'])
                ->find((int) $options['concurso-id']);

            if (!$concurso) {
                throw new \RuntimeException('No se encontro el concurso indicado.');
            }
            if ($concurso->trashed()) {
                throw new \RuntimeException('El concurso esta eliminado o cancelado.');
            }
            $rawDeadline = $concurso->getOriginal('fecha_limite_economicas');
            if (!$rawDeadline) {
                throw new \RuntimeException('El concurso no tiene fecha limite economica.');
            }

            $timezone = $concurso->cliente->customer_company->timeZone ?: 'UTC';
            date_default_timezone_set($timezone);
            $deadline = Carbon::parse($rawDeadline, $timezone);
            if ($options['expected-date'] && $deadline->format('Y-m-d') !== $options['expected-date']) {
                throw new \RuntimeException(sprintf(
                    'La fecha economica actual es %s y no coincide con --expected-date=%s.',
                    $deadline->format('Y-m-d H:i'),
                    $options['expected-date']
                ));
            }

            $participants = $concurso->oferentes->filter(function ($participant) use ($options) {
                if (!$participant->invitation || !$participant->company) {
                    return false;
                }

                return !$options['active-only'] ||
                    (!$participant->rechazado && !$participant->is_concurso_rechazado);
            });

            $rows = [];
            $seenEmails = [];
            foreach ($participants as $participant) {
                $emails = $participant->company->users
                    ->pluck('email')
                    ->push($participant->company->email)
                    ->filter(function ($email) {
                        return filter_var($email, FILTER_VALIDATE_EMAIL);
                    })
                    ->map(function ($email) {
                        return strtolower(trim($email));
                    })
                    ->unique()
                    ->values()
                    ->all();

                $emails = array_values(array_filter($emails, function ($email) use (&$seenEmails) {
                    if (isset($seenEmails[$email])) {
                        return false;
                    }
                    $seenEmails[$email] = true;
                    return true;
                }));

                if (!empty($emails)) {
                    $rows[] = [
                        'company' => $participant->company,
                        'emails' => $emails,
                    ];
                }
            }

            $this->printPreview($concurso, $deadline, $rows, $options);

            if (!$options['send']) {
                echo "\nDRY RUN: no se enviaron correos. Agregue --send para confirmar el envio.\n";
                return [
                    'success' => true,
                    'dry_run' => true,
                    'companies' => count($rows),
                    'recipients' => count($seenEmails),
                ];
            }

            if (empty($rows)) {
                throw new \RuntimeException('No se encontraron destinatarios validos.');
            }

            $sent = 0;
            $errors = 0;
            $subject = $options['subject'] ?: sprintf(
                '%s - Extension del plazo para presentar ofertas economicas',
                $concurso->nombre
            );

            $this->withCustomerMailContext($concurso, function () use (
                $rows,
                $concurso,
                $deadline,
                $subject,
                &$sent,
                &$errors
            ) {
                foreach ($rows as $row) {
                    try {
                        $html = $this->renderEmail($concurso, $deadline, $row['company']->business_name);
                        $result = (new EmailService())->send(
                            $html,
                            $subject,
                            $row['emails'],
                            $row['company']->business_name
                        );

                        if (empty($result['success'])) {
                            throw new \RuntimeException($result['message'] ?? 'El servicio de correo rechazo el envio.');
                        }

                        $sent += count($row['emails']);
                        echo "ENVIADO: {$row['company']->business_name} (" . implode(', ', $row['emails']) . ")\n";
                    } catch (\Throwable $e) {
                        $errors += count($row['emails']);
                        echo "ERROR: {$row['company']->business_name}: {$e->getMessage()}\n";
                    }
                }
            });

            echo "\nResultado: {$sent} destinatarios enviados; {$errors} errores.\n";

            return [
                'success' => $errors === 0,
                'sent' => $sent,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            echo "ERROR: {$e->getMessage()}\n";
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function parseArgs(array $args)
    {
        $options = [
            'concurso-id' => null,
            'expected-date' => null,
            'subject' => null,
            'send' => false,
            'active-only' => false,
            'help' => false,
        ];

        foreach ($args as $index => $argument) {
            if ($index === 0 || substr($argument, 0, 2) !== '--') {
                continue;
            }

            $parts = explode('=', substr($argument, 2), 2);
            $key = strtolower($parts[0]);
            if (!array_key_exists($key, $options)) {
                continue;
            }

            if (in_array($key, ['send', 'active-only', 'help'], true)) {
                $options[$key] = true;
            } else {
                $options[$key] = $parts[1] ?? null;
            }
        }

        if ($options['expected-date']) {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $options['expected-date']);
            if (!$parsedDate || $parsedDate->format('Y-m-d') !== $options['expected-date']) {
                throw new \RuntimeException('--expected-date debe tener formato YYYY-MM-DD.');
            }
        }

        return $options;
    }

    private function printPreview($concurso, Carbon $deadline, array $rows, array $options)
    {
        echo "Concurso: #{$concurso->id} - {$concurso->nombre}\n";
        echo "Nueva fecha economica: {$deadline->format('d/m/Y H:i')} ({$deadline->getTimezone()->getName()})\n";
        echo 'Modo: ' . ($options['send'] ? 'ENVIO REAL' : 'DRY RUN') . "\n";
        echo 'Empresas destinatarias: ' . count($rows) . "\n";

        foreach ($rows as $row) {
            echo " - {$row['company']->business_name}: " . implode(', ', $row['emails']) . "\n";
        }
    }

    private function renderEmail($concurso, Carbon $deadline, $companyName)
    {
        $templatesPath = dirname(__DIR__, 2) . '/resources/views/templates';
        $compileDir = dirname(__DIR__, 2) . '/storage/tmp/templates_c';
        $cacheDir = dirname(__DIR__, 2) . '/storage/tmp/cache';

        foreach ([$compileDir, $cacheDir] as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException('No se pudo crear el directorio ' . $directory);
            }
        }

        $smarty = new \Smarty();
        $smarty->setTemplateDir($templatesPath);
        $smarty->setCompileDir($compileDir);
        $smarty->setCacheDir($cacheDir);
        $smarty->assign('title', 'Extension del plazo de presentacion economica');
        $smarty->assign('ano', Carbon::now()->format('Y'));
        $smarty->assign('concurso', $concurso);
        $smarty->assign('company_name', $companyName);
        $smarty->assign('new_deadline', $deadline->format('d/m/Y H:i'));
        $smarty->assign('timezone', $deadline->getTimezone()->getName());

        return $smarty->fetch($templatesPath . '/email/economic-deadline-extension.tpl');
    }

    private function withCustomerMailContext($concurso, callable $callback)
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }

        $hadContext = array_key_exists('customer_company_id', $_SESSION);
        $previousContext = $hadContext ? $_SESSION['customer_company_id'] : null;
        $_SESSION['customer_company_id'] = $concurso->cliente->customer_company_id;

        try {
            $callback();
        } finally {
            if ($hadContext) {
                $_SESSION['customer_company_id'] = $previousContext;
            } else {
                unset($_SESSION['customer_company_id']);
            }
        }
    }

    private function usage()
    {
        return <<<TXT
Uso:
  php crons/notify_economic_deadline_extension.php --concurso-id=ID --expected-date=YYYY-MM-DD
  php crons/notify_economic_deadline_extension.php --concurso-id=ID --expected-date=YYYY-MM-DD --send

Opciones:
  --concurso-id=ID       Concurso cuya fecha economica fue extendida (obligatorio).
  --expected-date=FECHA  Verifica la fecha guardada antes de enviar (recomendado).
  --subject=TEXTO        Asunto personalizado.
  --active-only          Excluye oferentes rechazados o que declinaron.
  --send                 Confirma el envio real. Sin esta opcion solo muestra destinatarios.
  --help                 Muestra esta ayuda.

TXT;
    }
}
