<?php

namespace App\Http\Controllers;

use App\Cat;
use App\Couple;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyActionsController extends Controller
{
    /**
     * Set sire for a cat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cat  $cat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setSire(Request $request, Cat $cat)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $request->validate([
                'set_sire_id' => 'nullable',
                'set_sire'    => 'required_without:set_sire_id|max:255',
            ]);

            if ($request->get('set_sire_id')) {
                $cat->sire_id = $request->get('set_sire_id');
                $cat->save();
            } else {
                $sire = new Cat;
                $sire->id = Uuid::uuid4()->toString();
                $sire->full_name = $request->get('set_sire');
                $sire->gender_id = 1;

                $cat->setSire($sire);
            }

            return back();
        } else {
            return redirect('/');
        }
    }

    /**
     * Set dam for a cat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cat  $cat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setDam(Request $request, Cat $cat)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $request->validate([
                'set_dam_id' => 'nullable',
                'set_dam'    => 'required_without:set_dam_id|max:255',
            ]);

            if ($request->get('set_dam_id')) {
                $cat->dam_id = $request->get('set_dam_id');
                $cat->save();
            } else {
                $dam = new Cat;
                $dam->id = Uuid::uuid4()->toString();
                $dam->full_name = $request->get('set_dam');
                $dam->gender_id = 2;

                $cat->setDam($dam);
            }

            return back();
        } else {
            return redirect('/');
        }
    }

    /**
     * Add child for a cat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cat  $cat
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addChild(Request $request, Cat $cat)
    {
        if (Auth::user() && Auth::user()->is_admin) {
            $request->validate([
                'add_child_name'        => 'required|string|max:255',
                'add_child_gender_id'   => 'required|in:1,2',
                'add_child_parent_id'   => 'nullable|exists:couples,id',
            ]);

            $child = new Cat;
            $child->id = Uuid::uuid4()->toString();
            $child->full_name = $request->get('add_child_name');
            $child->gender_id = $request->get('add_child_gender_id');

            \DB::beginTransaction();
            $child->save();

            if ($request->get('add_child_parent_id')) {
                $couple = Couple::find($request->get('add_child_parent_id'));
                $child->sire_id = $couple->husband_id;
                $child->dam_id = $couple->wife_id;
                $child->save();
            } else {
                if ($cat->gender_id == 1) {
                    $child->setSire($cat);
                } else {
                    $child->setDam($cat);
                }

            }

            \DB::commit();

            return back();
        } else {
            return redirect('/');
        }
    }

    // /**
    //  * Add wife for male cat.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Cat  $cat
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function addWife(Request $request, Cat $cat)
    // {
    //     $request->validate([
    //         'set_wife_id'   => 'nullable',
    //         'set_wife'      => 'required_without:set_wife_id|max:255',
    //         'marriage_date' => 'nullable|date|date_format:Y-m-d',
    //     ]);

    //     if ($request->get('set_wife_id')) {
    //         $wife = Cat::findOrFail($request->get('set_wife_id'));
    //     } else {
    //         $wife = new Cat;
    //         $wife->id = Uuid::uuid4()->toString();
    //         $wife->full_name = $request->get('set_wife');
    //         $wife->gender_id = 2;
    //     }

    //     $cat->addWife($wife, $request->get('marriage_date'));

    //     return back();
    // }

    // /**
    //  * Add husband for female cat.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Cat  $cat
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function addHusband(Request $request, Cat $cat)
    // {
    //     $this->validate($request, [
    //         'set_husband_id' => 'nullable',
    //         'set_husband'    => 'required_without:set_husband_id|max:255',
    //         'marriage_date'  => 'nullable|date|date_format:Y-m-d',
    //     ]);

    //     if ($request->get('set_husband_id')) {
    //         $husband = Cat::findOrFail($request->get('set_husband_id'));
    //     } else {
    //         $husband = new Cat;
    //         $husband->id = Uuid::uuid4()->toString();
    //         $husband->full_name = $request->get('set_husband');
    //         $husband->gender_id = 1;
    //     }

    //     $cat->addHusband($husband, $request->get('marriage_date'));

    //     return back();
    // }

    // /**
    //  * Set parent for a cat.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  \App\Cat  $cat
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function setParent(Request $request, Cat $cat)
    // {
    //     $cat->parent_id = $request->get('set_parent_id');
    //     $cat->save();

    //     return redirect()->route('cats.show', $cat);
    // }
}
