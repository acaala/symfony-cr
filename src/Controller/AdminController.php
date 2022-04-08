<?php

namespace App\Controller;

use App\Services\CacheHelper;
use App\Services\LocationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{

    #[Route('/', name: 'app_admin')]
    public function admin(LocationHelper $locationHelper, CacheHelper $cacheHelper): Response
    {
        $countryCode = $locationHelper->getCountryCode();
        $assets = $cacheHelper->getCachedAssets();
        return $this->render('admin.html.twig', ['cc' => $countryCode, 'assets' => $assets]);
    }
    #[Route('/recache/{slug}')]
    public function deleteSlugCache(CacheHelper $cacheHelper, string $slug): Response
    {
        $deleted = $cacheHelper->deleteSlugCache($slug);
        return $this->json($deleted);
    }

    #[Route('/{slug}/clear', name: 'app_admin_clear')]
    public function adminCacheClear(CacheHelper $cacheHelper, string $slug): Response
    {
        if($slug == 'home') $slug = '/';
        $this->addFlash('cacheClearStatus' . $slug, $cacheHelper->cacheClear($slug));
        $this->addFlash('showInfoFor', $slug);
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/load-info/{slug}')]
    public function loadInfo(string $slug): Response
    {
        $this->addFlash('showInfoFor', $slug);
        return $this->redirectToRoute('app_admin');
    }
}
