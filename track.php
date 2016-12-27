<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 24.12.2016
 * Time: 13:49
 */

$waybill = $_REQUEST['waybill'];
$phonePart = $_REQUEST['phonePart'];

try {
    require_once "SdekTracking.php";
    $tracking = new SdekTracking();
    $loadTrackingFromSdek = $tracking->loadTrackingFromSdek($waybill, $phonePart);
    $preparedResponse = $tracking->prepareResponse($loadTrackingFromSdek);
    header('Content-Type: application/json');
    echo $preparedResponse;
} catch (Exception $e) {
    echo json_encode(array(
        "errorCode" => -100,
        "errorText" =>  $e->getMessage()
    ));
}