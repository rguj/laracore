

@php
	//dump(3);
    $element1 = $elements[0];

    // ATTRIBUTES
    $attributes_['descriptionStyle'] = array_key_exists('descriptionStyle', $attributes_) ? $attributes_['descriptionStyle'] : '';
    $attributes_['elementStyle'] = array_key_exists('elementStyle', $attributes_) ? $attributes_['elementStyle'] : '';
    $attributes_['minSearchResult'] = (int)(array_key_exists('minSearchResult', $attributes_) ? $attributes_['minSearchResult'] : -1);

@endphp


<script>
    $(function(){
        let EL = @json($element1['name']);
        let EID = '#' + EL;
        FD[EL] = @json($element1['msg']['default']);

        let ph_s2 = @json($element1['placeholder_s2']);
        
        let minSearchResult = @json($attributes_['minSearchResult']);
        minSearchResult = minSearchResult >= 0  ? minSearchResult : Infinity;
        
        $(EID+'').select2({
            placeholder: ph_s2,
            minimumResultsForSearch: minSearchResult,
            allowClear: true,
            data: FP[EL],
        }).val(FV[EL]).trigger('change').on('change', function(){
            FV2[EL] = $(this).val();
            //$(EID).val($(this).val());
            $(this).closest('.form-group').removeClass('fg-success fg-error').addClass('fg-normal');
            $(this).closest('.form-group').find('span.form-text').html(FD[EL]);
        });

    });
</script>

<label class="">{!! $element1['html']['label'] !!}</label>
<div class="form-group {{ $element1['class_fgs'] }}">
    {{-- <input 
        type          = "hidden" 
        id            = "{{ $element1['name'] }}" 
        name          = "{{ $element1['name'] }}" 
        value         = "" 
    /> --}}
    <select 
        name          = "{{ $element1['name'] }}" 
        id            = "{{ $element1['name'] }}" 
        value         = "{{ $element1['value'] }}"
        class         = "form-control select2 {{ $element1['class'] }}" 
        style         = "{{ $attributes_['elementStyle'] }} width:100%;" 
        {{ $element1['attr']['others'] }}
    ></select>
    <span class="form-text" style="{{ $attributes_['descriptionStyle'] }}">{{ $element1['msg']['current'] }}</span>
</div>

