<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__csv_read_headers__vsh0_0_2')) {
    function all_snippets__helper__csv_read_headers__vsh0_0_2($csv_file, array $expected_headers = []) {
        // --- 1. INPUT VALIDATION --- //
        if (empty($csv_file)) {
            return null;
        }
        if (!file_exists($csv_file) && function_exists('all_snippets__helper__create_required_resources__vsh0_0_2')) {
            all_snippets__helper__create_required_resources__vsh0_0_2();
        }
        if (!file_exists($csv_file)) {
            return null;
        }
        $header_string = !empty($expected_headers) ? implode(',', $expected_headers) : '';
        $handle = fopen($csv_file, 'r');
        if ($handle === false) {
            return null;
        }
        $headers = fgetcsv($handle);
        if (!is_array($headers) || empty($headers)) {
            if (!empty($expected_headers)) {
                $headers = $expected_headers;
            }
        } elseif (!empty($expected_headers) && implode(',', $headers) !== $header_string) {
            fclose($handle);
            $handle = null;
            if (function_exists('all_snippets__helper__create_required_resources__vsh0_0_2')) {
                all_snippets__helper__create_required_resources__vsh0_0_2();
            }
            if (!file_exists($csv_file)) {
                return null;
            }
            $handle = fopen($csv_file, 'r');
            if ($handle === false) {
                return null;
            }
            $headers = fgetcsv($handle);
            if (!is_array($headers) || empty($headers)) {
                if (!empty($expected_headers)) {
                    $headers = $expected_headers;
                }
            }
        }
        if (empty($headers) && !empty($expected_headers)) {
            $headers = $expected_headers;
        }
        if (empty($headers)) {
            fclose($handle);
            return null;
        }
        // --- 1. KONEC: INPUT VALIDATION --- //


        // --- 2. MAIN LOGIC --- //
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (!is_array($row) || empty($row)) {
                continue;
            }
            if (!empty($headers) && count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }
            $rows[] = $row;
        }
        fclose($handle);
        // --- 2. KONEC: MAIN LOGIC --- //


        // --- 3. RETURN --- //
        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
        // --- 3. KONEC: RETURN --- //
    }
}