<?php


namespace App\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\UserRepository;
use App\Entity\User;
use DateTime;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Paweł Lodzik <Pawemol12@gmail.com>
 */
class UserLoginSuccessHandler implements AuthenticationSuccessHandlerInterface {

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     */
    public function __construct(UserRepository $userRepository, RouterInterface $router) {
        $this->userRepository = $userRepository;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        /**
         * @var User
         */
        $user = $token->getUser();
        $user->setLastLoginDate(new DateTime());
        $this->userRepository->save($user);

        return new RedirectResponse($this->router->generate('index'));
    }
}