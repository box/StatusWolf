<?php
/**
 * MysqlSessionHandler
 *
 * Save session data to a MySQL database instead of the filesystem.
 * The PHP session handler expects the following methods to be implemented:
 * open
 * close
 * read
 * write
 * destroy
 * gc
 *
 * Session handler database structure:
 * CREATE TABLE `session_handler` (
 * `id` varchar(32) NOT NULL,
 * `data` mediumtext NOT NULL,
 * `timestamp` int(255) NOT NULL,
 * PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 22 May 2013
 *
 * @package StatusWolf.Util.Session
 */
class MysqlSessionHandler {

  protected $session_db;

  protected $session_table;

  public function __construct($session = array())
  {
    $this->session_db = new mysqli($session['db_host'], $session['db_user'], $session['db_password'], $session['database']);
    $this->session_table = $session['session_table'];
    if (mysqli_connect_error())
    {
      throw new SWException('Session database connect error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    return true;
  }

  public function open()
  {
    $expire = time() - DAY;
    $clean_query = sprintf("DELETE FROM %s WHERE timestamp < %s", $this->session_table, $expire);
    $this->session_db->query($clean_query);
    if (mysqli_error($this->session_db))
    {
      throw new SWException('Session open error: ' . mysqli_errno($this->session_db) . ' ' . mysqli_error($this->session_db));
    }
    else
    {
      return true;
    }
  }

  public function close()
  {
    return $this->session_db->close();
  }

  public function read($id)
  {
    $read_query = sprintf("SELECT data FROM %s WHERE id = '%s'", $this->session_table, $this->session_db->escape_string($id));
    if ($result = $this->session_db->query($read_query))
    {
      if ($result->num_rows && $result->num_rows > 0)
      {
        $session_data = $result->fetch_assoc();
        return $session_data['data'];
      }
      else
      {
        return false;
      }
    }
    else if (mysqli_error($this->session_db))
    {
      throw new SWException('Session read error: ' . mysqli_errno($this->session_db) . ' ' . mysqli_error($this->session_db));
    }
    else
    {
      return false;
    }
  }

  public function write($id, $data)
  {
    $write_query = sprintf("REPLACE INTO %s VALUES('%s', '%s', '%s')"
                           ,$this->session_table
                           ,$this->session_db->escape_string($id)
                           ,$this->session_db->escape_string($data)
                           ,time());
    $write_result = $this->session_db->query($write_query);
    if (mysqli_error($this->session_db))
    {
      throw new SWException('Session write error: ' . mysqli_errno($this->session_db) . ' ' . mysqli_error($this->session_db));
    }
    else
    {
      return true;
    }
  }

  public function destroy($id)
  {
    $destroy_query = sprintf("DELETE FROM %s WHERE id = '%s'", $this->session_table, $this->session_db->escape_string($id));
    $this->session_db->query($destroy_query);
    if (mysqli_error($this->session_db))
    {
      throw new SWException('Session destroy error: ' . mysqli_errno($this->session_db) . ' ' . mysqli_error($this->session_db));
    }
    else
    {
      return true;
    }
  }

  public function gc($expire)
  {
    $gc_query = sprintf("DELETE FROM %s WHERE timestamp < %s", $this->session_table, time() - intval($expire));
    return $this->session_db->query($gc_query);
  }

}
