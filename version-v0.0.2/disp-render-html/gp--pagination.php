<?php defined('WPINC') || die; ?>
<div class="gp-pagination--root gp-sidebar--adaptive-margin">

    <!-- Leva stran: Rows per page -->
    <div class="gp-pagination--limit-wrap">
        <label class="gp-pagination--limit-label">Rows per page:</label>
        <div class="gp-pagination--limit-controls">
            <input type="number" name="limit" class="gp-pagination--limit-input" value="<?php echo $alsnp__generate_userpref__limit; ?>" min="1" max="500">
            <button type="button" class="gp-pagination--limit-btn">Apply</button>
        </div>
    </div>

    <!-- Sredina: Back + številke strani + Next -->
    <div class="gp-pagination--arrow-number-root">

        <!-- Back gumb -->
        <?php if ($alsnp__generate_url__page > 1) : ?>
            <a class="gp-pagination--arrow-link"
               href="<?php echo add_query_arg(['paged' => max(1, $alsnp__generate_url__page - 1)], admin_url('admin.php?page=' . $page_url . '')); ?>">
                <span>&lt;</span>
                <span>Back</span>
            </a>
        <?php else: ?>
            <span class="gp-pagination--arrow-link disabled">
                <span>&lt;</span>
                <span>Back</span>
            </span>
        <?php endif; ?>

        <!-- Številke strani -->
        <div class="gp-pagination--pages-number-root">
            <?php
            $current_page = $alsnp__generate_url__page;
            $total_pages = $alsnp__count_total_refin_pages;
            $range = 2; // Koliko številk pred in za trenutno stran

            // Prva stran
            if ($current_page > $range + 2) {
                ?>
                <a class="gp-pagination--page-number-link"
                   href="<?php echo add_query_arg(['paged' => 1], admin_url('admin.php?page=' . $page_url . '')); ?>">1</a>
                <span class="gp-pagination--page-number-dots-separator">...</span>
                <?php
            }

            // Strani okoli trenutne
            for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
                if ($i == $current_page) {
                    ?>
                    <span class="gp-pagination--page-number-link current-page"><?php echo $i; ?></span>
                    <?php
                } else {
                    ?>
                    <a class="gp-pagination--page-number-link"
                       href="<?php echo add_query_arg(['paged' => $i], admin_url('admin.php?page=' . $page_url . '')); ?>"><?php echo $i; ?></a>
                    <?php
                }
            }

            // Zadnja stran
            if ($current_page < $total_pages - $range - 1) {
                ?>
                <span class="gp-pagination--page-number-dots-separator">...</span>
                <a class="gp-pagination--page-number-link"
                   href="<?php echo add_query_arg(['paged' => $total_pages], admin_url('admin.php?page=' . $page_url . '')); ?>"><?php echo $total_pages; ?></a>
                <?php
            }
            ?>
        </div>

        <!-- Next gumb -->
        <?php if ($alsnp__generate_url__page < $alsnp__count_total_refin_pages) : ?>
            <a class="gp-pagination--arrow-link"
               href="<?php echo add_query_arg(['paged' => min($alsnp__count_total_refin_pages, $alsnp__generate_url__page + 1)], admin_url('admin.php?page=' . $page_url . '')); ?>">
                <span>Next</span>
                <span>&gt;</span>
            </a>
        <?php else: ?>
            <span class="gp-pagination--arrow-link disabled">
                <span>Next</span>
                <span>&gt;</span>
            </span>
        <?php endif; ?>
    </div>

    <!-- Desna stran: Page input + Go gumb -->
    <div class="gp-pagination--goto-wrap">
        <label class="gp-pagination--goto-label">Page</label>
        <div class="gp-pagination--goto-controls">
            <input type="number" name="paged" class="gp-pagination--goto-input" value="<?php echo $alsnp__generate_url__page; ?>" min="1" max="<?php echo $alsnp__count_total_refin_pages; ?>">
            <div class="gp-pagination--goto-validation-notice hidden">Maximum value is <?php echo $alsnp__count_total_refin_pages; ?>.</div>
            <button type="button" class="gp-pagination--goto-btn">Go</button>
        </div>
    </div>

</div>