@props([
    'isCenter' => false,
    'isFloatEnd' => true,
    'name' => 'Submit',
])
<div class="{{ $isCenter ? 'd-flex justify-content-center' : '' }}">
    <button class="btn btn-primary  {{ $isFloatEnd ? 'float-end' : '' }}  mt-3" id="submit" type="submit">
        <i class="fa fa-spinner fa-spin" style="display:none;"></i>
        <span class="btn-text"><i class="fa fa-paper-plane"></i> {{ $name }}</span>
    </button>
</div>
<script>
    $('input[type=submit]').click(function(event) {
        document.getElementById('submit').disabled = true;
        $(".btn .fa-spinner").show();
        $(this).closest('form').submit();
    });
</script>
