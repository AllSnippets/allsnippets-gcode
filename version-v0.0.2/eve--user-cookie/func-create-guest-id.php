<?php defined('WPINC') || die;
if (!function_exists('all_snippets__helper__get_guest_user_id__vsh0_0_2')) {
    function all_snippets__helper__get_guest_user_id__vsh0_0_2() {
        // --- 1. INPUT VALIDATION --- //
        // Preverimo obstoj piškotka
        if (isset($_COOKIE['all-snippets__visitor-id'])) {
            return $_COOKIE['all-snippets__visitor-id'];
        }
        // --- 1. KONEC: INPUT VALIDATION --- //



        // --- 2. MAIN LOGIC --- //
        // Če piškotek ne obstaja, ustvarimo novega
        $guest_id = 'guest_' . bin2hex(random_bytes(5)); // Ustvari krajši unikatni ID
        setcookie('all-snippets__visitor-id', $guest_id, time() + (86400 * 30), "/", "", true, true); // Shrani za 30 dni
        // --- 2. KONEC: MAIN LOGIC --- //



        // --- 3. RETURN --- //
        return $guest_id;
        // --- 3. KONEC: RETURN --- //
    }
}
