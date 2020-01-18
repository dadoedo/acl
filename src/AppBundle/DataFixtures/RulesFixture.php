<?php


namespace AppBundle\DataFixtures;


use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\Role;
use AppBundle\Entity\RoleRule;
use AppBundle\Entity\RoleUser;
use AppBundle\Entity\Rule;
use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RulesFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Entries for tests
        // 2 of User, Resource, Role and Action
        $userJohn = new User();
        $userJohn->setName('John');
        $userJohn->setSurname('Doe');
        $userJames = new User();
        $userJames->setName('James');
        $userJames->setSurname('Dunn');
        $resourceImage = new Resource();
        $resourceImage->setName('Image');
        $resourceArticle = new Resource();
        $resourceArticle->setName('Article');
        $roleEditor = new Role();
        $roleEditor->setName('Editor');
        $roleViewer = new Role();
        $roleViewer->setName('Viewer');
        $actionEdit = new Action();
        $actionEdit->setName('Edit');
        $actionEdit->setDescription('Edit Articles and such');
        $actionView = new Action();
        $actionView->setName('View');
        $actionView->setDescription('View Articles and such');
        ###
//        $userJames->addRole($roleEditor);
        $newRoleUser = new RoleUser();
        $newRoleUser->setRole($roleEditor);
        $newRoleUser->setUser($userJames);
        $manager->persist($newRoleUser);
        $newRule = new Rule();
        $newRule->setName('EditArticle');
        $newRule->setDescription('...');
        $newRule->setResource($resourceArticle);
        $newRule->setAction($actionEdit);

        $manager->persist($newRule);
        $newRuleRole = new RoleRule();
        $newRuleRole->setAllowed(true);
        $newRuleRole->setRole($roleEditor);
        $newRuleRole->setRule($newRule);
        $manager->persist($newRuleRole);
        ###
        $manager->persist($actionEdit);
        $manager->persist($actionView);
        $manager->persist($roleViewer);
        $manager->persist($roleEditor);
        $manager->persist($resourceArticle);
        $manager->persist($resourceImage);
        $manager->persist($userJohn);
        $manager->persist($userJames);
        $manager->flush();
    }
}