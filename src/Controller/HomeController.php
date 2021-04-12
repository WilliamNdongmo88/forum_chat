<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/index", name="home", methods={"GET"})
     */
    public function index(Request $request)
    {
        $session = $request->getSession();
        if (!$session->has('name'))
        {
            $this->get('session')->getFlashBag()->add('info', 'Erreur de  Connection veuillez vous connectez !');
            return $this->redirectToRoute('connexion');
        }else{
            $name = $session->get('name');
            $use = ["use1","use2","use3","use4","use5","use6"];
            return $this->render('home/index.html.twig', [
                "uses"=> $use,
                "name"=>$name
            ]);
        }



    }

    /**
     * @Route("/inscription", name="inscription", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $session = $request->getSession();
        $name = $session->get('name');
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('connexion');
        }

        return $this->render('home/inscription.html.twig', [
            'user' => $user,'name'=>$name,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="connexion", methods={"GET","POST"})
     */
    public function connexion(Request $request,UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        $session = $request->getSession();
        $session->clear();
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('userEmail', TextType::class,[
                'attr' => [
                    'placeholder' => 'Taper votre email',
                ],

            ])
            ->add('userPassword', PasswordType::class,[
                'attr' => [
                    'placeholder' => 'Taper votre mot de passe',
                ],

            ])

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $userPassword   = $user->getUserPassword();
            $userEmail = $user->getUserEmail();
            $user1 = $userRepository->findOneBy(array('userEmail'=>$userEmail,
                'userPassword'=>$userPassword));
            if (!$user1)
            {
                $this->get('session')->getFlashBag()->add('info',
                    'Email ou mot de passe Incorrecte Vérifier les svp !');
            }
            else
            {
                if (!$session->has('name'))
                {
                    $session->set('name',$user1->getUserName());
                    $name = $session->get('name');

                    return $this->render('home/index.html.twig', [
                        'name'=>$name
                    ]);
                }
            }
        }

        return $this->render('home/connexion.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
