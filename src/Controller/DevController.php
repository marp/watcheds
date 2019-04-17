<?php

namespace App\Controller;

use App\Form\AvatarType;
use App\Entity\Episodes;
use App\Entity\Titles;
use App\Form\UserType;
use App\Entity\Watched;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Elastica\Processor\Json;
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
//use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Elastica\Type;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Elastica\Query;
use Elastica\Suggest;
use Elastica\Search;
use Elastica\Query\MatchPhrasePrefix;

use FOS\ElasticaBundle\Doctrine\RepositoryManager;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

//use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/dev")
 */
class DevController extends AbstractController
{
    /**
     * @Route("/search")
     */
    public function searchElastic(RepositoryManagerInterface $finder, Request $request)
    {
        $searchTerm = $request->query->get('s');
        $searchTerm = htmlentities($searchTerm, ENT_QUOTES);

        $matchQuery = new MatchPhrasePrefix();
        $matchQuery->setFieldQuery('username', $searchTerm);
//        $finder = $finder->getRepository(\App\Entity\User::class)->find($searchTerm);
        $finder = $finder->getRepository(\App\Entity\User::class)->find($matchQuery, 7);

        $json = array();

        foreach ($finder as $f) {
            array_push($json,$f->getUsername());
        }
/*        $json = [];
        foreach ($finder as $f) {
            $json[] = [
                'id' => $f->getId(),
                'username' => $f->getUsername(),
            ];
        }*/
        $json = json_encode($json);
//        $json = json_decode($json);

        return new JsonResponse($json);
    }


    /**
     * @Route("/ajaxowy")
     */
    function ajaxowy(Request $request)
    {
        return $this->render('dev/output.html.twig', [
            'output' => 'abc'
        ]);
    }

//    /**
//     * @Route("/search2")
//     */
//    function searchElastic2(PaginatedFinderInterface $finder, Request $request)
//    {
////        /** var FOS\ElasticaBundle\Manager\RepositoryManagerInterface */
//        /** var FOS\ElasticaBundle\Finder\PaginatedFinderInterface */
//        $repositoryManager = $finder;
//        /** var FOS\ElasticaBundle\Repository */
//        $repository = $repositoryManager->getRepository(\App\Entity\User::class);
//
//        /** var array of Acme\UserBundle\Entity\User */
//        $users = $repository->find('bob');
//
//        return new JsonResponse($users);
//    }

    /**
     * @Route("/chat", name="chat")
     */
    public
    function chat(Request $request)
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
}