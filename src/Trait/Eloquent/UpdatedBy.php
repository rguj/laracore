<?php

namespace Rguj\Laracore\Trait\Eloquent;

trait UpdatedBy
{
    public function update(array $attributes = [], array $options = [])
    {
        $attributes['updated_by'] = backpack_auth()->id();

        // fire parent
        return PARENT::update($attributes, $options);
    }
}