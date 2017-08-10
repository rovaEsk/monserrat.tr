var ListeClients = function () {

    var initPickers = function () {
        //init date pickers
        $('.date-picker').datepicker({
            rtl: App.isRTL(),
            language: 'fr',
            autoclose: true
        });
    }
    
    
    var handleRecords = function(){
        var grid = new Datatable();
            grid.init({
                src: $("#datatable_ajax_liste_clients"),
                onSuccess: function(grid){   	
                },
                onError: function(grid){
                    // execute some code on network or other general error  
                },
                dataTable: {  // here you can define a typical datatable settings from http://datatables.net/usage/options 
                    /* 
                        By default the ajax datatable's layout is horizontally scrollable and this can cause an issue of dropdown menu is used in the table rows which.
                        Use below "sDom" value for the datatable layout if you want to have a dropdown menu for each row in the datatable. But this disables the horizontal scroll. 
                    */
                	                	
                	"oLanguage": {
        	            "oAria": {
        	            	"sSortAscending": " - click/return to sort ascending",
        	            	"sSortDescending": " - click/return to sort descending"
        	            },
        		        "oPaginate": {
        		            "sFirst": "Première page",
        		            "sLast": "Dernière page",
        		            "sPrevious": "Page précédente",
        		            "sNext": "Page suivante",
        		            "sPageOf": "sur"
        		        },
        	            "sEmptyTable": "Aucun client",
        	            "sInfo": " &nbsp; &nbsp; _TOTAL_ résultats en tout (_START_ à _END_)",
        	            "sInfoEmpty": "Aucun résultat à afficher",
        	            "sInfoFiltered": " - filtering from _MAX_ records",
        	            /*"sInfoPostFix": "All records shown are derived from real information.",*/
        	            "sInfoThousands": "'",
        	            "sLengthMenu": " &nbsp; &nbsp; Montrer _MENU_ résultats",
        	            "sLoadingRecords": "Chargement ...",
        	            "sProcessing": '<img src="assets/img/loading-spinner-grey.gif"/><span>&nbsp;&nbsp;Chargement...</span>',
        	            "sSearch": "Filtrer les résultats :",
        	            "sZeroRecords": "Aucun résultat à afficher"/*,
        	            "sUrl": "http://www.sprymedia.co.uk/dataTables/lang.txt"*/
        	        },
                	
                    //"sDom" : "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>r>>", 
                   
        	        //"sDom": 'C<"clear">lfrtip',
        	        
        	        //"sDom": 'R',
        	        
        	        "sDom": 'Rrplitpi',
        	        //"sScrollX": "200%",
    	            
        	        //"sDom": 'RC<"clear">lfrtip',
        	                
        	        /*"oColReorder": {
        	            "fnReorderCallback": function (){        	            	
        	                //alert(colReorder.fnOrder());
        	            }
        	        },*/
        	        
        	        "aoColumns": [
        	                      {"sClass": "hidden-xs", "bSortable": true, "mData": "userid", "iDataSort":0, "data": "userid", "bVisible": true},
        	                      {"sClass": "hidden-sm hidden-xs", "bSortable": true, "mData": "dateins", "iDataSort":1, "data": "dateins", "bVisible": true},
        	                      {"sClass": "", "bSortable": true, "mData": "mail", "iDataSort":2, "data": "mail", "bVisible": true},
        	                      {"sClass": "hidden-md hidden-sm hidden-xs",  "bSortable": true, "mData": "raisonsociale", "iDataSort":3, "data": "raisonsociale", "bVisible": true},
        	                      {"sClass": "", "bSortable": true, "mData": "nom", "iDataSort":4, "data": "nom", "bVisible": true},
        	                      {"sClass": "hidden-xs", "bSortable": true, "mData": "prenom", "iDataSort":5, "data": "prenom", "bVisible": true},
        	                      {"sClass": "hidden-sm hidden-xs", "bSortable": true, "mData": "pays", "iDataSort":6, "data": "pays", "bVisible": true}, 
        	                      {"sClass": "", "bSortable": false, "mData": "telephone", "iDataSort":7, "data": "telephone", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "telephone2", "iDataSort":8, "data": "telephone2", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "siret", "iDataSort":9, "data": "siret", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "tva", "iDataSort":10, "data": "tva", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "adresse", "iDataSort":11, "data": "adresse", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "adresse2", "iDataSort":12, "data": "adresse2", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "codepostal", "iDataSort":13, "data": "codepostal", "bVisible": false}, 
        	                      {"sClass": "", "bSortable": false, "mData": "ville", "iDataSort":14, "data": "ville", "bVisible": false},
        	                      {"sClass": "ta-center", "bSortable": false, "mData": "actions", "iDataSort":15, "data": "actions", "bVisible": true}
        	        ],
        	               	                	           	        
                    "aLengthMenu": [
                        [20, 50, 100, 150,300],
                        [20, 50, 100, 150,300] // change per page values here
                    ],
                    "iDisplayStart":0,
                    "iDisplayLength": 20, // default record count per page                    
                    "bProcessing": true,
            		"bServerSide": true,
                    "sAjaxSource": "clients_ajax.php", // ajax source
                    "aaSorting": [[ 0, "asc" ]] // set first column as a default sort by asc   
        	        
                }
            });            
      
            var oTable = $('#datatable_ajax_liste_clients').dataTable();
            
            // execute some code after table records loaded
        	jQuery('#datatable_ajax_liste_clients_wrapper .dataTables_filter input').addClass("form-control input-small input-inline"); // modify table search input
            jQuery('#datatable_ajax_liste_clients_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
            jQuery('#datatable_ajax_liste_clients_wrapper .dataTables_length select').select2(); //.addClass("form-control");// initialize select2 dropdown

            $('#datatable_ajax_liste_clients_column_toggler input[type="checkbox"]').change(function(){
                /* Get the DataTables object again - this is not a recreation, just a get of the object */
                var iCol = parseInt($(this).attr("data-column"));
                var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
                oTable.fnSetColumnVis(iCol, (bVis ? false : true));
            });
              

            /*grid.getTableWrapper().on('click', '.table-group-action-submit', function(e){
            	grid.addAjaxParam("test1", "value1");
                grid.addAjaxParam("test2", "value2");
                grid.addAjaxParam("test3", "value3");
                grid.addAjaxParam("test4", "value4");
            });*/
            
            // handle group actionsubmit button click
            grid.getTableWrapper().on('click', '.table-group-action-submit', function(e){
                e.preventDefault();
                var action = $(".table-group-action-input", grid.getTableWrapper());
                if (action.val() != "" && grid.getSelectedRowsCount() > 0) {
                    grid.addAjaxParam("sAction", "group_action");
                    grid.addAjaxParam("sGroupActionName", action.val());
                    var records = grid.getSelectedRows();
                    for (var i in records) {
                        grid.addAjaxParam(records[i]["name"], records[i]["value"]);
                    }
                    grid.getDataTable().fnDraw();
                    grid.clearAjaxParams();
                } else if (action.val() == "") {
                    App.alert({type: 'danger', icon: 'warning', message: 'Please select an action', container: grid.getTableWrapper(), place: 'prepend'});
                } else if (grid.getSelectedRowsCount() === 0) {
                    App.alert({type: 'danger', icon: 'warning', message: 'No record selected', container: grid.getTableWrapper(), place: 'prepend'});
                }
            });
            
            
            grid.getTableWrapper().on('click', '.pagination-panel', function(e){
                e.preventDefault();                
                $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', grid.getTableWrapper()).each(function(){
                	if($(this).val() != "") {
                		grid.addAjaxParam($(this).attr("name"), $(this).val());
                		//alert($(this).val());
                	}
                });                
            });
            
            grid.getTableWrapper().on('keyup', 'textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', function(e){
                e.preventDefault();                
                $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', grid.getTableWrapper()).each(function(){
                	if($(this).val() != "") {
                		grid.addAjaxParam($(this).attr("name"), $(this).val());
                		//alert($(this).val());
                	}
                });                
            });
            
            grid.getTableWrapper().on('change', 'textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', function(e){
                e.preventDefault();                  
                $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', grid.getTableWrapper()).each(function(){
                	if($(this).val() != "") {
                		grid.addAjaxParam($(this).attr("name"), $(this).val());
                		//alert($(this).val());
                	}
                });                
            });
            
            grid.getTableWrapper().on('click', '.filter-cancel', function(e){
                e.preventDefault();
                $('textarea.form-filter, input.form-filter', grid.getTableWrapper()).each(function(){
                    $(this).val("");
                });
                $('select.form-filter', grid.getTableWrapper()).each(function(){
                    $(this).val(0);
                });
                $('input.form-filter[type="checkbox"]', grid.getTableWrapper()).each(function(){
                    $(this).attr("checked", false);
                });             
                grid.clearAjaxParams();
            });          
    }

    return {

        //main function to initiate the module
        init: function () {
            initPickers();
            handleRecords();
        }

    };

}();