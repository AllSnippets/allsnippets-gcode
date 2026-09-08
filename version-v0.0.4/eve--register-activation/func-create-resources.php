<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__create_required_resources__vsh0_0_4')) {
    function all_snippets__helper__create_required_resources__vsh0_0_4() {
        $all_constants = get_defined_constants(true);
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $value) {
                if (substr($name, -29) === '__WP_CONTENT__REQUIRED_RESOURCES' || substr($name, -30) === '__DATABASE__REQUIRED_RESOURCES') {
                    
                    // --- 1. BASE DIR --- //
                    if (isset($value['base_dir']) && !empty($value['base_dir'])) {
                        $base_dir = $value['base_dir'];
                        if (!is_dir($base_dir)) {
                            wp_mkdir_p($base_dir);
                        }
                        // Security index.php
                        $base_index = $base_dir . '/index.php';
                        if (!file_exists($base_index)) {
                            file_put_contents($base_index, '<?php defined(\'WPINC\') || die; ?>');
                        }
                    }
                    // --- 1. KONEC: BASE DIR --- //



                    // --- 2. SUB DIRECTORIES --- //
                    if (isset($value['directories']) && is_array($value['directories'])) {
                        foreach ($value['directories'] as $dir) {
                            if (!empty($dir)) {
                                if (!is_dir($dir)) {
                                    wp_mkdir_p($dir);
                                }
                                // Security index.php
                                $dir_index = $dir . '/index.php';
                                if (!file_exists($dir_index)) {
                                    file_put_contents($dir_index, '<?php defined(\'WPINC\') || die; ?>');
                                }
                            }
                        }
                    }
                    // --- 2. KONEC: SUB DIRECTORIES --- //



                    // --- 3. FILES --- //
                    if (isset($value['files']) && is_array($value['files'])) {
                        foreach ($value['files'] as $file_path => $content) {
                            if (empty($file_path)) {
                                continue;
                            }

                            if (!file_exists($file_path)) {
                                if (is_array($content) || is_object($content)) {
                                    $content = json_encode($content, JSON_PRETTY_PRINT);
                                }
                                file_put_contents($file_path, $content);
                            } elseif (is_string($content) && substr($file_path, -4) === '.csv') {
                                $expected_header = explode(',', $content);
                                $handle = fopen($file_path, 'r');
                                if ($handle !== false) {
                                    $current_header = fgetcsv($handle);
                                    if ($current_header !== $expected_header) {
                                        $rows_text = '';
                                        while (!feof($handle)) {
                                            $line = fgets($handle);
                                            if ($line === false) {
                                                break;
                                            }
                                            $rows_text .= $line;
                                        }
                                        fclose($handle);

                                        $out = fopen($file_path, 'w');
                                        if ($out !== false) {
                                            fputcsv($out, $expected_header);
                                            if ($rows_text !== '') {
                                                fwrite($out, $rows_text);
                                            }
                                            fclose($out);
                                        }
                                    } else {
                                        fclose($handle);
                                    }
                                }
                            }
                        }
                    }
                    // --- 3. KONEC: FILES --- //
                }
            }
        }
    }
}