
{{--
    Reserved keywords
        data
        render
        resolveView
        shouldRender
        view
        withAttributes
        withName
    --}}

@php
	//dump(3);
    //dd(get_defined_vars()['__data']);
    $element1 = $elements[0];

    // ATTRIBUTES
    $attributes_['descriptionStyle'] = array_key_exists('descriptionStyle', $attributes_) ? $attributes_['descriptionStyle'] : '';
    $attributes_['elementStyle'] = array_key_exists('elementStyle', $attributes_) ? $attributes_['elementStyle'] : '';
    $attributes_['inputType'] = array_key_exists('inputType', $attributes_) ? $attributes_['inputType'] : 'text';

@endphp

<script>

    $(function() {
        let EL = @json($element1['name']);
        let EID = '#' + EL;
        FD[EL] = @json($element1['msg']['default']);

        $(EID).on('input', function(){
            FV2[EL] = $(this).val();
            $(this).closest('.form-group').removeClass('fg-success fg-error').addClass('fg-normal');
            $(this).closest('.form-group').find('span.form-text').html(FD[EL]);
        });
        
        
    });
</script>

<label class="">{!! $element1['html']['label'] !!}</label>
<div class="form-group {{ $element1['class_fgs'] }}">
    <input 
        type           = "{{ $attributes_['inputType'] }}" 
        name           = "{{ $element1['name'] }}" 
        id             = "{{ $element1['name'] }}" 
        value          = "{{ $element1['value'] }}" 
        placeholder    = "{{ $element1['placeholder'] }}" 
        class          = "form-control {{ $element1['class'] }}" 
        style          = "{{ $attributes_['elementStyle'] }}" 
        {{ $element1['attr']['others'] }}
    />
    <span class="form-text" style="{{ $attributes_['descriptionStyle'] }}">{{ $element1['msg']['current'] }}</span>
</div>

