<?php
/**
 * DetectTimeSeriesAnomaly
 *
 * A component of QuAD - Queisser Anomaly Detection
 *
 * Authors: Jeff Queisser <jeff@box.com> and Mark Troyer <disco@box.com>
 * Date Created: 11 June 2013
 *
 * @package StatusWolf.Util
 */
class DetectTimeSeriesAnomaly {
  public function detect_anomaly($graph_data, $accuracy_margin)
  {
    $anomalies = array();
    $violation = array();
    $in_violation = false;
    $violation_score_threshold = 2;
    $point_count = 6;

    foreach ($graph_data as $entry)
    {
      $timestamp = $entry['timestamp'];
      $actual = $entry['value'][1][1];
      list($low_value, $projected_value, $high_value) = $entry['value'][0];

      if ($actual < $low_value || $actual > $high_value)
      {
        $anomaly_unit = (float) $accuracy_margin * (int) $projected_value;

        if ($actual < $low_value)
        {
          $violation[$timestamp] = ($actual - $low_value) / $anomaly_unit;
        }
        else
        {
          $violation[$timestamp] = ($actual - $high_value) / $anomaly_unit;
        }
        $is_violation = true;
      }
      else
      {
        $is_violation = false;
      }

      if ($is_violation && $in_violation)
      {
        if (($point_count + 1) >= count($graph_data))
        {
          if ($this->_abs_total_violation_score($violation) >= $violation_score_threshold)
          {
            $possible_anomaly = $this->_classify_anomaly($violation);
            if ($possible_anomaly)
            {
              $anomalies[] = $possible_anomaly;
            }
          }
          else
          {
            $in_violation = false;
            $violation = array();
          }
        }
        else
        {

        }
      }
      else if ($is_violation && !$in_violation)
      {
        if (($point_count + 1) >= count($graph_data))
        {
          if ($this->_abs_total_violation_score($violation) >= $violation_score_threshold)
          {
            $possible_anomaly = $this->_classify_anomaly($violation);
            if ($possible_anomaly)
            {
              $anomalies[] = $possible_anomaly;
            }
          }
          else
          {
            $violation = array();
          }
        }
        else
        {
          $in_violation = true;
        }
      }
      else if (!$is_violation && $in_violation)
      {
        if ($this->_abs_total_violation_score($violation) >= $violation_score_threshold)
        {
          $possible_anomaly = $this->_classify_anomaly($violation);
          if ($possible_anomaly)
          {
            $anomalies[] = $possible_anomaly;
          }
        }
        $in_violation = false;
        $violations = array();
      }
      else if (!$is_violation && !$in_violation)
      {

      }
      $point_count++;
    }

    return $anomalies;
  }

  private function _abs_total_violation_score($violation)
  {
    $violation_score = 0;

    foreach ($violation as $v)
    {
      $violation_score += abs($v);
    }

    return $violation_score;
  }

  private function _classify_anomaly($anomaly)
  {
    $anomaly_type = null;
    $spike_threshold = 1;

    foreach ($anomaly as $a)
    {
      if ($a <= -$spike_threshold)
      {
        $anomaly_type = 'DROPOFF';
        break;
      }
      else if ($a >= $spike_threshold)
      {
        $anomaly_type = 'SPIKE_UP';
        break;
      }
    }

    if (is_null($anomaly_type))
    {
      $average = array_sum($anomaly_type) / count($anomaly);
      if ($average > 0)
      {
        $anomaly_type = 'OVERAGE';
      }
      else
      {
        $anomaly_type = 'UNDERAGE';
      }
    }

    $anomaly['anomaly_type'] = $anomaly_type;

    return $anomaly;
  }
}
