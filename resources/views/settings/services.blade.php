<style>
    .bootstrap-tagsinput {
        width: 100%;
    }
</style>
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.settings.update-price') }}" method="post">
            @csrf
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <label for="gemstone_report" class="form-label">Service Types</label>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <input type="text" class="form-control" data-role="tagsinput" name="service_types"
                        value="{{ old('service_types', $settings['service_types'] ?? null) }}">
                    @error('service_types')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <label for="languages" class="form-label">Languages</label>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <input type="text" class="form-control" data-role="tagsinput" name="languages"
                        value="{{ old('languages', $settings['languages'] ?? null) }}">
                    @error('languages')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <label for="expertise" class="form-label">Expertise</label>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <input type="text" class="form-control" data-role="tagsinput" name="expertise"
                        value="{{ old('expertise', $settings['expertise'] ?? null) }}">
                    @error('expertise')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <label for="specialization" class="form-label">Professional Title </label>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <input type="text" class="form-control" data-role="tagsinput" name="specialization"
                        value="{{ old('specialization', $settings['specialization'] ?? null) }}">
                    @error('specialization')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-2 col-md-2">
                    <label for="keywords" class="form-label">Keywords </label>
                </div>
                <div class="col-xxl-10 col-md-10">
                    <input type="text" class="form-control" data-role="tagsinput" name="keywords"
                        value="{{ old('keywords', $settings['keywords'] ?? null) }}">
                    @error('keywords')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row gy-4 mt-5">
                <div class="col-xxl-2 col-md-2">
                </div>
                <div class="col-xxl-10 col-md-10">
                    <button class="btn btn-primary float-end mt-3"
                        onclick="document.querySelector('form').submit();">
                        <span class="btn-text"><i class="fa fa-paper-plane"></i> Save </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
