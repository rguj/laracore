<?php

use Rguj\Laracore\Middleware\ClientInstanceMiddleware;



/* -----------------------------------------------
 * CURRENT USER
 */

function cuser_has_role2($val)
{
    /** @var \Spatie\Permission\Models\Permission */
    $user = auth()->user();
    return cuser_is_auth() ? $user->hasRole($val) : false;
    // return arr_colval_exists($needle, config('user.types') ?? [], $col_key, $strict);
}

function cuser_has_role_id($val)
{
    return cuser_has_role2($val);
    // return cuser_has_role($needle, 'role_id', $strict);
}

function cuser_has_role_role($val)
{
    return cuser_has_role2($val);
    // return cuser_has_role($needle, 'role', $strict);
}

// function cuser_has_role_short($needle, bool $strict=false)
// {
//     return cuser_has_role($needle, 'short', $strict);
// }



function cuser_id()
{
    return cuser_is_auth() ? auth()->id() : null;
}

function cuser_is_verified()
{
    return cuser_is_auth() ? (bool)(cuser_data()['verify']['email']['is_verified'] ?? false) : false;
}

function cuser_is_active()
{
    return cuser_is_auth() && cuser_data()['state']['is_active'] === 1;
}


// function cuser_data()
// {
//     return cuser_is_auth() ? auth()->user() : null;
// }
function cuser_data()
{
    return config('user');
}

function cuser_is_auth()
{
    return auth()->check() && auth()->id();
}

function cuser_is_guest()
{
    return !cuser_is_auth();
}

/**
 * Get config roles
 *
 * @return array
 */
function z_roles_arr()
{
    return config('z.base.roles');
}

function z_roles(bool $objectKey = true)
{
    $r = [];
    foreach(z_roles_arr() as $k=>$v) {
        $r[$v[1]] = $v[0];
    }
    return $objectKey ? (object)$r : $r;
}

function z_roles_names()
{
    return array_column(z_roles_arr(), 1);
}

function z_roles_ids()
{
    return array_column(z_roles_arr(), 0);
}


function cuser_is_admin()
{
    return cuser_has_role_id(z_roles()->admin);
}

function cuser_is_rstaff()
{
    return cuser_has_role_id(z_roles()->rstaff);
}

function cuser_is_eofficer()
{
    return cuser_has_role_id(z_roles()->eofficer);
}

function cuser_is_student()
{
    return cuser_has_role_id(z_roles()->student);
}

function cuser_is_cashier()
{
    return cuser_has_role_id(z_roles()->cashier);
}

function cuser_is_cstaff()
{
    return cuser_has_role_id(z_roles()->cstaff);
}

function cuser_is_jappl()
{
    return cuser_has_role_id(z_roles()->jappl);
}

function cuser_is_osdsstaff()
{
    return cuser_has_role_id(z_roles()->osdsstaff);
}

function cuser_is_misstaff()
{
    return cuser_has_role_id(z_roles()->misstaff);
}





function cuser_has_rstaffp($needle, string $col_key, bool $strict=false)
{
    return arr_colval_exists($needle, config('user.rstaffs_programs'), $col_key, $strict);
}

function cuser_has_rstaffp_id($needle, bool $strict=false)
{
    return cuser_has_rstaffp($needle, 'program_id', $strict);
}

function cuser_has_rstaffp_code($needle, bool $strict=false)
{
    return cuser_has_rstaffp($needle, 'code', $strict);
}

function cuser_has_rstaffp_program($needle, bool $strict=false)
{
    return cuser_has_rstaffp($needle, 'program', $strict);
}

function cuser_has_eofficerp($needle, string $col_key, bool $strict=false)
{
    return arr_colval_exists($needle, config('user.eofficers_programs'), $col_key, $strict);
}

function cuser_has_eofficerp_id($needle, bool $strict=false)
{
    return cuser_has_eofficerp($needle, 'program_id', $strict);
}

function cuser_has_eofficerp_code($needle, bool $strict=false)
{
    return cuser_has_eofficerp($needle, 'code', $strict);
}

function cuser_has_eofficerp_program($needle, bool $strict=false)
{
    return cuser_has_eofficerp($needle, 'program', $strict);
}
