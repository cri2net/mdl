<?php

class Http
{
    public static function redirect($location, $permanently = true, $exit = true)
    {
        if ($permanently) {
            header("HTTP/1.1 301 Moved Permanently");
        } else {
            header("HTTP/1.1 307 Temporary Redirect");
        }
        header("Location: $location");
        if ($exit) {
            exit();
        }
    }

    /**
     * Enable GZIP
     * 
     * @param  string  $data           content for compression
     * @param  boolean $echo           need echo. OPTIONAL
     * @param  string  $content_type   Content-type for header. OPTIONAL
     * @param  string  $charset        charset of $data. OPTIONAL
     * @param  integer $offset         offset for expire header. OPTIONAL
     * 
     * @return void | string
     */
    public static function gzip($data, $echo = true, $content_type = 'text/html', $charset = 'UTF-8', $offset = 1209600)
    {
        $supportsGzip = (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) && function_exists('gzencode');
        $expire = "expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        
        if (!$supportsGzip) {
            if (!$echo) {
                return $data;
            }
            $content = $data;
        } else {
            $content = gzencode(trim(preg_replace('/\s+/', ' ', $data)), 9);
        }

        if (!$echo) {
            return $content;
        }

        if ($supportsGzip) {
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
        }
        
        header("Content-type: $content_type; charset: $charset");
        header("cache-control: must-revalidate");
        header($expire);
        header('Content-Length: ' . strlen($content));

        echo $content;
    }

    public static function httpGet($url, $checkStatus = true)
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPGET => true,
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if ($checkStatus) {
            self::getStatus($ch, $response);
        } else {
            curl_close($ch);
        }
        
        return $response;
    }
    
    public static function httpPost($url, $data, $checkStatus = true)
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_SSL_VERIFYHOST => false
        );
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if ($checkStatus) {
            self::getStatus($ch, $response);
        } else {
            curl_close($ch);
        }
        
        return $response;
    }

    private static function getStatus($ch, $response)
    {
        if (!$response) {
            curl_close($ch);
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 666) {
            curl_close($ch);
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        } elseif ($httpCode == 665) {
            curl_close($ch);
            $errorMsg = json_decode($response);
            throw new Exception($errorMsg->record->msg);
        }
    }

    public static function fgets($url, $method = 'GET', $data = array())
    {
        $data = http_build_query($data);

        $context = stream_context_create(array('http'=>
            array(
                'method' => $method,
                'header' => array(
                    // "Content-type: application/x-www-form-urlencoded",
                    "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0",
                ),
                'timeout' => 15,
                'content' => $data
            )
        ));

        return file_get_contents($url, false, $context);
    }

}
