<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Resource;
use AppBundle\Services\EntitySerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResourceController extends Controller
{

    /**
     * @Route("/api/resource/getAll", name="api_resource_get_all")
     * @Method("GET")
     * @return JsonResponse
     */
    public function getAll()
    {
        /** @var EntitySerializer $entitySerializer */
        $entitySerializer = $this->get('app.entity_serializer');

        /** @var Resource[] $resources */
        $resources = $this->getDoctrine()->getRepository(Resource::class)->findAll();
        $result = [];
        foreach ($resources as $resource){
            $result[] = $entitySerializer->serializeResource($resource);
        }

        return new JsonResponse($result, 200);
    }
}