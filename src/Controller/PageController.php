<?php

namespace App\Controller;

use App\Services\CacheHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{

    #[Route('/')]
    public function homepage(CacheHelper $cacheHelper): Response
    {
        if(isset($_GET['s']) ) {
            return $this->redirectToRoute('app_search', ['slug'=> $_GET['s']]);
        } else {
            $page = $cacheHelper->cache('/');
            $size = mb_strlen($page['html'], '8bit');
            return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
        }
    }

    #[Route('/{slug}')]
    public function page(string $slug, CacheHelper $cacheHelper): Response
    {
        $page = $cacheHelper->cache($slug);
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }

    #[Route('/{slug}/{nestedSlug}')]
    public function nestedPage(string $slug, ?string $nestedSlug, CacheHelper $cacheHelper): Response
    {
        $page = $cacheHelper->cache($slug, $nestedSlug);
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }

    #[Route('/{slug}/{nestedSlug}/{article}')]
    public function article(string $slug, ?string $nestedSlug, ?string $article, CacheHelper $cacheHelper): Response
    {
        $page = $cacheHelper->cache($slug, $nestedSlug, $article);
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }

    #[Route('/search/{slug}', name: 'app_search')]
    public function search(CacheHelper $cacheHelper, string $slug): Response
    {
        $page = $cacheHelper->cache('/search/'.$slug);
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }
}
