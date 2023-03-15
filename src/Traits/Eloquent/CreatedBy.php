<?php

namespace Rguj\Laracore\Traits\Eloquent;

trait CreatedBy
{
    public function save(array $options = []) {
        $this->created_by = backpack_auth()->id();
                
        // fire parent
        return PARENT::save($options);
    }
}