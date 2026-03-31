define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'laoban/yuebao/index' + location.search,
                    add_url: 'laoban/yuebao/add',
                    edit_url: 'laoban/yuebao/edit',
                    del_url: 'laoban/yuebao/del',
                    multi_url: 'laoban/yuebao/multi',
                    import_url: 'laoban/yuebao/import',
                    table: 'yuebao',
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
                        // {field: 'id', title: __('Id')}, // 保留搜索
                        {field: 'admin_id', title: __('Admin_id')}, // 保留搜索
                        {field: 'year', title: __('Year')}, // 保留搜索
                        {field: 'month', title: __('Month')}, // 保留搜索
                        {field: 'needday', title: __('Needday'), operate: false},
                        {field: 'shijiday', title: __('Shijiday'), operate: false},
                        {field: 'chidaoday', title: __('上班迟到天数'), operate: false},
                        {field: 'choukaday', title: __('抽卡迟到次数'), operate: false},
                        {field: 'zaotuiday', title: __('上班早退天数'), operate: false},
                        {field: 'weidakanum', title: __('未打卡次数'), operate: false},
                        {field: 'qingjiaci', title: __('Qingjiaci'), operate: false},
                        {field: 'qingjiaday', title: __('Qingjiaday'), operate: false},
                        // {field: 'remake', title: __('Remake'), operate: false, table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'createtime', title: __('Createtime'), operate: false, formatter: Table.api.formatter.datetime},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
