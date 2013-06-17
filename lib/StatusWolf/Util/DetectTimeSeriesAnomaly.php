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

  /**
   * DetectTimeSeriesAnomaly::detect_anomaly()
   *
   * Uses projected data, current data and an accuracy margin
   * to determine periods where the current data is out of
   * range for the projection
   *
   * @param array $graph_data
   * @param float $accuracy_margin
   * @return array
   */
  public function detect_anomaly($graph_data, $accuracy_margin)
  {
    $violations = array();
    $in_violation = false;
    $start_violation = null;
    $violation_score = 0;
    $violation_score_threshold = 7;

    foreach ($graph_data as $entry)
    {
      $timestamp = $entry['timestamp'];
      $actual = $entry['value'][1][1];
      list($low_value, $projected_value, $high_value) = $entry['value'][0];

      $is_violation = ($actual < $low_value || $actual > $high_value);

      if ($is_violation)
      {
        $score_of_one = $accuracy_margin * $projected_value;

        if ($actual < $low_value)
        {
          $violation_score += ($low_value - $actual) / $score_of_one;
        }
        else
        {
          $violation_score += ($actual - $high_value) / $score_of_one;
        }
      }

      if ($is_violation && $in_violation)
      {

      }
      else if ($is_violation && !$in_violation)
      {
        $start_violation = $timestamp;
        $in_violation = true;
      }
      else if (!$is_violation && $in_violation)
      {
        if ($violation_score >= $violation_score_threshold)
        {
          $violations[] = array('start' => $start_violation, 'end' => $timestamp);
        }
        $start_violation = null;
        $in_violation = false;
        $violation_score = 0;
      }
      else if (!$is_violation && !$in_violation)
      {

      }
    }

    return $violations;
  }

}
