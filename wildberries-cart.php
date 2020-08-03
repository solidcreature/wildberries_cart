<?php
/*
Plugin Name: WildBerries Cart Integration
Plugin URI: 
Description: Интеграция внешней корзины WildBerries для сайтов благотворительных организаций
Version: 1.0
Author: Nikolay Mironov
Author URI: http://wpfolio.ru 
*/


define( 'WBC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WBC_URL', plugin_dir_url( __FILE__ ) );

//Подключаем необходимые модули плагина
include WBC_DIR . '/inc/plugin-options.php';
include WBC_DIR . '/inc/post-types.php';
include WBC_DIR . '/inc/acf-related.php';
include WBC_DIR . '/inc/acf-groups.php';
include WBC_DIR . '/inc/encrypt.php';


//Функция прием информации об оплате
function wbc_update_after_payment($token) {
	$crypto = new Crypto();
	$text = $crypto->decrypt($token);
	//$len = strlen($text); 
	//$arr = json_decode(substr($text,0,$len-8));
	$text = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $text);
	$arr = json_decode($text);
	
	print_r( $arr );
	
	$args = array(
		'post_type' => 'wb_product',
		'meta_key' => 'wbp_returncode',
		'meta_value' =>  $arr->returncode
	);
				
	$query = new WP_Query($args);
	
	//Находим нужную корзину по уникальному returncode
	if ( $query->have_posts() )	{
		while ( $query->have_posts() ): $query->the_post();
			$cart_id = get_the_ID();
		endwhile; 
	}
		
	//Выполняем действия только если нашли корзину
	if ($cart_id) {
	
		$log = get_field('wbp_success_log',$cart_id);
		$log .= PHP_EOL . date('Y-m-d H:i:s', time() + 10800) . PHP_EOL . json_encode($arr, true) . PHP_EOL; 
		update_field('wbp_success_log',$log,$cart_id);
			
		$data = $arr->data; 
		
		foreach($data as $wb_product) {		 
			$products_num = get_field('products_num',$cart_id); //Сколько товаров в корзине
			$product_id = $wb_product -> id; //ID оплаченного товара
			$products_bought =  $wb_product -> rnum + $wb_product -> quantity; //итоговое количество купленных товаров после успешной оплаты заказа
			
			//В цикле ищем тот товар, который был оплачен и обновляем остаток
			$counter = 1;
			while ($counter <= $products_num) {
				if ( get_field('wbp_id_' . $counter, $cart_id) == $product_id ) {
					update_field('wbp_rnum_' . $counter, $products_bought, $cart_id);
				} //if
				$counter++;
			} //while
		} //foreach
	} //if
	
} //wbc_update_after_payment


//Функция приема информации о неуспешной оплате
function wbc_update_after_fail($token) {
	//Раскодируем данные, полученные в токене
	$crypto = new Crypto();
	$text = $crypto->decrypt($token);
	$text = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $text);
	
	//Парсим джейсон
	$arr = json_decode($text);
	
	//С пмоощью WP Query по returncode узнаем номер корзины
	$args = array(
		'post_type' => 'wb_product',
		'meta_key' => 'wbp_returncode',
		'meta_value' =>  $arr->returncode
	);
				
	$query = new WP_Query($args);
	
	if ( $query->have_posts() )	{
		while ( $query->have_posts() ): $query->the_post();
			$cart_id = get_the_ID();
		endwhile; 
	}
		
	//Выполняем действия только если нашли корзину
	if ($cart_id) {
		$log = get_field('wbp_fail_log',$cart_id);
		$log .= PHP_EOL . date('Y-m-d H:i:s', time() + 10800) . PHP_EOL . json_encode($arr, true) . PHP_EOL; 
		update_field('wbp_fail_log',$log,$cart_id);
		
		print_r($arr);
	} 
}


//Получаем информацию о продукте в лучшем виде, чем "родной" JSON
function wbc_get_product($url) {
	$url = str_replace('https://www.wildberries.ru/catalog/','',$url);
	$url= explode('/',$url);
	$sku = $url[0];
	
	$request = wp_remote_get( "https://napi.wildberries.ru/api/catalog/$sku/detail.aspx?targetUrl=NW" );
	$body = wp_remote_retrieve_body( $request );
    $data = json_decode( $body );
	
	$name = $data -> data -> productInfo -> name;
	$description = $data -> data -> productInfo -> description;
	$colors = $data -> data -> colors;
	
	$variants = array();

	foreach ($colors as $wb_color):
		if ($wb_color->cod1S == $sku) {
			$var_color = $wb_color->name;
			$var_preview = $wb_color->previewUrl;

			$wb_sizes = $wb_color->nomenclatures;
			$wb_sizes = $wb_sizes[0]->sizes;

			foreach ($wb_sizes as $wb_size):
				$variants[] = array(
					"color" => $var_color, 
					"preview" => $var_preview,
					"size" => $wb_size->sizeNameRus,
					"price" => $wb_size-> priceWithSale,
					"sizeid" => $wb_size -> characteristicId
					);
			endforeach;
		}	
    endforeach;
	
	$wbproduct = array(
		"id" => $sku,
		"name" => $name,
		"description" => $description,
		"variants" => $variants
	);	
	
	return $wbproduct;
}



//Шорткод для вывода основной информации о продукте
function wbc_show_cart($atts) {
    extract( shortcode_atts( array(
        'id' => 139,
		'head' => true, //пока не используется
		'btn' => 'Купить', //пока не используется
		'all' => 'Оплатить все' //пока не используется
    ), $atts ) );
    
	$basket_id = $id;
	$add = get_field('address', $basket_id);
	$con = get_field('contact', $basket_id);
	$title = get_field('wbp_title',$basket_id);
	$description = get_field('wbp_description',$basket_id);

	$html_result = '<div class="wbp-description"><h3 class="wbp-description_title">' . $title . '</h3><div class="wbp-description_text">' . $description . '</div></div>';

	$counter = 1; 
	$products_num = get_field('products_num', $basket_id); 
	
	while( $counter <= $products_num ): 
		$link = get_field('wbp_link_' . $counter, $basket_id); 
		
		if ($link) { 
			$sizeid = get_field('wbp_sizeid_' . $counter, $basket_id); 
			$product = wbc_get_product($link); 
			
			//Получаем JSON для генерации токена
			$id = get_field('wbp_id_' . $counter, $basket_id);
			$size = get_field('wbp_sizeid_' . $counter, $basket_id);
			$returncode = get_field('wbp_returncode', $basket_id);
			$rnum = get_field('wbp_rnum_' . $counter, $basket_id);
			$number = get_field('wbp_number_' . $counter, $basket_id);
			
			$options = get_option('wbc_options');
    		$wbc_id = $options['wbc_merchant_id'];
		
			$data = '{"id": ' . $id . ',"size": '  . $size . ', "rnum": ' . $rnum . ', "number": ' . $number . '}';

			$text = '{"id": ' . $wbc_id . ',"returncode": "' . $returncode . '","city": "' . $add['city'] . '","street_name": "' . $add['street_name'] . '","home": "' . $add['home'] . '","flat": "' . $add['flat'] . '","h_entrance": "' . $add['h_entrance'] . '","index": "' . $add['index'] . '","province": "' . $add['province'] . '","area": "' . $add['area'] . '","precision": "precision","coords": "POINT (' . $add['latitude'] . ' ' . $add['longitude'] . ')","phone": ' . $con['phone'] . ',"first_name": "' . $con['first_name'] . '","middle_name": "' . $con['middle_name'] . '","last_name": "' . $con['last_name'] . '","nname": "' . $con['nname'] . '","data": [' . $data . ']}';

			
			//Шифруем токен и меняем символы для УРЛ
			$crypto = new Crypto();
			$token = $crypto->encrypt($text);
			$token = urlencode($token);
			
			//Получаем ID Мерчанта
			$options = get_option('wbc_options');
    		$wbc_id = $options['wbc_merchant_id'];

			$buylink = 'https://eo.wildberries.ru/basket?id=' . $wbc_id . '&token=' .  $token;
					
			//Получаем ключевые детали, выбранной вариации
			$variants = $product['variants']; 

			foreach ($variants as $variant) {
				if ($sizeid == $variant['sizeid']) {
					$info = '<strong>Цена:</strong> ' . $variant['price'];
					if ($variant['color']) $info .= ' <strong>Цвет:</strong> ' . $variant['color'];
					if ($variant['size']) $info .= ' <strong>Размер:</strong> ' . $variant['size'];
					$preview = $variant['preview'];
				}
    		}
			
			
			if ($rnum < $number): 
				$link = '<a class="wbp-item_buy_link" href="' . $buylink . '">Купить</a>';
			else: 
				$link = '<a class="wbp-item_buy_link is__over" href="#">Уже Купили</a>';
			endif; 
		
    		
			$html_result .= '<div class="wbp-item"><div class="wbp-item_photo"><img src="' . $preview . '"></div><div class="wbp-item_content"><h3 class="wbp-item_title">' . $product['name'] . $counter . '<span>(Куплено ' . $rnum . ' из ' . $number . ')</span></h3><p class="wbp-item_description">' . $product['description'] . '</p><p class="wbp-item_info">' . $info . '</p></div><div class="wbp-item_buy">' . $link . '</div></div>';
			
		} 
		
		$counter++; 
	endwhile;	

return $html_result;
	
}

add_shortcode('wb_cart', 'wbc_show_cart');



//Шорткод для вывода основной информации о продукте
function wbc_show_product($atts) {
    extract( shortcode_atts( array(
        'sku' => 4428660,
        'color' => 0,
		'size' => 0,
    ), $atts ) );
    
    $request = wp_remote_get( "https://napi.wildberries.ru/api/catalog/$sku/detail.aspx?targetUrl=NW" );

        if( !is_wp_error( $request ) ) {

            $body = wp_remote_retrieve_body( $request );
            $data = json_decode( $body );
			
			//echo '<pre>';
			//print_r($data -> data -> colors);
			//echo '</pre>';

            echo '<h3>' . $data -> data -> productInfo -> name . '</h3>'; 
            $wb_colors = $data -> data -> colors;

            echo '<img src="' . $wb_colors[$color]->previewUrl . '">';
            
            echo '<p>' . $data -> data -> productInfo -> description . '</p>'; 
			echo '<p><strong>Цвета и размеры: </strong></p>';
            echo '<ul>';
            foreach ($wb_colors as $wb_color):
				if ($wb_color->cod1S == $sku) {
					echo '<li>' . $wb_color->name;
					$wb_sizes = $wb_color->nomenclatures;
					$wb_sizes = $wb_sizes[0]->sizes;
					echo '<ul>';
					foreach ($wb_sizes as $wb_size):
						echo '<li>' . $wb_size->sizeNameRus . ' - ' . $wb_size-> priceWithSale . ' - ' . $wb_size -> characteristicId . '</li>';
					endforeach;
					echo '</ul></li>';
				}	
            endforeach;
            echo '</ul>';            
        }	

}
add_shortcode('wb_product', 'wbc_show_product');



//Подгружаем стили для оформления шорткода
add_action('wp_enqueue_scripts', 'wbc_styles' );
function wbc_styles() {
	wp_enqueue_style('wbc-css', WBC_URL . 'inc/css/wb_products.css', array() );
}

//Подгружаем страницу после оплаты
add_action('init', function() {
  $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
  if ( $url_path === 'wbc_success_payment' ) {
	load_template( WBC_DIR . '/inc/success.php' );	
  }
  if ( $url_path === 'wbc_fail' ) {
	load_template( WBC_DIR . '/inc/fail.php' );	
  }
});




