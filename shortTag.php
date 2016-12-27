<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 05.12.2016
 * Time: 1:52
 */
?>

<!--<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>-->
<link href="https://cdn.jotfor.ms/static/formCss.css?3.3.16036" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" href="https://cdn.jotfor.ms/css/styles/nova.css?3.3.16036">
<link type="text/css" media="print" rel="stylesheet" href="https://cdn.jotfor.ms/css/printForm.css?3.3.16036">
<script>
    $(document).ready(function () {
        /*On load hide all not primary logic blocks*/
        $("#trackingInfo").fadeOut(0);
        $("#phonePartBlock").fadeOut(0);
        $("#trackingError").fadeOut(0);

        $('#sdekCalculationForm').on('submit', function (e) {
            e.preventDefault();
            var waybill = $("#waybill").val();
            var phonePart = $("#phone").val();
            console.log("Tracking was requested for waybill = " + waybill + " and phone part = " + phonePart + "...");
            $.ajax({
                url: "/wp-content/plugins/wp-plg-sdek-tracking/track.php",
                data: {
                    waybill: waybill,
                    phonePart: phonePart
                },
                dataType: "json"

            }).success(function (data) {
                var errorCode = data['errorCode'];
                var errorText = data['errorText'];

                var $trackingState = $("#trackingState");
                var trackingErrorMessageBox = $("#trackingError");

                if (errorCode) {
                    $("#trackingDetails").fadeOut(0);
                    trackingErrorMessageBox.html(errorText);
                    trackingErrorMessageBox.fadeIn();
                    if (errorCode == 4) {
                        console.log("Phone number was requested for tracking finished...");
                        /*phone part requested*/
                        $("#phonePartBlock").fadeIn();
                        $trackingState.html("Требуетя подтверждение");
                    } else {
                        console.log("Tracking has been finished with error with code " + errorCode + " and text: '" + errorText + "'");
                        /*Nothing to do. Just return error text - our or from SDEK*/
                        /*Hide phone part*/
                        $("#phonePartBlock").fadeOut();
                        $trackingState.html("Ошибка");
                    }
                } else {
                    console.log("Successful tracking!");
                    /*Success*/
                    /*Hide phone part, clear phone part value*/
                    $("#phonePartBlock").fadeOut();
                    trackingErrorMessageBox.fadeOut();
                    $("#trackingDetails").fadeIn(0);
                    $("#phone").val("");

                    if (data['delivered'] == true) {
                        $trackingState.html("Доставлено");
                    } else {
                        $trackingState.html("В процессе");
                    }

                    $("#orderNumber").html(data['orderNumber']);
                    $("#senderName").html(data['senderName']);
                    $("#cityFrom").html(data['cityFrom']);
                    $("#cityTo").html(data['cityTo']);
                    $("#orderDate").html(data['orderDate']);

                    $("#history").html("");
                    if (data['history']) {
                        $.each(data['history'], function (index, eventRecord) {
                            console.log('Tracking event record ' + index + ': ' + eventRecord);
                            $("#history").append(
                                '<div class="form-subHeader">' +
                                '<span class="eventDate">' + eventRecord['eventDate'] + '</span>' +
                                '<span class="eventCity">' + eventRecord['eventCity'] + '</span>' +
                                '<span class="eventText">' + eventRecord['eventText'] + '</span>' +
                                '</div>'
                            );
                        });
                    }
                }
                $("#trackingInfo").fadeIn();
            });
        });
    });
</script>
<form id="sdekCalculationForm" class="jotform-form" style="margin-left: 20%">
    <div class="form-all">
        <ul class="form-section page-section">
            <li class="form-input-wide" data-type="control_head">
                <div class="form-header-group">
                    <div class="header-text httal htvam">
                        <h1 class="form-header">
                            Отслеживание грузового отправления СДЭК
                        </h1>
                        <div class="form-subHeader">
                            Пожалуйста, укажите номер накладной вашего отправления.
                            Так же может потребоваться ввести последние 4 цифры номера вашего мобильного телефона
                        </div>
                    </div>
                </div>
            </li>
            <li class="form-line">
                <label class="form-label form-label-top form-label-auto"> Данные
                    накладной </label>
                <div class="form-input-wide jf-required">
                <span class="form-sub-label-container" style="vertical-align: top;">
                        <input class="form-textbox" type="text" size="30" name="waybill" id="waybill"
                               required="required">
                        <label class="form-sub-label" for="waybill" style="min-height: 13px;">Номер накладной</label>
                </span>
                </div>
                <div id="phonePartBlock" class="form-input-wide jf-required">
                <span class="form-sub-label-container" style="vertical-align: top;">
                        <input class="form-textbox" type="number" size="30" name="phone" id="phone">
                        <label class="form-sub-label" for="phone" style="min-height: 13px;">message</label>
                </span>
                </div>
            </li>
            <li class="form-line" data-type="control_button">
                <div class="form-input-wide">
                    <div class="form-buttons-wrapper">
                        <button id="calculate" type="submit" class="form-submit-button" data-component="button">
                            Отследить
                        </button>
                    </div>
                </div>
            </li>
            <li id="trackingInfo" class="form-input-wide">
                <div class="form-header-group">
                    <div class="header-text httal htvam">
                        <h1 class="form-header">Результат отслеживания накладной: <span
                                id="trackingState">Доставлено</span></h1>
                        <div id="trackingError" class="form-subHeader"></div>
                        <div id="trackingDetails">
                            <div class="form-subHeader">
                                Накладная <span id="orderNumber"></span>;
                                Отправитель <span id="senderName"></span>;
                            </div>

                            <div class="form-subHeader">
                                Отправлено: <span id="cityFrom"></span>&nbsp;
                                <span id="orderDate"></span>;
                                Назначение: <span id="cityTo"></span>
                            </div>
                            <label class="form-label form-label-top form-label-auto"> История
                                доставки:</label>
                            <div id="history"></div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</form>
