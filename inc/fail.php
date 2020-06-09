<?php get_header(); ?>
<h1>К сожалению что-то пошло не так</h1>	

<?php $token = htmlspecialchars($_GET["token"]); ?>
<?php wbc_update_after_fail($token); ?>

<?php get_footer(); ?>