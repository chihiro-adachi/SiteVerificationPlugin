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

namespace Plugin\SiteVerificationPlugin\Controller;

use Eccube\Common\EccubeConfig;
use Eccube\Controller\AbstractController;
use Plugin\SiteVerificationPlugin\Repository\ConfigRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SiteVerificationController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var EccubeConfig
     */
    private $config;

    public function __construct(ConfigRepository $configRepository, EccubeConfig $config, ValidatorInterface $validator)
    {
        $this->configRepository = $configRepository;
        $this->config = $config;
        $this->validator = $validator;
    }

    /**
     * @return Response
     */
    public function index(Request $request)
    {
        $path = ltrim($request->getPathInfo(), '/');
        $errors = $this->validator->validate($path, [
            new NotBlank(),
            new Length(['max' => 255]),
            new Regex([
                'pattern' => '/^[a-zA-Z0-9\\.]+$/',
            ]),
        ]);

        if ($errors->count() > 0) {
            throw new BadRequestHttpException();
        }

        $Config = $this->configRepository->findOneBy(['path' => $path]);

        if ($Config === null) {
            throw new NotFoundHttpException();
        }

        return new Response(htmlspecialchars($Config->getContent(), ENT_QUOTES, 'UTF-8'));
    }
}
