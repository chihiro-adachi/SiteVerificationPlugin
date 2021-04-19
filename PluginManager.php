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

namespace Plugin\SiteVerificationPlugin;

use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    public function install(array $meta, ContainerInterface $container)
    {
        $this->generateRouteFile($container);
    }

    public function uninstall(array $meta, ContainerInterface $container)
    {
        $this->removeRouteFile($container);
    }

    private function generateRouteFile(ContainerInterface $container)
    {
        $dir = $container->getParameter('kernel.project_dir').'/app/PluginData/SiteVerificationPlugin';
        $file = 'routes_generated.yaml';

        $fs = new Filesystem();
        $fs->remove($dir);
        $fs->mkdir($dir);
        $fs->dumpFile($dir.'/'.$file, '');
    }

    private function removeRouteFile(ContainerInterface $container)
    {
        $dir = $container->getParameter('kernel.project_dir').'/app/PluginData/SiteVerificationPlugin';

        $fs = new Filesystem();
        $fs->remove($dir);
    }
}
