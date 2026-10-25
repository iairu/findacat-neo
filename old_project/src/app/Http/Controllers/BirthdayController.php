<?php

namespace App\Http\Controllers;

use App\Cat;
use Illuminate\Support\Facades\DB;

class BirthdayController extends Controller
{
    public function index()
    {
        $cats = $this->getUpcomingBirthdays(date("m"), date("d"));

        return view('birthdays.index', compact('cats'));
    }

    public function date(int $month = 0, int $day = 0)
    {
        if ($month == 0) {
            $month = date("m");
        }
        if ($day == 0) {
            $day = date("d");
        }
        $cats = $this->getUpcomingBirthdays($month, $day);

        return view('birthdays.index', compact('cats'));
    }

    private function getUpcomingBirthdays(int $month, int $day)
    {
        if ($month <= 12 && $day <= 31 && $month > 0 && $day > 0) {
            $birthdayDateRaw = "concat(YEAR(CURDATE()), '-', RIGHT(dob, 5)) as birthday_date";
            $date = date('Y') . "-" . $month . "-" . $day;
    
            $catBirthdayQuery = Cat::whereNotNull('dob')
                ->select('cats.full_name', 'cats.dob', 'cats.id as cat_id', DB::raw($birthdayDateRaw))
                ->orderBy('birthday_date', 'asc')
                ->havingBetween('birthday_date', [date('Y-m-d', strtotime($date)), date('Y-m-d', strtotime($date. ' + 60 days'))]);
    
            $cats = $catBirthdayQuery->get();
    
            return $cats;
        } else {
            return [];
        }
    }
}
