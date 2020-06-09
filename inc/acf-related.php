<?php

//Функция меняет содержание поля wbp_id_X в зависимости от wbp_link_X
function set_wbp_id( $field ) {
    global $post;
    $post_id = $post->ID;
    
    //Сложная конструкция, чтобы работало для wbp_id_1, wbp_id_2, wbp_id_3 ...
    $field_name = $field['_name'];
    $num = str_replace('wbp_id_','', $field_name);
    $link = get_field('wbp_link_' . $num, $post_id);
    $wb_product = wbc_get_product($link);
    
    $id = $wb_product["id"];
    
    $field['value'] = $id;
    
    return $field;
}

//Функция меняет содержание поля wbp_sizeid_X в зависимости от wbp_link_X
function set_wbp_sizeid( $field ) {
    
    global $post;
    $post_id = $post->ID;
    
    //Сложная конструкция, чтобы работало для wbp_sizeid_1, wbp_sizeid_2, wbp_sizeid_3 ...
    $field_name = $field['_name'];
    $num = str_replace('wbp_sizeid_','', $field_name);
    $link = get_field('wbp_link_' . $num, $post_id);
    $wb_product = wbc_get_product($link);
    
    $variants = $wb_product['variants'];
    
    
    foreach ($variants as $variant) {
    	$label = 'Цена: ' . $variant['price'] . ' Цвет: ' . $variant['color'] . ' Размер: ' . $variant['size'] . ' (' . $variant['sizeid'] . ')';
    	$value = $variant['sizeid'];
    	
    	$field['choices'][ $value ] = $label;
    }

    return $field;
}

//Функция автоматически генерирует уникальный returncode для поля returncode
function set_wbp_returncode( $field ) {
    if( !$field['value'] ) {    
      
        $characters = "abcdefghijklmnopqrstuvwxyz0123456789";	
    	$strlength = strlen($characters);
    	
    	$random = '';
	
    	for ($i = 0; $i < 15; $i++) {
    		$random .= $characters[rand(0, $strlength - 1)];
    	}
	    
	    $field['value'] = $random;      
    } 
    
    return $field;
}


//Фильтры для подготовк содержания соответствующих полей
add_filter('acf/prepare_field/name=wbp_returncode', 'set_wbp_returncode');
add_filter('acf/prepare_field/name=wbp_id_1', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_2', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_3', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_4', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_5', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_6', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_7', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_8', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_9', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_id_10', 'set_wbp_id');
add_filter('acf/prepare_field/name=wbp_sizeid_1', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_2', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_3', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_4', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_5', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_6', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_7', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_8', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_9', 'set_wbp_sizeid');
add_filter('acf/prepare_field/name=wbp_sizeid_10', 'set_wbp_sizeid');