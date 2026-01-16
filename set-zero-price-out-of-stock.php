<?php
/**
 * Одноразовий скрипт: встановлює "Немає в наявності" для товарів з ціною 0
 * Запустіть один раз і видаліть файл
 * 
 * Як запустити:
 * 1. Завантажте файл у корінь теми
 * 2. Перейдіть в адмін-панель WordPress
 * 3. Відкрийте: /wp-admin/admin.php?page=natura-zero-price-fix
 */

// Додаємо сторінку в адмін-меню
add_action( 'admin_menu', 'natura_zero_price_fix_menu' );

function natura_zero_price_fix_menu() {
    add_management_page(
        'Виправити нульові ціни',
        'Виправити нульові ціни',
        'manage_woocommerce',
        'natura-zero-price-fix',
        'natura_zero_price_fix_page'
    );
}

function natura_zero_price_fix_page() {
    // Перевірка nonce при виконанні
    $executed = false;
    $results = array();
    
    if ( isset( $_POST['run_zero_price_fix'] ) && check_admin_referer( 'natura_zero_price_fix_action' ) ) {
        $results = natura_set_zero_price_products_out_of_stock();
        $executed = true;
    }
    
    ?>
    <div class="wrap">
        <h1>🔧 Виправити нульові ціни</h1>
        
        <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px; max-width: 600px; margin-top: 20px;">
            <h2 style="margin-top: 0;">Що робить цей скрипт?</h2>
            <p>Знаходить усі товари з <strong>ціною 0</strong> і встановлює їм статус <strong>"Немає в наявності"</strong>.</p>
            
            <?php if ( ! $executed ) : ?>
                <form method="post" style="margin-top: 20px;">
                    <?php wp_nonce_field( 'natura_zero_price_fix_action' ); ?>
                    <p style="background: #fff3cd; padding: 15px; border-radius: 4px; border-left: 4px solid #ffc107;">
                        ⚠️ <strong>Увага:</strong> Ця дія змінить статус товарів. Переконайтеся, що маєте резервну копію.
                    </p>
                    <button type="submit" name="run_zero_price_fix" class="button button-primary button-large" style="margin-top: 10px;">
                        🚀 Запустити скрипт
                    </button>
                </form>
            <?php else : ?>
                <div style="background: #d4edda; padding: 15px; border-radius: 4px; border-left: 4px solid #28a745; margin-top: 20px;">
                    <h3 style="margin-top: 0; color: #155724;">✅ Скрипт виконано!</h3>
                    
                    <?php if ( empty( $results['updated'] ) ) : ?>
                        <p>Товарів з ціною 0 не знайдено. Усе в порядку! 👍</p>
                    <?php else : ?>
                        <p><strong>Оновлено товарів: <?php echo count( $results['updated'] ); ?></strong></p>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <?php foreach ( $results['updated'] as $item ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( get_edit_post_link( $item['id'] ) ); ?>" target="_blank">
                                        <?php echo esc_html( $item['name'] ); ?>
                                    </a>
                                    (ID: <?php echo esc_html( $item['id'] ); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    
                    <p style="margin-top: 20px; padding: 10px; background: #e7f3ff; border-radius: 4px;">
                        💡 <strong>Тепер можете видалити цей файл:</strong><br>
                        <code><?php echo esc_html( __FILE__ ); ?></code>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Основна функція: встановлює "Немає в наявності" для товарів з ціною 0
 */
function natura_set_zero_price_products_out_of_stock() {
    $results = array(
        'updated' => array(),
        'skipped' => array(),
    );
    
    // Отримуємо всі опубліковані товари
    $args = array(
        'status'  => 'publish',
        'limit'   => -1,
        'return'  => 'objects',
    );
    
    $products = wc_get_products( $args );
    
    foreach ( $products as $product ) {
        $price = $product->get_price();
        
        // Перевіряємо чи ціна 0 або пуста
        if ( $price === '' || $price === null || floatval( $price ) == 0 ) {
            // Перевіряємо чи вже не в статусі "outofstock"
            if ( $product->get_stock_status() !== 'outofstock' ) {
                $product->set_stock_status( 'outofstock' );
                $product->save();
                
                $results['updated'][] = array(
                    'id'   => $product->get_id(),
                    'name' => $product->get_name(),
                );
            } else {
                $results['skipped'][] = array(
                    'id'   => $product->get_id(),
                    'name' => $product->get_name(),
                    'reason' => 'Вже "Немає в наявності"',
                );
            }
        }
    }
    
    return $results;
}
