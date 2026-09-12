<?php

declare(strict_types=1);

namespace StoneSamin\Core\Modules\Catalog;

use StoneSamin\Core\Contracts\ModuleInterface;
use WP_Term;

defined('ABSPATH') || exit;

final class ProductTaxonomyThumbnailModule implements ModuleInterface
{
    private const META_KEY = 'thumbnail_id';

    private const FIELD_NAME = 'ss_stonesamin_taxonomy_thumbnail_id';

    private const NONCE_NAME = 'ss_stonesamin_taxonomy_thumbnail_nonce';

    private const NONCE_ACTION = 'ss_stonesamin_save_taxonomy_thumbnail';

    private const SCRIPT_HANDLE = 'ss-stonesamin-taxonomy-thumbnail';

    private const TAXONOMIES = [
        'ss_stone_usage',
        'ss_stone_material',
    ];

    public function register(): void
    {
        add_action('init', [$this, 'registerTermMeta'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        foreach (self::TAXONOMIES as $taxonomy) {
            add_action(
                "{$taxonomy}_add_form_fields",
                [$this, 'renderAddField']
            );

            add_action(
                "{$taxonomy}_edit_form_fields",
                [$this, 'renderEditField']
            );

            add_action(
                "created_{$taxonomy}",
                [$this, 'saveThumbnail']
            );

            add_action(
                "edited_{$taxonomy}",
                [$this, 'saveThumbnail']
            );

            add_filter(
                "manage_edit-{$taxonomy}_columns",
                [$this, 'addThumbnailColumn']
            );

            add_filter(
                "manage_{$taxonomy}_custom_column",
                [$this, 'renderThumbnailColumn'],
                10,
                3
            );
        }
    }

    public function registerTermMeta(): void
    {
        foreach (self::TAXONOMIES as $taxonomy) {
            register_term_meta(
                $taxonomy,
                self::META_KEY,
                [
                    'type'              => 'integer',
                    'single'            => true,
                    'default'           => 0,
                    'sanitize_callback' => 'absint',
                    'show_in_rest'      => true,
                ]
            );
        }
    }

    public function enqueueAdminAssets(string $hookSuffix): void
    {
        if (!in_array($hookSuffix, ['edit-tags.php', 'term.php'], true)) {
            return;
        }

        $screen = get_current_screen();

        if (!$screen || !in_array($screen->taxonomy, self::TAXONOMIES, true)) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            SS_STONESAMIN_CORE_URL
                . 'assets/admin/js/taxonomy-thumbnail.js',
            ['jquery'],
            SS_STONESAMIN_CORE_VERSION,
            true
        );

        wp_localize_script(
            self::SCRIPT_HANDLE,
            'ssStoneSaminTaxonomyThumbnail',
            [
                'frameTitle'  => 'انتخاب تصویر بندانگشتی',
                'buttonTitle' => 'استفاده از این تصویر',
            ]
        );
    }

    public function renderAddField(string $taxonomy): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        ?>
        <div class="form-field ss-taxonomy-thumbnail-field">
            <label for="<?php echo esc_attr(self::FIELD_NAME); ?>">
                تصویر بندانگشتی
            </label>

            <input
                type="hidden"
                id="<?php echo esc_attr(self::FIELD_NAME); ?>"
                class="ss-taxonomy-thumbnail-id"
                name="<?php echo esc_attr(self::FIELD_NAME); ?>"
                value="">

            <div class="ss-taxonomy-thumbnail-preview"></div>

            <button
                type="button"
                class="button ss-taxonomy-thumbnail-upload">
                انتخاب تصویر
            </button>

            <button
                type="button"
                class="button ss-taxonomy-thumbnail-remove"
                hidden>
                حذف تصویر
            </button>

            <p class="description">
                تصویری را برای نمایش این مورد در فهرست محصولات انتخاب کنید.
            </p>
        </div>
        <?php
    }

    public function renderEditField(WP_Term $term): void
    {
        $thumbnailId = (int) get_term_meta(
            $term->term_id,
            self::META_KEY,
            true
        );
        ?>
        <tr class="form-field ss-taxonomy-thumbnail-field">
            <th scope="row">
                <label for="<?php echo esc_attr(self::FIELD_NAME); ?>">
                    تصویر بندانگشتی
                </label>
            </th>

            <td>
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>

                <input
                    type="hidden"
                    id="<?php echo esc_attr(self::FIELD_NAME); ?>"
                    class="ss-taxonomy-thumbnail-id"
                    name="<?php echo esc_attr(self::FIELD_NAME); ?>"
                    value="<?php echo esc_attr((string) $thumbnailId); ?>">

                <div class="ss-taxonomy-thumbnail-preview">
                    <?php
                    if ($thumbnailId > 0) {
                        echo wp_kses_post(
                            wp_get_attachment_image(
                                $thumbnailId,
                                'thumbnail',
                                false,
                                [
                                    'class' => 'ss-taxonomy-thumbnail-image',
                                    'style' => 'max-width:150px;height:auto;display:block;margin-bottom:10px;',
                                ]
                            )
                        );
                    }
                    ?>
                </div>

                <button
                    type="button"
                    class="button ss-taxonomy-thumbnail-upload">
                    انتخاب تصویر
                </button>

                <button
                    type="button"
                    class="button ss-taxonomy-thumbnail-remove"
                    <?php echo $thumbnailId > 0 ? '' : 'hidden'; ?>>
                    حذف تصویر
                </button>
            </td>
        </tr>
        <?php
    }

    public function saveThumbnail(int $termId): void
    {
        if (!isset($_POST[self::NONCE_NAME])) {
            return;
        }

        $nonce = sanitize_text_field(
            wp_unslash($_POST[self::NONCE_NAME])
        );

        if (!wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        if (!current_user_can('edit_term', $termId)) {
            return;
        }

        $thumbnailId = isset($_POST[self::FIELD_NAME])
            ? absint(wp_unslash($_POST[self::FIELD_NAME]))
            : 0;

        if ($thumbnailId > 0 && wp_attachment_is_image($thumbnailId)) {
            update_term_meta($termId, self::META_KEY, $thumbnailId);

            return;
        }

        delete_term_meta($termId, self::META_KEY);
    }

    /**
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public function addThumbnailColumn(array $columns): array
    {
        $updatedColumns = [];

        foreach ($columns as $columnName => $columnLabel) {
            $updatedColumns[$columnName] = $columnLabel;

            if ('cb' === $columnName) {
                $updatedColumns['ss_thumbnail'] = 'تصویر';
            }
        }

        return $updatedColumns;
    }

    public function renderThumbnailColumn(
        string $content,
        string $columnName,
        int $termId
    ): string {
        if ('ss_thumbnail' !== $columnName) {
            return $content;
        }

        $thumbnailId = (int) get_term_meta(
            $termId,
            self::META_KEY,
            true
        );

        if ($thumbnailId <= 0) {
            return '—';
        }

        $image = wp_get_attachment_image(
            $thumbnailId,
            [60, 60],
            false,
            [
                'alt'   => '',
                'style' => 'width:60px;height:60px;object-fit:cover;border-radius:4px;',
            ]
        );

        return $image ? wp_kses_post($image) : '—';
    }
}

