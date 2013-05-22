<?php
/**
 * SWConfig
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 20 May 2013
 *
 * @package StatusWolf
 */
class SWConfig {

  /**
   * Container for the complete configuration of the application
   *
   * @var array
   */
  protected static $_config_values = array();

  /**
   * Reads an INI-formatted config file and stores it in the $_config_values array
   *
   * @static
   * @param string $config_file The file name to read
   * @param string|null $category The configuration category, will be
   * 	the top level of the array of values as stored. If not given will
   * 	default to the first part of the config file name, e.g. items
   * 	in datasource.ini will be in an array named
   * 	'$_config_values['datasource']
   * @return bool
   * @throws Exception
   */
  public static function load_config($config_file, $category = null)
  {
    $file = CFG . $config_file;
    if (!file_exists($file))
    {
      throw new SWException('Configuration file ' . $file . ' does not exist');
    }
    else
    {
      $contents = parse_ini_file($file, true);
      if (empty($category))
      {
        $file_bits = explode('.', $config_file);
        $category = $file_bits[0];
      }
      self::write_values($category, $contents);
      return true;
    }
  }

  /**
   * Returns the config value or values as specified in the paramater.
   * Config values are queried in the form of 'Category.item'. Due to
   * the nature of the INI format the values may be nested up to four
   * levels deep, e.g. 'datasource.OpenTSDB.url.dc'. If no parameter
   * is given the entire configuration will be returned.
   *
   * @static
   * @param string|null $item
   * @return array|null
   */
  public static function read_values($item = null)
  {
    if (empty($item))
    {
      return self::$_config_values;
    }

    if (strpos($item, '.'))
    {
      $config_path = explode('.', $item);
      $category = array_shift($config_path);
      if (count($config_path) == 3)
      {
        if ($config_values = self::$_config_values[$category][$config_path[0]][$config_path[1]][$config_path[2]])
        {
          return $config_values;
        }
      }
      else if (count($config_path) == 2)
      {
        if ($config_values = self::$_config_values[$category][$config_path[0]][$config_path[1]])
        {
          return $config_values;
        }
      }
      else
      {
        if ($config_values = self::$_config_values[$category][$config_path[0]])
        {
          return $config_values;
        }
      }
      return null;
    }

    if ($config_values = self::$_config_values[$item])
    {
      return $config_values;
    }

    return null;

  }

  /**
   * Takes a category and either an array of config values or a
   * name/value pair and stores them in the active configuration.
   *
   * @static
   * @param string $category
   * @param string|array $config
   * @param string|null $config_value
   */
  public static function write_values($category, $config, $config_value = null)
  {

    if (!is_array($config))
    {
      self::$_config_values[$category][$config] = $config_value;
    }
    else
    {
      foreach ($config as $name => $value)
      {
        self::$_config_values[$category][$name] = $value;
      }
    }

  }

}
