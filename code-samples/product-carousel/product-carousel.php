<?php

defined('ABSPATH') || exit;

/**
 * Reusable homepage carousel partial.
 *
 * Expected arguments:
 * - products: WC_Product[]
 * - title: string
 * - subtitle?: string
 * - archive_url?: string
 * - archive_label?: string
 *
 * @var array<string, mixed> $args
 */

$products = isset($args['products']) && is_array($args['products'])
    ? array_values(
        array_filter(
            $args['products'],
            static fn ($product): bool =>
                $product instanceof WC_Product && $product->is_visible()
        )
    )
    : [];

if ([] === $products) {
    return;
}

$title = isset($args['title'])
    ? sanitize_text_field((string) $args['title'])
    : '';
$subtitle = isset($args['subtitle'])
    ? sanitize_text_field((string) $args['subtitle'])
    : '';
$archiveUrl = isset($args['archive_url'])
    ? esc_url((string) $args['archive_url'])
    : '';
$archiveLabel = isset($args['archive_label'])
    ? sanitize_text_field((string) $args['archive_label'])
    : 'مشاهده همه';
$carouselId = wp_unique_id('stonesamin-product-carousel-');
?>
<section
    id="<?php echo esc_attr($carouselId); ?>"
    class="stonesamin-product-carousel"
    aria-labelledby="<?php echo esc_attr($carouselId); ?>-title">
    <div class="container">
        <header class="stonesamin-product-carousel__header">
            <div>
                <?php if ('' !== $subtitle) : ?>
                    <p class="stonesamin-product-carousel__eyebrow">
                        <?php echo esc_html($subtitle); ?>
                    </p>
                <?php endif; ?>

                <h2
                    id="<?php echo esc_attr($carouselId); ?>-title"
                    class="stonesamin-product-carousel__title">
                    <?php echo esc_html($title); ?>
                </h2>
            </div>

            <div class="stonesamin-product-carousel__controls">
                <button
                    type="button"
                    class="stonesamin-product-carousel__next"
                    aria-label="محصول بعدی">
                    <span aria-hidden="true">←</span>
                </button>
                <button
                    type="button"
                    class="stonesamin-product-carousel__prev"
                    aria-label="محصول قبلی">
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </header>

        <div
            class="swiper stonesamin-product-carousel__slider"
            data-stonesamin-product-carousel
            data-slides-mobile="1.15"
            data-slides-tablet="2"
            data-slides-desktop="3">
            <div class="swiper-wrapper">
                <?php foreach ($products as $product) : ?>
                    <div class="swiper-slide">
                        <?php
                        get_template_part(
                            'partials/woocommerce/product-card',
                            null,
                            ['product' => $product]
                        );
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ('' !== $archiveUrl) : ?>
            <div class="stonesamin-product-carousel__footer">
                <a class="button button--outline" href="<?php echo $archiveUrl; ?>">
                    <?php echo esc_html($archiveLabel); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
