define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'laoban/daka/index' + location.search,
                    add_url: 'laoban/daka/add',
                    edit_url: 'laoban/daka/edit',
                    del_url: 'laoban/daka/del',
                    multi_url: 'laoban/daka/multi',
                    import_url: 'laoban/daka/import',
                    table: 'daka',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                pagination: false, // 禁用分页
                pageSize: 999999, // 设置很大的页面大小
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id'), operate: false},
                        {field: 'admin_id', title: __('Admin_id')}, // 只保留这个字段的搜索
                        {field: 'daka_date', title: __('日期'), operate: false, formatter: function(value, row, index) {
                            // 格式化日期显示：显示日期和星期
                            if (value && row.week) {
                                return value + ' ' + row.week;
                            }
                            return value || '';
                        }},
                        {field: 'week', title: __('星期'), visible: false, operate: false}, // 隐藏星期列，因为已经在日期列中显示了
                        {field: 'dakatime', title: __('期望打卡时间'), operate: false},
                        {field: 'createtime', title: __('实际打卡时间'), operate: false, formatter: Table.api.formatter.datetime},
                        {field: 'typelist', title: __('打卡类型'), operate: false, formatter: function(value, row, index) {
                            // 0=上班打卡, 2=下班打卡
                            if (value === '0' || value === 0) {
                                return '<span style="color: #28a745; font-weight: bold;">上班</span>';
                            } else if (value === '2' || value === 2) {
                                return '<span style="color: #17a2b8; font-weight: bold;">下班</span>';
                            }
                            return value || '-';
                        }},
                        {field: 'chuqinstatus', title: __('状态'), operate: false},
                        {field: 'chuqinlist', title: __('出勤记录'), operate: false, formatter: function(value, row, index) {
                            if (!value) return '';
                            
                            // 定义不同出勤记录的颜色
                            var colorMap = {
                                // 正常状态 - 绿色
                                '按时上班': '#28a745',
                                '按时下班': '#28a745',
                                '周末假': '#17a2b8',
                                '节日假': '#17a2b8',
                                
                                // 异常状态 - 红色
                                '上班迟到': '#dc3545',
                                '下班早退': '#dc3545',
                                '上班缺卡': '#dc3545',
                                '下班缺卡': '#dc3545',
                                
                                // 请假状态 - 橙色
                                '请假': '#ff9800'
                            };
                            
                            // 获取颜色
                            var color = colorMap[value];
                            
                            // 如果没有精确匹配，检查是否包含"假"或"请假"等关键字
                            if (!color) {
                                if (value.indexOf('假') !== -1 || value.indexOf('请假') !== -1) {
                                    color = '#ff9800'; // 请假相关 - 橙色
                                } else if (value.indexOf('迟到') !== -1 || value.indexOf('早退') !== -1 || value.indexOf('缺卡') !== -1) {
                                    color = '#dc3545'; // 异常相关 - 红色
                                } else if (value.indexOf('按时') !== -1) {
                                    color = '#28a745'; // 正常相关 - 绿色
                                } else {
                                    color = '#6c757d'; // 默认 - 灰色
                                }
                            }
                            
                            // 返回带颜色的 HTML
                            return '<span style="color: ' + color + '; font-weight: bold;">' + value + '</span>';
                        }},
                        {field: 'year', title: __('Year'), visible: false, operate: false}, // 隐藏年列
                        {field: 'month', title: __('Month'), visible: false, operate: false}, // 隐藏月列
                        // {field: 'ischidao', title: __('Ischidao'), searchList: {"0":__('Ischidao 0'),"1":__('Ischidao 1')}, formatter: Table.api.formatter.normal},
                        // {field: 'iszaotui', title: __('Iszaotui'), searchList: {"0":__('Iszaotui 0'),"1":__('Iszaotui 1')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                // 处理加载完成后显示统计信息
                onLoadSuccess: function (data) {
                    if (data && data.summary) {
                        // 显示统计信息
                        $("#summaryText").text(data.summary);
                        $("#summaryInfo").show();
                    } else {
                        // 如果没有统计信息，隐藏区域
                        $("#summaryInfo").hide();
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
