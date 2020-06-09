<?php get_header(); ?>
<h1>Оплата прошла успешно</h1>	

<?php $token = htmlspecialchars($_GET["token"]); ?>
<?php wbc_update_after_payment($token); ?>



<?php get_footer(); ?>