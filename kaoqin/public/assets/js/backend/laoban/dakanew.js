define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'laoban/dakanew/index' + location.search,
                    // add_url: 'laoban/dakanew/add',
                    // edit_url: 'laoban/dakanew/edit',
                    // del_url: 'laoban/dakanew/del',
                    multi_url: 'laoban/dakanew/multi',
                    // import_url: 'laoban/dakanew/import',
                    table: 'daka',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pagination: false,
                search:false,
                showSearch:false,
                columns: [
                    [
                        // {checkbox: true},
                        // {field: 'id', title: __('Id') ,operate:'false'},
                        {field: 'admin_id', title: '员工名称'},
                        {field: 'daka_date', title: '查询时间'},
                        // {field: 'dakatime', title: __('Dakatime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {
                            field: 'createtime', 
                            title: __('Createtime'), 
                            operate: 'false', 
                            addclass: 'datetimerange', 
                            autocomplete: false, 
                            formatter: function(value, row, index) {
                                // 如果值为空或无值，返回 "--"
                                if (!value) {
                                    return '--';
                                }
                                // 如果有值，正常显示时间
                                return Table.api.formatter.datetime(value, row, index);
                            }
                        },
                        // ★ 给“出勤状态”加颜色示例：
                        {
                            field: 'chuqinstatus', 
                            title: '出勤状态' ,operate:'false',
                            formatter: function(value, row, index) {
                                // 根据不同状态自定义颜色
                                if (value === '正常休假') {
                                    return '<span style="color:green;">' + value + '</span>';
                                } else if (value === '请假') {
                                    return '<span style="color:orange;">' + value + '</span>';
                                } else if (value === '上班异常出勤' || value === '上班缺勤') {
                                    return '<span style="color:red;">' + value + '</span>';
                                }else if (value === '下班异常出勤' || value === '下班缺勤') {
                                    return '<span style="color:red;">' + value + '</span>';
                                }
                                // 默认原样输出
                                return value;
                            }
                        },

                        // ★ 给“出勤记录”加颜色示例：
                        {
                            field: 'chuqinlist', 
                            title: '出勤记录' ,operate:'false',
                            formatter: function (value, row, index) {
                                // 你可以根据不同情况返回不同颜色
                                if (value === '周末假') {
                                    return '<span style="color:green;">' + value + '</span>';
                                } else if (value === '节日假') {
                                    return '<span style="color:blue;">' + value + '</span>';
                                } else if (value === '上班迟到' || value === '下班早退') {
                                    return '<span style="color:red;">' + value + '</span>';
                                } else if (value === '上班缺卡' || value === '下班缺卡') {
                                    return '<span style="color:#b94a48;">' + value + '</span>';
                                }
                                // 默认
                                return value;
                            }
                        },
                        // {field: 'typelist', title: __('Typelist'), searchList: {"0":__('Typelist 0'),"1":__('Typelist 1'),"2":__('Typelist 2')}, formatter: Table.api.formatter.normal},
                       
                       {field: 'week', title: "星期几",operate:'false'},
                        {field: 'year', title: '年份' ,operate:'false'},
                        {field: 'month', title: '月份',operate:'false'},
                        // {field: 'ischidao', title: __('Ischidao'), searchList: {"0":__('Ischidao 0'),"1":__('Ischidao 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'iszaotui', title: __('Iszaotui'), searchList: {"0":__('Iszaotui 0'),"1":__('Iszaotui 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ] // 处理加载完成后显示统计信息
                ,onLoadSuccess: function (data) {
                    console.log(data);
                    if (data.summary) {
                        // 假设你有一个id为summaryInfo的容器来显示统计信息
                        $("#summaryInfo").text(data.summary);
                    }
                }
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
