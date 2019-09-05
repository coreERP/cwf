/**
 * @link http://www.vishwayon.com/
 * @copyright Copyright (c) 2008 Vishwayon Software Pvt Ltd
 * @license http://www.vishwayon.com/license/
 */

/**
 * cwf Client Code
 *
 * @author girish
 */
window.cwfClient = {};

cwfClient.formats = {
    dateFormat: "dd-mm-yyyy",
    ccy: 'l',
    userTimeZone: 'IST'
};

cwfClient.utils = {
    formatDate: function(dateVal) {
        if (dateval == '1970-01-01') {
            return '';
        }
        var dtm = moment(new Date(dateVal));
        return dtm.format((cwfClient.formats.dateFormat).toUpperCase());
    },
    formatDateTime: function(dateVal) {
        if (dateVal.indexOf('UTC') == -1) {
            dateVal += ' UTC';
        }
        var dtm = moment(new Date(dateVal));
        return dtm.tz(cwfClient.formats.userTimeZone).format((cwfClient.formats.dateFormat).toUpperCase() + ' HH:mm:ss z');
    },
    formatNumber: function(numVal, scale) {
        if (typeof numVal === 'undefined') {
            return '0';
        }
        numVal = Number.parseFloat(numVal);
        if (cwfClient.formats.ccy === 'l') {
            return numVal.toLocaleString('en-IN', {minimumFractionDigits: scale, maximumFractionDigits: scale});
        } else {
            return numVal.toLocaleString('en-US', {minimumFractionDigits: scale, maximumFractionDigits: scale});
        }
    }
};

cwfClient.collectionView = {
        fetch: function (redraw) {
            var lnk = $('#data-lnk').attr('href');
            $.ajax({
               url: lnk,
               type: 'GET',
               data: {},
               dataType: 'json',
               success: function(jdata) {
                   cwfClient.collectionView.drawTable(jdata, true);
               },
               error: function(err) {
                   console.log(err.responseText);
               }
            });
            return;
            
            /*
            redraw == undefined ? redraw = false : redraw = true;
            var img = $('#collrefresh_image');
            if (typeof img !== undefined) {
                $(img).addClass('fa-spin');
            }
            var qpRoute = $('#qp').attr('qp-route');
            var qpColl = $('#qp').attr('qp-CollName');
            var qpType = $('#qp').attr('qp-bizobj');
            var lnk;
            if (typeof qpType != 'undefined' && qpType != '') {
                lnk = '?r=/' + qpRoute + '/form/filter-collection&formName=' + qpColl;
            } else {
                lnk = $('#collrefresh').attr('posturl');
            }

            $.ajax({
                url: lnk,
                type: 'GET',
                data: {
                    filters: $('#collectionfilter').serialize()
                },
                dataType: 'json',
                success: function (jdata) {
                    coreWebApp.collectionView.drawTable(jdata, redraw);
                },
                error: function (err) {
                    coreWebApp.toastmsg('error', 'Fetch Collection Data Error', err.responseText, true);
                },
                complete: function () {
                    var img = $('#collrefresh_image');
                    if (typeof img !== undefined) {
                        $(img).removeClass('fa-spin');
                    }
                }
            });
            */
        },
        drawTable: function (jdata, redraw) {
            var tableCols = [];
            var colCnt = jdata.cols.length;
            $.each(jdata.cols, function (colid, col) {
                var colDef = {
                    data: col.columnName,
                    title: col.displayName,
                    width: (100 / colCnt).toFixed(2) + "%"
                };
                if (typeof col.format !== undefined) {
                    switch (col.format) {
                        case "Link":
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html('<a style="color: brown;" href="#" onclick="coreWebApp.collectionView.getDoc(\'' + rowData[jdata.def.keyField] + '\',\'' + jdata.def.afterLoad + '\')">' + cellData + '</a>');
                            };
                            break;
                        case "Date":
                            // Create display format for date filter
                            colDef.data = {
                                _: col.columnName,
                                filter: col.columnName + '_filter'
                            };
                            var fcol = col.columnName + '_filter';
                            $.each(jdata.data, function (rid, row) {
                                row[fcol] = cwfClient.utils.formatDate(row[col.columnName]);
                            });
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html(cwfClient.utils.formatDate(cellData));
                            };
                            break;
                        case "Amount":
                            colDef.className = "dt-right";
                            switch (col.scale) {
                                case "amt":
                                    colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                        $(td).html(cwfClient.utils.formatNumber(cellData, 2));
                                    };
                                    break;
                                case "rate":
                                    colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                        $(td).html(cwfClient.utils.formatNumber(cellData, 3));
                                    };
                                    break;
                                case "qty":
                                    colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                        $(td).html(cwfClient.utils.formatNumber(cellData, 3));
                                    };
                                    break;
                                default:
                                    colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                        $(td).html(cwfClient.utils.formatNumber(cellData, 0));
                                    };
                                    break;
                            }
                            break;
                        case 'Datetime':
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html(cwfClient.utils.formatDateTime(cellData));
                            };
                            break;
                        case 'Status':
                            colDef.render = function (cellData) {
                                return cellData == 0 || cellData == 1 ? 'Pending' : cellData == 3 ? 'Workflow' : cellData == 5 ? 'Posted' : '';
                            };
                            break;
                        case 'Rate':
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html(cwfClient.utils.formatNumber(cellData, 4));
                            };
                            break;
                        case 'Qty':
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html(cwfClient.utils.formatNumber(cellData, 3));
                            };
                            break;
                        case 'FC':
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html(cwfClient.utils.formatNumber(cellData, 4));
                            };
                            break;
                        case 'Html':
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html(cellData);
                            };
                            break;
                        default:
                            colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                                $(td).html($('<div>').text(cellData).html());
                            };
                            break;
                    }
                }
                if (col.wrapIn !== null) {
                    // This would overwrite format information and create wrapin
                    colDef.createdCell = function (td, cellData, rowData, crow, ccol) {
                        var content = '<' + col.wrapIn + ' style="' + col.style + '">' + cellData + '</' + col.wrapIn + '>';
                        $(td).html(content);
                    };
                }
                tableCols.push(colDef);
            });
            // create Edit/View link column
            tableCols.push({
                data: jdata.def.keyField,
                orderable: false,
                createdCell: jdata.def.al > 1 ? function (td, cellData, rowData, row, col) {
                    $(td).html('<a href="#" onclick="coreWebApp.collectionView.getDoc(\'' + rowData[jdata.def.keyField] + '\',\'' + jdata.def.afterLoad + '\')" ><i class="glyphicon glyphicon-pencil"></i></a>');
                } : function (td, cellData, rowData, row, col) {
                    $(td).html('<a href="#" onclick="coreWebApp.collectionView.getDoc(\'' + rowData[jdata.def.keyField] + '\',\'' + jdata.def.afterLoad + '\')" ><i class="glyphicon glyphicon-eye-open"></i></a>');
                }
            });

            if ($.fn.dataTable.isDataTable('#cv-data-table') && !redraw) {
                tbl = $('#cv-data-table').DataTable();
                tbl.clear();
                tbl.rows.add(jdata.data);
                tbl.draw();
            } else {
                if ($.fn.dataTable.isDataTable('#cv-data-table')) {
                    var t = $('#cv-data-table').DataTable();
                    t.destroy(true);
                }
                var p = $('#cv-data');
                p.append('<table id="cv-data-table" class="row-border hover"></table>');
                //$('#contentholder').height($('#content-root').height() - 10);
                //$('#contents').height($('#content-root').height() - 20);
                tbl = $('#cv-data-table').DataTable({
                    createdRow: function (tr, rowData, dataIndex) {
                        $(tr).on('dblclick', function () {
                            cwfClient.collectionView.getDoc(rowData[jdata.def.keyField], jdata.def.afterLoad);
                        });
                    },
                    data: jdata.data,
                    autoWidth: false,
                    columns: tableCols,
                    deferRender: true,
                    scrollY: '500px', //Todo: use the css height: calc(100% - 5rem)
                    scrollX: true,
                    searching: true,
                    scroller: true
                });
            }
            $('.dataTables_empty').text('No data to display');
            $('.dataTables_scrollBody').css("min-height", ($('.dataTables_scrollBody').height()).toString() + 'px');
            $('.dataTables_scrollBody').css("height", ($('.dataTables_scrollBody').height()).toString() + 'px');
            $('.dataTables_scrollBody').css("background", "transparent");
            $('#thelist_length').hide();
            /*if ($('#after_fetch')) {
                var funcbody = $('#after_fetch').val();
                if (funcbody != undefined || funcbody != '') {
                    var func = new Function('data', 'tbl', '{' + funcbody + '(data, tbl); }');
                    func(jdata.data, tbl);
                }
            }*/
        }
        
    };

