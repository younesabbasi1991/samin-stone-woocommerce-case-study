<?php

defined('ABSPATH') || exit;

/**
 * Return the published catalog's usable price bounds.
 *
 * The lookup table avoids loading every product object just to calculate
 * the minimum and maximum values used by the range controls.
 *
 * @return array{min: float, max: float}
 */
function stonesamin_get_store_price_bounds(): array
{
    static $bounds = null;

    if (is_array($bounds)) {
        return $bounds;
    }

    global $wpdb;

    $lookupTable = $wpdb->prefix . 'wc_product_meta_lookup';

    $result = $wpdb->get_row(
        "
        SELECT
            MIN(price_lookup.min_price) AS min_price,
            MAX(price_lookup.max_price) AS max_price
        FROM {$lookupTable} AS price_lookup
        INNER JOIN {$wpdb->posts} AS products
            ON products.ID = price_lookup.product_id
        WHERE products.post_type = 'product'
            AND products.post_status = 'publish'
            AND price_lookup.min_price IS NOT NULL
            AND price_lookup.max_price IS NOT NULL
        ",
        ARRAY_A
    );

    $minimum = isset($result['min_price'])
        ? floor((float) $result['min_price'])
        : 0.0;

    $maximum = isset($result['max_price'])
        ? ceil((float) $result['max_price'])
        : 1.0;

    if ($maximum <= $minimum) {
        $maximum = $minimum + 1;
    }

    $bounds = [
        'min' => $minimum,
        'max' => $maximum,
    ];

    return $bounds;
}

function stonesamin_remove_price_query_var(string $queryKey): void
{
    unset($_GET[$queryKey], $_REQUEST[$queryKey]);
}

/**
 * Keep blank or unchanged range inputs out of WooCommerce's price query.
 */
function stonesamin_normalize_price_filter_query(): void
{
    foreach (['min_price', 'max_price'] as $queryKey) {
        if (!isset($_GET[$queryKey])) {
            continue;
        }

        $rawValue = wp_unslash($_GET[$queryKey]);

        if (is_array($rawValue) || '' === trim((string) $rawValue)) {
            stonesamin_remove_price_query_var($queryKey);
            continue;
        }

        $normalizedValue = wc_format_decimal($rawValue);

        if ('' === $normalizedValue) {
            stonesamin_remove_price_query_var($queryKey);
            continue;
        }

        $_GET[$queryKey] = $normalizedValue;
        $_REQUEST[$queryKey] = $normalizedValue;
    }

    if (!isset($_GET['min_price'], $_GET['max_price'])) {
        return;
    }

    $bounds = stonesamin_get_store_price_bounds();

    if (
        (float) $_GET['min_price'] <= $bounds['min']
        && (float) $_GET['max_price'] >= $bounds['max']
    ) {
        stonesamin_remove_price_query_var('min_price');
        stonesamin_remove_price_query_var('max_price');
    }
}

add_action('init', 'stonesamin_normalize_price_filter_query', 30);

/**
 * Read a list of positive term IDs from a public query parameter.
 *
 * @return int[]
 */
function stonesamin_get_filter_term_ids(string $queryKey): array
{
    if (!isset($_GET[$queryKey])) {
        return [];
    }

    $rawValues = (array) wp_unslash($_GET[$queryKey]);
    $termIds = array_map('absint', $rawValues);

    return array_values(array_unique(array_filter($termIds)));
}

/**
 * Build one OR group across the catalog's three classification dimensions.
 * Existing WooCommerce constraints stay in the outer AND group.
 *
 * @param array<int|string, mixed> $taxQuery
 * @return array<int|string, mixed>
 */
function stonesamin_apply_product_taxonomy_filters(array $taxQuery): array
{
    $filters = [
        'ss_materials'  => 'ss_stone_material',
        'ss_usages'     => 'ss_stone_usage',
        'ss_categories' => 'product_cat',
    ];

    $filterGroup = ['relation' => 'OR'];

    foreach ($filters as $queryKey => $taxonomy) {
        if (!taxonomy_exists($taxonomy)) {
            continue;
        }

        $termIds = stonesamin_get_filter_term_ids($queryKey);

        if ([] === $termIds) {
            continue;
        }

        $filterGroup[] = [
            'taxonomy'         => $taxonomy,
            'field'            => 'term_id',
            'terms'            => $termIds,
            'operator'         => 'IN',
            'include_children' => is_taxonomy_hierarchical($taxonomy),
        ];
    }

    if (count($filterGroup) > 1) {
        $taxQuery[] = $filterGroup;
    }

    return $taxQuery;
}

add_filter(
    'woocommerce_product_query_tax_query',
    'stonesamin_apply_product_taxonomy_filters'
);

/**
 * Fetch visible filter terms without coupling the template to get_terms().
 *
 * @return WP_Term[]
 */
function stonesamin_get_filter_terms(string $taxonomy): array
{
    if (!taxonomy_exists($taxonomy)) {
        return [];
    }

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    return is_wp_error($terms) ? [] : $terms;
}

/**
 * Render a filter option. The value is escaped even though IDs are numeric,
 * and the visible name is escaped independently.
 */
function stonesamin_render_filter_term(
    WP_Term $term,
    string $queryKey,
    array $selectedIds
): void {
    $inputId = sprintf(
        'stonesamin-filter-%s-%d',
        sanitize_html_class($queryKey),
        $term->term_id
    );
    ?>
    <label
        class="stonesamin-filter-checkbox"
        for="<?php echo esc_attr($inputId); ?>">
        <input
            id="<?php echo esc_attr($inputId); ?>"
            type="checkbox"
            name="<?php echo esc_attr($queryKey); ?>[]"
            value="<?php echo esc_attr((string) $term->term_id); ?>"
            <?php checked(in_array($term->term_id, $selectedIds, true)); ?>>

        <span class="stonesamin-filter-checkbox__control" aria-hidden="true"></span>
        <span class="stonesamin-filter-checkbox__name">
            <?php echo esc_html($term->name); ?>
        </span>
        <span class="stonesamin-filter-checkbox__count">
            <?php echo esc_html(number_format_i18n($term->count)); ?>
        </span>
    </label>
    <?php
}
