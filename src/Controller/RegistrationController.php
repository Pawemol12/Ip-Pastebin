<?php


namespace App\Controller;

use App\Enum\PagesEnum;
use App\Enum\UserTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Enum\AlertsEnum;
use App\Enum\UserRolesEnum;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use DateTime;


class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     * @param TranslatorInterface $translator
     * @param TokenStorageInterface $tokenStorage
     * @return Response
     * @throws Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository, TranslatorInterface $translator, TokenStorageInterface
    $tokenStorage): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'action' => $this->generateUrl('user_register'),
            'type' => UserTypeEnum::REGISTER,
            'attr' => ['id' => 'RegisterForm']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('password')->getData()
            );

            $user->setPassword($encodedPassword);

            $nowDate = new DateTime();

            $user->setCreatedAt($nowDate);
            $user->setLastLoginDate($nowDate);

            $user->setRoles([UserRolesEnum::USER_ROLE_USER]);

            $userRepository->save($user);

            $this->addFlash(AlertsEnum::ALERT_FLASH_SUCCESS, $translator->trans('registration.registrationSuccessfull'));

            $token = new UsernamePasswordToken(
                $user,
                $encodedPassword,
                'main',
                $user->getRoles()
            );
            $tokenStorage->setToken($token);

            return $this->redirectToRoute('index');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
            'page' => PagesEnum::REGISTER_PAGE
        ]);
    }

}