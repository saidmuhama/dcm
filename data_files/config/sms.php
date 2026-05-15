<?php

require 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

class SmsService
{
    /**
     * Send SMS and save response to MySQL
     *
     * @param mysqli $db
     * @param string $phone
     * @param string $message
     * @return array
     */
    public static function sendSMS($db, $phone, $message)
    {
        // =========================
        // AFRICASTALKING CREDENTIALS
        // =========================
        $username = "MyAppsUsername";
        $apiKey   = "MyAppAPIKey";
        $senderId = "mySenderId"; // optional

        // =========================
        // INITIALIZE SDK
        // =========================
        $AT  = new AfricasTalking($username, $apiKey);
        $sms = $AT->sms();

        try {

            // =========================
            // SEND SMS
            // =========================
            $result = $sms->send([
                'to'      => $phone,
                'message' => $message,
                'from'    => $senderId
            ]);

            // =========================
            // EXTRACT RESPONSE
            // =========================
            $responseData = $result['SMSMessageData']['Recipients'][0];

            $statusCode = $responseData['statusCode'] ?? '';
            $status     = $responseData['status'] ?? '';
            $cost       = $responseData['cost'] ?? '';
            $messageId  = $responseData['messageId'] ?? '';

            $overallMessage = $result['SMSMessageData']['Message'] ?? '';

            // =========================
            // SAVE TO DATABASE
            // =========================
            $stmt = $db->prepare("
                INSERT INTO sms_logs 
                (
                    phone_number,
                    message_body,
                    message_id,
                    status,
                    status_code,
                    sms_cost,
                    api_response,
                    created_at
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");

            $jsonResponse = json_encode($result);

            $stmt->bind_param(
                "sssssss",
                $phone,
                $message,
                $messageId,
                $status,
                $statusCode,
                $cost,
                $jsonResponse
            );

            $stmt->execute();

            return [
                "status"  => true,
                "message" => $overallMessage,
                "data"    => $responseData
            ];

        } catch (Exception $e) {

            // =========================
            // SAVE FAILED RESPONSE
            // =========================
            $errorMessage = $e->getMessage();

            $stmt = $db->prepare("
                INSERT INTO sms_logs
                (
                    phone_number,
                    message_body,
                    status,
                    api_response,
                    created_at
                )
                VALUES
                (
                    ?, ?, 'FAILED', ?, NOW()
                )
            ");

            $stmt->bind_param(
                "sss",
                $phone,
                $message,
                $errorMessage
            );

            $stmt->execute();

            return [
                "status"  => false,
                "message" => $errorMessage
            ];
        }
    }
}