<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Auth\RegisterForm;
use App\Helpers\EventTrait;
use App\Helpers\Message;
use App\Repository\UserRepository;
use Damian972\AuthBundle\Entity\PasswordResetConfirm;
use Damian972\AuthBundle\Entity\PasswordResetRequest;
use Damian972\AuthBundle\Entity\ResetToken;
use Damian972\AuthBundle\Event\AccountConfirmedEvent;
use Damian972\AuthBundle\Event\RegisteredUserEvent;
use Damian972\AuthBundle\Exception\OngoingPasswordResetException;
use Damian972\AuthBundle\Exception\UserNotFoundException;
use Damian972\AuthBundle\Form\PasswordResetConfirmForm;
use Damian972\AuthBundle\Form\PasswordResetRequestForm;
use Damian972\AuthBundle\Helpers\TokenGenerator;
use Damian972\AuthBundle\Service\PasswordReset;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/auth")
 */
class AuthController extends AbstractController
{
    use EventTrait;

    /**
     * @var array
     */
    private $events = [
        'register' => RegisteredUserEvent::class,
        'confirm_account' => AccountConfirmedEvent::class,
    ];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/register", methods={"GET", "POST"}, name="auth.register")
     */
    public function register(Request $request, AuthorizationCheckerInterface $authChecker, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        if (true === $authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword($user, $form->get('password')->getData())
            );
            $user->setIsActive(false)
                ->setToken(TokenGenerator::generate(TokenGenerator::REGISTER_TYPE))
            ;

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', Message::AUTH_SUCCESS_ACTIVATION_MAIL_SENT);

            $this->dispatchEvent('register', $user);

            return $this->redirectToRoute('auth.login');
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", methods={"GET", "POST"}, name="auth.login")
     */
    public function login(AuthorizationCheckerInterface $authChecker, AuthenticationUtils $authUtils): Response
    {
        if (true === $authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $error = $authUtils->getLastAuthenticationError();
        if ($error) {
            if ($error instanceof BadCredentialsException) {
                $this->addFlash('danger', Message::AUTH_ERROR_BAD_CREDENTIALS);
            } elseif ($error instanceof InvalidCsrfTokenException) {
                $this->addFlash('danger', Message::AUTH_ERROR_INVALID_TOKEN);
            } else {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->render('auth/login.html.twig');
    }

    /**
     * @Route("/confirm-account", methods={"GET"}, name="auth.confirm_account")
     */
    public function confirmAccount(Request $request, AuthorizationCheckerInterface $authChecker, UserRepository $repository): Response
    {
        if (true === $authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $token = $request->query->get('token');
        if (!$token || false === TokenGenerator::validate($token, TokenGenerator::REGISTER_TYPE)) {
            $this->addFlash('danger', Message::AUTH_ERROR_INVALID_TOKEN);

            return $this->redirectToRoute('auth.login');
        }

        $user = $repository->findOneBy(compact('token'));
        if ($user instanceof User) {
            $entityManager = $this->getDoctrine()->getManager();

            $user->setIsActive(true)
                ->setRoles(['ROLE_USER'])
                ->setToken(null)
            ;

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', Message::AUTH_SUCCESS_ACCOUNT_COMFIRMED);

            $this->dispatchEvent('confirm_account', $user);

            return $this->redirectToRoute('auth.login');
        }
        $this->addFlash('danger', Message::AUTH_ERROR_INVALID_TOKEN);

        return $this->redirectToRoute('auth.login');
    }

    /**
     * @Route("/password-reset", methods={"GET", "POST"}, name="auth.reset_password")
     */
    public function resetPassword(Request $request, AuthorizationCheckerInterface $authChecker, PasswordReset $passwordReset): Response
    {
        if (true === $authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        $data = new PasswordResetRequest();
        $form = $this->createForm(PasswordResetRequestForm::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $passwordReset->resetPassword($data);
                $this->addFlash('success', Message::AUTH_SUCCESS_PASSWORD_RESET_REQUEST_SENT);

                return $this->redirectToRoute('auth.login');
            } catch (Exception $e) {
                if (!$e instanceof OngoingPasswordResetException || !$e instanceof UserNotFoundException) {
                    $error = Message::ERROR_OCCURED;
                } else {
                    $error = $e->getMessage();
                }
            }
        }

        return $this->render('auth/reset-password.html.twig', [
            'error' => $error ?? null,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/password-reset/confirm/{token}", methods={"GET", "POST"}, name="auth.password_reset.comfirm")
     */
    public function resetPasswordConfirm(Request $request, AuthorizationCheckerInterface $authChecker, PasswordReset $passwordReset, ?ResetToken $token): Response
    {
        if (true === $authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('homepage');
        }

        if (!$token instanceof ResetToken || $passwordReset->isExpired($token) || !$token->getUser() instanceof User) {
            $this->addFlash('danger', Message::AUTH_ERROR_INVALID_TOKEN);

            return $this->redirectToRoute('auth.login');
        }

        $data = new PasswordResetConfirm();
        $form = $this->createForm(PasswordResetConfirmForm::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordReset->updatePassword($data->getPassword(), $token);
            $this->addFlash('success', Message::AUTH_SUCCESS_PASSWORD_RESET_COMFIRMED);

            return $this->redirectToRoute('auth.login');
        }

        return $this->render('auth/reset-password-confirm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", methods={"GET"}, name="auth.logout")
     */
    public function logout(): Response
    {
        throw new Exception('This should never be reached');
    }
}
