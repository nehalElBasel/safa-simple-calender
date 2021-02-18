<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CalenderTaskController extends Controller
{

    /**
     * @return mixed
     */
    public function calculate()
    {
        //valisate request
        request()->validate([
            'start_date'          => 'required|date|date_format:Y-m-d|after:yesterday',
            'chapter_no'          => 'required|integer|min:1',
            'chapter_sessions_no' => 'required|integer|min:1',
            'days_list'           => 'required|array',
            'days_list.*'         => 'integer',
        ]);

        //set the carbon start date sunday rather than monday
        /*
        0 : sunday
        1 : monday
        2 : tuesday
        3 : wensday
        4 : thursday
        5 : friday
        6 : saterday
         */
        Carbon::setWeekStartsAt(Carbon::SUNDAY); //... @deprecated

        //get post values
        $start_date = new Carbon(request('start_date'));
        $chapter_no = request("chapter_no");
        $chapter_sessions_no = request("chapter_sessions_no");
        $start_date_day = $start_date->dayOfWeek; // get day of week 0 : sunday 6 : saterday
        $days_list = request('days_list');

        //to be sure days is unique ans sorted
        $days_list = array_unique($days_list);
        sort($days_list);
        //return $days_list;

        //get total no of days to finish chapters
        $total_session = $chapter_no * $chapter_sessions_no;

        $calender = [];
        if ($total_session > 0 && $days_list) {
            $divided_count = ceil($total_session / count($days_list)); //if division is 0return error
        } else {
            return response()->json([
                'success' => false,
                'error'   => 'please try again with valid data',
            ], 400);
        }

        /*
        loop to the no of weeks to finish the chapters then loop inner of list daya
        $divided_count means number of weeks and will add 1 for ignored date in first time
         */

        $k = 0;
        //$first_session = false;
        //if start date day exists in days list work so clender start with it for first session $i=0
        if (in_array($start_date_day, $days_list)) {
            $calender[0] = $start_date->format('Y-m-d');
            $k++;
        }

        for ($i = 0; $i < $divided_count + 1; $i++) {

            for ($j = 0; $j < count($days_list); $j++) {

                if (count($calender) >= $total_session) {
                    break;
                }
                //ignore first session days if less than startdate
                //if start date day exists in days list work so clender start with it for first session $i=0
                if (($days_list[$j] < $start_date_day && $i == 0) || $days_list[$j] == $start_date_day && $i == 0) {
                    //echo $start_date->format('Y-m-d');
                    continue;
                }

                $calender[$k] = $start_date->next((int) $days_list[$j])->format('Y-m-d');
                $k++;

            }

        }
        return response()->json([
            'success'  => true,
            'sessions' => $calender,
            'count'    => count($calender),
        ], 200);

    }
}
