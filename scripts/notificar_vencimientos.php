<?php
/**
 * Script de notificaciones por email — pensado para correr 1 vez por día vía cron.
 *
 * Local (XAMPP, prueba manual):
 *   C:\xampp\php\php.exe C:\xampp\htdocs\timesaver-tasks\scripts\notificar_vencimientos.php
 *
 * Hostinger (cron job, diario a las 08:00):
 *   php /home/USUARIO/domains/TUDOMINIO/public_html/timesaver-tasks/scripts/notificar_vencimientos.php
 *
 * Avisa por email a cada usuario cuando una tarea/decisión asignada a él está
 * "próxima a vencer" (dentro de DIAS_AVISO_PROXIMO) o ya "vencida", una sola vez
 * por estado (columnas notificado_proximo / notificado_vencido evitan spam).
 */

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/models.php';

function enviar_email(string $to, string $asunto, string $cuerpo): void
{
    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
    // mail() nativo de PHP. Si Hostinger requiere SMTP autenticado, reemplazar
    // este bloque por PHPMailer configurado con las credenciales SMTP del hosting.
    @mail($to, $asunto, $cuerpo, $headers);
}

$pdo = db();

// --- Tareas ---
$tareas = $pdo->query("
    SELECT t.*, u.email AS asignado_email, u.nombre AS asignado_nombre
    FROM tareas t
    JOIN usuarios u ON u.id = t.asignado_a
    WHERE t.estado != 'hecha' AND t.fecha_limite IS NOT NULL
      AND (t.notificado_proximo = 0 OR t.notificado_vencido = 0)
")->fetchAll();

foreach ($tareas as $t) {
    $estado = estado_plazo($t['fecha_limite'], false);

    if ($estado === 'proximo' && !$t['notificado_proximo']) {
        enviar_email(
            $t['asignado_email'],
            'Tarea próxima a vencer: ' . $t['titulo'],
            "Hola {$t['asignado_nombre']},\n\nLa tarea \"{$t['titulo']}\" vence el " . formatear_fecha($t['fecha_limite']) . ".\n\nRevisala en Time Saver Tasks."
        );
        $pdo->prepare('UPDATE tareas SET notificado_proximo = 1 WHERE id = ?')->execute([$t['id']]);
    }

    if ($estado === 'vencido' && !$t['notificado_vencido']) {
        enviar_email(
            $t['asignado_email'],
            'Tarea vencida: ' . $t['titulo'],
            "Hola {$t['asignado_nombre']},\n\nLa tarea \"{$t['titulo']}\" venció el " . formatear_fecha($t['fecha_limite']) . " y sigue sin completarse.\n\nRevisala en Time Saver Tasks."
        );
        $pdo->prepare('UPDATE tareas SET notificado_vencido = 1 WHERE id = ?')->execute([$t['id']]);
    }
}

// --- Decisiones (se avisa a todos los participantes) ---
marcar_vencidas_automaticamente();

$decisiones = $pdo->query("
    SELECT * FROM decisiones
    WHERE estado != 'resuelta'
      AND (notificado_proximo = 0 OR notificado_vencido = 0)
")->fetchAll();

foreach ($decisiones as $d) {
    $estado = estado_plazo($d['fecha_limite'], false);
    if ($estado !== 'proximo' && $estado !== 'vencido') {
        continue;
    }

    $participantes = $pdo->prepare('SELECT u.email, u.nombre FROM decision_participantes dp JOIN usuarios u ON u.id = dp.usuario_id WHERE dp.decision_id = ?');
    $participantes->execute([$d['id']]);

    foreach ($participantes->fetchAll() as $p) {
        if ($estado === 'proximo' && !$d['notificado_proximo']) {
            enviar_email(
                $p['email'],
                'Decisión próxima a vencer: ' . $d['titulo'],
                "Hola {$p['nombre']},\n\nLa decisión \"{$d['titulo']}\" debe resolverse antes del " . formatear_fecha($d['fecha_limite']) . ".\n\nRevisala en Time Saver Tasks."
            );
        }
        if ($estado === 'vencido' && !$d['notificado_vencido']) {
            enviar_email(
                $p['email'],
                'Decisión vencida sin resolver: ' . $d['titulo'],
                "Hola {$p['nombre']},\n\nLa decisión \"{$d['titulo']}\" venció el " . formatear_fecha($d['fecha_limite']) . " sin resolución.\n\nRevisala en Time Saver Tasks."
            );
        }
    }

    if ($estado === 'proximo' && !$d['notificado_proximo']) {
        $pdo->prepare('UPDATE decisiones SET notificado_proximo = 1 WHERE id = ?')->execute([$d['id']]);
    }
    if ($estado === 'vencido' && !$d['notificado_vencido']) {
        $pdo->prepare('UPDATE decisiones SET notificado_vencido = 1 WHERE id = ?')->execute([$d['id']]);
    }
}

echo "Notificaciones procesadas: " . count($tareas) . " tareas revisadas, " . count($decisiones) . " decisiones revisadas.\n";
