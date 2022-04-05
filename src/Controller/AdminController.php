<?php

namespace App\Controller;

use App\Services\CacheHelper;
use App\Services\LocationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{

    #[Route('/', name: 'app_admin')]
    public function admin(LocationHelper $locationHelper): Response
    {
        $countryCode = $locationHelper->getCountryCode();
        return $this->render('admin.html.twig', ['cc' => $countryCode]);
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
