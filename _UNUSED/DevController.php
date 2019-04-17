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
use FOS\ElasticaBundle\FOSElasticaBundle;
use Elastica\QueryBuilder;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

use Elastica\Query;
use Elastica\Suggest;

use FOS\ElasticaBundle\Doctrine\RepositoryManager;



/**
 * @Route("/dev")
 */
class DevController extends AbstractController
{
    /**
     * @Route("/search")
     */
    public function searchElastic(RepositoryManagerInterface $finder, Request $request){
        $searchTerm = $request->query->get('s');
        $searchTerm = htmlentities($searchTerm, ENT_QUOTES);

        $finder = $finder->getRepository(\App\Entity\User::class)->find($searchTerm);
        return new response(var_dump($finder[0]->getUsername()));

//        $completion = new Suggest\Completion('suggest', 'name_suggest');
//        $completion->setText($searchTerm);
//        $completion->setFuzzy(array('fuzziness' => 2));
//        /** var array of App\Entity\User */
//        $resultSet = $finder->getRepository(\App\Entity\User::class)->search((Query::create($completion)));
//        var_dump($resultSet);
//        $suggestions = array();
//        foreach ($resultSet->getSuggests() as $suggests) {
//            foreach ($suggests as $suggest) {
//                foreach ($suggest['options'] as $option) {
//                    $suggestions[] = array(
//                        'id' => $option['_source']['id'],
//                        'username' => $option['_source']['username']
//                    );
//                }
//            }
//        }
//        return new JsonResponse(array(
//            'suggestions' => $suggestions,
//        ));
    }

    /**
     * @Route("/search2")
     */
    function searchElastic2(PaginatedFinderInterface $finder, Request $request)
    {
//        /** var FOS\ElasticaBundle\Manager\RepositoryManagerInterface */
        /** var FOS\ElasticaBundle\Finder\PaginatedFinderInterface */
        $repositoryManager = $finder;
        /** var FOS\ElasticaBundle\Repository */
        $repository = $repositoryManager->getRepository(\App\Entity\User::class);

        /** var array of Acme\UserBundle\Entity\User */
        $users = $repository->find('bob');

        return new JsonResponse($users);
    }

    /**
     * @Route("/chat", name="chat")
     */
    public function chat(Request $request)
    {
        return $this->render('dev/chat.html.twig');
    }

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