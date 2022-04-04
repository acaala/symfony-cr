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
        $this->addFlash('cacheClearStatus' . $slug, $cacheHelper->cacheClear($slug));
        $this->addFlash('showInfoFor', $slug);
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/load-info/{slug}')]
    public function loadInfo(string $slug): Response
    {
        $this->addFlash('showInfoFor', $slug);
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/')]
    public function homepage(CacheHelper $cacheHelper): Response
    {
        $page = $cacheHelper->cache('/');
        $size = mb_strlen($page['html'], '8bit');
        return $this->render('index.html.twig', [ 'html' => $page['html'], 'time' => $page['time'], 'size' => $size ]);
    }

    // Cache all assets
    #[Route('/assets/cache-all', name: 'app_cache_assets')]
    public function assets(CacheHelper $cacheHelper): Response
    {
        $cacheHelper->cacheAllAssets();
        return $this->redirectToRoute('app_admin');
    }

    // Cache specific stylesSheet
    #[Route('/assets/css/{slug}', name: 'app_fetch_css')]
    public function css(CacheHelper $cacheHelper, string $slug): Response
    {
        $styles = $cacheHelper->cacheAsset('css/'.$slug);
        return new Response($styles, 200, [ 'content-type' => 'text/css']);
    }
    // Cache specific script
    #[Route('/assets/scripts/{slug}', name: 'app_fetch_js')]
    public function script(CacheHelper $cacheHelper, string $slug): Response
    {
        $script = $cacheHelper->cacheAsset('scripts/'.$slug);
        return new Response($script, 200, [ 'content-type' => 'text/javascript']);
    }

    // Cache Icons
    #[Route('/assets/icon/{slug}', name: 'app_fetch_icon')]
    public function icons(CacheHelper $cacheHelper, string $slug): Response
    {
        $script = $cacheHelper->cacheAsset('icon/'.$slug);
        return new Response($script, 200, [ 'content-type' => 'image/png']);
    }
    // Cache Icons
    #[Route('/assets/manifest/{slug}', name: 'app_fetch_manifest')]
    public function manifest(CacheHelper $cacheHelper, string $slug): Response
    {
        $manifest = $cacheHelper->cacheAsset('manifest/'.$slug);
        return new Response($manifest, 200, [ 'content-type' => 'html/text']);
    }
    // Cache Icons
    #[Route('/assets/lang/{slug}', name: 'app_fetch_lang')]
    public function language(CacheHelper $cacheHelper, string $slug): Response
    {
        $lang = $cacheHelper->cacheAsset('lang/'.$slug);
        return new Response($lang, 200, [ 'content-type' => 'html/text']);
    }

    #[Route('/scripts-info/{slug}', name: 'app_fetch_js_info')]
    public function scriptInfo(CacheHelper $cacheHelper, string $slug): Response
    {
        $script = $cacheHelper->cacheScriptsInfo($slug);
        $this->addFlash('jsCacheInfo' . $slug, $script);
        return $this->redirectToRoute('app_admin');
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
}
