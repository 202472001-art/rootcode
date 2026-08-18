<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

try {
    $rows = db()->query('SELECT palabras_clave, respuesta FROM chatbot_respuestas WHERE activo = 1 ORDER BY prioridad DESC, id ASC')->fetchAll();
    $data = array_map(static function (array $row): array {
        $words = array_values(array_filter(array_map('trim', explode(',', mb_strtolower($row['palabras_clave'])))));
        return ['words' => $words, 'answer' => $row['respuesta']];
    }, $rows);
    echo json_encode(['ok' => true, 'answers' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'answers' => []], JSON_UNESCAPED_UNICODE);
}
