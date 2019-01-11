<?php

namespace App\Controller;

use App\Form\AdminVerificationEmail;
use App\Form\UserEditType;
use App\Entity\User;
use App\Entity\Verification;

use App\Form\VerifyEmailType;
use App\Repository\VerificationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function admin()
    {
        return $this->render('admin/adminhomepage.html.twig');
    }
    /**
     * @Route("/users", name="adminUsers")
     */
    public function adminUsers(Request $request){
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        return $this->render('admin/users.html.twig', array(
            'users' => $users,
        ));
    }
    /**
     * @Route("/users/{slug}", name="adminUserID")
     */
    public function adminUserID($slug, Request $request, UserPasswordEncoderInterface $passwordEncoder){
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy(['id' => $slug]);
        if(!$user){
            $this->addFlash('danger', 'User not found');
            return $this->redirectToRoute('adminUsers');
        }

        //delete
        $delete = $request->request->get('delete');
        if($delete=='delete'){
            $this->addFlash('danger', 'User '.$user->getUsername().' has been removed.');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->redirectToRoute('adminUsers');
        }

        //edit
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
//            $user->setCreationdatetime(new \DateTime(date("Y-m-d H:i:s")));
//            $user->setVisibility(1);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }


        //VerificationEmail
        $verifyForm = $this->createForm(AdminVerificationEmail::class);
        $verifyForm->handleRequest($request);
        if ($verifyForm->isSubmitted() && $verifyForm->isValid()) {
            $verifyFormData = $verifyForm->getData();
            $this->addFlash('success', "Option has been changed.");
            $user->setVerified($verifyFormData['verification']);
            $verifyEmailWithTheSameUID = $this->getDoctrine()
                ->getRepository(Verification::class)
                ->findByUserid($user->getId());
             foreach ($verifyEmailWithTheSameUID as $emails) {
                 $entityManager->remove($emails);
             };
            $entityManager->persist($user);
             $entityManager->flush();
        }

            return $this->render('admin/user_id.html.twig', array(
                'user' => $user,
                'edit' => $form->createView(),
                'verify' => $verifyForm->createView(),
            ));

    }
}