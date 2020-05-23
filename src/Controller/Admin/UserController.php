<?php


namespace App\Controller\Admin;


use App\Entity\User;
use App\Enum\AlertsEnum;
use App\Enum\UserRolesEnum;
use App\Enum\UserTypeEnum;
use App\Form\UsersSearchForm;
use App\Form\UserType;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use DateTime;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/users", name="adminUsers")
     */
    public function adminUsers(Request $request, UserRepository $userRepository, PaginatorInterface $paginator, SessionInterface $session)
    {
        $usersSearchForm = $this->createUsersSearchForm();

        $page = $request->query->getInt('page', 1);
        $session->set('admin/user/usersListLastPage', $page);

        $usersListPagination = $this->createUsersListPagination($session, $paginator, $userRepository);

        return $this->render('user/userList.html.twig', [
            'searchForm' => $usersSearchForm->createView(),
            'pagination' => $usersListPagination
        ]);
    }

    /**
     * @Route("/admin/users/search", name="userListSearch")
     */
    public function pasteListSearch(Request $request, UserRepository $userRepository, PaginatorInterface $paginator, SessionInterface $session)
    {
        $usersSearchForm = $this->createUsersSearchForm();
        $usersSearchForm->handleRequest($request);

        if ($usersSearchForm->isSubmitted() && $usersSearchForm->isValid()) {
            $session->set('admin/user/usersListSearchFormData', $usersSearchForm->getData());
        }

        $page = $request->query->getInt('page', 1);
        $session->set('admin/user/usersListLastPage', $page);

        $usersListPagination = $this->createUsersListPagination($session, $paginator, $userRepository);

        if ($request->isXmlHttpRequest()) {
            return $this->render('user/table/usersTableWrapper.html.twig', [
                'pagination' => $usersListPagination
            ]);
        }

        return $this->render('user/userList.html.twig', [
            'searchForm' => $usersSearchForm->createView(),
            'pagination' => $usersListPagination
        ]);
    }

    /**
     * @Route("/admin/users/add", name="admin_users_add")
     */
    public function userAdd(Request $request, UserRepository $userRepository, TranslatorInterface $translator, PaginatorInterface $paginator, SessionInterface $session, UserPasswordEncoderInterface $passwordEncoder)
    {
        $userForm = $this->createUserForm();
        $userForm->handleRequest($request);

        if (!$userForm->isSubmitted()) {
            return $this->render('user/userFormModal.html.twig', [
                'form' => $userForm->createView(),
                'title' => $translator->trans('usersPage.userAddTitle')
            ]);
        }

        if (!$userForm->isValid()) {
            return $this->render('user/form/userForm.html.twig', [
                'form' => $userForm->createView()
            ]);
        }

        /**
         * @var User
         */
        $user = $userForm->getData();

        $encodedPassword = $passwordEncoder->encodePassword(
            $user,
            $userForm->get('password')->getData()
        );

        $user->setPassword($encodedPassword);
        $user->setCreatedAt(new DateTime());

        $userRepository->save($user);

        $usersListPagination = $this->createUsersListPagination($session, $paginator, $userRepository);

        return $this->render('user/table/usersTableWrapper.html.twig', [
            'pagination' => $usersListPagination,
            'alerts' => [
                [
                    'type' =>  AlertsEnum::ALERT_TYPE_SUCCESS,
                    'message' => $translator->trans('usersPage.userAddSuccess')
                ]
            ]
        ]);
    }

    /**
     * @Route("/admin/users/edit/{userId}", name="userEdit", requirements={"userId"="\d+"})
     */
    public function userEdit(int $userId, Request $request, UserRepository $userRepository, TranslatorInterface $translator, PaginatorInterface $paginator, SessionInterface $session, UserPasswordEncoderInterface $passwordEncoder)
    {
        /**
         * @var User
         */
        $user = $userRepository->findOneById($userId);
        if (!$user) {
            return $this->json([
                'type' => 'alert',
                'alert_type' => AlertsEnum::ALERT_TYPE_ERROR,
                'message' => $translator->trans('usersPage.userNotExist')
            ]);
        }

        $userForm = $this->createUserForm($user, $this->generateUrl('userEdit', [
            'userId' => $userId
        ]), 'edit');
        $userForm->handleRequest($request);

        if (!$userForm->isSubmitted()) {
            return $this->render('user/userFormModal.html.twig', [
                'form' => $userForm->createView(),
                'title' => $translator->trans('usersPage.userEditTitle', [
                    '{{ username }}' => $user->getUsername()
                ])
            ]);
        }

        if (!$userForm->isValid()) {
            return $this->render('user/form/userForm.html.twig', [
                'form' => $userForm->createView()
            ]);
        }

        $password = $userForm->get('password')->getData();

        if ($password) {
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $password
            );
            $user->setPassword($encodedPassword);
        }

        $userRepository->save($user);

        $usersListPagination = $this->createUsersListPagination($session, $paginator, $userRepository);

        return $this->render('user/table/usersTableWrapper.html.twig', [
            'pagination' => $usersListPagination,
            'alerts' => [
                [
                    'type' =>  AlertsEnum::ALERT_TYPE_SUCCESS,
                    'message' => $translator->trans('usersPage.userEditSuccess')
                ]
            ]
        ]);
    }

    /**
     * @Route("/admin/users/delete/{userId}", name="userDelete", requirements={"userId"="\d+"})
     */
    public function userDelete(int $userId, Request $request, UserRepository $userRepository, TranslatorInterface $translator, PaginatorInterface $paginator, SessionInterface $session)
    {
        $user = $userRepository->findOneById($userId);
        if (!$user) {
            return $this->json([
                'type' => 'alert',
                'alert_type' => AlertsEnum::ALERT_TYPE_ERROR,
                'message' => $translator->trans('usersPage.userNotExist')
            ]);
        }

        if ($request->query->getInt('delete') == 1) {

            $userRepository->delete($user);

            $usersListPagination = $this->createUsersListPagination($session, $paginator, $userRepository);

            return $this->render('user/table/usersTableWrapper.html.twig', [
                'pagination' => $usersListPagination,
                'alerts' => [
                    [
                        'type' =>  AlertsEnum::ALERT_TYPE_SUCCESS,
                        'message' => $translator->trans('usersPage.userDeleteSuccess')
                    ]
                ]
            ]);
        }

        return $this->render('snippets/yesNoModal.html.twig', [
            'ModalId' => 'UserDeleteModal',
            'title' => $translator->trans('usersPage.userDeleteTitle', ['{{ username }}' => $user->getUsername()]),
            'text' => $translator->trans('usersPage.userDeleteQuestion'),
            'actionUrl' => $this->generateUrl('userDelete', ['userId' => $userId, 'delete' => 1])
        ]);
    }

    private function createUsersSearchForm()
    {
        return $this->createForm(UsersSearchForm::class, [], [
            'action' => $this->generateUrl('userListSearch')
        ]);
    }

    private function createUserForm(User $user = null, string $actionUrl = '', $mode = 'add') {
        if (empty($actionUrl))
        {
            $actionUrl = $this->generateUrl('admin_users_add');
        }

        return $this->createForm(UserType::class, $user, [
            'action' => $actionUrl,
            'type' => UserTypeEnum::ADMIN,
            'mode' => $mode
        ]);
    }

    /**
     * @param SessionInterface $session
     * @param PaginatorInterface $paginator
     * @param UserRepository $userRepository
     * @return PaginationInterface
     */
    private function createUsersListPagination(SessionInterface $session, PaginatorInterface $paginator, UserRepository $userRepository)
    {
        $searchFormData = $session->get('admin/user/usersListSearchFormData', []);
        $page = $session->get('admin/user/usersListLastPage', 1);
        $pastesQb = $userRepository->getUsersByFormData($searchFormData);

        $pagination = $paginator->paginate($pastesQb, $page, $this->getParameter('users_per_page'));
        $pagination->setUsedRoute('userListSearch');

        return $pagination;
    }
}