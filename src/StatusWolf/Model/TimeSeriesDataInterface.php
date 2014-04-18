<?php
/**
 * TimeSeriesDataInterface
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 7 March 2014
 *
 */

namespace StatusWolf\Model;

interface TimeSeriesDataInterface {
    
    function read($key = null);

    function read_json($key = null);

    function read_csv($key = null);

    function get_start();

    function get_end();

}
