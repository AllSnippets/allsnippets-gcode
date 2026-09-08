<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__on_deactivation_delete_old_versions__vsh0_0_4')) {
    // Dodamo parameter, da vemo, kateri vtičnik se deaktivira (in ga ne upoštevamo kot aktivnega)
    function all_snippets__helper__on_deactivation_delete_old_versions__vsh0_0_4($deactivating_plugin_file = null) {
        // --- 1. SECURITY & INIT --- //
        // VAROVALKA: Če je definiran ALL_SNIPPETS_DEV_MODE (v wp-config.php), ne izvajaj ničesar.
        if (defined('ALL_SNIPPETS_DEV_MODE') && ALL_SNIPPETS_DEV_MODE) {
            return;
        }

        // Samo 1x na request
        static $checked = false;
        if ($checked) return;
        $checked = true;

        // Preveri, ali shared code mapa obstaja (uporabimo generično pot glede na wp-content/uploads)
        $shared_code_dir = rtrim(ALL_SNIPPETS__SHARED_CODE_DIR, '/');
        
        if (!is_dir($shared_code_dir)) {
            return; // Če mapa ne obstaja, ni nič za čiščenje
        }
        // --- 1. KONEC: SECURITY & INIT --- //



        // --- 2. GATHER REQUIRED VERSIONS --- //
        // Zberi vse potrebne verzije iz aktivnih AllSnippets vtičnikov
        $required_versions = [];
        
        // Uporabimo get_defined_constants(true) za detekcijo verzij iz aktivnih vtičnikov. To je bolj zanesljivo in hitrejše kot parsanje datotek, ter se izogne hardcoded potem.
        $all_constants = get_defined_constants(true);
        
        if (isset($all_constants['user'])) {
            foreach ($all_constants['user'] as $name => $value) {
                // Iščemo konstante oblike ALL_...__SHARED_CODE_VERSION (npr. ALL_BROKEN_MEDIA__SHARED_CODE_VERSION)
                if (preg_match('/^ALL_[A-Z0-9_]+__SHARED_CODE_VERSION$/', $name)) {
                    if (!in_array($value, $required_versions)) {
                        $required_versions[] = $value;
                    }
                }
            }
        }
        // --- 2. KONEC: GATHER REQUIRED VERSIONS --- //



        // --- 3. FIND VERSION FOLDERS --- //
        // OPOMBA: Če je $required_versions prazen, to pomeni, da ni nobenega aktivnega AllSnippets vtičnika več. V tem primeru želimo IZBRISATI VSE verzije (tudi to, ki se trenutno izvaja).
        
        // Poišči vse verzijske mape v shared-code-load mapi
        $folders_to_check = [];
        $iterator = new DirectoryIterator($shared_code_dir);
        
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $foldername = $fileinfo->getFilename();
                // Preveri, ali je mapa verzijska (vzorec: version-v0.0.1)
                if (preg_match('/^version-v(\d+\.\d+\.\d+)(\.[^.]+)?$/', $foldername, $matches)) {
                    $folder_version = $matches[1];
                    $folders_to_check[] = [
                        'path' => $fileinfo->getPathname(),
                        'version' => $folder_version,
                        'foldername' => $foldername
                    ];
                }
            }
        }
        // --- 3. KONEC: FIND VERSION FOLDERS --- //



        // --- 4. DELETE UNUSED VERSIONS --- //
        // Izbriši mape z verzijami, ki niso potrebne
        foreach ($folders_to_check as $folder_data) {
            // Če verzija mape ni v seznamu potrebnih verzij, jo izbriši
            if (!in_array($folder_data['version'], $required_versions)) {
                // Rekurzivno brisanje mape
                $dir = $folder_data['path'];
                if (is_dir($dir)) {
                    // Poskusimo izbrisati. Uporabimo @ za utišanje napak (npr. na Windows, če je datoteka v uporabi)
                    $it = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    
                    foreach ($it as $file) {
                        if ($file->isDir()) {
                            @rmdir($file->getPathname());
                        } else {
                            @unlink($file->getPathname());
                        }
                    }
                    @rmdir($dir);
                }
            }
        }
        // --- 4. KONEC: DELETE UNUSED VERSIONS --- //
    }
}