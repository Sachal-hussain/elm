<?php
// cURL to fetch CAPTCHA token or data
$ch = curl_init();

// Set the URL of the CAPTCHA API or endpoint
curl_setopt($ch, CURLOPT_URL, 'https://orionstars.vip:8781/Tools/VerifyImagePage.aspx');

// Set options to return response as string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Set additional headers if necessary (you can inspect them using developer tools)
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Cookie: ASP.NET_SessionId=your_session_id',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
]);

// Execute cURL request and get the response
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL
curl_close($ch);

// Process the response if needed (you can check if it contains the CAPTCHA number)
echo $response;
?>
