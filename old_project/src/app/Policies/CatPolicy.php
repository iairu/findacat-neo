<?php

namespace App\Policies;

use App\Cat;
use Illuminate\Auth\Access\HandlesAuthorization;

class CatPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can edit the cat data.
     *
     * @param  \App\Cat  $cat
     * @param  \App\Cat  $editableCat
     * @return mixed
     */
    public function edit(Cat $cat, Cat $editableCat)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the cat.
     *
     * @param  \App\Cat  $cat
     * @param  \App\Cat  $editableCat
     * @return mixed
     */
    public function delete(Cat $cat, Cat $editableCat)
    {
        return true;
    }
}
