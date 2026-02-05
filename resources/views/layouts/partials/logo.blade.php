<div class="row text-center">
	@if(file_exists(public_path('img/logo-jumtukaay.png')))
		<div class="col-xs-12">
			<img src="img/logo-jumtukaay.png" class="img-rounded" alt="Logo" width="150" style="margin-bottom: 30px;">
		</div>
	@else
    	<h1 class="text-center page-header">{{ config('app.name', 'ultimatePOS') }}</h1>
    @endif
</div>