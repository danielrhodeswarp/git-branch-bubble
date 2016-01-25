<?php

namespace Danielrhodeswarp\GitBranchBubble;

use Illuminate\Support\ServiceProvider;

/**
 * Laravel ServiceProvider to glue GitBranchBubble's services into the main project
 * 
 * @package    git-branch-bubble (https://github.com/danielrhodeswarp/git-branch-bubble)
 * @author     Daniel Rhodes <daniel.rhodes@warpasylum.co.uk>
 * @copyright  Copyright (c) 2016 Daniel Rhodes
 * @license    see LICENCE file in source code root folder     The MIT License
 */
class GitBranchBubbleServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //register the GitBranchBubble middleware so the end user doesn't
        //have to go shufting about in app/Http/Kernel.php
        $this->registerMiddleware('Danielrhodeswarp\GitBranchBubble\Http\Middleware\GitBranchBubble');
        
        //make config publishable (and give specific tag of 'config')
        $this->publishes([
            __DIR__ . '/config/gitbranchbubble.php' => config_path('gitbranchbubble.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //let 'em override *individual* things in their copy of the gitbranchbubble.php config file
        $this->mergeConfigFrom(
            __DIR__ . '/config/gitbranchbubble.php', 'gitbranchbubble'    //not actually sure what the second parm is for here...
        );
    }
    
    /**
     * Push the specified middleware into the kernel
     * (so that end users don't have to go fiddling in app/Http/kernel.php)
     *
     * @param string $middlewareClass
     */
    protected function registerMiddleware($middlewareClass)
    {
        $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
        $kernel->pushMiddleware($middlewareClass);
    }
}