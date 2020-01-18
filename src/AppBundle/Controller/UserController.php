<?php


namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Services\EntitySerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * @Route("/api/user/getAll", name="api_user_get_all")
     * @Method("GET")
     */
    public function getAll()
    {
        /** @var EntitySerializer $entitySerializer */
        $entitySerializer = $this->get('app.entity_serializer');

        /** @var User[] $users */
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $result = ['users' => []];
        foreach ($users as $user){
            $result['users'][] = $entitySerializer->serializeUser($user);
        }

        return new JsonResponse($result, 200);
    }
}