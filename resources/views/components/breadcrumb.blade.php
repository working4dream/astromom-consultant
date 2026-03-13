<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
            @if(isset($backarrow))
                <a href="{{ $backarrow }}" class=" btn btn-primary waves-effect waves-light"><i class="fa fa-arrow-left fa-lg" aria-hidden="true"></i></a> &nbsp;
            @endif 
                  {{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ $backarrow??'#' }}">{{ $li_1 }}</a></li>
                    @if(isset($title))
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    @endif
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->
