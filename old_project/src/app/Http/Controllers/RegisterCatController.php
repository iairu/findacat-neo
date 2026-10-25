<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Cat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;

class RegisterCatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new cats as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    // use RegistersUsers; // todo maybe remove

    /**
     * Where to redirect after registration.
     *
     * @var string
     */
    protected $redirectTo = '/register-cat';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
    }


    public function index()
    {
        if (Auth::user() && Auth::user()->is_admin) {
            return view('cats.register-cat');
        } else {
            return redirect('/');
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'full_name' => 'required|string|max:255',
            'gender_id' => 'required|numeric|in:1,2',
        ]);
    }

    /**
     * Create a new cat instance after a valid registration.
     *
     * @param  array  $data
     * @return Cat
     */
    protected function create(Request $request)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $cat = Cat::create([
                'id' => Uuid::uuid4()->toString(),
                'titles_before_name' => $request->get('titles_before_name'),
                'full_name' => $request->get('full_name'),
                'titles_after_name' => $request->get('titles_after_name'),
                'gender_id' => $request->get('gender_id'),
                'breed' => $request->get('breed'),
                'ems_color' => $request->get('ems_color'),
                'chip_number' => $request->get('chip_number'),
                'genetic_tests' => $request->get('genetic_tests'),
                'dob' => $request->get('dob'),
                'breeding_station' => $request->get('breeding_station'),
                'country_code' => $request->get('country_code'),
                'alternative_name' => $request->get('alternative_name'),
                'print_name_r1' => $request->get('print_name_r1'),
                'print_name_r2' => $request->get('print_name_r2'),
                'dod' => $request->get('dod'),
                'original_reg_num' => $request->get('original_reg_num'),
                'last_reg_num' => $request->get('last_reg_num'),
                'reg_num_2' => $request->get('reg_num_2'),
                'reg_num_3' => $request->get('reg_num_3'),
                'notes' => $request->get('notes'),
                'breeder' => $request->get('breeder'),
                'current_owner' => $request->get('current_owner'),
                'country_of_origin' => $request->get('country_of_origin'),
                'country' => $request->get('country'),
                'ownership_notes' => $request->get('ownership_notes'),
                'personal_info' => $request->get('personal_info')
            ]);
            $cat->save();

            return view('cats.register-cat');
        } else {
            return redirect('/');
        }
    }
}
