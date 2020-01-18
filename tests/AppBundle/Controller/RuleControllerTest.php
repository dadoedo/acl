<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\RulesFixture;
use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\Role;
use AppBundle\Entity\Rule;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Tests\AppBundle\FixtureAwareTestCase;


class RuleControllerTest extends FixtureAwareTestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    private function mySetUp()
    {
        $this->addFixture(new RulesFixture());
        $this->executeFixtures();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }


    /**
     * @test OneRuleForOneRole
     * @info Creates rule for one resource and role - tests user with that role and one without
     * @throws ORMException
     */
    public function testOneRuleForOneRole()
    {
        $this->mySetUp();
        $em = $this->entityManager;

        /** @var User $userJohn */ $userJohn = $em->getRepository('AppBundle:User')->findOneBy(["name" => "John", "surname"=>"Doe"]);
        /** @var User $userJames */ $userJames = $em->getRepository('AppBundle:User')->findOneBy(["name" => "James", "surname"=>"Dunn"]);
        /** @var Role $roleEditor */ $roleEditor = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Editor"]);
        /** @var Resource $resourceArticle */ $resourceArticle = $em->getRepository('AppBundle:Resource')->findOneBy(["name" => "Article"]);
        /** @var Action $actionEdit */ $actionEdit = $em->getRepository('AppBundle:Action')->findOneBy(["name" => "Edit"]);

        $userJames->addRole($roleEditor);
        $em->persist($userJames);

        $newRule = new Rule();
        $newRule->setName('EditArticle');
        $newRule->setDescription('...');
        $newRule->setResource($resourceArticle);
        $newRule->setAction($actionEdit);
        $newRule->addRole($roleEditor);
        $em->persist($newRule);
        $em->flush();
        $client = static::createClient();

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJames->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
            );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "User should be allowed");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJohn->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertFalse($content['isAllowed'], "User should not be allowed");

    }

    /**
     * @test testRuleAfterEdit
     * @info Changes rule available for 2 roles down to 1 role, confirms for both roles
     * @throws ORMException
     */
    public function testRuleAfterEdit()
    {

        $this->mySetUp();
        $em = $this->entityManager;

        /** @var User $userJohn */ $userJohn = $em->getRepository('AppBundle:User')->findOneBy(["name" => "John", "surname"=>"Doe"]);
        /** @var User $userJames */ $userJames = $em->getRepository('AppBundle:User')->findOneBy(["name" => "James", "surname"=>"Dunn"]);
        /** @var Role $roleEditor */ $roleEditor = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Editor"]);
        /** @var Role $roleViewer*/ $roleViewer = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Viewer"]);
        /** @var Resource $resourceArticle */ $resourceArticle = $em->getRepository('AppBundle:Resource')->findOneBy(["name" => "Article"]);
        /** @var Action $actionEdit */ $actionEdit = $em->getRepository('AppBundle:Action')->findOneBy(["name" => "Edit"]);

//        $userJames->addRole($roleEditor);
//        $userJames->addRole($roleViewer);
//        $userJohn->addRole($roleViewer);
//
//        $em->persist($userJames);
//        $em->persist($userJohn);
//
//        $newRule = new Rule();
//        $newRule->setName('EditArticle');
//        $newRule->setDescription('...');
//        $newRule->setResource($resourceArticle);
//        $newRule->setAction($actionEdit);
//        $newRule->addRole($roleEditor);
//        $newRule->addRole($roleViewer);
//        $em->persist($newRule);
//        $em->flush();
        $client = static::createClient();

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJames->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "User should be allowed");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJohn->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "User should be allowed");

        $data = [
            'rule_id' => $newRule->getId(),
            'name' => 'EditArticleEditorOnly',
            'role_id' => $roleEditor->getId(),
        ];

        $client->request('PUT',
            '/api/rule/editAction',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json',
            ),
            json_encode($data),

        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code (Rule Edit)");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJames->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "User should be allowed");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJohn->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertFalse($content['isAllowed'], "Viewer should not be allowed");

    }

    /**
     * @test testRuleCreate
     * @info Tests rule API create new action
     * @throws ORMException
     */
    public function testRuleCreate()
    {

        $this->mySetUp();
        $em = $this->entityManager;
        $client = static::createClient();

        /** @var User $userJames */ $userJames = $em->getRepository('AppBundle:User')->findOneBy(["name" => "James", "surname"=>"Dunn"]);
        /** @var Role $roleViewer*/ $roleViewer = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Viewer"]);
        /** @var Role $roleEditor*/ $roleEditor = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Editor"]);
        /** @var Resource $resourceArticle */ $resourceArticle = $em->getRepository('AppBundle:Resource')->findOneBy(["name" => "Article"]);
        /** @var Action $actionEdit */ $actionEdit = $em->getRepository('AppBundle:Action')->findOneBy(["name" => "Edit"]);

//        $userJames->addRole($roleViewer);
//        $em->persist($userJames);
//        $em->flush();
        $data = [
            'name' => 'EditArticleRule',
            'description' => 'some rule',
            'resource_id' => $resourceArticle->getId(),
            'action_id' => $actionEdit->getId(),
            'role_id' => [$roleViewer->getId() , $roleEditor->getId()],
        ];

        $client->request('POST',
            '/api/rule/newAction',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data));

        $this->assertEquals(201, $client->getResponse()->getStatusCode(), "Wrong response code");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJames->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "User should be allowed");
    }

    /**
     * @test testRuleDeletion
     * @info Tests rule API deletion
     * @throws ORMException
     */
    public function testRuleDeletion()
    {
        $this->mySetUp();
        $em = $this->entityManager;

        /** @var User $userJohn */ $userJohn = $em->getRepository('AppBundle:User')->findOneBy(["name" => "John", "surname"=>"Doe"]);
        /** @var User $userJames */ $userJames = $em->getRepository('AppBundle:User')->findOneBy(["name" => "James", "surname"=>"Dunn"]);
        /** @var Role $roleEditor */ $roleEditor = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Editor"]);
        /** @var Resource $resourceArticle */ $resourceArticle = $em->getRepository('AppBundle:Resource')->findOneBy(["name" => "Article"]);
        /** @var Action $actionEdit */ $actionEdit = $em->getRepository('AppBundle:Action')->findOneBy(["name" => "Edit"]);

        $userJames->addRole($roleEditor);
        $em->persist($userJames);

        $newRule = new Rule();
        $newRule->setName('EditArticle');
        $newRule->setDescription('...');
        $newRule->setResource($resourceArticle);
        $newRule->setAction($actionEdit);
        $newRule->addRole($roleEditor);
        $em->persist($newRule);
        $em->flush();
        $client = static::createClient();

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJames->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "User should be allowed");


        $client->request('DELETE',
            '/api/rule/deleteAction?rule_id=' . $newRule->getId(),
            );

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJohn->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertFalse($content['isAllowed'], "User should not be allowed action after rule deletion");
    }
}

