<?php

namespace App\Policies;

use App\Cat;
use App\Couple;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouplePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can edit the couple.
     *
     * @param  \App\Cat  $cat
     * @param  \App\Couple  $couple
     * @return mixed
     */
    public function edit(Cat $cat, Couple $couple)
    {
        return true;
    }
}
