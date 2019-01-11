<?php

namespace App\Controller;

use App\Form\AvatarType;
use App\Entity\Episodes;
use App\Entity\Titles;
use App\Form\UserType;
use App\Entity\Watched;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SwiftmailerBundle;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;

class DevController extends AbstractController
{

//    /**
//     * @Route("/mail")
//     */
/*    public function mail(\Swift_Mailer $mailer){
        $message = (new \Swift_Message('Email Verification'))
            ->setFrom(['watchedscom@gmail.com' => 'Symfony-template'])
            ->setTo('szybkimail@o2.pl')
            ->setBody(
                $this->renderView(
                    'emails/verification.html.twig',array(
                        'name'=>'ty',
                        'code'=>'to jest losowy kod 1234'
                    )),
                'text/html'
            );
        $mailer->send($message);
        return new Response(
                'msg has been sent successfully!'
        );
    }*/

    /**
     * @Route("/ajax")
     */
    public function ajax(Request $request){
/*                if($request->query->get('tconst')) {
                    $tconst = $request->query->get('tconst');
                    //make something curious, get some unbelieveable data
                    $repositoryTitles = $this->getDoctrine()->getRepository(Titles::class);
                    $repositoryWatched = $this->getDoctrine()->getRepository(Watched::class);

                    $etc = [];

                    $iswatched = $repositoryWatched->findBy(['episode'=>$tconst , 'userid'=>$this->getUser()->getId()]);
                    if(!$iswatched)
                    {
                        $iswatched = 'n/a';
                    }else{
                        $etc[] =$iswatched[0]->getId();
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->remove($iswatched[0]);
                        $entityManager->flush();
                    }

                    $arrData = [
                        'method' => 'get',
                        'output' => $tconst,
                        'watched?'=> $iswatched,
                        'etc' => $etc,
                    ];
                    return new JsonResponse($arrData);
                }*/
        if($request->request->get('tconst')){
            $tconst = $request->request->get('tconst');
            $repositoryWatched = $this->getDoctrine()->getRepository(Watched::class);

            $etc = [];

            $iswatched = $repositoryWatched->findBy(['episode'=>$tconst , 'userid'=>$this->getUser()->getId()]);
            $entityManager = $this->getDoctrine()->getManager();
            if(!$iswatched)
            {
                $iswatched = 'n/a, so created new one';

                $watched = new Watched();
                $watched->setUserid($this->getUser()->getId());
                $watched->setEpisode($tconst);
                $entityManager->persist($watched);
                $entityManager->flush();

            }else{
                $etc[] =$iswatched[0]->getId();
//                        $etc[] =print_r($iswatched);
                $entityManager->remove($iswatched[0]);
                $entityManager->flush();
            }

            $arrData = [
                'method' => 'get',
                'output' => $tconst,
                'watched?'=> $iswatched,
                'etc' => $etc,
            ];
            return new JsonResponse($arrData);
        }else {
            return new JsonResponse(['error' => 'check php controller']);
        }
    }

}