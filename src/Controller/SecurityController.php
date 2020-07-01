<?php

namespace App\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        // rien ici
    }

    /**
     * @Route("/2fa", name="2fa_login")
     *
     * @param GoogleAuthenticatorInterface $googleAuthenticatorInterface
     * @return void
     */
    public function check2fa(GoogleAuthenticatorInterface $googleAuthenticatorInterface)
    {
        $qrCode = $googleAuthenticatorInterface->getQRContent($this->getUser());
        $url = "http://chart.apis.google.com/chart?cht=qr&chs=150x150&chl=" . $qrCode;
        return $this->render(
            'security/2fa_login.html.twig',
            [
                'url' => $url
            ]
        );
    }
}
