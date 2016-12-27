<?php

/**
 * Created by PhpStorm.
 * User: denis
 * Date: 24.12.2016
 * Time: 0:10
 */
class SdekTracking
{
    /**
     * @param $waybill
     * @param $phonePart
     * @return mixed
     */
    function loadTrackingFromSdek($waybill, $phonePart)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.edostavka.ru/ajax.php?JsHttpRequest=0-xml");

        $data_string = "Action=GetDataByInvoice&invoice=" . $waybill;
        if (!empty($phonePart)) {
            $data_string = $data_string . "&phone=" . $phonePart;
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/octet-stream',
                'Referer: http://www.edostavka.ru/track.html?order_id=' . $waybill,
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                'Content-Type: application/octet-stream',
                'Content-Length: ' . strlen($data_string)
            )
        );


        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $data
     * @return string
     */
    function prepareResponse($data)
    {
        if(strpos($data, 'http-equiv="refresh"') !== false){
            return json_encode(array(
                "errorCode" => -4,
                /*Скорее всего самая популярная ошибка. Она и "не нашлась накладная" и "какая-то хрень со сдековским сервером"*/
                "errorText" => "В настоящий момент выполнить отслеживание по данной накладно невозможно. Попробуйте запросить информацию позже."
            ));
        }

        $json = json_decode($data);

        if (sizeof($json) > 0) {

            $js = $json->js;
            if ($js->Error != false) {
                return json_encode(array(
                    "errorCode" => -3,
                    "errorText" => $js->Error
                ));
            }


            $content = $js->Content;
            if (sizeof($content->error) > 0) {
                $errorCode = $content->error[0]->code;
                $errorText = $content->error[0]->text;

                return json_encode(array(
                    "errorCode" => $errorCode,
                    "errorText" => $errorText
                ));

            } else {
                if (sizeof($content->result) > 0) {

                    $result = $content->result[0];

                    $orderNumber = $result->orderNumber;
                    $cityFrom = $result->cityFrom;
                    $cityTo = $result->cityTo;
                    $senderName = $result->senderFullName;
                    $orderDate = $result->orderDate;

                    $history = array();
                    $delivered = false;

                    if (sizeof($result->delivery) > 0) {
                        foreach ($result->delivery as $row) {
                            $stageCity = $row->cityName;
                            foreach ($row->eventsDate as $stageDateTag) {
                                $eventDate = str_replace("&nbsp;", " ", $stageDateTag->date);
                                foreach ($stageDateTag->events as $event) {
                                    $rowRecord = array(
                                        "eventCity" => $stageCity,
                                        "eventDate" => $eventDate,
                                        "eventText" => $event);
                                    array_push($history, $rowRecord);
                                    if (!$delivered &&
                                        (strpos($event, 'доставлено') !== false /*||strpos($event, 'докинуто') !== false*/)
                                    ) {
                                        $delivered = true;
                                    }
                                }
                            }
                        }
                    }

                    return json_encode(array(
                        "orderNumber" => $orderNumber,
                        "cityFrom" => $cityFrom,
                        "cityTo" => $cityTo,
                        "senderName" => $senderName,
                        "orderDate" => $orderDate,
                        "delivered" => $delivered,
                        "history" => $history
                    ));
                } else {
                    return json_encode(array(
                        "errorCode" => -1,
                        "errorText" => "Доступная информация по данной накладной отсутствует"
                    ));
                }
            }
        } else {
            return json_encode(array(
                "errorCode" => -2,
                "errorText" => "Отправление не найдено"
            ));
        }
    }
}