define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'laoban/chouka/index' + location.search,
                    add_url: 'laoban/chouka/add',
                    edit_url: 'laoban/chouka/edit',
                    del_url: 'laoban/chouka/del',
                    multi_url: 'laoban/chouka/multi',
                    import_url: 'laoban/chouka/import',
                    table: 'chouka',
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
                        // {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'statuslist', title: __('Statuslist'), searchList: {"0":__('Statuslist 0'),"1":__('Statuslist 1'),"2":__('Statuslist 2')}, formatter: Table.api.formatter.normal},
                        {field: 'tuisongtime', title: '系统推送时间', operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'choukatime', title: __('Choukatime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'chouendtime', title: __('Chouendtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        {field: 'dakatime', title: __('Dakatime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'dakalist', title: __('Dakalist'), searchList: {"0":__('Dakalist 0'),"1":__('Dakalist 1'),"2":__('Dakalist 2')}, formatter: Table.api.formatter.normal},
                        // {field: 'year', title: __('Year')},
                        // {field: 'month', title: __('Month')},
                        // {field: 'day', title: __('Day')},
                        {field: 'jilunlist', title: __('Jilunlist'), searchList: {"1":__('Jilunlist 1'),"2":__('Jilunlist 2'),"3":__('Jilunlist 3'),"4":__('Jilunlist 4')}, formatter: Table.api.formatter.normal},
                        {field: 'dijige', title: __('Dijige')},
                        // {field: 'token', title: __('Token'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},

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
