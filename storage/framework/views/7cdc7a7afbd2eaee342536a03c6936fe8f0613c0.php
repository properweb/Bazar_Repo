<?php echo $__env->make('backend::header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Products</h6>
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

    const Days = 30;

    const loadmsg = "Please wait while we process your request....";

    const columnDefs = [

        {headerName: "Sl No", width: 100, field: "slno", filter: "agSetColumnFilter",headerCheckboxSelection: false,
            headerCheckboxSelectionFilteredOnly: false,
            checkboxSelection: false,
            rowDrag: true},
        {headerName: "Name", width: 100, field: "name", filter: "agSetColumnFilter"},

        {headerName: "SKU", width: 100, field: "sku", filter: "agSetColumnFilter"},
        {headerName: "Default Currency", width: 100, field: "default_currency", filter: "agSetColumnFilter"},
        {headerName: "Case Quantity", width: 100, field: "case_quantity", filter: "agSetColumnFilter"},
        {headerName: "Min Order Qty", width: 100, field: "min_order_qty", filter: "agSetColumnFilter"},
        {headerName: "Availability", width: 100, field: "availability", filter: "agSetColumnFilter"},
        {headerName: "Total Variation", width: 100, field: "variations_count", filter: "agSetColumnFilter"},
        {headerName: "", width: 100, field: "", "editable": false, pinned: 'right', lockPinned: true, cellClass: 'lock-pinned',cellRenderer: function(params) {
                return '<a href="productdetail?id='+params.data.id+'" ><i class="fa fa-eye" aria-hidden="true"></i></a>'
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

        const brand_id = <?php echo e($brand_id); ?>;
        gridOptions.api.showLoadingOverlay();
        fetch("<?php echo e(URL::to('/backend/vendorproduct?brand_id=')); ?>"+brand_id+"").then(function (response) {
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

<?php echo $__env->make('backend::footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /home/bazarcenter/public_html/staging.bazarcenter.ca/Modules/Backend/Resources/views/ProductList.blade.php ENDPATH**/ ?>