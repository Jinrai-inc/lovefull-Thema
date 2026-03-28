<?php
/**
 * CSVインポート処理ロジック
 *
 * @package KoiRiaPortal
 */

defined('ABSPATH') || exit;

function koi_ria_handle_csv_import(): array {
    $summary = [
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'error'   => 0,
        'errors'  => [],
    ];

    if (empty($_FILES['csv_file']['tmp_name'])) {
        $summary['error'] = 1;
        $summary['errors'][] = 'ファイルがアップロードされていません';
        return $summary;
    }

    $type = sanitize_text_field($_POST['import_type'] ?? '');
    $mode = sanitize_text_field($_POST['import_mode'] ?? 'upsert');

    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$file) {
        $summary['error'] = 1;
        $summary['errors'][] = 'ファイルを開けませんでした';
        return $summary;
    }

    // BOMスキップ
    $bom = fread($file, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($file);
    }

    // ヘッダー行
    $headers = fgetcsv($file);
    if (!$headers) {
        fclose($file);
        $summary['error'] = 1;
        $summary['errors'][] = 'CSVヘッダーが読み取れません';
        return $summary;
    }

    $headers = array_map('trim', $headers);
    $line = 1;

    while (($data = fgetcsv($file)) !== false) {
        $line++;
        if (count($data) !== count($headers)) {
            $summary['error']++;
            $summary['errors'][] = "行{$line}: カラム数が一致しません";
            continue;
        }

        $row = array_combine($headers, array_map('trim', $data));

        $result = match ($type) {
            'show'     => koi_ria_upsert_show($row, $mode),
            'season'   => koi_ria_upsert_season($row, $mode),
            'cast'     => koi_ria_upsert_cast($row, $mode),
            'relation' => koi_ria_upsert_relation($row, $mode),
            default    => ['status' => 'error', 'message' => "不明なインポートタイプ: {$type}"],
        };

        switch ($result['status']) {
            case 'created':
                $summary['created']++;
                break;
            case 'updated':
                $summary['updated']++;
                break;
            case 'skipped':
                $summary['skipped']++;
                break;
            case 'error':
                $summary['error']++;
                $summary['errors'][] = "行{$line}: " . ($result['message'] ?? '不明なエラー');
                break;
        }
    }

    fclose($file);
    return $summary;
}
