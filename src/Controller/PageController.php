<?php

namespace App\Controller;

use App\Services\CacheHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class PageController extends AbstractController
{
    
    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        return $this->render('admin.html.twig');
    }
    
    #[Route('/admin-{slug}-clear', name: 'app_admin_clear')]
    public function adminCacheClear(CacheHelper $cacheHelper, string $slug): Response
    {   
        if($slug == 'home') $slug = '/';
        return $this->redirectToRoute('app_admin', [ 'message' => $cacheHelper->cacheClear($slug) ]);
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
        dump($page);
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
