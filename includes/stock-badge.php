<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: includes/stock-badge.php
 * Description: Reusable PHP helper to render stock badges
 *              on product cards (Amazon/Flipkart style)
 *
 * Usage on any product card:
 *   <?php renderStockBadge($product); ?>
 *
 * Expects $product to have:
 *   - id (int)
 *   - stock_quantity (int)   — from getStockSelector() in repository
 *   - status (string)        — 'out_of_stock' | 'published' etc.
 *
 * Also exports:
 *   getStockStatus(array $product): string
 *   renderNotifyForm(int $productId): void
 * =============================================================
 */

if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config/config.php';
}

/**
 * Compute stock_status string from a product array.
 * Mirrors StockRepository::computeStatus() — backend is always source of truth.
 */
function getStockStatus(array $product): string {
    // Explicit out-of-stock DB status overrides everything
    $dbStatus = strtolower((string)($product['status'] ?? ''));
    if ($dbStatus === 'out_of_stock') {
        return 'out_of_stock';
    }

    $qty = (int)($product['stock_quantity'] ?? 0);
    if ($qty <= 0)  return 'out_of_stock';
    if ($qty <= 5)  return 'low_stock';
    return 'in_stock';
}

/**
 * Render the stock badge HTML for a product.
 *
 * @param array $product   Product row from DB / service layer
 * @param bool  $showBar   Whether to show the urgency progress bar (low stock)
 */
function renderStockBadge(array $product, bool $showBar = true): void {
    $status = getStockStatus($product);
    $qty    = (int)($product['stock_quantity'] ?? 0);

    if ($status === 'in_stock') {
        echo '<span class="c-stock-badge c-stock-badge--in">In Stock</span>';
        return;
    }

    if ($status === 'low_stock') {
        echo '<span class="c-stock-badge c-stock-badge--low">Only ' . (int)$qty . ' left</span>';
        if ($showBar) {
            $fill = max(10, min(100, (int)round(($qty / 5) * 100)));
            echo '<div class="c-stock-urgency">';
            echo   '<div class="c-stock-urgency__bar-track">';
            echo     '<div class="c-stock-urgency__bar-fill" style="width:' . $fill . '%"></div>';
            echo   '</div>';
            echo '</div>';
        }
        return;
    }

    // out_of_stock
    echo '<span class="c-stock-badge c-stock-badge--out">Out of Stock</span>';
}

/**
 * Render the "Notify Me when back in stock" inline form.
 *
 * @param int $productId   Product ID
 */
function renderNotifyForm(int $productId): void {
    echo <<<HTML
<div class="c-notify-wrap" style="display:none; margin-top:8px;">
    <button type="button"
            class="btn-notify-me"
            aria-label="Get notified when back in stock">
        <i class="bi bi-bell"></i> Notify Me
    </button>
    <form class="c-notify-form" novalidate>
        <input  type="email"
                class="c-notify-form__input"
                placeholder="Enter your email"
                required
                autocomplete="email">
        <button type="submit" class="c-notify-form__submit">
            Notify Me
        </button>
    </form>
</div>
HTML;
}

/**
 * Render the Add-to-Cart button with stock awareness.
 *
 * @param array  $product
 * @param string $weight    Default weight option
 */
function renderAddToCartBtn(array $product, string $weight = '500g'): void {
    $status    = getStockStatus($product);
    $productId = (int)($product['id'] ?? 0);
    $isOos     = ($status === 'out_of_stock');

    $disabledAttr  = $isOos ? 'disabled aria-disabled="true"' : '';
    $disabledClass = $isOos ? 'is-disabled' : '';
    $btnText       = $isOos ? 'Out of Stock' : 'Add to Cart';

    echo <<<HTML
<button type="button"
        class="btn-add-to-cart {$disabledClass}"
        data-product-id="{$productId}"
        data-weight="{$weight}"
        data-quantity="1"
        {$disabledAttr}>
    {$btnText}
</button>
HTML;

    if ($isOos) {
        renderNotifyForm($productId);
    }
}
