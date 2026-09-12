<?php

defined('ABSPATH') || exit;

$searchQuery = get_search_query();
?>
<form
    class="stonesamin-product-search"
    role="search"
    method="get"
    action="<?php echo esc_url(home_url('/')); ?>">
    <label
        class="screen-reader-text"
        for="stonesamin-product-search-field">
        جست‌وجو در محصولات
    </label>

    <input
        id="stonesamin-product-search-field"
        class="stonesamin-product-search__field"
        type="search"
        name="s"
        value="<?php echo esc_attr($searchQuery); ?>"
        placeholder="نام سنگ یا محصول..."
        autocomplete="off">

    <input type="hidden" name="post_type" value="product">

    <button
        class="stonesamin-product-search__submit"
        type="submit"
        aria-label="جست‌وجوی محصول">
        <span aria-hidden="true">⌕</span>
    </button>
</form>
