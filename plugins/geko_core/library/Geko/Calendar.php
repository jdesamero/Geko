<?php
/*
 * "geko_core/library/Geko/Calendar.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Calendar
{
	public $sMonthYearTitle;
	public $aSelectedDays;
	
	public $sShowCurr;
	public $sShowPrev;
	public $sShowNext;
	
	
	
	//
	public function __construct()
	{

		// dates
		
		$aSelectedMonthYear = array();
		if ( isset( $_GET['show'] ) ) {
			if ( preg_match('/([0-9]{4})-([0-9]{2})/', $_GET['show'], $aRegs) ) {
				$aSelectedMonthYear = array(
					'year' => $aRegs[1],
					'month' => intval($aRegs[2]),
					'day' => 1
				);		
			}
		}
		
		// var_dump($aSelectedMonthYear);
		
		$date = ($aSelectedMonthYear) ? new Zend_Date($aSelectedMonthYear) : new Zend_Date();
		$date->set(1, Zend_Date::DAY);
		
		
		$this->sMonthYearTitle = $date->get(Zend_Date::MONTH_NAME) . ' ' . $date->get(Zend_Date::YEAR);
		
		
		// links
		
		$iYear = $iPrevYear = $iNextYear = $date->get(Zend_Date::YEAR);
		$iMonth = $iPrevMonth = $iNextMonth = $date->get(Zend_Date::MONTH_SHORT);
		
		$iPrevMonth--;
		$iNextMonth++;
		
		if ( 1 == $iMonth ) {
			$iPrevYear--;
			$iPrevMonth = 12;
		} elseif ( 12 == $iMonth ) {
			$iNextYear++;
			$iNextMonth = 1;
		}
		
		
		// echo $iYear . ' ' . $iMonth;
		
		$sShowCurr = $iYear . '-' . sprintf( '%02d', $iMonth );
		$sShowPrev = $iPrevYear . '-' . sprintf( '%02d', $iPrevMonth );
		$sShowNext = $iNextYear . '-' . sprintf( '%02d', $iNextMonth );
		
		
		
		//// calendar
		
		$iMonthLastDay = $date->get(Zend_Date::MONTH_DAYS);
		
		$aSelectedDays = array();
		for ($i = 1; $i <= $iMonthLastDay; $i++) {
			$aSelectedDays[] = array(
				'day' => $i,
				'day_arr' => array('year' => $iYear, 'month' => $iMonth, 'day' => $i),
				'day_full' => $sShowCurr . '-' . sprintf( '%02d', $i ),
				'class' => 'curr'
			);
		}
		
		// pad prev month
		
		$iFirstWeekdayOfMonth = $date->get(Zend_Date::WEEKDAY_DIGIT);
		
		if ( $iFirstWeekdayOfMonth ) {
			
			$datePrevMonth = new Zend_Date(array(
				'year' => $iPrevYear,
				'month' => $iPrevMonth,
				'day' => 1		
			));
			
			$iPrevDay = $datePrevMonth->get(Zend_Date::MONTH_DAYS);
			
			for ($i = $iPrevDay; $i > ($iPrevDay - $iFirstWeekdayOfMonth) ; $i--) {
				array_unshift($aSelectedDays, array(
					'day' => $i,
					'day_arr' => array('year' => $iPrevYear, 'month' => $iPrevMonth, 'day' => $i),
					'day_full' => $sShowPrev . '-' . sprintf( '%02d', $i ),
					'class' => 'prev'
				));
			}
		
		}
		
		// pad next month
		
		$iDaysLeft = 7 - (count($aSelectedDays) % 7);
		
		if ( $iDaysLeft && ( $iDaysLeft < 7 ) ) {
			for ($i = 1; $i <= $iDaysLeft; $i++) {
				$aSelectedDays[] = array(
					'day' => $i,
					'day_arr' => array('year' => $iNextYear, 'month' => $iNextMonth, 'day' => $i),
					'day_full' => $sShowNext . '-' . sprintf( '%02d', $i ),
					'class' => 'next'
				);
			}
		}
		
		
		
		
		$sMinDay = $aSelectedDays[0]['day_full'];
		$sMaxDay = $aSelectedDays[ count($aSelectedDays) - 1 ]['day_full'];
		
		$this->aSelectedDays = $aSelectedDays;

		$this->sShowCurr = $sShowCurr;
		$this->sShowPrev = $sShowPrev;
		$this->sShowNext = $sShowNext;
		
		// echo $iMonthLastDay . ' - ' . $sMinDay . ' ' . $sMaxDay . ' &nbsp;&nbsp;&nbsp; ' . $sMinTest . ' ' . $sMaxTest;
		
	}

}



