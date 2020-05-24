<?php


namespace App\Service;


use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;


class TextProcessor
{
    /**
     * @var IpToAsnApiService
     */
    private $ipToAsnApi;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * TextProcessor constructor.
     * @param $ipToAsnApi
     */
    public function __construct(IpToAsnApiService $ipToAsnApi, Environment $twig, TranslatorInterface $translator)
    {
        $this->ipToAsnApi = $ipToAsnApi;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public function markAllIpV4AddressesInText(string $text, array $ipAddresses)
    {

        foreach ($ipAddresses as $ipAddress)
        {
            $ipAddressFormat = '<span title="" rel="tooltip" class="ipV4Address" data-html="true">'.$ipAddress.'</span>';
            $text = str_replace($ipAddress, $ipAddressFormat,  $text);
        }

        return $text;
    }

    public function markAllIpV6AddressesInText(string $text, array $ipAddresses)
    {


        foreach ($ipAddresses as $ipAddress)
        {
            $ipAddressFormat = '<span class="ipV6Address" title="" rel="tooltip" class="ipV6Address" data-html="true">'.$ipAddress.'</span>';
            $text = str_replace($ipAddress, $ipAddressFormat,  $text);
        }

        return $text;
    }

    public function getIpInfo($ipAddress)
    {
        $ipInfo = $this->ipToAsnApi->getInfoAboutIp($ipAddress);

        if (empty($ipInfo)) {
            $tooltipTitle = $this->translator->trans('apiConnectionError');
        } else if (!$ipInfo['announced']) {
            $tooltipTitle = $this->translator->trans('noIpInfo');
        } else {
            $tooltipTitle = $this->twig->render('paste/ipInfo.html.twig', [
                'ipInfo' => $ipInfo
            ]);
        }

        return $tooltipTitle;
    }
}