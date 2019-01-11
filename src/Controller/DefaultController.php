<?php

namespace App\Controller;

use App\Entity\Titles;
use App\Entity\Season;
use App\Entity\User;

use App\Repository\SeasonRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints as Assert;
//use App\Repository\UserRepository;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('main/homepage.html.twig');
    }

    /**
     * @Route("/search", name="searchget", methods={"GET"})
     */
    public function searchget(Request $request)
    {
        $get = $request->query->get('s');
        if(!$get){
            return $this->redirect("/");
        }else {
            return $this->redirect("/search/$get/1");
        }
    }
//     * @Route("/search/{page}", name="search")
    /**
     * @Route("/search/{slug}/{page}", defaults={"_format"="html"}, methods={"GET"}, name="searchPagin")
     */
    public function search(Request $request, $slug, $page)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $repositoryTitles = $this->getDoctrine()->getRepository(Titles::class);

        $limit = $request->query->get('l', 8);
        $sort = $request->query->get('s', 'DESC');
        $sortBy = $request->query->get('b', 'date');
        $search_in = $request->query->get('in', 'all');
        if (($search_in == "all") || ($search_in == "users")) {
            $users = $repository->findBySearchQuery($slug, $limit, $sort, $sortBy);
            $results = [];
            foreach ($users as $post) {
                $results[] = [
                    'username' => htmlspecialchars($post->getUsername(), ENT_COMPAT | ENT_HTML5),
                    'creationdatetime' => htmlspecialchars($this->time_elapsed_string($post->getCreationdatetime()), ENT_COMPAT | ENT_HTML5),
                    'avatar' => htmlspecialchars($post->getAvatar(), ENT_COMPAT | ENT_HTML5),
                ];
            }
            $users = $results;
        }

//        $titles = $repositoryTitles->findBySearchQuery($slug, $limit, $sort, $sortBy, $page);
//        $titles = $repositoryTitles->findLatest($page);
        if(($search_in == "all") || ($search_in == "titles")){
        if ($page == 1) {
//        THE BEST RESULTS
            $best = $repositoryTitles->findBy(['originaltitle' => $slug]);
            $best_tconst = [];
            $resultsTitles = [];
            foreach ($best as $tx) {
                $best_tconst[] = htmlspecialchars($tx->getTconst(), ENT_COMPAT | ENT_HTML5);
                $resultsTitles[] = [
                    'tconst' => htmlspecialchars($tx->getTconst(), ENT_COMPAT | ENT_HTML5),
                    'title' => htmlspecialchars($tx->getPrimarytitle(), ENT_COMPAT | ENT_HTML5),
                    'startYear' => $tx->getStartYear(),
                    'endYear' => $tx->getEndYear(),
                ];
            }
//         END
        } else {
            $best_tconst = [0, 1];
        }
        $titles = $repositoryTitles->findBySearchQueryPagin2($slug, $limit, $sort, $sortBy, $page, $best_tconst);
//        $titlesPagin = $repositoryTitles->findLatest($page);
//        $titlesPagin = $repositoryTitles->findBySearchQueryPagin2($slug, $limit, $sort, $sortBy, $page);
        $titlesPagin = $titles;

        foreach ($titles as $t) {
            $resultsTitles[] = [
                'tconst' => htmlspecialchars($t->getTconst(), ENT_COMPAT | ENT_HTML5),
                'title' => htmlspecialchars($t->getPrimarytitle(), ENT_COMPAT | ENT_HTML5),
                'startYear' => $t->getStartYear(),
                'endYear' => $t->getEndYear(),
            ];
        }
    }

        if((!$slug)||($slug=="")){
            return $this->redirect("/");
        }else {
            if(($search_in == "titles")) {
                return $this->render("main/search.html.twig", array(
                    'slug' => $slug,
                    'titles' => $resultsTitles,
                    'titlesPagin' => $titlesPagin,
                    'search_in' => $search_in,
                ));
            }elseif(($search_in == "users")) {
                return $this->render("main/search.html.twig", array(
                    'slug' => $slug,
                    'users' => $users,
                    'search_in' => $search_in,
                ));
            }else{
                return $this->render("main/search.html.twig", array(
                    'slug' => $slug,
                    'titles' => $resultsTitles,
                    'titlesPagin' => $titlesPagin,
                    'users' => $users,
                    'search_in' => $search_in,
                ));
            }
        }
    }
    /**
     * @Assert\DateTime()
     */
    public function time_elapsed_string($datetime, $full = false) {
        $now = new \DateTime('now');
        $ago = new \DateTime($datetime->format('Y-m-d H:i:s'));
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
    /**
     * @Route("/about", name="about")
     */
    public function about(){
        return $this->render('main/about.html.twig');
    }
    /**
     * @Route("/faq", name="faq")
     */
    public function faq(){
        return $this->render('main/faq.html.twig');
    }

}