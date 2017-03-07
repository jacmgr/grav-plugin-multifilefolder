<?php
namespace Grav\Plugin;

//JUST PUT THIS NOICE AND NEAT IN THE GRAV PAGES......  And use a FAKE ON EVENT function NAME......


use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Page\Page;
// -----------------------------------------
//use Grav\Common\Cache;
use Grav\Common\Config\Config;
//use Grav\Common\Data\Blueprint;
//use Grav\Common\Data\Blueprints;
//use Grav\Common\Filesystem\Folder;
use Grav\Common\Grav;
//use Grav\Common\Language\Language;
//use Grav\Common\Taxonomy;
use Grav\Common\Utils;
//use Grav\Plugin\Admin;
// use RocketTheme\Toolbox\Event\Event;
//use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use Whoops\Exception\ErrorException;
//use Collator as Collator;



class JacmultifilefolderPlugin extends Plugin
{
    /**
     * Subscribe to grav events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        // initialize when plugins are ready
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }
    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        // Don't load in Admin-Backend
        // grav admin Backend only knows how to deal with the Grav single file folders
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }
        //load up the plugin configs
        $this->jaccmsConfig = $this->config->get('plugins.jacmultifilefolder');

        $this->enable([
                //'onPagesInitialized' => ['onPagesInitialized', 0],  //too late, cache is done..
                //'onFolderProcessed' =>  ['onFolderProcessed', 0],   //don't undersand fireing pattern
                'onPageProcessed' =>  ['onPageProcessed', 0],         //get "file" siblings of the page.
            ]);
    }
// =========================================================================
// onPagesInitialized() NOT USED
// =========================================================================
    /**
     * Grav Pages all loaded
     */
    public function onPagesInitialized()
    {  
        return;
    }
// =========================================================================
// onFolderProcessed() NOT USED
// =========================================================================
    /**
     */
    public function onFolderProcessed(Event $event)
    {
        //new Event(['page' => $page]
        return;
    }    
// =========================================================================
// onPageProcessed()  This is the event we are using.....
// =========================================================================
    public function onPageProcessed(Event $event)
    {
        //new Event(['page' => $page]
        $page = $event['page'];

        //WARNING: MINOR HACK TO PAGES.PHP
        //jacmgr made $this->grav['pages']->instances var public in Pages.php
        //no way to get instances back into Pages.php, so...did that..sorry.
        //maybe if pass in the event?
        $this->jaccms_instances = $this->grav['pages']->instances;  
        $this->jaccms_children = $this->grav['pages']->children;
        //Alternatively at them to the event in Pages ?? did not work???
        //$this->jaccms_instances = $event['instances'];  
        //$this->jaccms_children = $event['children']; 
      
        //get siblings, if any for this page
        $file = $page->path().'/'.$page->name();
        $siblings = $this->buildSiblingList($file);
        //add siblings to instances
        if($siblings != null){
          $this->addSiblingsToInstances($siblings);    
          $this->grav['pages']->instances = $this->jaccms_instances;
          $this->grav['pages']->children = $this->jaccms_children;      
        }
    }
// =========================================================================
// Local Functions
// =========================================================================    
    /**
     *
     */
    protected function buildSiblingList($filematch)
    {
        //http://php.net/manual/en/class.splfileinfo.php
        /*
        getBasename 
        getExtension
        getPathname
        */
        $siblings = null;
        $folder = dirname($filematch);
        $iterator = new \FilesystemIterator($folder);
        foreach ($iterator as $file) 
        {
            if ($file->isFile()) 
            {
               //ignore all except the md files
               if($file->getExtension() == "md")
               {
                 //$name = $file->getBasename('.md');
                 $name = $file->getBasename();
                 $path = $file->getPath();
                 //$FFkey = str_replace('\\', '/', $path.'/'.$name);
                 $FFkey = $path.'/'.$name;
                  /*
                  Should I include the page grav red in or not? I am including it since many of
                  my md files reference the folder home page explicitely i.e. /pages/folder/blog versus
                  the grav way of /pages/folder which auto displays the blog.md file.
                  */
                 //if you want to exclude the grav found page keep this
                 $keepindexfile = false;
                 if ($keepindexfile)
                 {
                    $siblings[$FFkey] = $file;
                 } 
                 else 
                 {
                   if ($filematch !== $FFkey){
                    $siblings[$FFkey] = $file;
                   }
                 }
              }
            }
        }
      return $siblings;
    } 
    /**
    *
    *  addSiblingsToInstances
    */
    protected function addSiblingsToInstances($siblings)
    {
        /*
        For each one due what pages->recurse does, but for only a single page..
        */
        $config = $this->grav['config'];
        $this->ignore_files = $config->get('system.pages.ignore_files');
        $this->ignore_folders = $config->get('system.pages.ignore_folders');
        $this->ignore_hidden = $config->get('system.pages.ignore_hidden');
        /** @var Language $language */
        $language = $this->grav['language'];        

        foreach ($siblings as $filename => $FileObjectSPL) {
          
          $path_parts = pathinfo($filename);
          /*
          // given: C:/xampp56/htdocs/grav/core/core02/user/pages/01.home/homepagefile.md
          echo $path_parts['dirname'], "\n";   // C:/xampp56/htdocs/grav/core/core02/user/pages/01.home
          echo $path_parts['basename'], "\n";  // homepagefile.md
          echo $path_parts['extension'], "\n"; // md
          echo $path_parts['filename'], "\n";  // homepagefile   
          */

          $page = new Page;
          //sets flag so plugins know this is a NON-GRAV FOLDER PAGE........
          //we are allowing the MultiFIleFolder method to exist.
          //NOTE:  GRAV and no other plugin except mine recognizes this!!
          $page->jaccms = true; 
                    
          //Multi File Folder "VIRTUAL" (Fake) DIRECTORY IS FILE NAME to be compatible with grav        
          $directory = $path_parts['dirname'].'/'.$path_parts['filename'];
          $page->path($directory);
          //$parent = $path_parts['dirname'];
          $parent = null;
          if($this->jaccms_instances[$path_parts['dirname']]){ 
            $parent = $this->jaccms_instances[$path_parts['dirname']];
          }
          if ($parent) {
              $page->parent($parent);
          }
          //do we need this?
          $page->orderDir($config->get('system.pages.order.dir'));
          $page->orderBy($config->get('system.pages.order.by'));

          // Add page into instances
          if (!isset($this->jaccms_instances[$page->path()])) {
              $this->jaccms_instances[$page->path()] = $page;
              if ($parent && $page->path()) {
                  $this->jaccms_children[$parent->path()][$page->path()] = ['slug' => $page->slug()];
              }
          } else {
              //excedption since you will be overwriting an existing instance!!
              echo '<pre>922 Pages::buildMultiFileFolder</pre>';
              echo '<pre>'; echo 'EXISTS'.$page->path(); echo '</pre>';
              echo '<pre>'; echo dirname($page->path()); print_r($this->jaccms_instances); echo '</pre>'; 
              exit;
              throw new \RuntimeException('Fatal error when creating page instances.');
          }        
          //already have the SPL object from siblinglist
          //$page_found = new \SplFileObject($filename);
          $page_found = $FileObjectSPL;  //see the foreach above
          $page->init($page_found, $path_parts['extension']);
          //jacmgr
          $page->path($directory);  //already set above???
          
          $content_exists = true;
          
          // set current modified of page
          $last_modified = $page->modified();        
                   $MygetMTIME = filemtime($filename);
                   $MygetBasename = $path_parts['basename'];
                   // Update the last modified if it's newer than already found
                  if (!in_array($MygetBasename, $this->ignore_files) && ($modified = $MygetMTIME) > $last_modified) {
                      $last_modified = $modified;
                  }       
          
          // Override the modified and ID so that it takes the latest change into account
          $page->modified($last_modified);
          $page->id($last_modified . md5($page->filePath()));
  
          //jacmgr: DO I REALLY NEED TO DO THIS ONE? I commented out.
          // Sort based on Defaults or Page Overridden sort order
          //$this->children[$page->path()] = $this->sort($page);  
       }
    }
}
