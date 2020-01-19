<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\RulesFixture;
use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\Role;
use AppBundle\Entity\RoleRule;
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
        // Resources  = [Image, Article]
        // Actions    = [View, Edit]
        //
        //  User |   Role
        // ---------------
        // James |  Editor
        //  John |  Viewer
        //
        // Rule = (Editor, Edit, Article, allowed)
        $this->addFixture(new RulesFixture());
        $this->executeFixtures();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }


    /**
     * @test OneRuleForOneRole
     * @info Tests rule application on allowed user and not allowed one
     */
    public function testIsAllowed()
    {
        $this->mySetUp();
        $em = $this->entityManager;

        /** @var User $userJohn */ $userJohn = $em->getRepository('AppBundle:User')->findOneBy(["name" => "John", "surname"=>"Doe"]);
        /** @var User $userJames */ $userJames = $em->getRepository('AppBundle:User')->findOneBy(["name" => "James", "surname"=>"Dunn"]);
        /** @var Resource $resourceArticle */ $resourceArticle = $em->getRepository('AppBundle:Resource')->findOneBy(["name" => "Article"]);
        /** @var Action $actionEdit */ $actionEdit = $em->getRepository('AppBundle:Action')->findOneBy(["name" => "Edit"]);

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
        /** @var Rule $rule */ $rule = $em->getRepository(Rule::class)->findOneBy(["name" => "EditArticle"]);

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

        $data = [
            'rule_id' => $rule->getId(),
            'name' => 'EditArticleEditorAndViewer',
            'description' => '...',
            'resource_id' => $resourceArticle->getId(),
            'action_id' => $actionEdit->getId(),
            'role_id' => [$roleEditor->getId(), $roleViewer->getId()],
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
        $this->assertTrue($content['isAllowed'], "Editor should be allowed");

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJohn->getId() . '&' .
            'action_id=' . $actionEdit->getId() . '&' .
            'resource_id=' . $resourceArticle->getId()
        );

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertTrue($content['isAllowed'], "Viewer should be allowed");

    }

    /**
     * @test testRuleCreate
     * @info Tests rule API create new action
     */
    public function testRuleCreate()
    {

        $this->mySetUp();
        $em = $this->entityManager;
        $client = static::createClient();

        /** @var User $userJames */ $userJames = $em->getRepository('AppBundle:User')->findOneBy(["name" => "James", "surname"=>"Dunn"]);
        /** @var User $userJohn */ $userJohn = $em->getRepository('AppBundle:User')->findOneBy(["name" => "John", "surname"=>"Doe"]);
        /** @var Role $roleViewer*/ $roleViewer = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Viewer"]);
        /** @var Role $roleEditor*/ $roleEditor = $em->getRepository("AppBundle:Role")->findOneBy(["name" => "Editor"]);
        /** @var Resource $resourceArticle */ $resourceArticle = $em->getRepository('AppBundle:Resource')->findOneBy(["name" => "Article"]);
        /** @var Action $actionEdit */ $actionEdit = $em->getRepository('AppBundle:Action')->findOneBy(["name" => "Edit"]);


        $data = [
            'name' => 'RandomRule',
            'description' => '...',
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

        $client->request('GET', '/api/rule/isAllowed?' .
            'user_id=' . $userJohn->getId() . '&' .
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
        /** @var Rule $rule */ $rule = $em->getRepository(Rule::class)->findOneBy(["name" => "EditArticle"]);

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
            '/api/rule/deleteAction?rule_id=' . $rule->getId(),
            );

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Wrong response code");
        $this->assertCount(0, $em->getRepository(RoleRule::class)->findAll());

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

