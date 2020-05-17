<?php


namespace App\Service;


class IpToAsnApiService
{
    public const API_ADDRESS = 'https://api.iptoasn.com';

    public function getInfoAboutIp(string $ipAddress)
    {
        $link = self::API_ADDRESS.'/v1/as/ip/'.$ipAddress;

        $ch = curl_init($link);

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if($curlError) {
            return [];
        }

        return json_decode($response, true);
    }

}