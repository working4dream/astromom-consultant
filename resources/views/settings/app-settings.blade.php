<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.settings.update-app-settings') }}" method="post">
            @csrf
            <input type="hidden" name="active_tab" id="active_tab">
            <div class="row gy-4 mt-2">
                <div class="col-xxl-3 col-md-2">
                    <div>
                        <label for="is_ios_review" class="form-label">Is iOS Review</label>
                    </div>
                </div>
                <div class="col-xxl-9 col-md-10">
                    <div>
                        <select class="form-control" id="is_ios_review" name="is_ios_review">
                            <option value="true" {{ ($settings['is_ios_review'] ?? '') == 'true' ? 'selected' : '' }}>True</option>
                            <option value="false" {{ ($settings['is_ios_review'] ?? '') == 'false' ? 'selected' : '' }}>False</option>
                        </select>
                        @error('is_ios_review')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-3 col-md-2">
                    <label class="form-label">Features</label>
                </div>
                <div class="col-xxl-9 col-md-10">
                    <div id="feature-wrapper">
                        @if (!empty($settings['features']))
                            @foreach (json_decode($settings['features']) as $item)
                                <div class="feature-row d-flex align-items-center mb-2">
                                    <div class="form-check form-switch me-3">
                                        <input class="form-check-input feature-switch" 
                                            type="checkbox" 
                                            name="enabled[]"
                                            {{ $item->enabled ? 'checked' : '' }}>
                                    </div>
                                    <input type="text" 
                                        name="features[]" 
                                        class="form-control me-2" 
                                        value="{{ $item->name }}">
                                    <button type="button" class="btn btn-danger remove-row">Remove</button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary mt-2">Add Feature</button>
                    @error('features')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row gy-4 mt-2">
                <div class="col-xxl-3 col-md-2">
                </div>
                <div class="col-xxl-9 col-md-10">
                    <button class="btn btn-primary float-end mt-3" onclick="document.querySelector('form').submit();">
                        <span class="btn-text"><i class="fa fa-paper-plane"></i> Save </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#add-row').click(function () {
            let newRow = `
                <div class="feature-row d-flex align-items-center mb-2">
    
                    <div class="form-check form-switch me-3">
                        <input class="form-check-input feature-switch" type="checkbox" name="enabled[]">
                    </div>
    
                    <input type="text" name="features[]" class="form-control me-2" placeholder="Enter Feature">
    
                    <button type="button" class="btn btn-danger remove-row">Remove</button>
                </div>
            `;
            $('#feature-wrapper').append(newRow);
        });
        $(document).on('click', '.remove-row', function () {
            $(this).closest('.feature-row').remove();
        });
    });
    </script>
    
    
