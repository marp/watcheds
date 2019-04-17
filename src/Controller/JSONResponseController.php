<?php

namespace App\Controller;

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
 * @Route("/JSON")
 */
class JSONResponseController extends AbstractController
{
    /**
     * @Route("/autocompleteUser")
     */
    public function autocompleteUser(RepositoryManagerInterface $finder, Request $request)
    {
        $searchTerm = $request->query->get('s');
        $searchTerm = htmlentities($searchTerm, ENT_QUOTES);
        $matchQuery = new MatchPhrasePrefix();
        $matchQuery->setFieldQuery('username', $searchTerm);
        $finder = $finder->getRepository(\App\Entity\User::class)->find($matchQuery, 7);
        $json = array();
        foreach ($finder as $f) {
            array_push($json,$f->getUsername());
        }
        $json = json_encode($json);
        return new JsonResponse($json);
    }
    /**
     * @Route("/autocompleteSeries")
     */
    public function autocompleteSeries(RepositoryManagerInterface $finder, Request $request)
    {
        $searchTerm = $request->query->get('s');
        $searchTerm = htmlentities($searchTerm, ENT_QUOTES);
        $matchQuery = new MatchPhrasePrefix();
        $matchQuery->setFieldQuery('primarytitle', $searchTerm);
//        $matchQuery->setFieldQuery('primarytitle', $searchTerm);
        $finder = $finder->getRepository(\App\Entity\Titles::class)->find($matchQuery, 7);
        $json = array();
        foreach ($finder as $f) {
            array_push($json,$f->getPrimarytitle());
        }
        $json = json_encode($json);
        return new JsonResponse($json);
    }
    //        return $this->render('dev/output.html.twig',['output'=>$json]);
}