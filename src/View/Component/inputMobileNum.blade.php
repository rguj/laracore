

@php
$element1 = $elements[0];
$prefix = $element1['preloads']['prefix'];
$val = ltrim($element1['value'], $prefix);

// ATTRIBUTES
$attributes_['descriptionStyle'] = array_key_exists('descriptionStyle', $attributes_) ? $attributes_['descriptionStyle'] : '';
$attributes_['elementStyle'] = array_key_exists('elementStyle', $attributes_) ? $attributes_['elementStyle'] : '';

@endphp

<script>
$(function(){
    let EL = @json($element1['name']);
    let EID = '#' + EL;
    FD[EL] = @json($element1['msg']['default']);
    FV[EL] = @json($val);

    let dmin = moment();

    $(EID).inputmask({
        //regex: '[0-9]{3}\-[0-9]{3}\-[0-9]{4}',
        mask: '9999999999',
        clearIncomplete: true,
    });

    $(EID).on('input', function(){
        FV2[EL] = $(this).val();
        $(this).closest('.form-group').removeClass('fg-success fg-error').addClass('fg-normal');
        $(this).closest('.form-group').find('span.form-text').html(FD[EL]);
    });
})
</script>
<label class="">{!! $element1['html']['label'] !!}</label>
<div class="form-group {{ $element1['class_fgs'] }}">
<div class="input-group">
    <div class="input-group-prepend"><span class="input-group-text">{{ $prefix }}</span></div>
    <input 
        type           = "text" 
        name           = "{{ $element1['name'] }}" 
        id             = "{{ $element1['name'] }}" 
        value          = "{{ $val }}" 
        placeholder    = "{{ $element1['placeholder'] }}" 
        class          = "form-control {{ $element1['class'] }}" 
        style          = "{{ $attributes_['elementStyle'] }} border-top-right-radius: 0.42rem;  border-bottom-right-radius: 0.42rem;" 
        {{ $element1['attr']['others'] }}
    />
    <span class="form-text" style="{{ $attributes_['descriptionStyle'] }}">{{ $element1['msg']['current'] }}</span>
</div>
</div>
