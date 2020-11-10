<?php

namespace CryptoCore\CryptoPayment\Helper\Api;

class CryptoCoreCommunicator
{
    private $server;

    /**
     * @param mixed $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    function getResponseHeader($header, $response)
    {
        foreach ($response as $key => $r) {
            // Match the header name up to ':', compare lower case
            if (stripos($r, $header . ':') === 0) {
                list($headername, $headervalue) = explode(":", $r, 2);
                return trim($headervalue);
            }
        }
    }

    public function sendRequest($xmlRequest, $timeout = 30)
    {
        $jsonResponse = "";
        $status = 0;
        if (intval($timeout) < 0) {
            $timeout = 30;
        }
        if ($this->server == 'test') {
            $sslsock = fsockopen("ssl://gateway.ccore.online", 443, $errno, $errstr, $timeout);
        } else {
            $sslsock = fsockopen("ssl://gateway.ccore.online", 443, $errno, $errstr, $timeout);
        }
        if (is_resource($sslsock)) {

            $request_data = $xmlRequest;
            $request_length = strlen($request_data);

            if ($this->server == 'test') {
                fputs($sslsock, "POST /order/payment/new HTTP/1.0\r\n");
            } else {
                fputs($sslsock, "POST /order/payment/new HTTP/1.0\r\n");
            }

            fputs($sslsock, "Host: ccore.online\r\n");
            fputs($sslsock, "Content-type: application/json\r\n");
            fputs($sslsock, "Content-Length: " . $request_length . "\r\n");
            fputs($sslsock, "Connection: close\r\n\r\n");
            fputs($sslsock, $request_data);

            $response = "";
            while (!feof($sslsock)) {
                $response .= @fgets($sslsock, 128);
            }
            fclose($sslsock);
            $resp = explode("\r\n\r\n", $response);
            if (count($resp) == 2) {
                $headers = explode("\r\n", $resp[0]);
                foreach ($headers as $header) {
                    if (strpos($header, "HTTP") !== false) {
                        $stat = explode(" ", $header, 3);
                        if (count($stat) == 3) {
                            $status = $stat[1];
                        }
                        break;
                    }
                }
                $jsonResponse = $resp[1];
            }
        }
        return Array($status, $jsonResponse);
    }

    public function getRedirectUrl($paymentId)
    {
        return "https://gateway.ccore.online/gateway?payment_id=".$paymentId;
    }

    public function newOrderSignature(\CryptoCore\CryptoPayment\Helper\Api\CryptoCoreNewOrder $ccorder, $secrectKey)
    {
        return sha1($ccorder->currency_code . $ccorder->order_id . $ccorder->result_url . $ccorder->user_return_url . $ccorder->user_id . $ccorder->amount . $secrectKey);
    }
    public function newExchangeSignature($userId, $secrectKey)
    {
        return sha1($userId . $secrectKey);
    }
}