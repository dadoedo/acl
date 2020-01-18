<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Services\EntitySerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    /**
     * @Route("/api/role/getAll", name="api_role_get_all")
     * @Method("GET")
     */
    public function getAll()
    {
        /** @var EntitySerializer $entitySerializer */
        $entitySerializer = $this->get('app.entity_serializer');

        /** @var Role[] $roles */
        $roles = $this->getDoctrine()->getRepository(Role::class)->findAll();

        $result = ['roles' => []];
        foreach ($roles as $role){
            $result['roles'][] = $entitySerializer->serializeRole($role);
        }

        return new JsonResponse($result, 200);
    }
}