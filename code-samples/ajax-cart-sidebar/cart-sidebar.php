<?php

defined('ABSPATH') || exit;

function stonesamin_get_cart_count(): int
{
    return WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}

function stonesamin_render_cart_count_badge(): string
{
    $count = stonesamin_get_cart_count();
    $className = 'stonesamin-cart-count-badge';

    if (0 === $count) {
        $className .= ' is-empty';
    }

    return sprintf(
        '<span class="%1$s" aria-label="%2$s">%3$s</span>',
        esc_attr($className),
        esc_attr(
            sprintf(
                /* translators: %s: number of products in the cart. */
                _n('%s محصول در سبد خرید', '%s محصول در سبد خرید', $count, 'stonesamin'),
                number_format_i18n($count)
            )
        ),
        esc_html(number_format_i18n($count))
    );
}

function stonesamin_render_cart_sidebar(): void
{
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }
    ?>
    <div
        id="stonesamin-cart-sidebar"
        class="stonesamin-cart-sidebar"
        aria-hidden="true">
        <button
            type="button"
            class="stonesamin-cart-sidebar__overlay"
            tabindex="-1"
            aria-label="بستن سبد خرید">
        </button>

        <aside
            class="stonesamin-cart-sidebar__panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="stonesamin-cart-sidebar-title">
            <header class="stonesamin-cart-sidebar__header">
                <h2 id="stonesamin-cart-sidebar-title">سبد خرید</h2>
                <button
                    type="button"
                    class="stonesamin-cart-sidebar__close"
                    aria-label="بستن سبد خرید">
                    <span aria-hidden="true">&times;</span>
                </button>
            </header>

            <p
                class="screen-reader-text"
                data-stonesamin-cart-status
                aria-live="polite">
            </p>

            <?php
            // This HTML is generated exclusively by the escaped renderer below.
            echo stonesamin_render_cart_sidebar_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </aside>
    </div>
    <?php
}

add_action('wp_footer', 'stonesamin_render_cart_sidebar', 30);

/**
 * Render a deliberately small cart fragment for the off-canvas drawer.
 */
function stonesamin_render_cart_sidebar_content(): string
{
    ob_start();
    ?>
    <div class="stonesamin-cart-sidebar__content">
        <?php if (WC()->cart && !WC()->cart->is_empty()) : ?>
            <ul class="stonesamin-mini-cart" role="list">
                <?php foreach (WC()->cart->get_cart() as $cartItemKey => $cartItem) : ?>
                    <?php
                    $product = apply_filters(
                        'woocommerce_cart_item_product',
                        $cartItem['data'],
                        $cartItem,
                        $cartItemKey
                    );

                    if (
                        !$product instanceof WC_Product
                        || !$product->exists()
                        || $cartItem['quantity'] <= 0
                    ) {
                        continue;
                    }

                    $productName = apply_filters(
                        'woocommerce_cart_item_name',
                        $product->get_name(),
                        $cartItem,
                        $cartItemKey
                    );

                    $productUrl = apply_filters(
                        'woocommerce_cart_item_permalink',
                        $product->is_visible() ? $product->get_permalink($cartItem) : '',
                        $cartItem,
                        $cartItemKey
                    );
                    ?>
                    <li class="stonesamin-mini-cart__item">
                        <div class="stonesamin-mini-cart__media">
                            <?php if ($productUrl) : ?>
                                <a href="<?php echo esc_url($productUrl); ?>">
                                    <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
                                </a>
                            <?php else : ?>
                                <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
                            <?php endif; ?>
                        </div>

                        <div class="stonesamin-mini-cart__body">
                            <?php if ($productUrl) : ?>
                                <a
                                    class="stonesamin-mini-cart__name"
                                    href="<?php echo esc_url($productUrl); ?>">
                                    <?php echo wp_kses_post($productName); ?>
                                </a>
                            <?php else : ?>
                                <span class="stonesamin-mini-cart__name">
                                    <?php echo wp_kses_post($productName); ?>
                                </span>
                            <?php endif; ?>

                            <?php echo wp_kses_post(wc_get_formatted_cart_item_data($cartItem)); ?>

                            <div class="stonesamin-mini-cart__price" dir="ltr">
                                <?php
                                echo wp_kses_post(
                                    sprintf(
                                        '%1$s × %2$s',
                                        number_format_i18n($cartItem['quantity']),
                                        WC()->cart->get_product_price($product)
                                    )
                                );
                                ?>
                            </div>
                        </div>

                        <?php
                        echo wp_kses_post(
                            apply_filters(
                                'woocommerce_cart_item_remove_link',
                                sprintf(
                                    '<a href="%1$s" class="remove remove_from_cart_button" aria-label="%2$s" data-product_id="%3$s" data-cart_item_key="%4$s" data-product_sku="%5$s">&times;</a>',
                                    esc_url(wc_get_cart_remove_url($cartItemKey)),
                                    esc_attr(sprintf('حذف %s از سبد خرید', wp_strip_all_tags($productName))),
                                    esc_attr((string) $product->get_id()),
                                    esc_attr($cartItemKey),
                                    esc_attr($product->get_sku())
                                ),
                                $cartItemKey
                            )
                        );
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="stonesamin-mini-cart__subtotal">
                <span>مجموع</span>
                <strong dir="ltr">
                    <?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?>
                </strong>
            </div>

            <div class="stonesamin-mini-cart__actions">
                <a class="button button--outline" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                    مشاهده سبد خرید
                </a>
                <a class="button" href="<?php echo esc_url(wc_get_checkout_url()); ?>">
                    تسویه حساب
                </a>
            </div>
        <?php else : ?>
            <div class="stonesamin-mini-cart__empty">
                <p>سبد خرید شما خالی است.</p>
                <a class="button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                    رفتن به فروشگاه
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Refresh both the visible contents and the navbar count after AJAX actions.
 *
 * @param array<string, string> $fragments
 * @return array<string, string>
 */
function stonesamin_cart_sidebar_fragments(array $fragments): array
{
    $fragments['div.stonesamin-cart-sidebar__content'] =
        stonesamin_render_cart_sidebar_content();

    $fragments['span.stonesamin-cart-count-badge'] =
        stonesamin_render_cart_count_badge();

    return $fragments;
}

add_filter(
    'woocommerce_add_to_cart_fragments',
    'stonesamin_cart_sidebar_fragments'
);
