<?php

namespace App\Controller;

use App\Entity\Episodes;
use App\Entity\Titles;
use App\Form\UserType;
use App\Entity\Watched;
use App\Form\WatchedType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints as Assert;
//use App\Repository\UserRepository;



use App\Entity\Verification;
use App\Form\AvatarType;
use App\Form\ChangeVisibilityType;
use App\Entity\Passreset;
use App\Entity\User;
use App\Form\ChangeEmailType;

use App\Form\ChangePasswordType;
use App\Form\ResetPasswordCodeType;
use App\Form\ResetPasswordType;
use App\Form\VerifyEmailType;
use App\Repository\VerificationRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


/**
 * @Route("/s")
 */
class SeriesController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->redirectToRoute("index");
    }




    /**
     * @Route("/{slug}", name="list")
     */
    public function list(Request $request, $slug)
    {
        if($this->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            $isLogged = true;
//        }elseif($this->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')){
            }else{
            $isLogged = false;
            $this->addFlash('warning','You are not logged in. You can browse library, but if you want mark episodes you have to <a href="/login">log in.</a>');

        }


        $repository = $this->getDoctrine()->getRepository(Titles::class);
        $series = $repository->findOneBy(['tconst'=>$slug]);
//        ORDER BY seasonNumber, episodeNumber ASC;

        if($series){
//            $omdb->setParams( ['tomatoes' => TRUE, 'plot' => 'full', 'apikey' => '9fed90de'] );
//            var_dump($omdb);
//            $movie = $omdb->get_by_title( 'Pulp Fiction' );
//            $json = file_get_contents('https://www.omdbapi.com/?i=tt3896198&apikey=9fed90de');
//            $seriesjson = json_decode($json, true);


            $repositoryEpisodes = $this->getDoctrine()->getRepository(Episodes::class);
            $episodes = $repositoryEpisodes->findSort($slug);
            $watchedEpisodesRepositry = $this->getDoctrine()->getRepository(Watched::class);
            $tconsts = [];
            $resultsEpisodes = [];
            $num = 1;
            foreach ($episodes as $e){
                $tconsts[] = $e->getTconst();
            }
            $tconstsWatched = [];
            if($isLogged===true) {
                $watchedEpisodes = $watchedEpisodesRepositry->findBy(['episode' => $tconsts, 'userid' => $this->getUser()->getId()]);


                foreach ($watchedEpisodes as $we) {
//                if(in_array($we->getEpisode(), $tconsts)){
//                    $this->addFlash('success',"to juz ogladales!");
//                    if($episodes['tconst'] == $we->getEpisode())
//                }
                    $tconstsWatched[] = $we->getEpisode();
                }
            }

            $form = $this->createForm(WatchedType::class);
            $form->handleRequest($request);
            if ($request->request->get('submit')) {
                $entityManager = $this->getDoctrine()->getManager();
                    if(in_array($request->request->get('submit'), $tconstsWatched)){
                        $tounwatched =$watchedEpisodesRepositry->findBy(['episode'=> $request->request->get('submit')]);
                        foreach ($tounwatched as $tmp){
                            $entityManager->remove($tmp);
                        }
                        $entityManager->flush();
                        $pos = array_search($request->request->get('submit') , $tconstsWatched);
                        unset($tconstsWatched[$pos]);
                    }else{
                        $watched = new Watched();
                        $userid = $this->getUser()->getId();
                        $watched->setUserid($userid);
                        $key = $request->request->get('submit');
                        $watched->setEpisode($key);
                        $entityManager->persist($watched);
                        $entityManager->flush();
                        array_push($tconstsWatched, $request->request->get('submit'));
                    }
            }

            foreach ($episodes as $e) {
                if(in_array($e->getTconst(), $tconstsWatched)){
//                    $this->addFlash('success',"to juz ogladales!");
//                    if($episodes['tconst'] == $we->getEpisode())
                    $iswatched = '✔';
                }else{
                    $iswatched = '✘';
                }
                $resultsEpisodes[] = [
                    'season' => htmlspecialchars($e->getSeasonNumber(), ENT_COMPAT | ENT_HTML5),
                    'episodeNumber' => htmlspecialchars($e->getEpisodeNumber(), ENT_COMPAT | ENT_HTML5),
                    'tconst' => htmlspecialchars($e->getTconst(), ENT_COMPAT | ENT_HTML5),
                    'number' => $num++,
                    'iswatched' => $iswatched,
                ];
            }
            $episodes = $resultsEpisodes;



            $seriesName = $series->getPrimarytitle();
            $seriesStartYear = $series->getStartYear();
            $seriesEndYear = $series->getEndYear();
            $seriesGenres = $series->getGenres();
            $tconst = $series->getTconst();




            return $this->render('series/list.html.twig',array(
                'episodes' => $episodes,
                'series' => [
                    'name' => $seriesName,
                    'startYear' => $seriesStartYear,
                    'endYear' => $seriesEndYear,
                    'genres' => $seriesGenres,
                    'tconst' => $tconst,
                ],
                'number' => $num,
                'form' => $form->createView(),
                'isLogged' => $isLogged,

            ));

        }else{
           return $this->redirect("/");
        }

    }


}