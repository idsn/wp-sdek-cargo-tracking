<?php
/**
 * @package denlans_ru
 */
/*
Plugin Name: Отслеживание грузов через СДЭК
Plugin URI: http://denlans.ru/
Description: Для вставки формы отслеживания используйте шорткод [sdek-tracking]
Version: 1.0.0
Author: Denis Ryabikov
Author URI: http://denlans.ru/
Text Domain: wp-plg-sdek-tracking
*/
defined( 'ABSPATH' ) or die( "No script kiddies please!" );

function trackingForm() {
	include "shortTag.php";
}
/** @noinspection PhpUndefinedFunctionInspection */
add_shortcode( 'sdek-tracking', 'trackingForm' );


?>