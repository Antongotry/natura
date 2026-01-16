<?php
/**
 * Одноразовий скрипт: виправлення наявності товарів за ціною
 * 
 * Функція 1: Ціна = 0 → "Немає в наявності"
 * Функція 2: Ціна ≠ 0 → "Є в наявності"
 * 
 * Запустіть один раз і видаліть файл
 * 
 * Як запустити:
 * 1. Завантажте файл у корінь теми
 * 2. Перейдіть в адмін-панель WordPress
 * 3. Відкрийте: Інструменти → Виправити наявність
 */

// Додаємо сторінку в адмін-меню
add_action( 'admin_menu', 'natura_stock_fix_menu' );

function natura_stock_fix_menu() {
    add_management_page(
        'Виправити наявність товарів',
        'Виправити наявність',
        'manage_woocommerce',
        'natura-stock-fix',
        'natura_stock_fix_page'
    );
}

function natura_stock_fix_page() {
    $results_zero = array();
    $results_instock = array();
    $executed_zero = false;
    $executed_instock = false;
    
    // Функція 1: Ціна = 0 → Немає в наявності
    if ( isset( $_POST['run_zero_price_fix'] ) && check_admin_referer( 'natura_stock_fix_action' ) ) {
        $results_zero = natura_set_zero_price_out_of_stock();
        $executed_zero = true;
    }
    
    // Функція 2: Ціна ≠ 0 → Є в наявності
    if ( isset( $_POST['run_instock_fix'] ) && check_admin_referer( 'natura_stock_fix_action' ) ) {
        $results_instock = natura_set_priced_products_in_stock();
        $executed_instock = true;
    }
    
    ?>
    <div class="wrap">
        <h1>🔧 Виправити наявність товарів</h1>
        
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
            
            <!-- Функція 1: Ціна = 0 → Немає в наявності -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px; flex: 1; min-width: 300px;">
                <h2 style="margin-top: 0; color: #d32f2f;">🚫 Функція 1</h2>
                <h3>Ціна = 0 → "Немає в наявності"</h3>
                <p>Знаходить усі товари з <strong>ціною 0</strong> і встановлює їм статус <strong>"Немає в наявності"</strong>.</p>
                
                <?php if ( ! $executed_zero ) : ?>
                    <form method="post" style="margin-top: 20px;">
                        <?php wp_nonce_field( 'natura_stock_fix_action' ); ?>
                        <button type="submit" name="run_zero_price_fix" class="button button-secondary" style="background: #ffebee; border-color: #d32f2f; color: #d32f2f;">
                            🚫 Запустити
                        </button>
                    </form>
                <?php else : ?>
                    <div style="background: #ffebee; padding: 15px; border-radius: 4px; border-left: 4px solid #d32f2f; margin-top: 20px;">
                        <h4 style="margin-top: 0; color: #d32f2f;">✅ Виконано!</h4>
                        <?php if ( empty( $results_zero['updated'] ) ) : ?>
                            <p>Товарів з ціною 0 не знайдено. 👍</p>
                        <?php else : ?>
                            <p><strong>Оновлено: <?php echo count( $results_zero['updated'] ); ?></strong></p>
                            <ul style="margin: 10px 0; padding-left: 20px; max-height: 150px; overflow-y: auto;">
                                <?php foreach ( $results_zero['updated'] as $item ) : ?>
                                    <li>
                                        <a href="<?php echo esc_url( get_edit_post_link( $item['id'] ) ); ?>" target="_blank">
                                            <?php echo esc_html( $item['name'] ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Функція 2: Ціна ≠ 0 → Є в наявності -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px; flex: 1; min-width: 300px;">
                <h2 style="margin-top: 0; color: #388e3c;">✅ Функція 2</h2>
                <h3>Ціна ≠ 0 → "Є в наявності"</h3>
                <p>Знаходить усі товари з <strong>ціною більше 0</strong> і встановлює їм статус <strong>"Є в наявності"</strong>.</p>
                
                <?php if ( ! $executed_instock ) : ?>
                    <form method="post" style="margin-top: 20px;">
                        <?php wp_nonce_field( 'natura_stock_fix_action' ); ?>
                        <button type="submit" name="run_instock_fix" class="button button-primary" style="background: #4caf50; border-color: #388e3c;">
                            ✅ Запустити
                        </button>
                    </form>
                <?php else : ?>
                    <div style="background: #e8f5e9; padding: 15px; border-radius: 4px; border-left: 4px solid #388e3c; margin-top: 20px;">
                        <h4 style="margin-top: 0; color: #388e3c;">✅ Виконано!</h4>
                        <?php if ( empty( $results_instock['updated'] ) ) : ?>
                            <p>Усі товари з ціною вже "Є в наявності". 👍</p>
                        <?php else : ?>
                            <p><strong>Оновлено: <?php echo count( $results_instock['updated'] ); ?></strong></p>
                            <ul style="margin: 10px 0; padding-left: 20px; max-height: 150px; overflow-y: auto;">
                                <?php foreach ( $results_instock['updated'] as $item ) : ?>
                                    <li>
                                        <a href="<?php echo esc_url( get_edit_post_link( $item['id'] ) ); ?>" target="_blank">
                                            <?php echo esc_html( $item['name'] ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 20px; max-width: 650px;">
            💡 <strong>Після використання видаліть цей файл:</strong><br>
            <code><?php echo esc_html( __FILE__ ); ?></code>
        </div>
    </div>
    <?php
}

/**
 * Функція 1: Ціна = 0 → "Немає в наявності"
 */
function natura_set_zero_price_out_of_stock() {
    $results = array( 'updated' => array() );
    
    $products = wc_get_products( array(
        'status' => 'publish',
        'limit'  => -1,
        'return' => 'objects',
    ) );
    
    foreach ( $products as $product ) {
        $price = $product->get_price();
        
        // Ціна 0 або пуста
        if ( $price === '' || $price === null || floatval( $price ) == 0 ) {
            if ( $product->get_stock_status() !== 'outofstock' ) {
                $product->set_stock_status( 'outofstock' );
                $product->save();
                
                $results['updated'][] = array(
                    'id'   => $product->get_id(),
                    'name' => $product->get_name(),
                );
            }
        }
    }
    
    return $results;
}

/**
 * Функція 2: Ціна ≠ 0 → "Є в наявності"
 */
function natura_set_priced_products_in_stock() {
    $results = array( 'updated' => array() );
    
    $products = wc_get_products( array(
        'status' => 'publish',
        'limit'  => -1,
        'return' => 'objects',
    ) );
    
    foreach ( $products as $product ) {
        $price = $product->get_price();
        
        // Ціна більше 0
        if ( $price !== '' && $price !== null && floatval( $price ) > 0 ) {
            if ( $product->get_stock_status() !== 'instock' ) {
                $product->set_stock_status( 'instock' );
                $product->save();
                
                $results['updated'][] = array(
                    'id'   => $product->get_id(),
                    'name' => $product->get_name(),
                );
            }
        }
    }
    
    return $results;
}
