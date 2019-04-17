<?php

namespace App\Controller;


use App\Entity\Verification;
use App\Form\AvatarType;
use App\Form\ChangeVisibilityType;
use App\Form\UserType;
use App\Entity\Passreset;
use App\Entity\User;
use App\Form\ChangeEmailType;

use App\Form\ChangePasswordType;
use App\Form\ResetPasswordCodeType;
use App\Form\ResetPasswordType;
use App\Form\VerifyEmailType;
use App\Repository\VerificationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


use App\Form\ResetType;
use App\Form\EmailResetType;
use App\Form\RegistrationType;
use App\Form\ChangeDescriptionType;


class UserController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     *
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setCreationdatetime(new \DateTime(date("Y-m-d H:i:s")));
            $user->setVisibility(1);
            $user->setVerified(0);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $id = $user->getId();
            $verifyEmail = new Verification();
            $code = md5(uniqid());
            $verifyEmail->setUserid($id);
            $verifyEmail->setCode($code);
            $entityManager->persist($verifyEmail);
            $entityManager->flush();

            $message = (new \Swift_Message($this->getParameter('site_name').' - Email Verification'))
                ->setFrom(['gamemakingfun@gmail.com' => $this->getParameter('site_name')])
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/verification.html.twig',
                        array('name' => $user->getUsername(), 'code' => $code)
                    ),
                    'text/html'
                );
            $mailer->send($message);

            return $this->redirectToRoute('registrationdone');
        }


        return $this->render(
            'security/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/registrationdone", name="registrationdone")
     */
    public function registrationdone(){
        return $this->render('security/register2.html.twig');
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(
                         Request $request,
                         UserPasswordEncoderInterface $encoder
    ){
        $entityManager = $this->getDoctrine()->getManager();

        if($this->getUser()->getVerified() != 1){
            $this->addFlash('warning','You have not verified your e-mail. <a href="/verify">Click here to send another email.</a>');
        }

        //formChangePassword
        $formChangePassword = $this->createForm(ChangePasswordType::class);
        $formChangePassword->handleRequest($request);
        if ($formChangePassword->isSubmitted() && $formChangePassword->isValid()) {
            // $user = new User();
            $user = $this->getUser();
            $userInfo = $formChangePassword->getData();
            //$user = new App\Entity\User();
            $plainPassword = $userInfo['newpassword'];
            $encoded = $encoder->encodePassword($user, $plainPassword);

            $oldpass = $userInfo['oldpassword'];
            $newpass = $userInfo['newpassword'];

            $hash = $user->getPassword();
            $ok=true;
            if (!password_verify($oldpass, $hash)) {
                $this->addFlash('danger', "Wrong old password!");
                $ok = false;
            }

            if($oldpass === $newpass){
                $this->addFlash('danger', "The new password is the same as the old one! Choose a different password.");
                $ok=false;
            }
            if($ok===true){
                $user->setPassword($encoded);
                $entityManager->flush();
                $this->addFlash('success', "Your password has been changed.");
                $this->redirect('/profile#nav-edit');
            }

        }
        //formChangeEmail
        $formChangeEmail = $this->createForm(ChangeEmailType::class);
        $formChangeEmail->handleRequest($request);

        if ($formChangeEmail->isSubmitted() && $formChangeEmail->isValid()) {

            $userInfo = $formChangeEmail->getData();
            $email = $userInfo['email'];

            $user = $this->getUser();
            $user->setEmail($email);
            $entityManager->flush();
            $this->addFlash('success', "Your email has been changed.");
        }
        //form change visibility
        $formVisibility =$this->createForm(ChangeVisibilityType::class);
        $formVisibility->handleRequest($request);
        if ($formVisibility->isSubmitted() && $formVisibility->isValid()) {
            $userInfo = $formVisibility->getData();
            $user = $this->getUser();
            $user->setVisibility($userInfo['visibility']);
            $entityManager->flush();
            $this->addFlash('success', "Visibility setting has been changed!");
        }
        //form upload Avatar
        $user = $this->getUser();
        $formAvatar =$this->createForm(AvatarType::class);
        $formAvatar->handleRequest($request);
        if ($formAvatar->isSubmitted() && $formAvatar->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $formAvatar->getData();
            $file = $file['avatar'];
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            $fileMimeType = $file->getMimeType();
//            check if it is image
            $imageMimeTypes = array(
                'image/png',
                'image/gif',
                'image/jpeg');
            if (in_array($fileMimeType, $imageMimeTypes)) {
                try {
                    $file->move(
                        $this->getParameter('avatars_directory'),
                        $fileName
                    );
                    $fileSystem = new Filesystem();
                    try {
                        $fileSystem->remove($this->getParameter('avatars_directory')."/".$user->getAvatar());
                    } catch (IOExceptionInterface $exception) {
                        echo "An error occurred while removing file! ";
                    }
                } catch (FileException $e) {
                    $this->addFlash('danger', "Error");
                }
                $user->setAvatar($fileName);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', "Your avatar has been successfully changed");
            }else{
                $this->addFlash('danger',"It isn't image. Check file extension.");
            }
        }

        $formDescription = $this->createForm(ChangeDescriptionType::class);
        $formDescription->handleRequest($request);
        if ($formDescription->isSubmitted() && $formDescription->isValid()) {
            $data = $formDescription->getData();
            $user->setDescription($data['description']);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Your description has been successfully changed");
        }

        var_dump($request->request->get('deleteAccount'));
        if($request->request->get('deleteAccount')==true){
//            $user->setRoles([]);
//            $entityManager->persist($user);
//            $entityManager->flush();
            $this->addFlash('success', "Your account has been deleted.");
        }

        return $this->render('user/profile.html.twig',
            array(
                'formChangePassword' => $formChangePassword->createView(),
                'formChangeEmail' => $formChangeEmail->createView(),
                'formVisibility' => $formVisibility->createView(),
                'formAvatar' => $formAvatar->createView(),
                'formDescription' => $formDescription->createView()
            ));

    }

    /**
     * @Route("/profile/{slug}", name="profileUser")
     */
    public function profileUser($slug){
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['username' => $slug]);

        if($this->getUser()->getId()==$user->getId()){
            $owner = true;
        }else{
            $owner = false;
        }

        $userarray = [
            'id'=>$user->getId(),
            'username'=>$user->getUsername(),
            'roles'=>$user->getRoles(),
            'since'=>$user->getCreationdatetime(),
            'visibility'=>$user->getVisibility(),
            'email'=>$user->getEmail(),
            'avatar'=>$user->getAvatar(),
            'owner'=>$owner,
        ];

        return $this->render('user/profile_user.html.twig', array(
            'user' => $userarray
        ));
    }

    /**
     * @Route("/reset", name="resetPassword")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();

        if ($form->isSubmitted() && $form->isValid()) {

            $userInfo = $form->getData();
            $email = $userInfo['email'];

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'No user found for email '.$email);
            }else{


                $id = $user->getId();
                $username = $user->getUsername();

                $passreset = new Passreset();

                $tomorrow = new \DateTime(date("Y-m-d H:i:s", time() + 86400));
                $code = md5(uniqid());

                $passreset->setUserid($id);
                $passreset->setCode($code);
                $passreset->setExpiretime($tomorrow);
                $entityManager->persist($passreset);

                $message = (new \Swift_Message($this->getParameter('site_name').' - Password Reset'))
                    ->setFrom(['gamemakingfun@gmail.com' => $this->getParameter('site_name')])
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                        // templates/emails/registration.html.twig
                            'emails/resetpassword.html.twig',
                            array('name' => $username, 'code' => $code, 'exp' => $tomorrow->format('Y-m-d H:i:s'))
                        ),
                        'text/html'
                    );
                $mailer->send($message);
                $entityManager->flush();

                $this->addFlash('success', "The password reset link was sent, check your email now. If you don't see the email, check the spam folder.");
//                $this->redirect('/profile#nav-edit');
            }
        }
        return $this->render('security/reset.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/reset/{slug}", name="resetPasswordCode")
     */
    public function resetPasswordCode(Request $request, UserPasswordEncoderInterface $encoder, $slug){
        $passreset = $this->getDoctrine()
             ->getRepository(Passreset::class)
             ->findOneByCode($slug);
        if($passreset){
            $exptime = $passreset->getExpiretime();

            $now = new \DateTime();
            $ok=true;
            if($exptime->format('Y-m-d H:i:s') < $now->format('Y-m-d H:i:s'))
            {
              $this->addFlash('danger','You can\'t reset the password because, password reset code expired on '.$exptime->format('Y-m-d H:i:s'));
              $ok=false;
             }
        }
        if($passreset && $ok==true) {

            $form = $this->createForm(ResetPasswordCodeType::class);
            $form->handleRequest($request);
            $entityManager = $this->getDoctrine()->getManager();

            if ($form->isSubmitted() && $form->isValid()) {

                $passInfo = $form->getData();
                $newpass = $passInfo['password'];

                $userid = $passreset->getUserid();
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findOneById($userid);

                $password = $encoder->encodePassword($user, $newpass);
                $user->setPassword($password);
                $entityManager->remove($passreset);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Password has been set! You can log in now! ');
            }
            return $this->render('security/reset.html.twig', array(
                'form' => $form->createView(),
            ));
        }else{
            return $this->render('main/error.html.twig', array(
                'error' => 'We are sorry, but the code is not valid.',
            ));
        }
    }

    /**
     * @Route("/verify", name="verifyEmailResend")
     */
    public function verifyEmailResend(Request $request, \Swift_Mailer $mailer){
        $form = $this->createForm(VerifyEmailType::class);
        $form->handleRequest($request);
        $entityManager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $userInfo = $form->getData();
            $email = $userInfo['email'];

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'email'=>$email]);

            if (!$user) {
                $this->addFlash('danger', 'No user found for email '.$email);
            }elseif($user->getVerified()==1){
                $this->addFlash('warning', 'Email '.$email.' is already verified!');
            }else{
                $id = $user->getId();
                $verifyEmail = new Verification();
                $code = md5(uniqid());
                $verifyEmail->setUserid($id);
                $verifyEmail->setCode($code);
                $entityManager->persist($verifyEmail);
                $entityManager->flush();

                $message = (new \Swift_Message($this->getParameter('site_name').' - Email Verification'))
                    ->setFrom(['gamemakingfun@gmail.com' => $this->getParameter('site_name')])
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/verification.html.twig',
                            array('name' => $user->getUsername(), 'code' => $code)
                        ),
                        'text/html'
                    );
                $mailer->send($message);

                $this->addFlash('success', "The email has been sent. If you don't see the email, check the spam folder.");
            }
        }
        return $this->render('security/verifyEmailResend.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/verify/{slug}", name="verify")
     */
    public function verifyEmail(Request $request, $slug){
        $verifyEmail = $this->getDoctrine()
            ->getRepository(Verification::class)
            ->findOneByCode($slug);
        $ok=true;
        if($verifyEmail && $ok==true) {
                $entityManager = $this->getDoctrine()->getManager();
                $userId = $verifyEmail->getUserid();
                $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findOneById($userId);
                $user->setVerified(1);
                $verifyEmailWithTheSameUID = $this->getDoctrine()
                ->getRepository(Verification::class)
                ->findByUserid($userId);
                $entityManager->remove($verifyEmail);
                foreach ($verifyEmailWithTheSameUID as $emails) {
                    $entityManager->remove($emails);
                }
                $entityManager->persist($user);
                $entityManager->flush();
                $content = '
                            <h1>Email Verification</h1>
                            <p>Your email has been verified successfully!</p>
                            <br>
                            <a href="/login">Login</a>
                            ';
        }else{
            return $this->render('main/error.html.twig', array(
                'error' => 'We are sorry, but the code is not valid!',
            ));
        }

       return $this->render('main/content.html.twig',array(
           'content'=>$content
       ));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
