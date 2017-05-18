<?php

class BraveTime extends Brave {
	
    function recentTime($interval = '', $num = 1, $positive = true) {
    	$format = '';
    	switch($interval) {
    		case 'd':
    			$format = $num . ' day';
    			break;
    		case 'w':
    			$format = $num . ' week';
    			break;
    		case 'm':
    			$format = $num . ' month';
    			break;
    		case 'y':
    			$format = $num . ' year';
    			break;
    		default:
    			break;
    	}
    	
    	$format = $positive ? $format : '-' . $format;
    	
    	$now = time();
    	$time = strtotime($format,$now);
    	
    	return array('begin' => date("Y-m-d",$time),'end' => date("Y-m-d",$now));
    }
    
    function getOneHalfMonthDay($month = null) {
    	$return = array();
    	$week = $this->code('week');
    	if ($month) {
			$tmp = explode("-",$month);
			$time = strtotime($month . '-01');
    	} else {
    		$time = time();
    	}
    	
    	$currentYear = $tmp[0] ? $tmp[0] : date("Y");
    	$currentMonth = $tmp[1] ? $tmp[1] : date("m");
    	$currentDays = $tmp[0] ?  date("t",$time) : date("t");
    	$dateFormat = date("Y-m-",$time);
    	
    	for($i = 1; $i <= $currentDays; $i++) {
    		$date = $dateFormat . str_pad($i,2,0,STR_PAD_LEFT);
    		$return[$date] = array(
    			'no' => $i,
    			'date' => $date,
    			'week' => $week[date('w',strtotime($date))]['name'],
    		);
    	}
    	
    	if((int)$currentMonth == 12) {
    		$dateFormat = (date("Y") + 1) . '-01-';
    	} else {
    		$dateFormat = $currentYear . '-' . str_pad((int)$currentMonth + 1,2,0,STR_PAD_LEFT) . '-';
    	}
    	
    	for($i = 1; $i <= 15; $i++) {
    		$date = $dateFormat . str_pad($i,2,0,STR_PAD_LEFT);
    		$return[$date] = array(
    			'no' => $i,
    			'date' => $date,
    			'week' => $week[date('w',strtotime($date))]['name'],
    		);
    	}
    	
    	return $return;
    }
    
	function getDateRange($start, $end, $am, $pm) {
		$return = array();
		$startTime = strtotime($start);
    	$endTime = strtotime($end);
    	$startDate = date("Y-m-d",$startTime);
    	$startDateHour = date("G",$startTime);
    	$endDate = date("Y-m-d",$endTime);
    	$endDateHour = date("G",$endTime);
    	
		$am = preg_split("/-/",$am,-1,PREG_SPLIT_NO_EMPTY);
    	$amStart = $am[0];
    	$amEnd = $am[1];
    	$pm = preg_split("/-/",$pm,-1,PREG_SPLIT_NO_EMPTY);
    	$pmStart = $pm[0];
    	$pmEnd = $pm[1];
	
		if($startDate == $endDate) {
			if($startDateHour < $amStart) {
				if($endDateHour > $pmStart) {
					$return[$startDate. '-1'] = 1;
					$return[$startDate. '-2'] = 1;
				} elseif($endDateHour > $amStart && $endDateHour <= $pmStart) {
					$return[$startDate. '-1'] = 1;
				}
			} elseif($startDateHour >= $amStart && $startDateHour < $amEnd) {
				if($endDateHour > $startDateHour && $endDateHour > $pmStart) {
					$return[$startDate. '-1'] = 1;
					$return[$startDate. '-2'] = 1;
				} elseif($endDateHour > $startDateHour && $endDateHour <= $pmStart) {
					$return[$startDate. '-1'] = 1;
				}
			} elseif($startDateHour >= $amEnd && $startDateHour < $pmEnd) {
				if($endDateHour > $startDateHour && $endDateHour > $pmStart) {
					$return[$startDate. '-2'] = 1;
				}
			}
		} else {
			$days = (strtotime($endDate) - strtotime($startDate))/24/3600;
			for ($i= 0; $i <= $days; $i++) {
				$tmpStartDate = date("Y-m-d",strtotime("+{$i} day",strtotime($startDate)));
				if($i == 0) {
					$tmpStartDateHour = $startDateHour;
					$tmpEndDateHour = 23;
				} elseif($i == $days) {
					$tmpStartDateHour = 0;
					$tmpEndDateHour = $endDateHour;
				} else {
					$tmpStartDateHour = 0;
					$tmpEndDateHour = 23;
				}
	
				if($tmpStartDateHour < $amStart) {
					if($tmpEndDateHour > $pmStart) {
						$return[$tmpStartDate. '-1'] = 1;
						$return[$tmpStartDate. '-2'] = 1;
					} elseif($tmpEndDateHour > $amStart && $tmpEndDateHour <= $pmStart) {
						$return[$tmpStartDate. '-1'] = 1;
					}
				} elseif($tmpStartDateHour >= $amStart && $tmpStartDateHour < $amEnd) {
					if($tmpEndDateHour > $tmpStartDateHour && $tmpEndDateHour > $pmStart) {
						$return[$tmpStartDate. '-1'] = 1;
						$return[$tmpStartDate. '-2'] = 1;
					} elseif($tmpEndDateHour > $tmpStartDateHour && $tmpEndDateHour <= $pmStart) {
						$return[$tmpStartDate. '-1'] = 1;
					}
				} elseif($tmpStartDateHour >= $amEnd && $tmpStartDateHour < $pmEnd) {
					if($tmpEndDateHour > $tmpStartDateHour && $tmpEndDateHour > $pmStart) {
						$return[$tmpStartDate. '-2'] = 1;
					}
				}
			}
	 	}
	
		return $return;
	}
}

?>
