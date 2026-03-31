define(['jquery', 'bootstrap', 'backend', 'table', 'form','bootstrap-datetimepicker'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'kaoqin/attendance/index' + location.search,
                    add_url: 'kaoqin/attendance/add',
                    edit_url: 'kaoqin/attendance/edit',
                    del_url: 'kaoqin/attendance/del',
                    multi_url: 'kaoqin/attendance/multi',
                    import_url: 'kaoqin/attendance/import',
                    table: 'attendance',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'attendanceshift_id', title: __('Attendanceshift_id')},
                        {field: 'hobbydata', title: __('Hobbydata'), searchList: {"1":__('Hobbydata 1'),"2":__('Hobbydata 2'),"3":__('Hobbydata 3'),"4":__('Hobbydata 4'),"5":__('Hobbydata 5'),"6":__('Hobbydata 6'),"7":__('Hobbydata 7')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE',datetimeFormat:"HH:mm:ss", addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE',datetimeFormat:"HH:mm:ss", addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'group_ids', title: __('Group_ids'), operate: 'LIKE'},
                        {field: 'maincontent', title: __('Maincontent'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'typedata', title: __('Typedata'), searchList: {"0":__('Typedata 0'),"1":__('Typedata 1'),"2":__('Typedata 2')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            require(['bootstrap-datetimepicker'], function () {
                
                
                $('#c-endtime').datetimepicker({
                     format: 'HH:mm:ss',
                    
                    
                   
                });
            });
           
            
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
