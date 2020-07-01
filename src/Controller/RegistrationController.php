<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Form\Form;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * Renseigner le nom de cette route exécutera cette fonction !
     * @Route("/register", name="app_register")
     */
    public function register(GoogleAuthenticatorInterface $googleAuthenticatorInterface , Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $manager, ValidatorInterface $validator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        // Rajout pour la vérif du mot de passe
        if ($form->isSubmitted() && $form->isValid()) {
            $violations = $validator->validate($form->get('plainPassword')->getNormData(), [
                new NotCompromisedPassword()
            ]);
            if ($user->getCheckPassword() && $violations instanceof ConstraintViolationList && $violations->count()) {
                $password = $form->get('plainPassword');
                if ($password instanceof Form) {
                    $violationMessage = 'Changez de mot de passe, celui-ci a été dévoilé publiquement !';
                    $password->addError(new FormError((string) $violationMessage));
                }
            } else {
                // On remet tout le reste du code ici 
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $user->setGoogleAuthenticatorSecret($googleAuthenticatorInterface->generateSecret());
                //  Petite modif en ajoutant dans la signature de la fonction le EntityManagerInterfce, c'est une injection de dependance
                // $entityManager = $this->getDoctrine()->getManager();
                // $entityManager->persist($user);
                // $entityManager->flush();
                $manager->persist($user);
                $manager->flush();

                // do anything else you need here, like send an email
                // Choisir ici la route vers laquelle on arrive après s'être enregistré. Ici, app_login
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
