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

namespace Plugin\SiteVerificationPlugin\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Plugin\SiteVerificationPlugin\Entity\Config;
use Plugin\SiteVerificationPlugin\Form\Type\Admin\ConfigType;
use Plugin\SiteVerificationPlugin\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/site_verification_plugin/config", name="site_verification_plugin_admin_config")
     * @Template("@SiteVerificationPlugin/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Configs = $this->configRepository->findBy([], ['id' => 'ASC']);

        return [
            'Configs' => $Configs,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/site_verification_plugin/config/{id}/delete", requirements={"id" = "\d+"}, name="site_verification_plugin_admin_config_delete", methods={"DELETE"})
     * @Template("@SiteVerificationPlugin/admin/config.twig")
     */
    public function delete($id)
    {
        $this->isTokenValid();

        $Config = $this->configRepository->find($id);

        if (null === $Config) {
            throw new NotFoundHttpException();
        }

        $this->entityManager->remove($Config);
        $this->entityManager->flush();

        $this->generateRouteFile();

        $this->addSuccess('削除しました。', 'admin');

        return $this->redirectToRoute('site_verification_plugin_admin_config');
    }

    /**
     * @Route("/%eccube_admin_route%/site_verification_plugin/config/new", name="site_verification_plugin_admin_config_new")
     * @Route("/%eccube_admin_route%/site_verification_plugin/config/{id}/edit", requirements={"id" = "\d+"}, name="site_verification_plugin_admin_config_edit")
     * @Template("@SiteVerificationPlugin/admin/edit.twig")
     */
    public function createOrEdit(Request $request, $id = null, CacheUtil $cacheUtil)
    {
        $Config = new Config();
        if ($id !== null) {
            $Config = $this->configRepository->find($id);
            if ($Config === null) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush();

            $this->generateRouteFile();

            $this->addSuccess('登録しました。', 'admin');

            $cacheUtil->clearCache();

            return $this->redirectToRoute('site_verification_plugin_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function generateRouteFile()
    {
        $Configs = $this->configRepository->findBy([], ['id' => 'ASC']);

        $ConfigArray = [];
        foreach ($Configs as $Config) {
            $ConfigArray['_plg_site_verification_plugin_routes_'.$Config->getId()] = [
                'path' => '/'.$Config->getPath(),
                'controller' => 'Plugin\SiteVerificationPlugin\Controller\SiteVerificationController::index',
            ];
        }

        $yaml = $ConfigArray ? Yaml::dump($ConfigArray) : '';
        $file = $this->getParameter('kernel.project_dir').'/app/PluginData/SiteVerificationPlugin/routes_generated.yaml';
        file_put_contents($file, $yaml);
    }
}
