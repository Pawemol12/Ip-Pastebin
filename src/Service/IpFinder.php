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
        $regex = '/^(((?=(?>.*?(::))(?!.+3)))3?|([dA-F]{1,4}(3|:(?!$)|$)|2))(?4){5}((?4){2}|(25[0-5]|(2[0-4]|1d|[1-9])?d)(.(?7)){3})z/i';

        $ip_matches = [];

        preg_match_all($regex, $text, $ip_matches);

        $ipMatchesResult = [];
        foreach($ip_matches as $ipArr)
        {
            foreach($ipArr as $ipAddress)
            {
                if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 )) {
                    if (!in_array($ipAddress, $ipMatchesResult)) {
                        $ipMatchesResult[] = $ipAddress;
                    }
                }
            }
        }

        return $ipMatchesResult;
    }
}