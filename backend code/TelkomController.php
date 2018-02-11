<?php

class TelkomController extends Controller
{
    
    public static function kirimSMS($username, $content)
    {
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.mainapi.net/smsnotification/1.0.0/messages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"msisdn\"\r\n\r\n$username\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"content\"\r\n\r\n $content \r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
                "Cache-Control: no-cache",
                "Postman-Token: c0f32c0a-6f5a-9047-efe1-0ab50ad56eaf",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
            )
        ));
        
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response, TRUE);
            if ($data['status'] == "SUCCESS") {
                return true;
            } else {
                return false;
            }
            
            // echo $response;
        }
    }
    
    public static function kirimSMSOtp($username)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.mainapi.net/smsotp/1.0.1/otp/$username",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => "phoneNum=$username&digit=4",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
                "Cache-Control: no-cache",
                "Content-Type: application/x-www-form-urlencoded",
                "Postman-Token: a24d0b85-5d02-8d26-841a-b6a6ffa73507",
                "accept: application/json"
            )
        ));
        
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response, TRUE);
            return $data;
            
        }
    }
    
    public static function verifikasiHelio()
    {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.mainapi.net/helio/1.0.1/login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\r\n  \"email\": \"admin@35utech.com\",\r\n  \"password\": \"Try08986044235\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
                "Cache-Control: no-cache",
                "Content-Type: application/json",
                "Postman-Token: 74952ade-e7db-0ec7-06e9-5d47e651f72b"
            )
        ));
        
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response, TRUE);
            if ($data['message'] == 'ok') {
                return $data['result']['user']['token'];
                // echo 
            } else {
                echo "<pre>";
                print_r($data);
                echo "</pre>";
                echo "error";
            }
            // echo $response;
        }
    }
    
    
    public static function HelioKirimEmail($email, $subject, $content)
    {
        // exit;
        $token = TelkomController::verifikasiHelio();
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.mainapi.net/helio/1.0.1/sendmail",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\r\n  \"token\": \"$token\",\r\n  \"subject\": \"$subject\",\r\n  \"to\": \"$email\",\r\n  \"body\": \"$content\"\r\n}",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer bfdd35ca8e67275aa7d463f82454c076",
                "Cache-Control: no-cache",
                "Postman-Token: f4f57150-95e1-9870-a65a-861db654a2af"
            )
        ));
        
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // echo $response;
            $data = json_decode($response, TRUE);
            if ($data['status'] == 200) {
                return true;
            } else {
                return false;
            }
            
            // return $data['result']['user']['token'];
        }
        
        
        
    }
    
    
    
    
}