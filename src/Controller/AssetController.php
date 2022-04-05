<?php

namespace App\Controller;

use App\Services\CacheHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/assets')]
class AssetController extends AbstractController
{
    // Cache all assets
    #[Route('/cache-all', name: 'app_cache_assets')]
    public function assets(CacheHelper $cacheHelper): Response
    {
        $cacheHelper->cacheAllAssets();
        return $this->redirectToRoute('app_admin');
    }

    // Cache specific stylesSheet
    #[Route('/css/{slug}', name: 'app_fetch_css')]
    public function css(CacheHelper $cacheHelper, string $slug): Response
    {
        $styles = $cacheHelper->cacheAsset('css/'.$slug);
        return new Response($styles, 200, [ 'content-type' => 'text/css']);
    }
    // Cache specific script
    #[Route('/scripts/{slug}', name: 'app_fetch_js')]
    public function script(CacheHelper $cacheHelper, string $slug): Response
    {
        $script = $cacheHelper->cacheAsset('scripts/'.$slug);
        return new Response($script, 200, [ 'content-type' => 'text/javascript']);
    }

    // Cache Icons
    #[Route('/icon/{slug}', name: 'app_fetch_icon')]
    public function icons(CacheHelper $cacheHelper, string $slug): Response
    {
        $script = $cacheHelper->cacheAsset('icon/'.$slug);
        return new Response($script, 200, [ 'content-type' => 'image/png']);
    }
    // Cache Manifest
    #[Route('/manifest/{slug}', name: 'app_fetch_manifest')]
    public function manifest(CacheHelper $cacheHelper, string $slug): Response
    {
        $manifest = $cacheHelper->cacheAsset('manifest/'.$slug);
        return new Response($manifest, 200, [ 'content-type' => 'html/text']);
    }

    #[Route('/scripts-info/{slug}', name: 'app_fetch_js_info')]
    public function scriptInfo(CacheHelper $cacheHelper, string $slug): Response
    {
        $script = $cacheHelper->cacheScriptsInfo($slug);
        $this->addFlash('jsCacheInfo' . $slug, $script);
        return $this->redirectToRoute('app_admin');
    }
}