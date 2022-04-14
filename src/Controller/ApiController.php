<?php

namespace App\Controller;

use App\Services\CacheHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ApiController extends AbstractController
{
    #[Route('/recache/{key}/{slug}')]
    public function deleteSlugCacheFromCR(CacheHelper $cacheHelper, string $slug, string $key): Response
    {
        if ($key != $_ENV['API_KEY']) throw $this->createNotFoundException('404 Page Not Found');
        $deleted = $cacheHelper->deleteSlugCache($slug);
        return $this->json($deleted);
    }
}