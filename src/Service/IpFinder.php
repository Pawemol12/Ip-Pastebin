<?php


namespace App\Service;


class IpFinder
{
    public function findIpV4AddressesInText($text)
    {
        $ip_matches = [];

        preg_match_all('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $text, $ip_matches);

        $ipMatchesResult = [];
        foreach($ip_matches as $ipArr)
        {
            foreach($ipArr as $ipAddress)
            {
                if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )) {
                    if (!in_array($ipAddress, $ipMatchesResult)) {
                        $ipMatchesResult[] = $ipAddress;
                    }
                }
            }
        }

        return $ipMatchesResult;
    }

    public function findIpV6AddressesInText($text)
    {
        $regex = '/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))(?=\\s|$)/';

        $ip_matches = [];

        preg_match_all($regex, $text, $ip_matches);

        $ipMatchesResult = [];
        foreach($ip_matches as $ipArr)
        {
            foreach($ipArr as $ipAddress) {
                if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    if (!in_array($ipAddress, $ipMatchesResult)) {
                        $ipMatchesResult[] = $ipAddress;
                    }
                }
            }
        }


        return $ipMatchesResult;
    }
}