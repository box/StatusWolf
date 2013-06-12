<?php
/*=========================================================================*/
/* Name: PolynomialRegression.php                                          */
/* Uses: Calculates and returns coefficients for polynomial regression.    */
/* Date: 06/01/2009                                                        */
/* Author: Andrew Que (http://www.DrQue.net/)                              */
/* Revisions:                                                              */
/*  0.8 - 06/01/2009- QUE - Creation.                                      */
/*  0.9 - 06/14/2012- QUE -                                                */
/*   + Bug fix: removed notice cases by uninitialized variable.            */
/*   + Converted naming convention.                                        */
/*   + Fix spelling errors (or the ones I found).                          */
/*   + Changed to row-echelon method for solving matrix which is much      */
/*     faster than the determinant method.                                 */
/*  0.91 - 05/17/2013- QUE -                                               */
/*   = Changed name to Polynonial regression as this is more fitting to    */
/*     to the function.                                                    */
/* To be done:                                                             */
/*  + Add correlation coefficient.                                         */
/* ----------------------------------------------------------------------- */
/*                                                                         */
/* Polynomial regression class.                                            */
/* Copyright (C) 2009, 2012-2013 Andrew Que                                */
/*                                                                         */
/* This program is free software: you can redistribute it and/or modify    */
/* it under the terms of the GNU General Public License as published by    */
/* the Free Software Foundation, either version 3 of the License, or       */
/* (at your option) any later version.                                     */
/*                                                                         */
/* This program is distributed in the hope that it will be useful,         */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of          */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           */
/* GNU General Public License for more details.                            */
/*                                                                         */
/* You should have received a copy of the GNU General Public License       */
/* along with this program.  If not, see <http://www.gnu.org/licenses/>.   */
/*                                                                         */
/* ----------------------------------------------------------------------- */
/*                                                                         */
/*                      (C) Copyright 2009, 2012-2013                      */
/*                               Andrew Que                                */
/*                                   ð|>                                   */
/*=========================================================================*/
/**
 * Polynomial regression.
 *
 * <p>
 * Used for calculating polynomial regression coefficients.  Useful for
 * linear and non-linear regression, and polynomial curve fitting.
 *
 * @package PolynomialRegression
 * @author Andrew Que ({@link http://www.DrQue.net/})
 * @copyright Copyright (c) 2009, 2012, Andrew Que
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

/**
 * Used for calculating polynomial regression coefficients and interpolation using
 * those coefficients.  Useful for linear and non-linear regression, and polynomial
 * curve fitting.
 *
 * Note: Requires BC math to be compiled into PHP.  Higher-degree polynomials end up
 * with very large/small numbers, requiring an arbitrary precision arithmetic.  Make sure
 * to set "bcscale" as coefficients will likely have decimal values.
 *
 * Quick example of using this unit to calculate linear regression (1st degree polynomial):
 *
 * <code>
 * $regression = new PolynomialRegression( 2 );
 * // ...
 * $regression->addData( $x, $y );
 * // ...
 * $coefficients = $regression->getCoefficients();
 * // ...
 * $y = $regression->interpolate( $coefficients, $x );
 * </code>
 *
 *
 * @package PolynomialRegression
 */
class PolynomialRegression
{
  private $X_Powers;
  private $XY_Powers;
  private $degree;

  /**
   * Constructor
   *
   * Create new class.
   * @param int $degree Max degree of polynomial.
   */
  public function __construct( $degree = 3 )
  {
    $this->degree = $degree;
    $this->reset();

  } // __construct

  /**
   * Reset data.
   *
   * Clear all internal data and prepare for new calculation.
   * Must be called *after* setDegree if degree is changed.
   */
  public function reset()
  {
    $this->X_Powers = array();
    $this->XY_Powers = array();

    $squares = ( $this->degree - 1 ) * 2;

    // Initialize power arrays.
    for ( $index = 0; $index <= $squares; ++$index )
    {
      $this->X_Powers[ $index ] = 0;
      $this->XY_Powers[ $index ] = 0;
    }

  } // reset

  /**
   * Set degree.
   *
   * This is the maximum degree polynomial function that will be
   * calculated.  Note that the request for coefficients can be lower
   * then this value.  If degree is higher, data must be reset and
   * added again.
   * @param int $degree Max degree
   */
  public function setDegree( $degree )
  {
    $this->degree = $degree;

  } // setDegree

  /**
   * Add data
   *
   * Add a data point to calculation.
   * @param float $x Some real value.
   * @param float $y Some real value corresponding to $x.
   */
  public function addData( $x, $y )
  {
    $squares = ( $this->degree - 1 ) * 2;

    // Accumulate new data to power sums.
    for ( $index = 0; $index <= $squares; ++$index )
    {
      $this->X_Powers[ $index ] =
        bcadd( $this->X_Powers[ $index ], bcpow( $x, $index ) );

      $this->XY_Powers[ $index ] =
        bcadd
        (
          $this->XY_Powers[ $index ],
          bcmul( $y, bcpow( $x, $index ) )
        );
    }

  } // addData

  /**
   * Get coefficients.
   *
   * Calculate and return coefficients based on current data.
   * @param int $degree Integer value of the degree polynomial desired.  Default
   *                    is -1 which is the max degree set by class.
   * @return array Array of coefficients (as BC strings).
   */
  public function getCoefficients( $degree = -1 )
  {
    // If no degree specified, use standard.
    if ( $degree == -1 )
      $degree = $this->degree;

    // Build a matrix.
    // The matrix is made up of the sum of powers.  So if the number represents the power,
    // the matrix will look like this for a 5th degree polynomial:
    //     [ 0 1 2 3 ]
    //     [ 1 2 3 4 ]
    //     [ 2 3 4 5 ]
    //     [ 3 4 5 6 ]
    //     [ 4 5 6 7 ]
    //
    $matrix = array();
    for ( $row = 0; $row < $degree; ++$row )
    {
      $matrix[ $row ] = array();
      for ( $column = 0; $column < $degree; ++$column )
        $matrix[ $row ][ $column ] =
          $this->X_Powers[ $row + $column ];
    }

    // Create augmented matrix by adding X*Y powers.
    for ( $row = 0; $row < $degree; ++$row )
      $matrix[ $row ][ $degree ] = $this->XY_Powers[ $row ];

    // Determine number of rows in matrix.
    $rows = count( $matrix );

    // Initialize done.
    $isDone = array();
    for ( $column = 0; $column < $rows; ++$column )
      $isDone[ $column ] = false;

    // This loop will result in an upper-triangle matrix with the
    // diagonals all 1--the first part of row-reduction--using 2
    // elementary row operations: multiplying a row by a scalar, and
    // subtracting a row by a multiple of an other row.
    // NOTE: This loop can be done out-of-order.  That is, the first
    // row may not begin with the first term.  Order is tracked in the
    // "order" array.
    $order = array();
    for ( $column = 0; $column < $rows; ++$column )
    {
      // Find a row to work with.
      // A row that has a term in this column, and has not yet been
      // reduced.
      $activeRow = 0;
      while ( ( ( 0 == $matrix[ $activeRow ][ $column ] )
             || ( $isDone[ $activeRow ] ) )
           && ( $activeRow < $rows ) )
      {
        ++$activeRow;
      }

      // Do we have a term in this row?
      if ( $activeRow < $rows )
      {
        // Remember the order.
        $order[ $column ] = $activeRow;

        // Normalize row--results in the first term being 1.
        $firstTerm = $matrix[ $activeRow ][ $column ];
        for ( $subColumn = $column; $subColumn <= $rows; ++$subColumn )
          $matrix[ $activeRow ][ $subColumn ] =
            bcdiv( $matrix[ $activeRow ][ $subColumn ], $firstTerm );

        // This row is finished.
        $isDone[ $activeRow ] = true;

        // Subtract the active row from all rows that are not finished.
        for ( $row = 0; $row < $rows; ++$row )
          if ( ( ! $isDone[ $row ] )
            && ( 0 != $matrix[ $row ][ $column ] ) )
          {
             // Get first term in row.
             $firstTerm = $matrix[ $row ][ $column ];
             for ( $subColumn = $column; $subColumn <= $rows; ++$subColumn )
             {
               $accumulator = bcmul( $firstTerm, $matrix[ $activeRow ][ $subColumn ] );
               $matrix[ $row ][ $subColumn ] =
                 bcsub( $matrix[ $row ][ $subColumn ], $accumulator );
             }
          }
      }
    }

    // Reset done.
    for ( $row = 0; $row < $rows; ++$row )
     $isDone[ $row ] = false;

    $coefficients = array();

    // Back-substitution.
    // This will solve the matrix completely, resulting in the identity
    // matrix in the x-locations, and the coefficients in the last column.
    //   | 1  0  0 ... 0  c0 |
    //   | 0  1  0 ... 0  c1 |
    //   | .  .  .     .   . |
    //   | .  .  .     .   . |
    //   | 0  0  0 ... 1  cn |
    for ( $column = ( $rows - 1 ); $column >= 0; --$column )
    {
      // The active row is based on order.
      $activeRow = $order[ $column ];

      // The active row is now finished.
      $isDone[ $activeRow ] = true;

      // For all rows not finished...
      for ( $row = 0; $row < $rows; ++$row )
        if ( ! $isDone[ $row ] )
        {
          $firstTerm = $matrix[ $row ][ $column ];

          // Back substitution.
          for ( $subColumn = $column; $subColumn <= $rows; ++$subColumn )
          {
            $accumulator =
              bcmul( $firstTerm, $matrix[ $activeRow ][ $subColumn ] );
            $matrix[ $row ][ $subColumn ] =
              bcsub( $matrix[ $row ][ $subColumn ], $accumulator );
          }
        }

      // Save this coefficient for the return.
      $coefficients[ $column ] = $matrix[ $activeRow ][ $rows ];
    }

    // Coefficients are stored backward, so sort them.
    ksort( $coefficients );

    // Return the coefficients.
    return $coefficients;

  } // getCoefficients

  /**
   * Interpolate
   *
   * Return y point for given x and coefficient set.
   * @param array $coefficients Coefficients as calculated by 'getCoefficients'.
   * @param float $x X-coordinate from which to calculate Y.
   * @return float Y-coordinate (as floating-point).
   */
  public function interpolate( $coefficients, $x )
  {
    $degree = count( $coefficients );

    $y = 0;
    for ( $coefficentIndex = 0; $coefficentIndex < $degree; ++$coefficentIndex )
      $y =
        bcadd
        (
          $y,
          bcmul
          (
            $coefficients[ $coefficentIndex ],
            bcpow( $x, $coefficentIndex )
          )
        );

    return floatval( $y );

  } // interpolate

} // Class

?>
