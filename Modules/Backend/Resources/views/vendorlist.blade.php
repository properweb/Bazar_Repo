@include('backend::header')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">All Brands</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <div class="table-responsive p-0">
            <div id="myGrid" style="height: 600px;width:97%;" class="ag-theme-balham"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.10.3/xlsx.core.min.js"></script>
<script src="https://unpkg.com/ag-grid-enterprise@19.0.0/dist/ag-grid-enterprise.min.js"></script>
<script>



    var loadmsg = "Please wait while we process your request....";

    var columnDefs = [

        {headerName: "Sl No", width: 100, field: "slno", filter: "agSetColumnFilter",headerCheckboxSelection: false,
            headerCheckboxSelectionFilteredOnly: false,
            checkboxSelection: false,
            rowDrag: true},
        {headerName: "Name", width: 100, field: "name", filter: "agSetColumnFilter"},
        {headerName: "Email", width: 100, field: "email", filter: "agSetColumnFilter"},
        {headerName: "Gender", width: 100, field: "gender", filter: "agSetColumnFilter"},
        {headerName: "Reg Date", width: 100, field: "created_at", filter: "agSetColumnFilter"},
        {headerName: "Active", width: 100, field: "active_status", filter: "agSetColumnFilter"},
        {headerName: "Live Status", width: 100, field: "live_status", filter: "agSetColumnFilter"},
        {headerName: "Primary Category", width: 100, field: "prim_cat", filter: "agSetColumnFilter"},
        {headerName: "Country", width: 100, field: "country", filter: "agSetColumnFilter"},
        {headerName: "id", width: 100, field: "vendor_id", filter: "agSetColumnFilter",hide:true},
        {headerName: "user_id", width: 100, field: "user_id", filter: "agSetColumnFilter",hide:true},
        {headerName: "Brand", width: 100, field: "brand_name", filter: "agSetColumnFilter"},
        {headerName: "Number of wholesale products you sell", width: 100, field: "num_products_sell", filter: "agSetColumnFilter"},
        {headerName: "Phone", width: 100, field: "phone_number", filter: "agSetColumnFilter"},
        {headerName: "Language", width: 100, field: "language", filter: "agSetColumnFilter"},
        {headerName: "Year of Established", width: 100, field: "established_year", filter: "agSetColumnFilter"},
        {headerName: "", width: 100, field: "", "editable": false, pinned: 'right', lockPinned: true, cellClass: 'lock-pinned',cellRenderer: function(params) {
                return '<a href="vendordetails?id='+params.data.vendor_id+'" alt="Details"><i class="fa fa-user" aria-hidden="true"></i></a> <a href="product?brand_id='+params.data.vendor_id+'" alt="Products"><i class="fa fa-product-hunt" aria-hidden="true"></i></a> <a href="vendororder?brand_id='+params.data.vendor_id+'" alt="Orders"><i class="fa fa-usd" aria-hidden="true"></i></a>'
            }},

    ];
    var gridDiv;

    var gridOptions = {
        defaultColDef: {
            // allow every column to be aggregated
            enableValue: true,
            // allow every column to be grouped
            enableRowGroup: true,
            // allow every column to be pivoted
            enablePivot: true
        },
        columnDefs: columnDefs,
        rowGroupPanelShow: 'false',
        rowDragManaged: true,
        enableSorting: true,
        showToolPanel: false,
        floatingFilter: true,
        enableFilter: true,
        enableColResize: true,
        colResizeDefault: 'shift',
        animateRows: true,
        enableCellChangeFlash: true,
        onFirstDataRendered: onFirstDataRendered,
        getRowNodeId: function (data) {
            // return data.saleid;
        },

        //rowSelection: 'multiple',
        overlayLoadingTemplate: '<span class="ag-overlay-loading-center">' + loadmsg + '</span>',
        defaultExportParams: {
            //This is necessary for sheetJs to read the resultant file
            suppressTextAsCDATA: true
        },
        onGridReady: function (params) {
            params.api.sizeColumnsToFit();
        },
        rowSelection: 'multiple'

    };
    document.addEventListener('DOMContentLoaded', function () {
        gridDiv = document.querySelector('#myGrid');
        new agGrid.Grid(gridDiv, gridOptions);
        //createData(Days, TxnDate, PostedDate, Status,flags);
        createData();
    });


    function createData() {

        var param = '';
        gridOptions.api.showLoadingOverlay();
        fetch("{{URL::to('/backend/vendoralllist')}}").then(function (response) {
            return response.json();
        }).then(function (data) {
            gridOptions.api.hideOverlay();
            gridOptions.api.setRowData(data.data);
            autoSizeAllcols();
        });
    }
    function onFirstDataRendered (params) {
        params.api.sizeColumnsToFit()
        window.setTimeout(() => {
            const colIds = params.columnApi.getAllColumns().map(c => c.colId)
            params.columnApi.autoSizeColumns(colIds)
        }, 50)
    }



    function filter() {
        createData();
    }


    function redrawRow(id, url) {
        var rows = [];
        var rowNode = gridOptions.api.getRowNode(id);
        gridOptions.api.showLoadingOverlay();
        fetch(url)
            .then(function (response) {
                return response.json();
            }).then(function (res) {
            gridOptions.api.hideOverlay();
            var row = gridOptions.api.getDisplayedRowAtIndex(res.data.rowIndex);
            rows.push(row);
            rowNode.setData(res.data);
            gridOptions.api.redrawRows({rowNodes: rows});
            autoSizeAllcols();
        });
    }




</script>

@include('backend::footer')
