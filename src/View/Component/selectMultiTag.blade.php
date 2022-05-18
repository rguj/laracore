

@php
    $element1 = $elements[0];

    // ATTRIBUTES
    $attributes_['descriptionStyle'] = array_key_exists('descriptionStyle', $attributes_) ? $attributes_['descriptionStyle'] : '';
    $attributes_['elementStyle'] = array_key_exists('elementStyle', $attributes_) ? $attributes_['elementStyle'] : '';
    $attributes_['minSearchResult'] = (int)(array_key_exists('minSearchResult', $attributes_) ? $attributes_['minSearchResult'] : -1);
    $attributes_['hasClrBtn'] = (bool)(array_key_exists('hasClrBtn', $attributes_) ? $attributes_['hasClrBtn']==='true' : false);

@endphp

<script>
    $(function(){
        let EL = @json($element1['name']);
        let EID = '#' + EL;
        FD[EL] = @json($element1['msg']['default']);

        let ph_s2 = @json($element1['placeholder_s2']);

        let minSearchResult = @json($attributes_['minSearchResult']);
        minSearchResult = minSearchResult >= 0  ? minSearchResult : Infinity;
        
        let obj1 = [];
        $.each(FV[EL], function(k,v){
            obj1.push({'id':v, 'text':v, 'tags':v});
        });

        $(EID+'').select2({
            //allowClear: true,
            placeholder: ph_s2,
            minimumResultsForSearch: minSearchResult,
            tags: true,
            tokenSeparators: [','],
            data: obj1,
        }).val(FV[EL]).trigger('change').on('change', function(){
            FV2[EL] = $(this).val();
            $(this).closest('.form-group').removeClass('fg-success fg-error').addClass('fg-normal');
            $(this).closest('.form-group').find('span.form-text').html(FD[EL]);

            if(!$PHP.empty(FV2[EL])) {
                $('.btnClr-'+EL).show();
            } else {
                $('.btnClr-'+EL).hide();
            }
        }).trigger('change');

        $('.btnClr-'+EL).on('click', function(e){
            e.preventDefault();
            $(EID).val(null).trigger('change');
        })

    });
</script>

<label class="" style="width:100%;">
    {!! $element1['html']['label'] !!}
    @if($attributes_['hasClrBtn'] === true)
        <button class="btnClr-{{ $element1['name'] }} btn btn-link p-0 float-right" style="height:17px;" tabindex="-1">Clear</button>
        {{-- <script>
            $(function(){
                
            });
        </script> --}}
    @endif    
</label>
<div class="form-group {{ $element1['class_fgs'] }}">
    <select 
        multiple
        name          = "{{ $element1['name'] }}[]" 
        id            = "{{ $element1['name'] }}" 
        class         = "form-control select2 my-1 {{ $element1['class'] }}" 
        style         = "{{ $attributes_['elementStyle'] }} width:100%; min-height:38.39px;"
        {{ $element1['attr']['others'] }}
    ></select>    
    <span class="form-text" style="{{ $attributes_['descriptionStyle'] }}">{{ $element1['msg']['current'] }}</span>
</div>

