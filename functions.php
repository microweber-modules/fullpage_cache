<?php

use MicroweberPackages\App\Http\Controllers\FrontendController;
use MicroweberPackages\App\Http\Controllers\Traits\SitemapHelpersTrait;

Route::name('api.')
    ->prefix('api')
    ->middleware(['api','admin'])
    ->group(function () {

        Route::get('fullpage-cache-open-iframe', function () {

            $params = request()->all();
            if (isset($params['slug'])) {
                if (isset($params['iteration']) && isset($params['total_slugs'])) {
                    \Cache::put('fullpage_cached_last_iteration', $params['iteration']);
                    if ($params['iteration'] >= $params['total_slugs']) {
                        \Cache::put('is_fullpage_cached', true);
                    }
                }

                $contentId = intval($params['id']);
                $contentLink = content_link($contentId);

                if ($contentLink) {
                    $_SERVER['HTTP_REFERER'] = $contentLink;
                    $_REQUEST['content_id'] = $contentId;

                    $frontRender = new FrontendController();
                    $html = $frontRender->index();

                    echo $html;
                }
            }
        });
});

class FullpageCacheHelper {

    use SitemapHelpersTrait;

    public function getSlugWithIds()
    {
        $categorySlugs = [];
        $categories = $this->fetchCategoriesLinks();
        if (!empty($categories)) {
            foreach ($categories as $category) {
                if (isset($category['multilanguage_links'])) {
                    foreach ($category['multilanguage_links'] as $multilanguageLink) {
                        $multilanguageLink = str_replace(site_url(),'',$multilanguageLink['link']);
                        $categorySlugs[] = ['slug'=>$multilanguageLink,'id'=>$category['id']];
                    }
                } else {
                    $originalLink = str_replace(site_url(),'', $category['original_link']);
                    $categorySlugs[] = ['slug'=>$originalLink,'id'=>$category['id']];
                }
            }
        }

        $tagSlugs = [];
        $tags = $this->fetchTagsLinks();
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (isset($tag['multilanguage_links'])) {
                    foreach ($tag['multilanguage_links'] as $multilanguageLink) {
                        $multilanguageLink = str_replace(site_url(),'',$multilanguageLink['link']);
                        $tagSlugs[] = ['slug'=>$multilanguageLink,'id'=>0];
                    }
                } else {
                    $originalLink = str_replace(site_url(),'', $tag['original_link']);
                    $tagSlugs[] = ['slug'=>$originalLink,'id'=>0];
                }
            }
        }

        $postSlugs = [];
        $posts = $this->fetchPostsLinks();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                if (isset($post['multilanguage_links'])) {
                    foreach ($post['multilanguage_links'] as $multilanguageLink) {
                        $multilanguageLink = str_replace(site_url(),'',$multilanguageLink['link']);
                        $postSlugs[] = ['slug'=>$multilanguageLink,'id'=>$post['id']];
                    }
                } else {
                    $originalLink = str_replace(site_url(),'', $post['original_link']);
                    $postSlugs[] = ['slug'=>$originalLink,'id'=>$post['id']];
                }
            }
        }

        $pageSlugs = [];
        $pages = $this->fetchPagesLinks();
        if (!empty($pages)) {
            foreach ($pages as $page) {
                if (isset($page['multilanguage_links'])) {
                    foreach ($page['multilanguage_links'] as $multilanguageLink) {
                        $multilanguageLink = str_replace(site_url(),'',$multilanguageLink['link']);
                        $pageSlugs[] = ['slug'=>$multilanguageLink,'id'=>$page['id']];
                    }
                } else {
                    $originalLink = str_replace(site_url(),'', $page['original_link']);
                    $pageSlugs[] = ['slug'=>$originalLink,'id'=>$page['id']];
                }
            }
        }

        $allSlugs = $categorySlugs;
        $allSlugs = array_merge($allSlugs, $tagSlugs);
        $allSlugs = array_merge($allSlugs, $postSlugs);
        $allSlugs = array_merge($allSlugs, $pageSlugs);
        $allSlugs = array_filter($allSlugs);

        return $allSlugs;

    }

}
