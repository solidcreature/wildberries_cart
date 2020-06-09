<?php 
//Новый тип записи, в котором храним как продукты с вайлдберрис, так и данные получателя

function wbc_product_type() {

	$labels = array(
		'name'                  => _x( 'Корзины WildBerries', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Корзина WildBerries', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Корзины WildBerries', 'text_domain' ),
		'name_admin_bar'        => __( 'Корзину WildBerries', 'text_domain' ),
		'archives'              => __( 'Архив корзин', 'text_domain' ),
		'attributes'            => __( 'Атрибуты корзины', 'text_domain' ),
		'parent_item_colon'     => __( 'Родительский элемент', 'text_domain' ),
		'all_items'             => __( 'Все корзиы', 'text_domain' ),
		'add_new_item'          => __( 'Добавить новую корзину', 'text_domain' ),
		'add_new'               => __( 'Новая корзина', 'text_domain' ),
		'new_item'              => __( 'Новая корзина', 'text_domain' ),
		'edit_item'             => __( 'Редактировать', 'text_domain' ),
		'update_item'           => __( 'Обновить', 'text_domain' ),
		'view_item'             => __( 'Посмотреть', 'text_domain' ),
		'view_items'            => __( 'Посмотреть', 'text_domain' ),
		'search_items'          => __( 'Искать корзину', 'text_domain' ),
		'not_found'             => __( 'Не найдены', 'text_domain' ),
		'not_found_in_trash'    => __( 'Не найдены в удаленных', 'text_domain' ),
		'featured_image'        => __( 'Фотография корзины', 'text_domain' ),
		'set_featured_image'    => __( 'Задать фотографию', 'text_domain' ),
		'remove_featured_image' => __( 'Удалить фотографию', 'text_domain' ),
		'use_featured_image'    => __( 'Использовать', 'text_domain' ),
		'insert_into_item'      => __( 'Использовать для корзины', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Загружено для корзины', 'text_domain' ),
		'items_list'            => __( 'Список корзин', 'text_domain' ),
		'items_list_navigation' => __( 'Навигация по корзинам', 'text_domain' ),
		'filter_items_list'     => __( 'Отсортировать список участников', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Корзина WildBerries', 'text_domain' ),
		'description'           => __( 'Post Type Description', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title'),
		'taxonomies'            => array(),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-store',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'wb_product', $args );
}
add_action( 'init', 'wbc_product_type', 0 );