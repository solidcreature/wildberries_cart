<?php 
//Регистрируем страницу настроек плагина в админ-панели
function wbc_register_options_page() {
  add_options_page('Настройки корзины WildBerries', 'WildBerries Cart', 'manage_options', 'wbc', 'wbc_options');
}
add_action('admin_menu', 'wbc_register_options_page');



//Добавляем форму на страницу настроек плагина
function wbc_options()
{
	?>

	<div>
		<h2>Настройки WildBerries Cart</h2>
		<?php if( !function_exists('acf_add_local_field_group') ): ?>
			<p><strong>Для интеграции внешней корзины WildBerries нужна установка плагина <a href="https://ru.wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a></strong></p>
		<?php endif; ?>	
		
		<form action="options.php" method="post">
		<?php settings_fields('wbc_options'); ?>
		<?php do_settings_sections('wbc_options_main'); ?>
		 
		<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
	</div>
	 
	<?php
} 

//Здесь начинаем добавлять настройки
function wbc_options_fields(){
register_setting( 'wbc_options', 'wbc_options', 'plugin_options_validate' );
add_settings_section('wbc_options_main', 'Основные настройки', 'wbc_section_callback', 'wbc_options_main');
add_settings_field('wbc_merchant_id', 'ID Мерчанта', 'wbc_merchantid_func', 'wbc_options_main', 'wbc_options_main');
add_settings_field('wbc_merchant_key', 'Ключ шифровки данных', 'wbc_merchantkey_func', 'wbc_options_main', 'wbc_options_main');
}

add_action('admin_init', 'wbc_options_fields');

function wbc_section_callback() {
	return '';
}

function wbc_merchantid_func() {
$options = get_option('wbc_options');
echo "<input id='wbc_merchant_id' name='wbc_options[wbc_merchant_id]' size='110' type='text' value='{$options['wbc_merchant_id']}' />";
} 

function wbc_merchantkey_func() {
$options = get_option('wbc_options');
echo "<input id='wbc_merchant_key' name='wbc_options[wbc_merchant_key]' size='110' type='text' value='{$options['wbc_merchant_key']}' />";
} 


