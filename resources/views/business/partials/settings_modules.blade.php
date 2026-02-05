<div class="pos-tab-content">
	<div class="row">
	@if(!empty($modules))
		<h4>@lang('lang_v1.enable_disable_modules')</h4>
		@foreach($modules as $k => $v)
            <div class="col-sm-4">
                <div class="form-group">
                    <div class="checkbox">
                    <br>
                      <label>
                        {!! Form::checkbox('enabled_modules[]', $k,  in_array($k, $enabled_modules) , 
                        ['class' => 'input-icheck']); !!} {{$v['name']}}
                      </label>
                      @if(!empty($v['tooltip'])) @show_tooltip($v['tooltip']) @endif
                    </div>
                </div>
            </div>
        @endforeach
	@endif
	<div class="col-sm-4">
    <div class="form-group">
        <div class="checkbox">
            <label>
                {!! Form::checkbox('enabled_modules[]', 'whatsapp_module', in_array('whatsapp_module', $enabled_modules), ['class' => 'input-icheck']) !!}
                WhatsApp Notifications
            </label>
        </div>
    </div>
    </div>

	</div>
</div>