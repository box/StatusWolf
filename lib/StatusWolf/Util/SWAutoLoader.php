<?php
/**
 * SWAutoLoader
 *
 * Class autoloader functions for StatusWolf
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 21 May 2013
 *
 * @package StatusWolf.Util
 */
class SWAutoLoader {

  public function __construct()
  {
    $this->build_path_cache(LIB);
  }

  /**
   * build_path_cache
   *
   * Recursively searches the passed directory (LIB by default, see
   * conf/constants.php) and builds an array of all subdirectories
   * found there, caching them as a search list for loading classes.
   * The cached directory list is saved to the users session for
   * later retrieval
   *
   * @param string $base_dir
   */
  public static function build_path_cache($base_dir)
  {
    $lib_directories = new RecursiveDirectoryIterator($base_dir, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($lib_directories, RecursiveIteratorIterator::SELF_FIRST);
    $libdir_cache = array();
    foreach ($iterator as $lib_dir)
    {
      if ($lib_dir->isDir())
      {
        $libdir_cache[] = $lib_dir->getPathName();
      }
    }
    $_SESSION['_path_cache'] = serialize($libdir_cache);
  }

  /**
   * sw_autoloader
   *
   * The function which searches the cached list of directories and
   * already seen classes and loads them on demand
   *
   * @param string $class
   */
  public static function sw_autoloader($class)
  {
    // All class files must end in .php
    $class_file = $class . '.php';

    // If we already have a cached list of classes, load it
    if (array_key_exists('_class_cache', $_SESSION))
    {
      $class_cache = unserialize($_SESSION['_class_cache']);
    }
    else
    {
      $class_cache = array();
    }

    // Load the cached list of directories if it exists, if not create it
    if (array_key_exists('_path_cache', $_SESSION))
    {
      $path_cache = unserialize($_SESSION['_path_cache']);
    }
    else
    {
      self::build_path_cache(LIB);
      $path_cache = unserialize($_SESSION['_path_cache']);
    }

    // Check to see if the class has already been seen and cached
    if (array_key_exists($class_file, $class_cache))
    {
      if (file_exists($class_cache[$class_file]))
      {
        require_once $class_cache[$class_file];
      }
    }
    else
    {
      // Class hasn't been seen before, check the path cache to find it,
      // add it to the cache when we do.
      foreach ($path_cache as $lib_dir)
      {
        if (file_exists($lib_dir . DS . $class_file))
        {
          $class_cache[$class_file] = $lib_dir . DS . $class_file;
          $_SESSION['_class_cache'] = serialize($class_cache);
          $cached = true;
          require_once $class_cache[$class_file];
          break;
        }
        else
        {
          $cached = false;
        }
      }
      if (!$cached)
      {
        self::build_path_cache(LIB);
        self::sw_autoloader($class);
      }
    }

  }

}
