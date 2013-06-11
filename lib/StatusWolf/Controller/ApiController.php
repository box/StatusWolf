<?php
/**
 * ApiController
 *
 * Controller for backend calls to retrieve data, etc.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 02 June 2013
 *
 * @package StatusWolf.Controller
 */

class ApiController extends SWController
{

  public function __construct($url_path)
  {
    parent::__construct();
    if (!empty($url_path[0]))
    {
      $_api_function = array_shift($url_path);
      $this->$_api_function($url_path);
//      if (function_exists("$this->_api_function"))
//      {
//        $this->$this->_api_function($url_path);
//      }
//      else
//      {
//        throw new SWException('Unknown API call: ' . $this->_api_function);
//      }
    }
    else
    {
      throw new SWException('No API function found');
    }
  }

  protected function datasource_form($form)
  {
    if (!empty($form) && $form[0])
    {
      ob_start();
      include VIEWS . $form[0] . '.php';
      $raw_form = ob_get_contents();
      $form_data = array('form_source' => $raw_form);
      ob_end_clean();
      echo json_encode($form_data);
    }
    else
    {
      throw new SWException('No datasource form found');
    }
  }

  protected function tsdb_metric_list($query_bits) {
    list($q, $query) = explode('=', $query_bits[0]);
    $query_url = 'http://opentsdb.ve.box.net:4242/suggest?type=metrics&q=';
    $curl = new Curl($query_url . $query);
    $ret = json_decode($curl->request());
    $data = array();
    $data['query'] = $query;
    if (count($ret) > 20) {
      $ret = array_slice($ret, 0, 20);
    }
    $data['suggestions'] = $ret;
    echo json_encode($data);
  }

  protected function opentsdb_anomaly_model($path)
  {
    $query_bits = $_POST;
    $metric = $query_bits['metrics'][0]['name'];
    if (array_key_exists('tags', $query_bits['metrics'][0]))
    {
      $tag_key = implode(' ', $query_bits['metrics'][0]['tags']);
    }
    else
    {
      $tag_key = 'NONE';
    }
    $series = $metric . ' ' . $tag_key;
    $model_cache = CACHE . 'anomaly_model' . DS . md5($series) . '.model';
    if (file_exists($model_cache))
    {
      $anomaly_data = file_get_contents($model_cache);
      $anomaly_data = unserialize($anomaly_data);
    }
    else
    {
      $anomaly = new OpenTSDBAnomalyModel();
      $anomaly->generate($query_bits);
      $anomaly_data = $anomaly->read();
    }

    echo json_encode($anomaly_data);

  }

}
