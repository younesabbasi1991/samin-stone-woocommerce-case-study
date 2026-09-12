<?php

defined('ABSPATH') || exit;

const STONESAMIN_PRODUCT_UNIT_META_KEY = '_stonesamin_product_unit';

/**
 * @return array<string, string>
 */
function stonesamin_get_product_unit_choices(): array
{
    return [
        'square_meter' => 'متر مربع',
        'linear_meter' => 'متر طول',
        'piece'        => 'عدد',
    ];
}

function stonesamin_get_product_unit(WC_Product $product): string
{
    $unit = (string) $product->get_meta(
        STONESAMIN_PRODUCT_UNIT_META_KEY,
        true
    );

    if ('' === $unit && $product instanceof WC_Product_Variation) {
        $parent = wc_get_product($product->get_parent_id());

        if ($parent instanceof WC_Product) {
            $unit = (string) $parent->get_meta(
                STONESAMIN_PRODUCT_UNIT_META_KEY,
                true
            );
        }
    }

    return array_key_exists($unit, stonesamin_get_product_unit_choices())
        ? $unit
        : 'square_meter';
}

function stonesamin_get_product_unit_label(WC_Product $product): string
{
    $choices = stonesamin_get_product_unit_choices();

    return $choices[stonesamin_get_product_unit($product)];
}

function stonesamin_render_product_unit_field(): void
{
    woocommerce_wp_select([
        'id'          => STONESAMIN_PRODUCT_UNIT_META_KEY,
        'label'       => 'واحد سفارش',
        'description' => 'واحدی که مشتری هنگام تعیین تعداد مشاهده می‌کند.',
        'desc_tip'    => true,
        'options'     => stonesamin_get_product_unit_choices(),
        'value'       => get_post_meta(
            get_the_ID(),
            STONESAMIN_PRODUCT_UNIT_META_KEY,
            true
        ) ?: 'square_meter',
    ]);
}

add_action(
    'woocommerce_product_options_inventory_product_data',
    'stonesamin_render_product_unit_field'
);

function stonesamin_save_product_unit(WC_Product $product): void
{
    if (!isset($_POST[STONESAMIN_PRODUCT_UNIT_META_KEY])) {
        return;
    }

    $unit = sanitize_key(
        wp_unslash($_POST[STONESAMIN_PRODUCT_UNIT_META_KEY])
    );

    if (!array_key_exists($unit, stonesamin_get_product_unit_choices())) {
        $unit = 'square_meter';
    }

    $product->update_meta_data(STONESAMIN_PRODUCT_UNIT_META_KEY, $unit);
}

add_action(
    'woocommerce_admin_process_product_object',
    'stonesamin_save_product_unit'
);

function stonesamin_render_quantity_unit_label(): void
{
    global $product;

    if (!$product instanceof WC_Product) {
        return;
    }
    ?>
    <div class="stonesamin-product-unit" aria-live="polite">
        <span class="stonesamin-product-unit__caption">واحد سفارش:</span>
        <strong class="stonesamin-product-unit__value">
            <?php echo esc_html(stonesamin_get_product_unit_label($product)); ?>
        </strong>
    </div>
    <?php
}

add_action(
    'woocommerce_before_add_to_cart_quantity',
    'stonesamin_render_quantity_unit_label'
);

/**
 * Store a label snapshot in the cart. If an administrator changes the product
 * later, an existing customer's cart still describes the unit they selected.
 *
 * @param array<string, mixed> $cartItemData
 * @return array<string, mixed>
 */
function stonesamin_add_product_unit_to_cart_item(
    array $cartItemData,
    int $productId,
    int $variationId
): array {
    $resolvedProductId = $variationId > 0 ? $variationId : $productId;
    $product = wc_get_product($resolvedProductId);

    if (!$product instanceof WC_Product) {
        return $cartItemData;
    }

    $cartItemData['stonesamin_product_unit'] = [
        'key'   => stonesamin_get_product_unit($product),
        'label' => stonesamin_get_product_unit_label($product),
    ];

    return $cartItemData;
}

add_filter(
    'woocommerce_add_cart_item_data',
    'stonesamin_add_product_unit_to_cart_item',
    10,
    3
);

/**
 * @param array<int, array{key: string, value: string}> $itemData
 * @param array<string, mixed> $cartItem
 * @return array<int, array{key: string, value: string}>
 */
function stonesamin_display_product_unit_in_cart(
    array $itemData,
    array $cartItem
): array {
    $unit = $cartItem['stonesamin_product_unit'] ?? null;

    if (is_array($unit) && !empty($unit['label'])) {
        $itemData[] = [
            'key'   => 'واحد سفارش',
            'value' => sanitize_text_field((string) $unit['label']),
        ];
    }

    return $itemData;
}

add_filter(
    'woocommerce_get_item_data',
    'stonesamin_display_product_unit_in_cart',
    10,
    2
);

/**
 * Persist the same snapshot as order-line metadata for fulfilment and support.
 *
 * @param array<string, mixed> $values
 */
function stonesamin_save_product_unit_to_order_item(
    WC_Order_Item_Product $item,
    string $cartItemKey,
    array $values,
    WC_Order $order
): void {
    $unit = $values['stonesamin_product_unit'] ?? null;

    if (!is_array($unit) || empty($unit['label'])) {
        return;
    }

    $item->add_meta_data(
        'واحد سفارش',
        sanitize_text_field((string) $unit['label']),
        true
    );
}

add_action(
    'woocommerce_checkout_create_order_line_item',
    'stonesamin_save_product_unit_to_order_item',
    10,
    4
);
