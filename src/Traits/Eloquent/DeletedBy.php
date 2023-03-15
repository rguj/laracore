<?php

namespace Rguj\Laracore\Traits\Eloquent;

trait DeletedBy
{
    public function delete()
    {
        $this->deleted_at = now();
        $this->deleted_by = backpack_auth()->id();
        $this->save();

        // fire parent
        return PARENT::delete();
    }
}