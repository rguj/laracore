
@php
    use App\Libraries\AppFn;
    use App\Libraries\DT;
    use App\Libraries\WebClient;

	//dd(3);
    $element1 = $elements[0];

    // ATTRIBUTES
    $attributes_['descriptionStyle'] = array_key_exists('descriptionStyle', $attributes_) ? $attributes_['descriptionStyle'] : '';
    $attributes_['elementStyle'] = array_key_exists('elementStyle', $attributes_) ? $attributes_['elementStyle'] : '';
    
    $minDate = DT::STR_TryParse($with['form']['field_rules'][$element1['name']]['date_min'], '', ['UTC', WebClient::getTimeZone()]);
    $maxDate = DT::STR_TryParse($with['form']['field_rules'][$element1['name']]['date_max'], '', ['UTC', WebClient::getTimeZone()]);
    $defDate = DT::STR_TryParse($element1['value'] ?? '', '', ['UTC', WebClient::getTimeZone()]);
    $defDate = AppFn::STR_IsBlankSpace($defDate) ? null : $defDate;

    //dd($defDate);
@endphp


<script>
    $(function(){

        let EL = @json($element1['name']);
        let EID = '#' + EL;
        FD[EL] = @json($element1['msg']['default']);
        FR[EL]['format_in'] = $DT.translatePHPFormat(FR[EL]['format_in']);
        FV2[EL] = FV[EL] = null;

        let dfmt     = $DT.translatePHPFormat(@json(DT::getStandardDTFormat()));
        let dmin     = moment(@json($minDate), dfmt);
        let dmax     = moment(@json($maxDate), dfmt);
        let ddef     = moment(@json($defDate), dfmt);

        // create datetimepicker
        let options = { format:  FR[EL]['format_in'], allowInputToggle: true, buttons: {showClear: true} };
        if(dmin.isValid() === true) { options['minDate'] = dmin; }
        if(dmax.isValid() === true) { options['maxDate'] = dmax; }
        FV2[EL] = FV[EL] = (ddef.isValid() === true ? ddef.format(dfmt) : null);
        options['date'] = FV[EL];
        $('#__'+EL+'__').datetimepicker(options);

        $(EID).on('input', function(){
            FV2[EL] = $(this).val();
            $(this).closest('.form-group').removeClass('fg-success fg-error').addClass('fg-normal');
            $(this).closest('.form-group').find('span.form-text').html(FD[EL]);
        });

    });
</script>
<label class="">{!! $element1['html']['label'] !!}</label>
<div class="form-group {{ $element1['class_fgs'] }}">
    <div class="input-group date" id="__{{ $element1['name'] }}__" data-target-input="nearest">
        <input 
            type           = "text" 
            name           = "{{ $element1['name'] }}" 
            id             = "{{ $element1['name'] }}" 
            {{-- value          = "{{ $element1['value'] }}"  --}}
            placeholder    = "{{ $element1['placeholder'] }}" 
            class          = "form-control datetimepicker-input {{ $element1['class'] }}" 
            style          = "{{ $attributes_['elementStyle'] }}" 
            data-target    = "#__{{ $element1['name'] }}__"
            {{ $element1['attr']['others'] }}
        />
        <div class="input-group-append" data-target="#__{{ $element1['name'] }}__" data-toggle="datetimepicker">
            <span class="input-group-text"><i class="ki ki-calendar"></i></span>
        </div>
    </div>
    <span class="form-text" style="{{ $attributes_['descriptionStyle'] }}">{{ $element1['msg']['current'] }}</span>
</div>
