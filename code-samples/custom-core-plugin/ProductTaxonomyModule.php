<?php

declare(strict_types=1);

namespace StoneSamin\Core\Modules\Catalog;

use StoneSamin\Core\Contracts\ModuleInterface;

defined('ABSPATH') || exit;

final class ProductTaxonomyModule implements ModuleInterface
{
    private const STONE_USAGE_TAXONOMY = 'ss_stone_usage';

    private const STONE_MATERIAL_TAXONOMY = 'ss_stone_material';

    public function register(): void
    {
        add_action('init', [$this, 'registerTaxonomies']);
    }

    public function registerTaxonomies(): void
    {
        $this->registerStoneUsageTaxonomy();
        $this->registerStoneMaterialTaxonomy();
    }

    private function registerStoneUsageTaxonomy(): void
    {
        $labels = [
            'name'              => 'کاربردهای سنگ',
            'singular_name'     => 'کاربرد سنگ',
            'menu_name'         => 'کاربرد سنگ',
            'search_items'      => 'جستجوی کاربردها',
            'all_items'         => 'همه کاربردها',
            'parent_item'       => 'کاربرد والد',
            'parent_item_colon' => 'کاربرد والد:',
            'edit_item'         => 'ویرایش کاربرد',
            'view_item'         => 'مشاهده کاربرد',
            'update_item'       => 'به‌روزرسانی کاربرد',
            'add_new_item'      => 'افزودن کاربرد جدید',
            'new_item_name'     => 'نام کاربرد جدید',
            'not_found'         => 'کاربردی پیدا نشد',
            'back_to_items'     => 'بازگشت به کاربردها',
        ];

        register_taxonomy(
            self::STONE_USAGE_TAXONOMY,
            ['product'],
            $this->getTaxonomyArguments(
                $labels,
                'stone-usage'
            )
        );
    }

    private function registerStoneMaterialTaxonomy(): void
    {
        $labels = [
            'name'              => 'جنس‌های سنگ',
            'singular_name'     => 'جنس سنگ',
            'menu_name'         => 'جنس سنگ',
            'search_items'      => 'جستجوی جنس سنگ',
            'all_items'         => 'همه جنس‌های سنگ',
            'parent_item'       => 'جنس والد',
            'parent_item_colon' => 'جنس والد:',
            'edit_item'         => 'ویرایش جنس سنگ',
            'view_item'         => 'مشاهده جنس سنگ',
            'update_item'       => 'به‌روزرسانی جنس سنگ',
            'add_new_item'      => 'افزودن جنس سنگ جدید',
            'new_item_name'     => 'نام جنس سنگ جدید',
            'not_found'         => 'جنس سنگی پیدا نشد',
            'back_to_items'     => 'بازگشت به جنس‌های سنگ',
        ];

        register_taxonomy(
            self::STONE_MATERIAL_TAXONOMY,
            ['product'],
            $this->getTaxonomyArguments(
                $labels,
                'stone-material'
            )
        );
    }

    /**
     * @param array<string, string> $labels
     * @return array<string, mixed>
     */
    private function getTaxonomyArguments(
        array $labels,
        string $rewriteSlug
    ): array {
        return [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'hierarchical'       => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_admin_column'  => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => [
                'slug'         => $rewriteSlug,
                'with_front'   => false,
                'hierarchical' => true,
            ],
            'capabilities'       => [
                'manage_terms' => 'manage_product_terms',
                'edit_terms'   => 'edit_product_terms',
                'delete_terms' => 'delete_product_terms',
                'assign_terms' => 'assign_product_terms',
            ],
        ];
    }
}

