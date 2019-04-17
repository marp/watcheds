<?php

namespace App\Controller;

use App\Form\AddComment;
use App\Form\AddPost;
use App\Form\AvatarType;
use App\Entity\Episodes;
use App\Entity\Titles;
use App\Form\UserType;
use App\Entity\Watched;
use App\Entity\Post;
use App\Entity\Comments;
use App\Entity\User;
use App\Entity\Points;
use App\Repository\PostRepository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SwiftmailerBundle;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Verification;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Route("/mb")
 */
class MicroblogController extends AbstractController
{

    private function microblogEngine(Request $request, $params){

        $return = [];

        $postRepo = $this->getDoctrine()->getRepository(Post::class);
        $commentsRepo = $this->getDoctrine()->getRepository(Comments::class);
        $pointsRepo = $this->getDoctrine()->getRepository(Points::class);
        $entityManager = $this->getDoctrine()->getManager();

        //NEW POSTS
        $addPostForm = $this->createForm(AddPost::class);
        $addPostForm->handleRequest($request);
        if ($addPostForm->isSubmitted() && $addPostForm->isValid()) {
            $formData = $addPostForm->getData();
            $newPost = new Post();
            $newPost->setUser($this->getUser());
            $newPost->setContent($formData['content']);
            $newPost->setDate(new \DateTime('now'));
            $entityManager->persist($newPost);
            $entityManager->flush();
            return $this->redirectToRoute('mb_post_id', ['slug' => $newPost->getId()], 301);
        }

//NEW COMMENT
        $addCommentForm = $this->createForm(AddComment::class);
        $addCommentForm->handleRequest($request);
        if ($addCommentForm->isSubmitted() && $addCommentForm->isValid()) {
            $formData = $addCommentForm->getData();
            $newComment = new Comments();
            $newComment->setContent($formData['content']);
            $newComment->setDate(new \DateTime('now'));
            $newComment->setPost($postRepo->findOneBy(['id' => $formData['postid']]));
            $newComment->setUser($this->getUser());
            $newComment->setVisible(null);
//            var_dump($newComment);
            $entityManager->persist($newComment);
            $entityManager->flush();
            $this->addFlash('success', "Your post has been published.");
        }



//ADD POINT
        if (is_numeric($request->request->get('addPointToPost'))){
            $newPoint = new Points();
            $checkIfPointExistsAlready = $pointsRepo->findOneBy(['post' => $postRepo->findOneBy(['id' => $request->request->get('subtractPointToPost')]),'user'=>$this->getUser()]);
//            if($checkIfPointExistsAlready !== null){
//                $this->addFlash("warning",'You have already voted for this');
//            }else{
                $newPoint->setPost($postRepo->findOneBy(['id' => htmlentities($request->request->get('addPointToPost'))]));
                $newPoint->setUser($this->getUser());
                $entityManager->persist($newPoint);
                $entityManager->flush();
//            }
        }
//SUBTRACT POINT
        if (is_numeric($request->request->get('subtractPointToPost'))) {

            $remove = $pointsRepo->findOneBy(['post' => $postRepo->findOneBy(['id' => $request->request->get('subtractPointToPost')]),'user'=>$this->getUser()]);
            $entityManager->remove($remove);
            $entityManager->flush();
        }

        if($params['type']=="latest"){
            $postsResult = $postRepo->findBy([], ['date' => 'desc']);
        }elseif($params['type']=="onePost"){
            $params['slug'] = htmlspecialchars($params['slug'], ENT_COMPAT | ENT_HTML5);
            $postsResult = $postRepo->findBy(['id'=>$params['slug']]);
        }elseif($params['type']=="tag") {
            $params['slug'] = htmlspecialchars($params['slug'], ENT_COMPAT | ENT_HTML5);
            $postsResult = $postRepo->findByTag(20, $params['slug']);
        }




        $posts = [];
        $comments = [];

        foreach ($postsResult as $post) {

            if ($this->getUser()) {

                $addedPoint = false;
                /* $isPointAdded = $pointsRepo->findOneBy([
                     'post' => $post,
                     'user' => $this->getUser()
                 ]);
                 if ($isPointAdded) {
                     $addedPoint = true;
                 } else {
                     $addedPoint = false;
                 }*/
                if(!empty($post->getPoints())) {
                    foreach ($post->getPoints() as $sub_p) {

                        if ($sub_p->getUser() === $this->getUser()) {
                            $addedPoint = true;
//                           var_dump($addedPoint);
                        } else {
                            $addedPoint = false;
                        }
                    }
                }else{
                    $addedPoint = false;

                    var_dump($addedPoint);
                }


            } else {
                $addedPoint = false;
            }

            $posts[] = [
                'userid' => $post->getUser()->getId(),
                'date' => htmlspecialchars($this->time_elapsed_string($post->getDate()), ENT_COMPAT | ENT_HTML5),
                'content' => htmlspecialchars($post->getContent(), ENT_COMPAT | ENT_HTML5),
                'username' => htmlspecialchars($post->getUser()->getUsername(), ENT_COMPAT | ENT_HTML5),
                'avatar' => htmlspecialchars($post->getUser()->getAvatar(), ENT_COMPAT | ENT_HTML5),
//                'roles' => $usersRepo->findOneBy(['id'=>$post->getUser_id()])->getRoles(),
                'roles' => $post->getUser()->getRoles(),
                'id' => htmlspecialchars($post->getId(), ENT_COMPAT | ENT_HTML5),
                'points' => count($post->getPoints()),
                'addedPoint' => $addedPoint,
//                'comments' => $comments2
                'comments' => $post->getComments()

            ];
        }
        $hotPosts = $postRepo->findByHot24h(10);
//        $hotPosts = $postRepo->findBy(['date'=>'DESC']);
//        $hotPosts = [];
        $tags = [
            'programming',
            'lifestyle',
            'shit',
            'ask',
            'lorem',
            'ipsum',
            'color',
            'sit',
            'damet',
            'etcetera',
            'Suspendisse',
            'ornare',
            'luctus',
            'dolor',
            'sed',
            'finibus',
            'sapien',
            'cursus',
            'eu',
            'programming',
            'lifestyle',
            'shit',
            'ask',
            'lorem',
            'ipsum',
            'color',
            'sit',
            'damet',
            'etcetera',
            'Suspendisse',
            'ornare',
            'luctus',
            'dolor',
            'sed',
            'finibus',
            'sapien',
            'cursus',
            'eu'
        ];



        $return = [
            'posts' => $posts,
            'comments' => $comments,
            'tags' => $tags,
            'hotPosts' => $hotPosts,
            'addCommentsForm' => $addCommentForm->createView(),
            'addPostForm' => $addPostForm->createView(),
            'generateNewPost' => true,
        ];
        if($params['generateNewPost']==true) {
            $return['generateNewPost'] = true;
        }else{
            $return['generateNewPost'] = false;
        }
        return $return;
    }

    /**
     * @Route("/", name="mb_all")
     */
    public function mb(Request $request)
    {
        $params = [
            'type'=>'latest',
            'slug'=>null,
            'generateNewPost'=>true,
        ];
        $returnedFromEngine = $this->microblogEngine($request, $params);

//        var_dump($returnedFromEngine);
        return $this->render('mb/mb.html.twig', $returnedFromEngine);
    }

    /**
     * @Route("/post", name="mb_post_without_slug")
     */
    public function post()
    {
        return $this->redirectToRoute('mb_all');
    }

    /**
     * @Route("/post/{slug}", name="mb_post_id")
     */
    public function mb_post(Request $request, $slug)
    {
        $params = [
            'type'=>'onePost',
            'slug'=>$slug,
            'generateNewPost'=>false,
        ];
        $returnedFromEngine = $this->microblogEngine($request, $params);

//        var_dump($returnedFromEngine);

        return $this->render('mb/mb.html.twig', $returnedFromEngine);
    }

    /**
     * @Route("/tag", name="mb_tag_without_slug")
     */
    public function tag()
    {
        return $this->redirectToRoute('mb_all');
    }

    /**
     * @Route("/tag/{slug}", name="mb_tag")
     */
    public function tag_list(Request $request, $slug)
    {
        $params = [
            'type'=>'tag',
            'slug'=>$slug,
            'generateNewPost'=>true,
        ];
        $returnedFromEngine = $this->microblogEngine($request, $params);
        return $this->render('mb/mb.html.twig', $returnedFromEngine);
    }

    /**
     * @Route("/post/deletecom")
     */
    public function deletecom(Request $request)
    {
        if (($this->isGranted('ROLE_MOD')) || ($this->isGranted('ROLE_ADMIN'))) {
            if (($request->request->get('commentid')) || $request->query->get('commentid')) {
                $commentid = $request->request->get('commentid');
                $commentid = $request->query->get('commentid');
                $commentsRepo = $this->getDoctrine()->getRepository(Comments::class);

                $found = $commentsRepo->findOneBy(['id' => $commentid]);

                if ($found) {
                    $entityManager = $this->getDoctrine()->getManager();
                    if ($found->getVisible() === null) {
                        $found->setVisible(0);
                        $madeinvisibled = 1;
                    } else {
                        $found->setVisible(null);
                        $madeinvisibled = 0;
                    }
                    $entityManager->persist($found);
                    $entityManager->flush();
                } else {
                    return new JsonResponse(['status' => 'failed', 'why' => 'commentid not found in db']);
                }
                return new JsonResponse(['status' => 'success', 'why' => 'removed comment with id ' . $commentid, 'commentid' => $commentid, 'madeinvisible' => $madeinvisibled]);
            } else {
                return new JsonResponse(['status' => 'failed', 'why' => 'commentid in post method no exists']);
            }
        } else {
            return new JsonResponse(['status' => 'failed', 'why' => 'permission']);
        }

    }

    /**
     * @Assert\DateTime()
     */
    public function time_elapsed_string($datetime, $full = false)
    {
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
     * @Route("/mod/")
     */
    public function mod(Request $request)
    {
        $postRepo = $this->getDoctrine()->getRepository(Post::class);
        $commentsRepo = $this->getDoctrine()->getRepository(Comments::class);
        $pointsRepo = $this->getDoctrine()->getRepository(Points::class);
        $entityManager = $this->getDoctrine()->getManager();
        if ($request->query->get('action')) {

            $action = $request->query->get('action');
            $action = htmlentities($action);
            if (is_numeric($action)) {
                switch ($action) {
                    case 1:
                        $ok = ['info' => "Id $action is for permanent removal (comments)"];
                        $post_id = htmlentities($request->query->get('post_id'));
                        if (is_numeric($post_id)) {
                            $obj = $postRepo->findOneBy(['id' => $post_id]);
                            $entityManager->persist($obj);
                            $entityManager->flush();
                        }else{
                            $ok[]=['error'=>"$post_id \$post_id is not numeric!"];
                        }
                        break;
                    case 2:
                        echo "Your favorite color is blue!";
                        break;
                    case 3:
                        echo "Your favorite color is green!";
                        break;
                    case 4:
                        echo "Your favorite color is red!";
                        break;
                    case 5:
                        echo "Your favorite color is blue!";
                        break;
                    case 6:
                        echo "Your favorite color is green!";
                        break;
                    default:
                        $ok = ['error' => "The action with id $action is not recognized."];
                }
            } else {
                $ok = ['error' => "The string $action does not consist of all letters or digits."];
            }

            return new JsonResponse(json_encode($ok));
        } else {
            return new JsonResponse(json_encode(['error' => "The parameter action is missing."]));
        }
    }
}