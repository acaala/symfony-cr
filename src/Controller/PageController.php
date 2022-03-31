<?php

namespace App\Controller;

use App\Services\CacheHelper;
use App\Services\LocationHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PageController extends AbstractController
{
    
    #[Route('/admin', name: 'app_admin')]
    public function admin(LocationHelper $locationHelper): Response
    {
        $countryCode = $locationHelper->getCountryCode();
        return $this->render('admin.html.twig', ['cc' => $countryCode]);
    }
    
    #[Route('/admin/{slug}/clear', name: 'app_admin_clear')]
    public function adminCacheClear(CacheHelper $cacheHelper, string $slug): Response
    {   
        if($slug == 'home') $slug = '/';
        $cacheClearStatus = $this->addFlash('cacheClearStatus' . $slug, $cacheHelper->cacheClear($slug));
        return $this->redirectToRoute('app_admin', [ $cacheClearStatus ]);
    }

    #[Route('/')]
    public function homepage(CacheHelper $cacheHelper): Response
    {
        $page = $cacheHelper->cache('/');
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }
    
    #[Route('/{slug}')]
    public function page(string $slug = null, CacheHelper $cacheHelper): Response 
    {  
        $page = $cacheHelper->cache($slug);
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }

    #[Route('/{slug}/{nestedSlug}')]
    public function nestedPage(string $slug, string $nestedSlug, CacheHelper $cacheHelper): Response 
    {   
        $page = $cacheHelper->cache($slug, $nestedSlug);
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }


}
