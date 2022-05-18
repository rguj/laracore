


@php
    //dd(get_defined_vars()['__data']);

    $element1 = $elements[0];
    $element2 = $elements[1];

    // ATTRIBUTES
    $attributes_['descriptionStyle'] = array_key_exists('descriptionStyle', $attributes_) ? $attributes_['descriptionStyle'] : '';
    $attributes_['elementStyle'] = array_key_exists('elementStyle', $attributes_) ? $attributes_['elementStyle'] : '';
    $attributes_['elementStyle2'] = array_key_exists('elementStyle2', $attributes_) ? $attributes_['elementStyle2'] : '';

@endphp

<script>
$(function() {

    // ---------------------------------------------------
    // TYPE
    
    let EL2 = @json($element2['name']);
    let EID2 = '#' + EL2;
    FD[EL2] = @json($element1['msg']['default']);
    /*FP[EL2] = @json($element2['preloads']);
    FD[EL2] = @json($element2['value']);
    FLD[EL2] = @json($element2['description']);  
    FV[EL2] = FD[EL2];*/
    //let ahsb_types = ['Apartment', 'House #', 'Street', 'Barangay'];
    $(EID2).select2({
        placeholder: @json($element2['placeholder_s2']),
        minimumResultsForSearch: Infinity,
        allowClear: true,
        data: FP[EL2],
    }).val(FV[EL2]).trigger('change').on('change', function(){        
        FV2[EL2] = $(this).val();
        $(this).closest('.form-group').removeClass('fg-success fg-error').addClass('fg-normal');
        $(this).closest('.form-group').find('span.form-text').html(FD[EL2]);
    });
    $(EID2).next().find('.select2-selection').css({'border-top-right-radius':'0px', 'border-bottom-right-radius':'0px', 'height':'calc(1.5em + 1.3rem + 2px)'});


    // ---------------------------------------------------
    // SPECIFY

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
    <div class="input-group">

        {{-- ELEMENT 2 --}}
        <div class="input-group-prepend">
            <select 
                id            = "{{ $element2['name'] }}" 
                name          = "{{ $element2['name'] }}" 
                {{-- value         = "{{ $element2['value'] }}" --}}
                class         = "form-control select2 {{ $element2['class'] }}" 
                style         = "{{ $attributes_['elementStyle2'] }}"                 
                {{ $element2['attr']['others'] }}
            ></select>
        </div>

        {{-- MAIN ELEMENT --}}
        <input 
            type           = "text" 
            name           = "{{ $element1['name'] }}" 
            id             = "{{ $element1['name'] }}" 
            value          = "{{ $element1['value'] }}" 
            placeholder    = "{{ $element1['placeholder'] }}" 
            class          = "form-control {{ $element1['class'] }}" 
            style          = "{{ $attributes_['elementStyle'] }}" 
            {{ $element1['attr']['others'] }}
        />
    </div>

    <span class="form-text" style="{{ $attributes_['descriptionStyle'] }}">{{ $element1['msg']['current'] }}</span>
</div>


