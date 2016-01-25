<?php

namespace Danielrhodeswarp\GitBranchBubble\Http\Middleware;

use Closure;
use App;

use SebastianBergmann\Git\Git;
use SebastianBergmann\Git\RuntimeException;

/**
 * Middleware to inject the Git branch bubble into all (sensible) responses
 * 
 * @package    git-branch-bubble (https://github.com/danielrhodeswarp/git-branch-bubble)
 * @author     Daniel Rhodes <daniel.rhodes@warpasylum.co.uk>
 * @copyright  Copyright (c) 2016 Daniel Rhodes
 * @license    see LICENCE file in source code root folder     The MIT License
 */
class GitBranchBubble
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try
        {
            /** @var \Illuminate\Http\Response $response */
            $response = $next($request);
        }
        
        catch(\Exception $e)
        {
            //literally no idea what to do here (or if even needed / appropriate etc)
            DD($e->getMessage());
        }
        
        return $this->injectBubble($request, $response);
    }
    
    /**
     *
     */
    private function injectBubble($request, $response)
    {
        //NOP if not in browser or if an ajax request or etc
        if(App::runningInConsole() or $request->ajax() /*or $somethingElse*/)   //Laravel's $request->ajax() simply wraps Symfony's $request->isXmlHttpRequest()
        {
            return $response;
        }
        
        //steps:
        //[1] get current git branch (if possible / appropriate)
        //[2] inject git branch bubble into content of $response
        //[3] return $response
         
        //[1]--------        
        $content = $response->getContent();

        $bubbleText = 'unknown';
        
        //using sebastian/git
        $git = new Git(base_path());
        
        try
        {
            $bubbleText = $git->getCurrentBranch();
        }
        
        catch(RuntimeException $exception)
        {
            //NOP
        }
        
        /*
        //using kzykhys/git
        $git = new \PHPGit\Git(base_path());
        foreach($git->branch() as $branch)
        {
            if($branch['current'])
            {
                $bubbleText = $branch['name'];
            }
        }
        */
        
        //[2]--------
        $bubbleHtml = $this->getBubbleHtml($bubbleText);

        //get last </body> or </BODY> and - if present - inject just before it
        //(else simply append to $content)
        $position = strripos($content, '</body>');
        if($position !== false)
        {
            $content = substr($content, 0, $position) . $bubbleHtml . substr($content, $position);
        }
        
        else
        {
            $content = $content . $bubbleHtml;
        }

        $response->setContent($content);
        
        //[3]--------
        return $response;
    }
    
    /**
     * Return valid HTML for the bubble (based on config settings)
     *
     * @param string $bubbleText text to display in bubble
     * @return string valid HTML for the bubble
     */
    protected function getBubbleHtml($bubbleText)
    {
        //put configs into variables for HEREDOC
        $backgroundColour = config('gitbranchbubble.background-colour');
        $textColour = config('gitbranchbubble.text-colour');
        $opacity = config('gitbranchbubble.opacity');
        
        //round if appropriate
        $borderRadius = '';
        if(config('gitbranchbubble.shape') == 'round')
        {
            $borderRadius = 'border-radius:50%;';
        }
        
        //set position
        $topAndLeft = '';
        
        $position = config('gitbranchbubble.position');
        
        if($position == 'top-right')
        {
            $topAndLeft = 'top:2%; left:88%;';
        }
        
        elseif($position == 'bottom-right')
        {
            $topAndLeft = 'top:88%; left:88%;';
        }
        
        elseif($position == 'bottom-left')
        {
            $topAndLeft = 'top:88%; left:2%;';
        }
        
        else    //top-left as a failsafe
        {
            $topAndLeft = 'top:2%; left:2%;';
        }
        
        $bubbleHtml = <<<HTML
<div
id="danielrhodeswarp-gbb"
style="
z-index:10000;
text-align:center;
position:fixed;
border:1px solid black;
background-color:{$backgroundColour};
opacity:{$opacity};
{$borderRadius}
color:{$textColour};
{$topAndLeft}
width:10%;
height:10%;
">
<br>{$bubbleText}
</div>
HTML;
        
        return $bubbleHtml;
    }
}
