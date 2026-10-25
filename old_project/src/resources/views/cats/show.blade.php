@extends('layouts.cat-profile')

@section('ext_css')
<style>
body {
    background-image: url("images/cat-1045782-3.jpg");
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: fixed;
    }
.navbar-default  {
    background: white;
}
img {
    max-width: 100%;
}
#children details {
    padding: 10px;
}
#children details summary {
    cursor: pointer;
}
#children details:not(:last-of-type) {
    border-bottom: 1px solid #d3e0e9;
}
</style>
@endsection

@section('subtitle', trans('cat.profile'))

@section('cat-content')
    <div class="row">
        <div class="col-md-4">
            @include('cats.partials.profile')
        </div>
        <div class="col-md-8">
            @include('cats.partials.parent-spouse')
            @include('cats.partials.childs')
        </div>
    </div>
@endsection

@section ('ext_js')
<script src="{{ asset('js/plugins/select2.min.js') }}"></script>
<script src="{{ asset('js/plugins/jquery.datetimepicker.js') }}"></script>
@endsection

@section ('script')
<script>
(function () {
    $('select').select2();
    $('input[name=marriage_date]').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        closeOnDateSelect: true,
        scrollInput: false
    });
})();
</script>
<script>
    var parentchilds = [];
    var childcontainer = document.querySelector("#children");
    var childelms = Array.from(childcontainer.children);
    if (childelms) {
        childelms.forEach(child => {
            var parent = child.querySelector(".parent").innerHTML;
            var set = false;
            for (var i=0;i<parentchilds.length;i++) {
                if (parentchilds[i].parent == parent) {
                    parentchilds[i].children.push(child);
                    set = true;
                    break;
                }
            }
            if (set == false) {
                    parentchilds.push({
                        parent: parent,
                        children: [child]
                    });
            }
        });
    }
    document.querySelector("#children").innerHTML = "";
    var parentElm = document.querySelector("#children");
    childcontainer.innerHTML = "";
    parentchilds.forEach(parentchild => {
        var details = document.createElement("details");
        details.innerHTML = `
            <summary style="font-weight: bold;">${parentchild.parent}</summary>
        `;
        parentchild.children.forEach(child => {
            details.append(child.children[1]);
        });
        childcontainer.append(details);
    });
    // open closed details elements for printing
    window.addEventListener('beforeprint',() =>
    {
        const allDetails = document.body.querySelectorAll('details');
        for(let i=0; i<allDetails.length; i++)
        {
            if(allDetails[i].open)
            {
                allDetails[i].dataset.open = '1';
            }
            else
            {
                allDetails[i].setAttribute('open', '');
            }
        }
    });

    // after printing close details elements not opened before
    window.addEventListener('afterprint',() =>
    {
        const allDetails = document.body.querySelectorAll('details');
        for(let i=0; i<allDetails.length; i++)
        {
            if(allDetails[i].dataset.open)
            {
                allDetails[i].dataset.open = '';
            }
            else
            {
                allDetails[i].removeAttribute('open');
            }
        }
    });
</script>
@endsection
