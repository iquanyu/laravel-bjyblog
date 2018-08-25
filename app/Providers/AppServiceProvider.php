<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Chat;
use App\Models\Comment;
use App\Models\Config;
use App\Models\FriendshipLink;
use App\Models\GitProject;
use App\Models\Nav;
use App\Models\OauthUser;
use App\Models\Tag;
use File;
use Cache;
use App\Observers\CacheClearObserver;
use Illuminate\Support\ServiceProvider;
use DB;
use Illuminate\Database\QueryException;
use Artisan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        ini_set('memory_limit', "256M");

        // 开源项目数据
        view()->composer(['layouts/home', 'home/index/git'], function($view){
            $gitProject = Cache::remember('common:gitProject', 10080, function () {
                // 获取开源项目
                return GitProject::select('name', 'type')->orderBy('sort')->get();
            });
            // 分配数据
            $assign = compact('gitProject');
            $view->with($assign);
        });

        // 获取各种统计
        view()->composer(['layouts/home', 'admin/index/index'], function($view){
            $articleCount = Cache::remember('count:article', 10080, function () {
                // 统计文章总数
                return Article::count('id');
            });

            $commentCount = Cache::remember('count:comment', 10080, function () {
                // 统计评论总数
                return Comment::count('id');
            });

            $chatCount = Cache::remember('count:chat', 10080, function () {
                // 统计随言碎语总数
                return Chat::count('id');
            });

            $oauthUserCount = Cache::remember('count:oauthUser', 10080, function () {
                // 统计用户总数
                return OauthUser::count('id');
            });

            // 分配数据
            $assign = compact('articleCount', '', 'commentCount', 'chatCount', 'oauthUserCount');
            $view->with($assign);
        });

        // 使用 try catch 是为了解决 composer install 时候触发 php artisan optimize 但此时无数据库的问题
        try {
            Artisan::call('cache:clear');
            // 获取配置项
            $config = Cache::remember('config', 10080, function () {
                return Config::where('id', '>', 100)->pluck('value','name');
            });

            // 解决初次安装时候没有数据引起报错
            if ($config->isEmpty()) {
                Artisan::call('cache:clear');
            } else {
                // 用 config 表中的配置项替换 /config/ 目录下文件中的配置项
                config($config->toArray());
            }
        } catch (QueryException $e) {
            // 此处清除缓存是为了解决上面无数据库时缓存时 config 缓存了空数据 db:seed 后 config 走了缓存为空的问题
            Artisan::call('cache:clear');
            $config = [];
        }

        //分配前台通用的数据
        view()->composer('layouts/home', function($view){
            $category = Cache::remember('common:category', 10080, function () {
                // 获取分类导航
                return Category::select('id', 'name')->orderBy('sort')->get();
            });

            $tag = Cache::remember('common:tag', 10080, function () {
                // 获取标签下的文章数统计
                return Tag::has('articles')->withCount('articles')->get();
            });

            $topArticle = Cache::remember('common:topArticle', 10080, function () {
                // 获取置顶推荐文章
                return Article::select('id', 'title')
                    ->where('is_top', 1)
                    ->orderBy('created_at', 'desc')
                    ->get();
            });

            $newComment = Cache::remember('common:newComment', 10080, function () {
                // 获取最新评论
                $commentModel = new Comment();
                return $commentModel->getNewData();
            });

            $friendshipLink = Cache::remember('common:friendshipLink', 10080, function () {
                // 获取友情链接
                return FriendshipLink::select('name', 'url')
                    ->orderBy('sort')
                    ->get();
            });

            $nav = Cache::remember('common:nav', 10080, function () {
                // 获取菜单
                return Nav::select('name', 'url')
                    ->orderBy('sort')
                    ->get();
            });

            // 获取赞赏捐款文章
            $qunArticleId = config('bjyblog.qq_qun.article_id');
            if (empty($qunArticleId)) {
                $qqQunArticle = [];
            } else {
                $qqQunArticle = Cache::remember('qqQunArticle', 10080, function () use($qunArticleId) {
                    return Article::select('id', 'title')->where('id', $qunArticleId)->first();
                });
            }

            // 分配数据
            $assign = compact('category', 'tag', 'topArticle', 'newComment', 'friendshipLink', 'nav', 'qqQunArticle');
            $view->with($assign);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
