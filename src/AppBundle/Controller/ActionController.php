<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Action;
use AppBundle\Services\EntitySerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ActionController extends Controller
{
    /**
     * @Route("/api/action/getAll", name="api_action_get_all")
     * @Method("GET")
     * @return JsonResponse
     */
    public function getAll()
    {
        /** @var EntitySerializer $entitySerializer */
        $entitySerializer = $this->get('app.entity_serializer');

        /** @var Action[] $actions */
        $actions = $this->getDoctrine()->getRepository(Action::class)->findAll();
        $result = ['actions' => []];

        foreach ($actions as $action){
            $result['actions'][] = $entitySerializer->serializeAction($action);
        }

        return new JsonResponse($result, 200);
    }
}