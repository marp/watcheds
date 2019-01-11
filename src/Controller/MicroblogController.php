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


class MicroblogController extends AbstractController
{
    /**
     * @Route("/mb")
     */
    public function mb(Request $request){
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
            $newPost->setUser_id($this->getUser()->getId());
            $newPost->setContent($formData['content']);
            $newPost->setDate(new \DateTime('now'));
            $newPost->setPoints(0);
            $entityManager->persist($newPost);
            $entityManager->flush();
            return $this->redirectToRoute('mb_post_id',['slug'=>$newPost->getId()],301);
        }

//NEW COMMENT
        $addCommentForm = $this->createForm(AddComment::class);
        $addCommentForm->handleRequest($request);
        if ($addCommentForm->isSubmitted() && $addCommentForm->isValid()) {
            $formData = $addCommentForm->getData();
            $newComment = new Comments();
            $newComment->setContent($formData['content']);
            $newComment->setDate(new \DateTime('now'));
            $newComment->setPostid($formData['postid']);
            $newComment->setUser_id($this->getUser()->getId());
            var_dump($newComment);
            $entityManager->persist($newComment);
            $entityManager->flush();
            $this->addFlash('success', "Your post has been published.");
        }

        $postsResult = $postRepo->findBy([],['date'=>'desc']);
        $commentsResultTemp = [];
        foreach($postsResult as $p){
//            $commentsResult = $commentsRepo->findBy(['post_id'=> $p->getId()]);
        }
        $posts = [];
        $comments = [];

        //POINTS
        if(is_numeric($request->request->get('addPointToPost'))) {
            $newPoint = new Points();
            $newPoint->setPost_Id($request->request->get('addPointToPost'));
            $newPoint->setUserId($this->getUser()->getId());
            $changePoints = $postRepo->findOneBy(['id'=>$request->request->get('addPointToPost')]);
            $changePoints->addOnePoint();
            $entityManager->persist($newPoint);
            $entityManager->persist($changePoints);
            $entityManager->flush();
        }
        if(is_numeric($request->request->get('subtractPointToPost'))){
            $remove = $pointsRepo->findOneBy(['post_Id'=>$request->request->get('subtractPointToPost')]);
            $changePoints = $postRepo->findOneBy(['id'=>$request->request->get('subtractPointToPost')]);
            $changePoints->subtractOnePoint();
            $entityManager->remove($remove);
            $entityManager->persist($changePoints);
            $entityManager->flush();
        }

        foreach ($postsResult as $post) {
            $comments = $commentsRepo->findBy(['postid'=>$post->getId()],['date'=>'asc']);
            if($this->getUser()) {
//                var_dump($post->getId());
                $isPointAdded = $pointsRepo->findOneBy([


                    'post_Id' => $post->getId(),
//                    'post_Id' => 0,

                    'UserId' => $this->getUser()->getId()
//                    'UserId' => 0
                ]);
                if($isPointAdded){
                    $addedPoint = true;
                }else{
                    $addedPoint = false;
                }
            }else{
                $addedPoint = false;
            }
//            if($isPointAdded){
//                $addedPoint = true;
//            }else{
//                $addedPoint = false;
//            }
            $comments2 = [];
            foreach ($comments as $comment) {
                $comments2[] = [
                    'userid' => htmlspecialchars($comment->getUser_id(), ENT_COMPAT | ENT_HTML5),
                    'date' => htmlspecialchars($this->time_elapsed_string($comment->getDate()), ENT_COMPAT | ENT_HTML5),
                    'content' => htmlspecialchars($comment->getContent(), ENT_COMPAT | ENT_HTML5),
//                    'username' => htmlspecialchars($usersRepo->findOneBy(['id'=>$comment->getUserid()])->getUsername(), ENT_COMPAT | ENT_HTML5),
                    var_dump($comment->getUser()->getUsername()),
//                    var_dump($comment->getUser()->getUsername()),
//                    var_dump($comment->getUser()),
//                    var_dump($comment->getUser()),
//                    'username' => htmlspecialchars($comment->getUser()->getUsername(), ENT_COMPAT | ENT_HTML5),
                    'username' => 'zaq123',
                    'avatar' => htmlspecialchars($comment->getUser()->getAvatar(), ENT_COMPAT | ENT_HTML5),
                    'roles' => $comment->getUser()->getRoles(),
                    'postid' => htmlspecialchars($comment->getPostid(), ENT_COMPAT | ENT_HTML5),
                    'visible' => htmlspecialchars($comment->getVisible(), ENT_COMPAT | ENT_HTML5),
                    'id' => htmlspecialchars($comment->getId(), ENT_COMPAT | ENT_HTML5),
                ];
            }
            $posts[] = [
                'userid' => htmlspecialchars($post->getUser_id(), ENT_COMPAT | ENT_HTML5),
                'date' => htmlspecialchars($this->time_elapsed_string($post->getDate()), ENT_COMPAT | ENT_HTML5),
                'content' => htmlspecialchars($post->getContent(), ENT_COMPAT | ENT_HTML5),
                'username' => htmlspecialchars($post->getUser()->getUsername(), ENT_COMPAT | ENT_HTML5),
                'avatar' => htmlspecialchars($post->getUser()->getAvatar(), ENT_COMPAT | ENT_HTML5),
//                'roles' => $usersRepo->findOneBy(['id'=>$post->getUser_id()])->getRoles(),
                'roles' => $post->getUser()->getRoles(),
                'id' => htmlspecialchars($post->getId(), ENT_COMPAT | ENT_HTML5),
                'points' => htmlspecialchars($post->getPoints(), ENT_COMPAT | ENT_HTML5),
                'addedPoint'=> $addedPoint,
                'comments' => $comments2
            ];
        }

        $hotPosts = $postRepo->findByHot24h();



/*            echo "<pre>";
            var_dump($posts);
            var_dump($comments);
            var_dump($commentsResult);
            echo "</pre>";*/

            return $this->render('mb/mb.html.twig', [
                'posts' => $posts,
                'comments' => $comments,
                'hotPosts' => $hotPosts,
                'addPostForm' => $addPostForm->createView(),
                'addCommentsForm' => $addCommentForm->createView(),
            ]);
    }

//    /**
//     * @Route("/mb/post/{slug}", name="mb_post_id")
//     */
//    public function mb_post(Request $request, $slug){
        /*$postsRepo = $this->getDoctrine()->getRepository(Post::class);
        $commentsRepo = $this->getDoctrine()->getRepository(Comments::class);
        $pointsRepo = $this->getDoctrine()->getRepository(Points::class);
        $usersRepo = $this->getDoctrine()->getRepository(User::class);


        $addPostForm = $this->createForm(AddPost::class);
        $addPostForm->handleRequest($request);

        $addCommentForm = $this->createForm(AddComment::class);
        $addCommentForm->handleRequest($request);

        if ($addCommentForm->isSubmitted() && $addCommentForm->isValid()) {
            $formData = $addCommentForm->getData();
            $newComment = new Comments();
            $newComment->setUserid($this->getUser()->getId());
            $newComment->setContent($formData['content']);
            $newComment->setDate(new \DateTime('now'));
            $newComment->setPostid($formData['postid']);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newComment);
            $entityManager->flush();
            $this->addFlash('success', "Your post has been published.");
        }

        $postsResult = $postsRepo->findBy(['id'=>htmlspecialchars($slug, ENT_COMPAT | ENT_HTML5),],['date'=>'desc']);
        $commentsResultTemp = [];
        foreach($postsResult as $p){
            $commentsResult = $commentsRepo->findBy(['postid'=> $p->getId()]);
            $addedPoint = $pointsRepo->findBy(['postId'=>$p->getId()],['userId'=>$this->getUser()->getId()]);
        }
        $posts = [];
        $comments = [];

        foreach ($postsResult as $post) {
            $comments = $commentsRepo->findBy(['postid'=>$post->getId()],['date'=>'asc']);
            $comments2 = [];
            foreach ($comments as $comment) {
                $comments2[] = [
                    'userid' => htmlspecialchars($comment->getUserid(), ENT_COMPAT | ENT_HTML5),
                    'date' => htmlspecialchars($this->time_elapsed_string($comment->getDate()), ENT_COMPAT | ENT_HTML5),
                    'content' => htmlspecialchars($comment->getContent(), ENT_COMPAT | ENT_HTML5),
                    'username' => htmlspecialchars($usersRepo->findOneBy(['id'=>$comment->getUserid()])->getUsername(), ENT_COMPAT | ENT_HTML5),
                    'avatar' => htmlspecialchars($usersRepo->findOneBy(['id'=>$comment->getUserid()])->getAvatar(), ENT_COMPAT | ENT_HTML5),
                    'roles' => $usersRepo->findOneBy(['id'=>$comment->getUserid()])->getRoles(),
                    'postid' => htmlspecialchars($comment->getPostid(), ENT_COMPAT | ENT_HTML5),
                    'visible' => htmlspecialchars($comment->getVisible(), ENT_COMPAT | ENT_HTML5),
                    'id' => htmlspecialchars($comment->getId(), ENT_COMPAT | ENT_HTML5),
                ];
            }
            $posts[] = [
                'userid' => htmlspecialchars($post->getUserid(), ENT_COMPAT | ENT_HTML5),
                'date' => htmlspecialchars($this->time_elapsed_string($post->getDate()), ENT_COMPAT | ENT_HTML5),
                'content' => htmlspecialchars($post->getContent(), ENT_COMPAT | ENT_HTML5),
                'username' => htmlspecialchars($usersRepo->findOneBy(['id'=>$post->getUserid()])->getUsername(), ENT_COMPAT | ENT_HTML5),
                'avatar' => htmlspecialchars($usersRepo->findOneBy(['id'=>$post->getUserid()])->getAvatar(), ENT_COMPAT | ENT_HTML5),
                'roles' => $usersRepo->findOneBy(['id'=>$post->getUserid()])->getRoles(),
                'id' => htmlspecialchars($post->getId(), ENT_COMPAT | ENT_HTML5),
                'points' => htmlspecialchars($post->getPoints(), ENT_COMPAT | ENT_HTML5),
                'comments' => $comments2


            ];
        }
        /*            echo "<pre>";
                    var_dump($posts);
                    var_dump($comments);
                    var_dump($commentsResult);
                    echo "</pre>";*/
        /*
        return $this->render('mb/mb.html.twig', [
            'posts' => $posts,
            'comments' => $comments,
//            'addPostForm' => $addPostForm->createView(),
            'addCommentsForm' => $addCommentForm->createView(),
        ]);*/
//        return new Response('In construction');
//    }

    /**
     * @Route("/mb/post/deletecom")
     */
    public function deletecom(Request $request){
        if(($this->isGranted('ROLE_MOD'))||($this->isGranted('ROLE_ADMIN'))){
            if(($request->request->get('commentid'))||$request->query->get('commentid')){
                $commentid = $request->request->get('commentid');
                $commentid = $request->query->get('commentid');
                $commentsRepo = $this->getDoctrine()->getRepository(Comments::class);

                $found = $commentsRepo->findOneBy(['id'=>$commentid]);

                if($found)
                {
                    $entityManager = $this->getDoctrine()->getManager();
                    if($found->getVisible()===null){
                        $found->setVisible(0);
                        $madeinvisibled = 1;
                    }else{
                        $found->setVisible(null);
                        $madeinvisibled = 0;
                    }
                    $entityManager->persist($found);
                    $entityManager->flush();
                }else{
                    return new JsonResponse(['status'=>'failed','why'=>'commentid not found in db']);
                }
                return new JsonResponse(['status'=>'success','why'=>'removed comment with id '.$commentid, 'commentid'=>$commentid, 'madeinvisible'=>$madeinvisibled]);
            }else {
                return new JsonResponse(['status'=>'failed','why'=>'commentid in post method no exists']);
            }
        }else{
            return new JsonResponse(['status'=>'failed','why'=>'permission']);
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
}