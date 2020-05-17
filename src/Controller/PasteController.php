<?php

namespace App\Controller;

use App\Entity\Paste;
use App\Enum\AlertsEnum;
use App\Form\PasteType;
use App\Repository\PasteRepository;
use App\Service\IpFinder;
use App\Service\TextProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasteController extends AbstractController
{
    /**
     * @Route("/paste/new", name="pasteNew")
     */
    public function pasteNew(Request $request, PasteRepository $pasteRepository, TranslatorInterface $translator)
    {
        $pasteForm = $this->createPasteForm();
        $pasteForm->handleRequest($request);

        $alerts = [];
        $newPasteLink = '';

        if ($pasteForm->isSubmitted())
        {
            if (!$pasteForm->isValid()) {
                $alerts[] = [
                    'type' => AlertsEnum::ALERT_TYPE_ERROR,
                    'message' => $translator->trans('formValidationErrors')
                ];
            } else {
                $paste = $pasteForm->getData();

                $paste->setCode(uniqid());
                $paste->setDate(new \DateTime());

                $pasteRepository->save($paste);

                $newPasteLink = $this->generateUrl('pasteView', [
                    'pasteCode' => $paste->getCode()
                ]);

                $alerts[] = [
                    'type' => AlertsEnum::ALERT_TYPE_SUCCESS,
                    'message' => $translator->trans('pastebin.addPasteSuccess')
                ];
            }
        }


        return $this->render('paste/paste.html.twig', [
            'page' => 'pastebin',
            'form' => $pasteForm->createView(),
            'newPasteLink' => $newPasteLink,
            'alerts' => $alerts
        ]);
    }

    /**
     * @Route("/paste/view/{pasteCode}", name="pasteView")
     */
    public function pasteView(string $pasteCode, PasteRepository $pasteRepository, TranslatorInterface $translator, IpFinder $ipFinder, TextProcessor $textProcessor)
    {
        $paste = $pasteRepository->findOneByCode($pasteCode);
        if (!$paste) {
            return $this->render('paste/pasteView.html.twig', [
                'page' => 'pastebin',
                'alerts' => [
                    [
                        'type' => AlertsEnum::ALERT_TYPE_ERROR,
                        'message' => $translator->trans('pastebin.pasteNotExist')
                    ]
                ]
            ]);
        }

        $pasteText = $paste->getText();

        $ipV4Addresses = $ipFinder->findIpV4AddressesInText($pasteText);
        $ipV6Addresses = $ipFinder->findIpV6AddressesInText($pasteText);

        $pasteText = $textProcessor->markAllIpV4AddressesInText($pasteText, $ipV4Addresses);
        $pasteText = $textProcessor->markAllIpV6AddressesInText($pasteText, $ipV6Addresses);

        return $this->render('paste/pasteView.html.twig', [
            'page' => 'pastebin',
            'paste' => $paste,
            'pasteText' => $pasteText
        ]);
    }

    /**
     * @Route("/paste/list", name="pasteList")
     */
    public function pasteList(PasteRepository $pasteRepository)
    {
        $pasteList = $pasteRepository->findBy([], [
            'date' => 'DESC'
        ]);

        return $this->render('paste/pasteList.html.twig', [
            'pasteList' => $pasteList,
        ]);
    }

    private function createPasteForm(Paste $paste = null)
    {
        return $this->createForm(PasteType::class, $paste, [
            'action' => $this->generateUrl('pasteNew'),
        ]);
    }
}
