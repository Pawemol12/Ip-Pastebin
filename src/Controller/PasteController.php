<?php

namespace App\Controller;

use App\Entity\Paste;
use App\Enum\AlertsEnum;
use App\Enum\UserRolesEnum;
use App\Form\MyPastesSearchForm;
use App\Form\PastesSearchForm;
use App\Form\PasteType;
use App\Repository\PasteRepository;
use App\Service\IpFinder;
use App\Service\TextProcessor;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Security;
use App\Enum\PagesEnum;
use DateTime;

class PasteController extends AbstractController
{
    /**
     * @Route("/paste/new", name="pasteNew")
     */
    public function pasteNew(Request $request, PasteRepository $pasteRepository, TranslatorInterface $translator, Security $security)
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
                $paste->setCreateDate(new \DateTime());

                if ($security->isGranted('IS_AUTHENTICATED_FULLY'))
                {
                    $paste->setUser($security->getUser());
                }


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
            'page' => PagesEnum::PASTEBIN_PAGE,
            'form' => $pasteForm->createView(),
            'newPasteLink' => $newPasteLink,
            'title' => $translator->trans('pastebin.addNewPasteTitle'),
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
                'page' => PagesEnum::PASTEBIN_PAGE,
                'alerts' => [
                    [
                        'type' => AlertsEnum::ALERT_TYPE_ERROR,
                        'message' => $translator->trans('pastebin.pasteNotExist')
                    ]
                ]
            ]);
        }

        $expireDate = $paste->getExpireDate();

        if ($expireDate) {
            $currentDate = new DateTime();

            if ($expireDate < $currentDate) {
                return $this->render('paste/pasteView.html.twig', [
                    'page' => PagesEnum::PASTEBIN_PAGE,
                    'alerts' => [
                        [
                            'type' => AlertsEnum::ALERT_TYPE_ERROR,
                            'message' => $translator->trans('pastebin.pasteExpired')
                        ]
                    ]
                ]);
            }
        }

        $pasteText = $paste->getText();

        $ipV4Addresses = $ipFinder->findIpV4AddressesInText($pasteText);
        $ipV6Addresses = $ipFinder->findIpV6AddressesInText($pasteText);

        $pasteText = $textProcessor->markAllIpV4AddressesInText($pasteText, $ipV4Addresses);
        $pasteText = $textProcessor->markAllIpV6AddressesInText($pasteText, $ipV6Addresses);

        return $this->render('paste/pasteView.html.twig', [
            'page' => PagesEnum::PASTEBIN_PAGE,
            'paste' => $paste,
            'pasteText' => $pasteText
        ]);
    }

    /**
     * @Route("/paste/edit/{pasteCode}", name="pasteEdit")
     */
    public function pasteEdit(string $pasteCode, Request $request, PasteRepository $pasteRepository, TranslatorInterface $translator, Security $security)
    {
        $paste = $pasteRepository->findOneByCode($pasteCode);
        if (!$paste) {
            return $this->render('paste/pasteView.html.twig', [
                'page' => PagesEnum::PASTEBIN_PAGE,
                'alerts' => [
                    [
                        'type' => AlertsEnum::ALERT_TYPE_ERROR,
                        'message' => $translator->trans('pastebin.pasteNotExist')
                    ]
                ]
            ]);
        }

        $pasteOwner = $paste->getUser();
        if (!$this->isGranted(UserRolesEnum::USER_ROLE_ADMIN) && (empty($pasteOwner) || ($pasteOwner->getId() != $security->getUser()->getId()))) {
            return $this->render('paste/pasteView.html.twig', [
                'page' => PagesEnum::PASTEBIN_PAGE,
                'alerts' => [
                    [
                        'type' => AlertsEnum::ALERT_TYPE_ERROR,
                        'message' => $translator->trans('pastebin.pasteNotUser')
                    ]
                ]
            ]);
        }

        $pasteForm = $this->createPasteForm($paste, $this->generateUrl('pasteEdit', ['pasteCode' => $pasteCode]));
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

                $pasteRepository->save($paste);

                $newPasteLink = $this->generateUrl('pasteView', [
                    'pasteCode' => $paste->getCode()
                ]);

                $alerts[] = [
                    'type' => AlertsEnum::ALERT_TYPE_SUCCESS,
                    'message' => $translator->trans('pastebin.editPasteSuccess')
                ];
            }
        }

        return $this->render('paste/paste.html.twig', [
            'page' => PagesEnum::PASTEBIN_PAGE,
            'form' => $pasteForm->createView(),
            'newPasteLink' => $newPasteLink,
            'title' => $translator->trans('pastebin.editPasteTitle', [
                '{{ code }}' => $paste->getCode()
            ]),
            'alerts' => $alerts
        ]);
    }

    /**
     * @Route("/paste/deleteMine/{pasteCode}", name="pasteDeleteMine")
     */
    public function pasteDeleteMine(string $pasteCode, Request $request, PasteRepository $pasteRepository, TranslatorInterface $translator, PaginatorInterface $paginator, SessionInterface $session, Security $security)
    {
        $paste = $pasteRepository->findOneByCode($pasteCode);
        if (!$paste) {
            return $this->json([
                'type' => 'alert',
                'alert_type' => AlertsEnum::ALERT_TYPE_ERROR,
                'message' => $translator->trans('pastebin.pasteNotExist')
            ]);
        }

        $pasteOwner = $paste->getUser();
        if (empty($pasteOwner) || ($pasteOwner->getId() != $security->getUser()->getId())) {
            return $this->json([
                'type' => 'alert',
                'alert_type' => AlertsEnum::ALERT_TYPE_ERROR,
                'message' => $translator->trans('pastebin.pasteNotUser')
            ]);
        }

        if ($request->query->getInt('delete') == 1) {

            $pasteRepository->delete($paste);

            $userId = $security->getUser()->getId();
            $myPastesPagination = $this->createMyPastesListPagination($userId, $session, $paginator, $pasteRepository);

            return $this->render('paste/table/myPastesTableWrapper.html.twig', [
                'pagination' => $myPastesPagination,
                'alerts' => [
                    [
                        'type' =>  AlertsEnum::ALERT_TYPE_SUCCESS,
                        'message' => $translator->trans('pastebin.deletePasteSuccess')
                    ]
                ]
            ]);
        }

        return $this->render('snippets/yesNoModal.html.twig', [
            'ModalId' => 'MyPasteDeleteModal',
            'title' => $translator->trans('pastebin.paste.deleteTitle', ['{{ code }}' => $paste->getCode()]),
            'text' => $translator->trans('pastebin.paste.deleteQuestion'),
            'actionUrl' => $this->generateUrl('pasteDeleteMine', ['pasteCode' => $paste->getCode(), 'delete' => 1])
        ]);
    }

    /**
     * @Route("/paste/delete/{pasteCode}", name="pasteDelete")
     */
    public function pasteDelete(string $pasteCode, Request $request, PasteRepository $pasteRepository, TranslatorInterface $translator, PaginatorInterface $paginator, SessionInterface $session, Security $security)
    {
        $paste = $pasteRepository->findOneByCode($pasteCode);
        if (!$paste) {
            return $this->json([
                'type' => 'alert',
                'alert_type' => AlertsEnum::ALERT_TYPE_ERROR,
                'message' => $translator->trans('pastebin.pasteNotExist')
            ]);
        }

        if ($request->query->getInt('delete') == 1) {

            $pasteRepository->delete($paste);

            $pastesListPagination = $this->createPastesListPagination($session, $paginator, $pasteRepository);

            return $this->render('paste/table/pastesTableWrapper.html.twig', [
                'pagination' => $pastesListPagination,
                'alerts' => [
                    [
                        'type' =>  AlertsEnum::ALERT_TYPE_SUCCESS,
                        'message' => $translator->trans('pastebin.deletePasteSuccess')
                    ]
                ]
            ]);
        }

        return $this->render('snippets/yesNoModal.html.twig', [
            'ModalId' => 'PasteDeleteModal',
            'title' => $translator->trans('pastebin.paste.deleteTitle', ['{{ code }}' => $paste->getCode()]),
            'text' => $translator->trans('pastebin.paste.deleteQuestion'),
            'actionUrl' => $this->generateUrl('pasteDelete', ['pasteCode' => $paste->getCode(), 'delete' => 1])
        ]);
    }

    /**
     * @Route("/getIpInfo/{ipAddress}", name="getIpInfo")
     */
    public function getIpInfo(string $ipAddress, TextProcessor $textProcessor)
    {
        return new Response($textProcessor->getIpInfo($ipAddress));
    }

    /**
     * @Route("/paste/myList", name="myPasteList")
     */
    public function myPasteList(Request $request, PasteRepository $pasteRepository, Security $security, PaginatorInterface $paginator, SessionInterface $session)
    {
        $myPasteListSearchForm = $this->createMyPastesSearchForm();

        $page = $request->query->getInt('page', 1);
        $session->set('pastebin/pastes/myPastesListLastPage', $page);

        $userId = $security->getUser()->getId();

        $myPastesPagination = $this->createMyPastesListPagination($userId, $session, $paginator, $pasteRepository);

        return $this->render('paste/myPasteList.html.twig', [
            'searchForm' => $myPasteListSearchForm->createView(),
            'pagination' => $myPastesPagination
        ]);
    }

    /**
     * @Route("/paste/myList/search", name="myPasteListSearch")
     */
    public function myPasteListSearch(Request $request, PasteRepository $pasteRepository, Security $security, PaginatorInterface $paginator, SessionInterface $session)
    {
        $myPasteListSearchForm = $this->createMyPastesSearchForm();
        $myPasteListSearchForm->handleRequest($request);

        if ($myPasteListSearchForm->isSubmitted() && $myPasteListSearchForm->isValid()) {
            $session->set('pastebin/pastes/myPastesListSearchFormData', $myPasteListSearchForm->getData());
        }

        $page = $request->query->getInt('page', 1);
        $session->set('pastebin/pastes/myPastesListLastPage', $page);

        $userId = $security->getUser()->getId();
        $myPastesPagination = $this->createMyPastesListPagination($userId, $session, $paginator, $pasteRepository);

        if ($request->isXmlHttpRequest()) {
            return $this->render('paste/table/myPastesTableWrapper.html.twig', [
                'pagination' => $myPastesPagination
            ]);
        }

        return $this->render('paste/myPasteList.html.twig', [
            'searchForm' => $myPasteListSearchForm->createView(),
            'pagination' => $myPastesPagination
        ]);
    }

    /**
     * @Route("/paste/list", name="pasteList")
     */
    public function pasteList(Request $request, PasteRepository $pasteRepository, Security $security, PaginatorInterface $paginator, SessionInterface $session)
    {
        $pastesSearchForm = $this->createPastesSearchForm();

        $page = $request->query->getInt('page', 1);
        $session->set('pastebin/pastes/pastesListLastPage', $page);

        $pastesListPagination = $this->createPastesListPagination($session, $paginator, $pasteRepository);

        return $this->render('paste/pasteList.html.twig', [
            'searchForm' => $pastesSearchForm->createView(),
            'pagination' => $pastesListPagination
        ]);
    }

    /**
     * @Route("/paste/list/search", name="pasteListSearch")
     */
    public function pasteListSearch(Request $request, PasteRepository $pasteRepository, Security $security, PaginatorInterface $paginator, SessionInterface $session)
    {
        $pastesSearchForm = $this->createPastesSearchForm();
        $pastesSearchForm->handleRequest($request);

        if ($pastesSearchForm->isSubmitted() && $pastesSearchForm->isValid()) {
            $session->set('pastebin/pastes/pastesListSearchFormData', $pastesSearchForm->getData());
        }

        $page = $request->query->getInt('page', 1);
        $session->set('pastebin/pastes/pastesListLastPage', $page);

        $pastesListPagination = $this->createPastesListPagination($session, $paginator, $pasteRepository);

        if ($request->isXmlHttpRequest()) {
            return $this->render('paste/table/pastesTableWrapper.html.twig', [
                'pagination' => $pastesListPagination
            ]);
        }

        return $this->render('paste/pasteList.html.twig', [
            'searchForm' => $pastesSearchForm->createView(),
            'pagination' => $pastesListPagination
        ]);
    }

    private function createPasteForm(Paste $paste = null, string $actionUrl = '')
    {
        if (empty($actionUrl))
        {
            $actionUrl = $this->generateUrl('pasteNew');
        }

        return $this->createForm(PasteType::class, $paste, [
            'action' => $actionUrl,
        ]);
    }

    private function createMyPastesSearchForm()
    {
        return $this->createForm(MyPastesSearchForm::class, [], [
            'action' => $this->generateUrl('myPasteListSearch')
        ]);
    }

    private function createPastesSearchForm()
    {
        return $this->createForm(PastesSearchForm::class, [], [
            'action' => $this->generateUrl('pasteListSearch')
        ]);
    }

    /**
     * @param int $userId
     * @param SessionInterface $session
     * @param PaginatorInterface $paginator
     * @param PasteRepository $pasteRepository
     * @return PaginationInterface
     */
    private function createMyPastesListPagination(int $userId, SessionInterface $session, PaginatorInterface $paginator, PasteRepository $pasteRepository)
    {
        $searchFormData = $session->get('pastebin/pastes/myPastesListSearchFormData', []);
        $page = $session->get('pastebin/pastes/myPastesListLastPage', 1);
        $pastesQb = $pasteRepository->getUserPastesByFormData($searchFormData, $userId);

        $pagination = $paginator->paginate($pastesQb, $page, $this->getParameter('pastes_per_page'));
        $pagination->setUsedRoute('myPasteListSearch');

        return $pagination;
    }

    /**
     * @param SessionInterface $session
     * @param PaginatorInterface $paginator
     * @param PasteRepository $pasteRepository
     * @return PaginationInterface
     */
    private function createPastesListPagination(SessionInterface $session, PaginatorInterface $paginator, PasteRepository $pasteRepository)
    {
        $searchFormData = $session->get('pastebin/pastes/pastesListSearchFormData', []);
        $page = $session->get('pastebin/pastes/pastesListLastPage', 1);
        $pastesQb = $pasteRepository->getPastesByFormData($searchFormData);

        $pagination = $paginator->paginate($pastesQb, $page, $this->getParameter('pastes_per_page'));
        $pagination->setUsedRoute('pasteListSearch');

        return $pagination;
    }
}
