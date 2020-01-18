<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\Role;
use AppBundle\Entity\RoleRule;
use AppBundle\Entity\Rule;
use AppBundle\Entity\User;
use AppBundle\Services\AccessControlList;
use AppBundle\Services\EntitySerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RuleController extends Controller
{
    /**
     * @Route(path="/api/rule/isAllowed", name='api_rule_is_allowed')
     * @Method("GET")
     * @param Request $request
     * @return Response
     */
    public function isAllowedAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $userId = $request->get('user_id');
        $resourceId = $request->get('resource_id');
        $actionId = $request->get('action_id');

        if ($userId == null or $resourceId == null or $actionId == null) {
            return new JsonResponse('An ID is missing!', 404);
        }

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($userId);
        /** @var Action $action */
        $action = $em->getRepository(Action::class)->find($actionId);
        /** @var Resource $resource */
        $resource = $em->getRepository(Resource::class)->find($resourceId);

        if ($user == null or $resource == null or $action == null) {
            return new JsonResponse('Wrong ID!', 404);
        }

        /** @var AccessControlList $acl */
        $acl = $this->get('app.acl');

        $return = [
            'isAllowed' => $acl->isAllowed($action,$resource,$user)
        ];

        return new JsonResponse($return,200);
    }

    /**
     * @Route("/api/rule/newAction", name="api_rules_new")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $em = $this->getDoctrine()->getManager();

        $newRule = new Rule();
        $newRule->setName($data['name']);
        $newRule->setDescription($data['description']);

        $action = $em->getRepository(Action::class)->find($data['action_id']);
        $newRule->setAction($action);
        $resource = $em->getRepository(Resource::class)->find($data['resource_id']);
        $newRule->setResource($resource);

        $em->persist($newRule);
        $em->flush();

        $this->addRolesToRule($data['role_id'], $newRule);

        return new JsonResponse(null, 201);
    }

    /**
     * @Route("/api/rule/deleteAction", name="api_rules_delete")
     * @Method("DELETE")
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $ruleId = $request->get('rule_id');
        if ($ruleId == null) {
            return new Response('Wrong ID', 404);
        }

        $em = $this->getDoctrine()->getManager();

        $ruleToDelete = $em->getRepository(Rule::class)->find($ruleId);

        if ($ruleToDelete == null) {
            return new Response('Wrong ID', 404);
        }

        $em->remove($ruleToDelete);
        $em->flush();

        return new Response(null, 200);
    }

    /** TODO
     * @Route("/api/rule/editAction", name="api_rules_edit")
     * @Method("PUT")
     * @param Request $request
     * @return Response
     */

    public function editAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ($data['rule_id'] == null) {
            return new Response('Wrong/Missing ID', 404);
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Rule $ruleToEdit */
        $ruleToEdit = $em->getRepository(Rule::class)->find($data['rule_id']);

        if ($ruleToEdit == null) {
            return new Response('Wrong/Missing ID', 404);
        }
        // todo tu by sa hodila skorej Form
        $ruleToEdit->setName($data['name']);
        $ruleToEdit->setDescription($data['description']);
        $ruleToEdit->setResource($em->getRepository(Resource::class)->find($data['resource_id']));
        $ruleToEdit->setAction($em->getRepository(Action::class)->find($data['action_id']));

        $em->getRepository(RoleRule::class)->deleteAllWithRule($ruleToEdit->getId());
        $this->addRolesToRule($data['role_id'],$ruleToEdit);

        $em->persist($ruleToEdit);
        $em->flush();

        return new Response(null, 200);
    }

    /**
     * @Route("/api/rule/getAll", name="api_rules_get_all")
     * @Method("GET")
     */
    public function getAll()
    {
        /** @var EntitySerializer $entitySerializer */
        $entitySerializer = $this->get('app.entity_serializer');

        /** @var Rule[] $rules */
        $rules = $this->getDoctrine()->getRepository(Rule::class)->findAll();

        $result = ['rules' => []];
        foreach ($rules as $rule) {
            $result['rules'][] = $entitySerializer->serializeRule($rule);
        }

        return new JsonResponse($result, 200);
    }

    /**
     * @Route("/api/rule/getOne}", name="api_rules_get_one")
     * @Method("GET")
     */
    public function getOne(Request $request)
    {
        /** @var EntitySerializer $entitySerializer */
        $entitySerializer = $this->get('app.entity_serializer');

        $id = $request->get('id');

        if ($id == null) {
            return new Response('ID missing', 404);
        }

        /** @var Rule $rule */
        $rule = $this->getDoctrine()->getRepository(Rule::class)->find($id);

        if ($rule == null) {
            return new Response('Wrong ID', 404);
        }

        $result = $entitySerializer->serializeRule($rule);
        return new JsonResponse($result, 200);
    }

    /**
     * Takes id, or array of ids to be added
     * @param integer|string|array $roleIds
     * @param Rule $rule
     */
    private function addRolesToRule($roleIds, Rule $rule): void
    {
        $em = $this->getDoctrine()->getManager();

        if (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }

        foreach ($roleIds as $roleId) {
                /** @var Role $role */
                $role = $em->getRepository(Role::class)->find($roleId);
                $roleRule = new RoleRule();
                $roleRule->setRole($role);
                $roleRule->setRule($rule);
                $roleRule->setAllowed(true);
                $em->persist($roleRule);
        }
        $em->flush();
    }
}

