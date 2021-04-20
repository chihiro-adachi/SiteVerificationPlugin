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

namespace Plugin\SiteVerificationPlugin\Tests\Web\Admin;

use Eccube\Common\Constant;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Plugin\SiteVerificationPlugin\Entity\Config;
use Plugin\SiteVerificationPlugin\Repository\ConfigRepository;

class ConfigControllerTest extends AbstractAdminWebTestCase
{
    /**
     * @var ConfigRepository
     */
    private $configRepository;

    public function setUp()
    {
        parent::setUp();
        $this->configRepository = $this->entityManager->getRepository(Config::class);
    }

    public function testConfig()
    {
        // プラグイン設定画面
        $this->client->request(
            'GET',
            $this->generateUrl('site_verification_plugin_admin_config')
        );

        self::assertTrue($this->client->getResponse()->isSuccessful());

        // 新規登録
        $params = [
            'config' => [
                Constant::TOKEN_NAME => 'dummy',
                'name' => 'google site verification',
                'path' => 'googlew5d6ba3prvvcz9yveesa.html',
                'content' => 'google-site-verification: googlefw5d6ba3prvvcz9yveesa.html',
            ],
        ];
        $this->client->request(
            'POST',
            $this->generateUrl('site_verification_plugin_admin_config_new'),
            $params
        );
        self::assertTrue($this->client->getResponse()->isRedirection());

        $Config = $this->configRepository->findOneBy([], ['id' => 'DESC']);
        self::assertEquals($params['config']['name'], $Config->getName());
        self::assertEquals($params['config']['path'], $Config->getPath());
        self::assertEquals($params['config']['content'], $Config->getContent());

        // 更新
        $id = $Config->getId();
        $params['config']['name'] = 'update';
        $params['config']['path'] = 'update';
        $params['config']['content'] = 'update';

        $this->client->request(
            'POST',
            $this->generateUrl('site_verification_plugin_admin_config_edit', ['id' => $id]),
            $params
        );
        self::assertTrue($this->client->getResponse()->isRedirection());

        $this->entityManager->clear();
        $Config = $this->configRepository->find($id);
        self::assertEquals($params['config']['name'], $Config->getName());
        self::assertEquals($params['config']['path'], $Config->getPath());
        self::assertEquals($params['config']['content'], $Config->getContent());

        // 削除
        $this->client->request(
            'DELETE',
            $this->generateUrl('site_verification_plugin_admin_config_delete', ['id' => $id])
        );
        self::assertTrue($this->client->getResponse()->isRedirection());

        $this->entityManager->clear();
        $Config = $this->configRepository->find($id);
        self::assertNull($Config);
    }
}
