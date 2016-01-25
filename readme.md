# Git Branch Bubble

## What's this?

It's the Git Branch Bubble which will make the current Git branch you are working on face-punchingly obvious when
developing your Laravel 5 projects. It's useful if you are paranoid, forgetful or both.

## Installation

`composer require --dev danielrhodeswarp/git-branch-bubble`

Then add `Danielrhodeswarp\GitBranchBubble\GitBranchBubbleServiceProvider::class` to the `'providers'` bit of your project's /config/app.php

## Config

You may publish the gitbranchbubble.php config file into your project (`php artisan vendor:publish`) and then tinker with some visual properties of the bubble.

## Notes

Git branch will show as "unknown" under any and all failure / fringe cases (Git not installed, Laravel project not a Git repo, something wrong with Git etc).


