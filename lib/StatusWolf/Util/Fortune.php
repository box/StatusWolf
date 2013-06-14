<?php
/**
 * Fortune
 *
 * Like elevator music for the web. While you're sitting int the waiting
 * room of the app, waiting for your anomaly model to build for example,
 * please enjoy this random collection of pithy quotes and jokes pulled
 * randomly from the web.
 *
 * Author: Mark Troyer
 * Date Created: 14 June 2013
 *
 * @package StatusWolf.Util
 */
class Fortune {

  private $_urls = array(
    'chuck' =>'http://api.icndb.com/jokes/random/1'
    ,'quotes' => 'http://www.iheartquotes.com/api/v1/random/?format=json'
  );

  private $_quote_groups = array(
    'geek' => 'esr+humorix_misc+humorix_stories+joel_on_software+macintosh+math+mav_flame+osp_rules+paul_graham+prog_style+subversion'
    ,'general' => '1811_dictionary_of_the_vulgar_tongue+codehappy+fortune+liberty+literature+misc+murphy+oneliners+riddles+rkba+shlomif+shlomif_fav+stephen_wright'
    ,'pop' => 'calvin+forrestgump+friends+futurama+holygrail+powerpuff+simon_garfunkel+simpsons_cbg+simpsons_chalkboard+simpsons_homer+simpsons_ralph+south_park+starwars+xfiles'
    ,'religious' => 'bible+contentions+osho'
    ,'sci-fi' => 'cryptonomicon+discworld+dune+hitchhiker'
  );

  private $_quote_key = array(
    'chuck' => 'joke'
    ,'quotes' => 'quote'
  );

  public function __construct()
  {
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }
    $this->log_tag = '(' . $_SESSION['_sw_authsession']['username'] . '|' . $_SESSION['_sw_authsession']['sessionip'] . ') ';
  }

  public function get_fortune(array $args)
  {

    $source = 'quotes';
    if ($config_source = SWConfig::read_values('statuswolf.waiting.source'))
    {
      $source = $config_source;
    }
    $category = 'random';
    if ($config_category = SWConfig::read_values('statuswolf.waiting.category'))
    {
      $category = $config_category;
    }

    if (!empty($args))
    {
      if (array_key_exists('source', $args))
      {
        $source = $args['source'];
      }
      if (array_key_exists('category', $args))
      {
        $category = $args['category'];
      }
    }

    if (array_key_exists($category, $this->_quote_groups))
    {
      $category = $this->_quote_groups[$category];
    }

    if ($source === "chuck")
    {
      $url = $this->_urls['chuck'];
    }
    else
    {
      if ($category === "random")
      {
        $url = $this->_urls[$source];
      }
      else
      {
        $url = $this->_urls[$source] . '&source=' . $category;
      }
    }

    $this->loggy->logDebug('Fetching quote from ' . $url);
    $curl = new Curl($url);
    Curl::$_proxy = null;
    try
    {
      $raw_quote = $curl->request();
    }
    catch(SWException $e)
    {
      return json_encode(
        array(
             'fortune' => "We're sorry, all circuits are busy now. Please hang up and try your call again later"
             ,'error' => $e->getMessage()
        )
      );
    }

    $quote = json_decode($raw_quote, true);
    if (array_key_exists('value', $quote))
    {
      $quote = $quote['value'][0];
    }
    return array($quote[$this->_quote_key[$source]]);
  }

}
