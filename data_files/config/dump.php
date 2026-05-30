<?php 
require_once __DIR__ .'/selcom/vendor/autoload.php';
require 'sms/vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
// 
use Selcom\ApigwClient\Client;
class App
{

   public static function getBunnyStorageZone()
   {
    // return '1478965';
    return 'dcmbank';
   }

   public static function getBunnyStorageZoneAccessKey()
   {
    return '4de266cb-8471-4341-a20615111901-06dc-4d30';
   }

   //bunny storage deletion function
   public static function deleteBunnyStorageFile(
        $storageZone,
        $filePath,
        $apiKey
    )
    {
        // remove leading slash
        $filePath = ltrim($filePath, "/");

        // build url
        $url = "https://storage.bunnycdn.com/{$storageZone}/{$filePath}";

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => "DELETE",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "AccessKey: {$apiKey}"
            ]
        ]);

        $response = curl_exec($ch);

        // curl error
        if(curl_errno($ch)){

            $error = curl_error($ch);

            curl_close($ch);

            return [
                "status"  => "error",
                "message" => $error
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // success
        if(in_array($httpCode, [200,204])){

            return [
                "status"  => "success",
                "message" => "File deleted successfully"
            ];
        }

        return [
            "status"    => "error",
            "message"   => "Failed to delete file",
            "http_code" => $httpCode,
            "response"  => $response
        ];
    }
   //Creating bunny storage folder
   public static function createBunnyStorageFolder($storageZone, $folderPath, $apiKey)
   {
        // ✅ CLEAN PATH
        $folderPath = trim($folderPath, "/");

        // ✅ VALIDATE
        if(empty($storageZone) || empty($folderPath) || empty($apiKey)){

            return [
                "status"  => "error",
                "message" => "Missing required parameters"
            ];
        }

        // ✅ BUNNY STORAGE URL
        $url = "https://storage.bunnycdn.com/{$storageZone}/{$folderPath}/";

        // ✅ INIT CURL
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => "PUT",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                "AccessKey: {$apiKey}",
                "Content-Length: 0"
            ]
        ]);

        // ✅ EXECUTE
        $response = curl_exec($ch);

        // ❌ CURL ERROR
        if(curl_errno($ch)){

            $error = curl_error($ch);

            curl_close($ch);

            return [
                "status"  => "error",
                "message" => "Curl Error: " . $error
            ];
        }

        // ✅ HTTP STATUS
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // ✅ SUCCESS
        if(in_array($httpCode, [200, 201])){

            return [
                "status"      => "success",
                "message"     => "Folder created successfully",
                "folder_path" => $folderPath,
                "http_code"   => $httpCode
            ];
        }

        // ❌ FAILED
        return [
            "status"    => "error",
            "message"   => "Failed to create folder",
            "http_code" => $httpCode,
            "response"  => $response
        ];
    }
    //Uploading files .ppt .pdf .docx
   public static function uploadFileToBunnyStorage(
        $storageZoneName,
        $filePath,
        $remotePath,
        $apiKey,
        $fileName = null
    )
    {
        // ✅ CHECK FILE EXISTS
        if (!file_exists($filePath)) {

            return [
                "status"  => "error",
                "message" => "Local file not found"
            ];
        }

        // ✅ USE CUSTOM FILE NAME
        if(empty($fileName)){
            $fileName = basename($filePath);
        }

        // ✅ CLEAN REMOTE PATH
        $remotePath = trim($remotePath, "/");

        // ✅ BUILD URL
        $url      = "https://storage.bunnycdn.com/{$storageZoneName}/{$remotePath}/{$fileName}";
        $fileSize = filesize($filePath);
        $fh       = fopen($filePath, 'r');

        // ✅ INIT CURL — stream file directly, no file_get_contents
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_UPLOAD         => true,
            CURLOPT_INFILE         => $fh,
            CURLOPT_INFILESIZE     => $fileSize,
            CURLOPT_TIMEOUT        => 3600,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER     => [
                "AccessKey: {$apiKey}",
                "Content-Type: application/octet-stream",
            ],
        ]);

        // ✅ EXECUTE
        $response = curl_exec($curl);
        $errno    = curl_errno($curl);
        $errStr   = $errno ? curl_error($curl) : '';
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($fh);

        // ❌ CURL ERROR
        if ($errno) {
            return [
                "status"  => "error",
                "message" => "Curl error: " . $errStr
            ];
        }

        // ✅ SUCCESS
        if ($httpCode == 201 || $httpCode == 200) {

            return [
                "status"   => "success",
                "message"  => "File uploaded successfully",
                "file_url" => $url,
                "response" => $response
            ];
        }

        // ❌ FAILED
        return [
            "status"    => "error",
            "message"   => "Upload failed",
            "http_code" => $httpCode,
            "response"  => $response
        ];
    }
    //Update invitation code after registration completed 
    public static function updateInvitationCode($invitation_code)
    {
        require("db.php");
        $query = "UPDATE tbl_course_invitees SET status = 1 WHERE invitation_code = '$invitation_code'";
        mysqli_query($db, $query);
    }
    //Send SMS to User
    public static function sendSMS($phone, $message)
    {
         require("db.php");
        // =========================
        // AFRICASTALKING CREDENTIALS
        // =========================
        $username   = "digital_class";
        $apiKey     = "atsk_4f2bf313a37fb04f3a56dcc560fb521ad99faf7495b15097edfa6e2147848c3f769f36a1";

        $senderId = "DIGITALCLAS"; // optional

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

            // ==================================================
            // CONVERT OBJECT RESPONSE TO ASSOCIATIVE ARRAY
            // ==================================================
            $result = json_decode(json_encode($result), true);

            // ==================================================
            // EXTRACT RESPONSE SAFELY
            // ==================================================
            $responseData = $result['data']['SMSMessageData']['Recipients'][0] ?? [];

            $statusCode = $responseData['statusCode'] ?? '';
            $status     = $responseData['status'] ?? 'FAILED';
            $cost       = $responseData['cost'] ?? '';
            $messageId  = $responseData['messageId'] ?? '';

            $overallMessage = $result['data']['SMSMessageData']['Message'] ?? '';


        
            // =========================
            // SAVE TO DATABASE
            // =========================
            $stmt = $db->prepare("
                INSERT INTO tbl_sms_logs 
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
                INSERT INTO tbl_sms_logs
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

    public static function sendSMSs($phone, $message)
    {
        require("db.php");
        require 'vendor/autoload.php';

        // =========================
        // AFRICASTALKING CREDENTIALS
        // =========================
        $username   = "digital_class";
        $apiKey     = "atsk_4f2bf313a37fb04f3a56dcc560fb521ad99faf7495b15097edfa6e2147848c3f769f36a1";

        $senderId = "DIGITALCLAS"; // optional


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

            // ==================================================
            // CONVERT OBJECT RESPONSE TO ASSOCIATIVE ARRAY
            // ==================================================
            $result = json_decode(json_encode($result), true);

            // ==================================================
            // CHECK RESPONSE EXISTS
            // ==================================================
            $responseData = $result['SMSMessageData']['Recipients'][0] ?? [];

            $statusCode = $responseData['statusCode'] ?? '';
            $status     = $responseData['status'] ?? 'FAILED';
            $cost       = $responseData['cost'] ?? '';
            $messageId  = $responseData['messageId'] ?? '';

            $overallMessage = $result['SMSMessageData']['Message'] ?? '';

            // =========================
            // SAVE TO DATABASE
            // =========================
            $stmt = $db->prepare("
                INSERT INTO tbl_sms_logs 
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
                INSERT INTO tbl_sms_logs
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
    //Delete lesson and video both bunny and local
    public static function deleteVideo($libraryId, $videoId, $apiKey)
    {
    $url = "https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}";

    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL => $url,

        // ✅ CORRECT METHOD
        CURLOPT_CUSTOMREQUEST => "DELETE",

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            "AccessKey: {$apiKey}",
            "Content-Type: application/json"
        ],

        // ✅ IMPORTANT FOR BUNNY
        CURLOPT_POSTFIELDS => json_encode(new stdClass())

    ]);

    $response = curl_exec($ch);

    // =========================
    // CURL ERROR
    // =========================
    if (curl_errno($ch)) {

        $error = curl_error($ch);

        curl_close($ch);

        return [
            "status" => "error",
            "message" => "Curl error: " . $error
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    // =========================
    // SUCCESS
    // =========================
    if ($httpCode === 200 || $httpCode === 204) {

        return [
            "status" => "success",
            "message" => "Video deleted successfully"
        ];
    }

    // =========================
    // FAILURE
    // =========================
    return [
        "status" => "error",
        "message" => "Failed to delete video",
        "http_code" => $httpCode,
        "response" => json_decode($response, true)
    ];
}
    //renaming a video 
    public static function renameVideo($libraryId, $videoId, $newTitle, $apiKey)
    {
        $url = "https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}";

        $payload = json_encode([
            "title" => $newTitle
        ]);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "AccessKey: {$apiKey}",
                "Content-Type: application/json",
                "Content-Length: " . strlen($payload)
            ],
            CURLOPT_POSTFIELDS     => $payload
        ]);

        $response = curl_exec($ch);

        // ❌ CURL ERROR
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                "status" => "error",
                "message" => "Curl error: " . $error
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        // ✅ SUCCESS
        if ($httpCode === 200) {
            return [
                "status" => "success",
                "message" => "Video renamed successfully",
                "data" => $result
            ];
        }

        // ❌ FAILURE
        return [
            "status" => "error",
            "message" => "Failed to rename video",
            "http_code" => $httpCode,
            "response" => $result
        ];
    }
    //deleting bunny video library or Course 
    public static function deleteVideoLibrary($libraryId, $apiKey)
    {
        $url = "https://api.bunny.net/videolibrary/{$libraryId}";

        $ch = curl_init();

        curl_setopt_array($ch, [

            CURLOPT_URL => $url,

            // ✅ CORRECT METHOD FOR LIBRARY DELETE
            CURLOPT_CUSTOMREQUEST => "POST",

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPHEADER => [
                "AccessKey: {$apiKey}",
                "Content-Type: application/json"
            ],

            // REQUIRED
            CURLOPT_POSTFIELDS => json_encode(new stdClass()),

            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {

            $error = curl_error($ch);
            curl_close($ch);

            return [
                "status" => "error",
                "message" => "Curl error: " . $error
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // Bunny returns 200 or 204 depending on API version
        if ($httpCode == 200 || $httpCode == 204) {

            return [
                "status" => "success",
                "message" => "Library deleted successfully"
            ];
        }

        return [
            "status" => "error",
            "message" => "Failed to delete library",
            "http_code" => $httpCode,
            "response" => json_decode($response, true)
        ];
    }
    //rename bunny video library 
    public static function renameVideoLibrary($libraryId, $newName, $apiKey)
    {
        $url = "https://api.bunny.net/videolibrary/{$libraryId}";

        $payload = json_encode([
            "Name" => $newName
        ]);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "AccessKey: {$apiKey}",
                "Content-Type: application/json",
                "Content-Length: " . strlen($payload)
            ],
            CURLOPT_POSTFIELDS     => $payload
        ]);

        $response = curl_exec($ch);

        // Handle CURL errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                "status" => "error",
                "message" => "Curl error: " . $error
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 200) {
            return [
                "status" => "success",
                "message" => "Library renamed successfully",
                "data" => $result
            ];
        }

        return [
            "status" => "error",
            "message" => "Failed to rename library",
            "http_code" => $httpCode,
            "response" => $result
        ];
    }

    /**
     * Rename a Bunny Storage folder by copying every file to the new path
     * then deleting the originals. Returns a status report.
     *
     * Because Bunny Storage has no native rename/move API, this downloads
     * each file via the storage API and re-uploads it to the new path.
     */
    public static function renameBunnyStorageFolder(
        $storageZone, $oldFolder, $newFolder, $storageKey
    ) {
        $oldFolder = trim($oldFolder, '/');
        $newFolder = trim($newFolder, '/');

        if ($oldFolder === $newFolder) {
            return ['status'=>'success','message'=>'Folder names are identical — no action needed','moved'=>0];
        }

        // List objects in old folder
        $listUrl = "https://storage.bunnycdn.com/{$storageZone}/{$oldFolder}/";
        $ch = curl_init($listUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ["AccessKey: {$storageKey}", "Accept: application/json"],
        ]);
        $listJson = curl_exec($ch);
        $listCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $e = curl_error($ch); curl_close($ch);
            return ['status'=>'error','message'=>"cURL listing error: {$e}"];
        }
        curl_close($ch);

        // 404 means no files were ever uploaded to this folder — nothing to move
        if ($listCode === 404) {
            return ['status'=>'success','message'=>'Old folder did not exist — nothing to move','moved'=>0];
        }
        if ($listCode !== 200) {
            return ['status'=>'error','message'=>"Could not list old storage folder (HTTP {$listCode})"];
        }

        $items = json_decode($listJson, true);
        if (!is_array($items)) {
            return ['status'=>'error','message'=>'Invalid folder listing response from Bunny Storage'];
        }

        $moved  = 0;
        $errors = [];

        foreach ($items as $item) {
            if (!empty($item['IsDirectory'])) continue; // skip sub-folders

            $name = $item['ObjectName'];

            // Download from old path via Storage API
            $dlUrl = "https://storage.bunnycdn.com/{$storageZone}/{$oldFolder}/{$name}";
            $dlCh  = curl_init($dlUrl);
            curl_setopt_array($dlCh, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 180,
                CURLOPT_HTTPHEADER     => ["AccessKey: {$storageKey}"],
            ]);
            $fileData = curl_exec($dlCh);
            $dlCode   = curl_getinfo($dlCh, CURLINFO_HTTP_CODE);
            if (curl_errno($dlCh) || $dlCode !== 200 || $fileData === false) {
                $errors[] = "Download failed: {$name} (HTTP {$dlCode})";
                curl_close($dlCh);
                continue;
            }
            curl_close($dlCh);

            // Upload to new path
            $ulUrl = "https://storage.bunnycdn.com/{$storageZone}/{$newFolder}/{$name}";
            $ulCh  = curl_init($ulUrl);
            curl_setopt_array($ulCh, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => "PUT",
                CURLOPT_POSTFIELDS     => $fileData,
                CURLOPT_TIMEOUT        => 180,
                CURLOPT_HTTPHEADER     => [
                    "AccessKey: {$storageKey}",
                    "Content-Type: application/octet-stream",
                    "Content-Length: " . strlen($fileData),
                ],
            ]);
            $ulCode = curl_getinfo($ulCh, CURLINFO_HTTP_CODE);
            curl_exec($ulCh);
            curl_close($ulCh);
            unset($fileData); // free memory between iterations

            if (in_array($ulCode, [200, 201])) {
                self::deleteBunnyStorageFile($storageZone, "{$oldFolder}/{$name}", $storageKey);
                $moved++;
            } else {
                $errors[] = "Upload failed: {$name} (HTTP {$ulCode})";
            }
        }

        if (!empty($errors)) {
            return [
                'status'  => 'partial',
                'message' => "Moved {$moved} file(s) with " . count($errors) . " error(s)",
                'moved'   => $moved,
                'errors'  => $errors,
            ];
        }

        return ['status'=>'success','message'=>"Moved {$moved} file(s) to new folder",'moved'=>$moved];
    }

    //get selcom Private Key
     protected static function getPrivateKey(){
       return 'KxQhI2uTiLO3O1nTkbxBO9MGDDKVzOgT'; 
    }

    //get selcom Piblic Key
    protected static function getPublicKey(){
       return 'TILL61054532-WuVYcnwOi8izeQCO'; 
    }

    //get selcom Vendor Registration Till
    protected static function getVendorId(){
       return 'TILL61054532'; 
    }
    
    //push wallet payment 
    public static function makePaymentRequest($username, $order_id, $mobile_phone)
    {
    
        $apiKey    = self::getPublicKey();
        $apiSecret = self::getPrivateKey();
        $baseUrl   = "https://apigw.selcommobile.com";
        
        
        $client  = new Client($baseUrl, $apiKey, $apiSecret);
        $transid = strtoupper(bin2hex(random_bytes(5)));
        $walletPaymentArray = array(
            "transid"=> $transid,
            "order_id"=>$order_id,
            "msisdn"=> $mobile_phone,
            );
        // path relatiive to base url
        $walletPaymentPath = "/v1/checkout/wallet-payment";
        
        $response = $client->postFunc($walletPaymentPath,$walletPaymentArray);
        
        return json_encode($response);
    }
    //selcom creating customer order Card Payments
   public static function selcomCardPaymentOrder($username, $orderId, $currency, $country, $order_amount)
    {
        require("db.php");

        try {
            // ===== CONFIG =====
            $zipCode   = self::getCountryParams('iso', $country);
            $apiKey    = self::getPublicKey();
            $apiSecret = self::getPrivateKey();
            $baseUrl   = "https://apigw.selcommobile.com";

            $client = new Client($baseUrl, $apiKey, $apiSecret);

            $vendor            = self::getVendorId();
            $postcode_or_pobox = '00' . self::getCountryParams('phonecode', $country);
            $state_or_region   = $zipCode;
            $city              = $country;
            $address_1         = $country;

            // ===== USER DATA =====
            $firstname = self::getWhatFromWHere('first_name','tbl_students','usr_code',$username);
            $lastname  = self::getWhatFromWHere('last_name','tbl_students','usr_code',$username);
            $email     = self::getWhatFromWHere('email','tbl_students','usr_code',$username);
            $phone     = self::getWhatFromWHere('phone','tbl_students','usr_code',$username);

            $buyer_name  = $firstname . ' ' . $lastname;
            $amount      = $order_amount;
            $created_at  = date('Y-m-d H:i:s');
            $order_status = 0;

            // ===== OPTIONAL FIELDS =====
            $redirect_url     = "";
            $cancel_url       = "";
            $webhook          = "aHR0cHM6Ly93ZWdvZXZlbnRzLmNvLnR6L3BvcnRhbC9jbHMvYXBpL2NhbGxiYWNrLnBocA==";
            $buyer_remarks    = "None";
            $merchant_remarks = "None";
            $no_of_items      = 1;

            // ===== ORDER PAYLOAD =====
            $orderArray = [
                "vendor" => $vendor,
                "order_id" => $orderId,
                "buyer_email" => $email,
                "buyer_name" => $buyer_name,
                "buyer_phone" => $phone,
                "amount" => $amount,
                "currency" => $currency,
                "payment_methods" => "ALL",
                "webhook" => $webhook,

                "billing.firstname" => $firstname,
                "billing.lastname" => $lastname,
                "billing.address_1" => $address_1,
                "billing.city" => $city,
                "billing.state_or_region" => $state_or_region,
                "billing.postcode_or_pobox" => $postcode_or_pobox,
                "billing.country" => $zipCode,
                "billing.phone" => $phone,

                "buyer_remarks" => $buyer_remarks,
                "merchant_remarks" => $merchant_remarks,
                "no_of_items" => $no_of_items
            ];

            // ===== API CALL =====
            $orderPath = "/v1/checkout/create-order";
            $response = $client->postFunc($orderPath, $orderArray);

            // Normalize response (string or array safe)
            $data = is_string($response) ? json_decode($response, true) : $response;

            if (!$data || !isset($data['data'][0]['payment_gateway_url'])) {
                return json_encode([
                    "status" => "error",
                    "message" => "Invalid response from Selcom",
                    "raw" => $response
                ]);
            }

            // ===== EXTRACT PAYMENT URL =====
            $encodedUrl = $data['data'][0]['payment_gateway_url'];
            $decodedUrl = self::base64UrlDecode($encodedUrl);

            // Attach extra info
            $data['order_id'] = $orderId;
            $data['payment_gateway_url'] = $decodedUrl;

            // ===== SAVE TO DATABASE =====
            $stmt = $db->prepare("
                INSERT INTO tbl_payment_order(
                    pay_type, payment_gateway_url, username, address_1, city, state_or_region,
                    postcode_or_pobox, country, vendor, order_id, buyer_email, buyer_name,
                    buyer_phone, amount, currency, redirect_url, cancel_url, webhook,
                    buyer_remarks, merchant_remarks, no_of_items, order_status, created_at
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");

            $pay_type = "CARD";

            $stmt->bind_param(
                "sssssssssssssdssssssi",
                $pay_type,
                $decodedUrl,
                $username,
                $address_1,
                $city,
                $state_or_region,
                $postcode_or_pobox,
                $zipCode,
                $vendor,
                $orderId,
                $email,
                $buyer_name,
                $phone,
                $amount,
                $currency,
                $redirect_url,
                $cancel_url,
                $webhook,
                $buyer_remarks,
                $merchant_remarks,
                $no_of_items,
                $order_status,
                $created_at
            );

            if ($stmt->execute()) {
                return json_encode($data);
            } else {
                return json_encode([
                    "status" => "error",
                    "message" => $stmt->error
                ]);
            }

        } catch (Exception $e) {
            return json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
    //Selcom Creating customer order for mobile wallet Push to Pay
    public static function selcomMobileWalletOrder($username,$orderId, $order_amount)
    {
        
        require("db.php");
        $order_id         = date('YmdHis');
        $vendor           = self::getVendorId();
        $buyer_email      = $email;
        $buyer_name       = $fullname;
        $buyer_phone      = $phone;
        $amount           = $order_amount;
        $currency         = 'TZS';
        $redirect_url     = 'None';
        $cancel_url       = 'None';
        $webhook          = 'None';
        $buyer_remarks    = 'None';
        $merchant_remarks = 'None';
        $no_of_items      = 1;
        $order_status     = 0;
        $created_at       = date('Y-m-d H:i:s');
        
        $sqli = mysqli_query($db,"INSERT INTO tbl_payment_order(pay_type,username,vendor,order_id,buyer_email,buyer_name,buyer_phone,amount,currency,redirect_url,
                                    cancel_url,webhook,buyer_remarks,merchant_remarks,no_of_items,order_status,created_at)	
                                    VALUES('MNO','$username','$vendor','$order_id','$buyer_email','$buyer_name','$buyer_phone','$amount','$currency','$redirect_url',
                                    '$cancel_url','$webhook','$buyer_remarks','$merchant_remarks','$no_of_items','$order_status','$created_at')");


        $apiKey    = self::getPublicKey();
        $apiSecret = self::getPrivateKey();
        $baseUrl   = "https://apigw.selcommobile.com";
        
        $client    = new Client($baseUrl, $apiKey, $apiSecret);
        // data
        $orderMinArray = array(
        "vendor"       => $vendor,
        "order_id"     => $order_id,
        "buyer_email"  => $buyer_email,
        "buyer_name"   => $buyer_name,
        "buyer_phone"  => $buyer_phone,
        "amount"       => $amount,
        "currency"     => "TZS",
        "webhook"      => "aHR0cHM6Ly93ZWdvZXZlbnRzLmNvLnR6L3BvcnRhbC9jbHMvYXBpL2NhbGxiYWNrLnBocA==",
        "buyer_remarks"=>"None",
        "merchant_remarks"=>"None",
        "no_of_items"  =>  1
        );
        
        $orderMinPath = "/v1/checkout/create-order-minimal";
        $response     = $client->postFunc($orderMinPath,$orderMinArray);
        
        $response['order_id'] = $order_id;
       
        //This will return Card Payment gatway Url that will be lunched to Users.
        return json_encode($response);
    
    }
     public static function base64UrlDecode($input) {
    // Replace URL-safe characters with Base64 characters
    $base64 = str_replace(['-', '_'], ['+', '/'], $input);

    // Pad with `=` to make the length of the string a multiple of 4
    $padding = strlen($base64) % 4;
    if ($padding > 0) {
        $base64 .= str_repeat('=', 4 - $padding);
    }

    // Decode the Base64 string
    return base64_decode($base64);
    }
    // country code parameters
    public static function getCountryParams($what, $nicename)
    {
        require("db.php");
        $check = mysqli_query($db, "SELECT $what AS WHAT FROM  tbl_country WHERE nicename='$nicename'");
        $row   = mysqli_fetch_array($check);
        return $row['WHAT'];
    }
    //Getting user profile image
    public static function getUserProfileImage($usr_code,$user_role)
    {
        include('db.php');
        if($user_role == '1')
        {
            $sql = mysqli_query($db, "SELECT * FROM tbl_students WHERE usr_code = '$usr_code'");
            $row = mysqli_fetch_array($sql);
            $image = $row['image'];
            return $image;
        }
        elseif($user_role == '3')
        {
            $sql = mysqli_query($db, "SELECT * FROM tbl_tutors WHERE usr_code = '$usr_code'");
            $row = mysqli_fetch_array($sql);
            $image = $row['image'];
            return $image;
        }
    }

    //BunnyNet Account level API key 
    public static function getBunnyNetApiKey()
    {
        return "efc485b8-382d-4311-b85e-7b6175d94cf99c7ce3ba-ec16-4df8-b727-f0e5bd75bd74";
    }

    //BunnyNet Video Library Key
    public static function getBunnyLibraryKey($library_id, $api_key)
    {
            $url = "https://api.bunny.net/videolibrary/$library_id?includeAccessKey=true";

            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "AccessKey: $api_key",
                    "Content-Type: application/json"
                ]
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                curl_close($ch);
                return [
                    "status" => "error",
                    "message" => curl_error($ch)
                ];
            }

            curl_close($ch);

            $data = json_decode($response, true);

            if (!is_array($data)) {
                return [
                    "status" => "error",
                    "message" => "Invalid JSON response",
                    "raw" => $response
                ];
            }

            // Bunny returns LibraryKey when includeAccessKey=true
            $library_key = $data['ApiKey'] ?? null;

            if (!$library_key) {
                return null;
            }

            return  $library_key;
        }
    public static function signupStatus($usr_code)
    {
        include('db.php');
        $sql = mysqli_query($db, "SELECT * FROM  tbl_all_users  WHERE usr_code = '$usr_code'");
        $row = mysqli_fetch_array($sql);
        $signup_success = $row['signup_success'];
        return $signup_success;
    }
    //fetch anything from any table
    public static function getWhatFromWHere($what,$table, $column,$value)
    {
        include('db.php');
        $sql = mysqli_query($db, "SELECT $what AS WHAT FROM $table WHERE $column = '$value'");
        $row = mysqli_fetch_array($sql);
        $whatToReturn = $row['WHAT'];
        return $whatToReturn;
    }
    //profile completion status
    public static function getProfileCompletionStatus($usr_code, $user_role)
    {
        include('db.php');
        $completionPercentage = 10;

        if ($user_role == '1') {
            $sql = mysqli_query($db, "SELECT * FROM tbl_students WHERE usr_code = '" . mysqli_real_escape_string($db, $usr_code) . "'");
            $row = $sql ? mysqli_fetch_array($sql) : null;
            if (!$row) {
                $completionPercentage = 10;
            } elseif (!empty($row['parent_name']) && !empty($row['sub_academic_level'])) {
                $completionPercentage = 98;
                self::markProfileAsCompleted($usr_code);
            } elseif (!empty($row['sub_academic_level'])) {
                $completionPercentage = 87;
                self::markProfileAsCompleted($usr_code);
            } elseif (!empty($row['first_name'])) {
                $completionPercentage = 50;
            } else {
                $completionPercentage = 30;
            }
        } elseif ($user_role == '3') {
            $sql = mysqli_query($db, "SELECT * FROM tbl_tutors WHERE usr_code = '" . mysqli_real_escape_string($db, $usr_code) . "'");
            $row = $sql ? mysqli_fetch_array($sql) : null;
            if (!$row) {
                $completionPercentage = 10;
            } elseif (!empty($row['parent_name']) && !empty($row['sub_academic_level'])) {
                $completionPercentage = 98;
                self::markProfileAsCompleted($usr_code);
            } elseif (!empty($row['sub_academic_level'])) {
                $completionPercentage = 87;
                self::markProfileAsCompleted($usr_code);
            } else {
                $completionPercentage = 30;
            }
        }

        return $completionPercentage;
    }
    
    //mark profile as completed
    public static function markProfileAsCompleted($usr_code)
    {
        include('db.php');
        $sql = mysqli_query($db, "UPDATE tbl_all_users SET signup_success = 'Completed' WHERE usr_code = '$usr_code'");
        return true;
    }
}
