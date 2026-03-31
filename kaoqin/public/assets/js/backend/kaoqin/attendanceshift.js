define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'kaoqin/attendanceshift/index' + location.search,
                    add_url: 'kaoqin/attendanceshift/add',
                    edit_url: 'kaoqin/attendanceshift/edit',
                    del_url: 'kaoqin/attendanceshift/del',
                    multi_url: 'kaoqin/attendanceshift/multi',
                    import_url: 'kaoqin/attendanceshift/import',
                    table: 'attendanceshift',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'typelist', title: __('Typelist'), searchList: {"0":__('Typelist 0'),"1":__('Typelist 1')}, formatter: Table.api.formatter.normal},
                        {field: 'worktime', title: __('Worktime')},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss",autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss", autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'startbefore', title: __('Startbefore')},
                        {field: 'endbefore', title: __('Endbefore')},
                        {field: 'typedata', title: __('Typedata'), searchList: {"0":__('Typedata 0'),"1":__('Typedata 1')}, formatter: Table.api.formatter.normal},
                        {field: 'reststarttime', title: __('Reststarttime'), operate:'RANGE', datetimeFormat:"HH:mm:ss",addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'restendtime', title: __('Restendtime'), operate:'RANGE', datetimeFormat:"HH:mm:ss",addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'startmiss', title: __('Startmiss')},
                        {field: 'endmiss', title: __('Endmiss')},
                        {field: 'startbelate', title: __('Startbelate')},
                        {field: 'endlate', title: __('Endlate')},
                        {field: 'timedraw', title: __('Timedraw')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        
                        {field: 'onestarttime', title:'第一轮抽卡开始时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss",autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'oneendtime', title: '第一轮抽卡结束时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss", autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'onechou', title: '第一轮 抽卡次数'},
                        {field: 'twostarttime', title: '第二轮抽卡开始时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss",autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'twoendtime', title: '第二轮抽卡结束时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss", autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'twochou', title: '第二轮 抽卡次数'},
                        {field: 'threestarttime', title: '第三轮抽卡开始时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss",autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'threeendtime', title: '第三轮抽卡结束时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss", autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'threechou', title: '第三轮 抽卡次数'},
                        {field: 'fourstarttime', title: '第四轮抽卡开始时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss",autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'fourendtime', title: '第四轮抽卡结束时间', operate:'RANGE', addclass:'datetimerange',datetimeFormat:"HH:mm:ss", autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'fourchou', title: '第四轮 抽卡次数'},
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
