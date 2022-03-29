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

    #[Route('/')]
    public function homepage(CacheHelper $cacheHelper): Response
    {
        $t = microtime(true); 
        $html = $cacheHelper->cache('/');
        return $this->render('index.html.twig', [ 'html' => $html, 'time' => (microtime(true) - $t) ]);
    }
    
    
    #[Route('/{slug}')]
    public function page(string $slug, CacheHelper $cacheHelper): Response 
    {  
        $t = microtime(true); 
        $html = $cacheHelper->cache($slug);
        return $this->render('index.html.twig', [ 'html' => $html, 'time' => (microtime(true) - $t) ]);
    }
    #[Route('/{slug}/{nestedSlug}')]
    public function nestedPage(string $slug, string $nestedSlug, CacheHelper $cacheHelper): Response 
    {   
        $t = microtime(true); 
        $html = $cacheHelper->cache($slug, $nestedSlug);
        return $this->render('index.html.twig', [ 'html' => $html, 'time' => (microtime(true) - $t) ]);
    }


}
