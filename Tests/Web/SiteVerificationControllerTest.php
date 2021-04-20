<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\SiteVerificationPlugin\Tests\Web;

use Eccube\Tests\Web\AbstractWebTestCase;
use Plugin\SiteVerificationPlugin\Entity\Config;
use Symfony\Component\Yaml\Yaml;

class SiteVerificationControllerTest extends AbstractWebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $route = [
            '_plg_site_verification_plugin_routes_1' => [
                'path' => '/site_verification.html',
                'controller' => 'Plugin\SiteVerificationPlugin\Controller\SiteVerificationController::index',
            ],
        ];
        $yaml = Yaml::dump($route);
        $file = $this->container->getParameter('kernel.project_dir').'/app/PluginData/SiteVerificationPlugin/routes_generated.yaml';
        file_put_contents($file, $yaml);
    }

    public function tearDown()
    {
        $file = $this->container->getParameter('kernel.project_dir').'/app/PluginData/SiteVerificationPlugin/routes_generated.yaml';
        file_put_contents($file, '');

        parent::tearDown();
    }

    public function testIndex()
    {
        $Config = new Config();
        $Config->setName('site_verification');
        $Config->setPath('site_verification.html');
        $Config->setContent('site_verification');
        $this->entityManager->persist($Config);
        $this->entityManager->flush();

        $this->client->request(
            'GET',
            $this->generateUrl('_plg_site_verification_plugin_routes_1')
        );

        self::assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testNotFound()
    {
        $this->client->request(
            'GET',
            $this->generateUrl('_plg_site_verification_plugin_routes_1')
        );

        self::assertTrue($this->client->getResponse()->isNotFound());
    }
}
