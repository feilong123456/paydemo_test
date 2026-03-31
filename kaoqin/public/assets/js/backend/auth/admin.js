define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'auth/admin/index',
                    add_url: 'auth/admin/add',
                    edit_url: 'auth/admin/edit',
                    del_url: 'auth/admin/del',
                    multi_url: 'auth/admin/multi',
                }
            });

            var table = $("#table");

            //在表格内容渲染完成后回调的事件
            table.on('post-body.bs.table', function (e, json) {
                $("tbody tr[data-index]", this).each(function () {
                    if (parseInt($("td:eq(1)", this).text()) == Config.admin.id) {
                        $("input[type=checkbox]", this).prop("disabled", true);
                    }
                });
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {field: 'state', checkbox: true, },
                        {field: 'id', title: 'ID'},
                        {field: 'username', title: __('Username')},
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'groups_text', title: __('Group'), operate:false, formatter: Table.api.formatter.label},
                        {field: 'email', title: __('Email')},
                        {field: 'mobile', title: __('Mobile')},
                        {field: 'chatid', title: "用户tg的chatid"},
                        {field: 'typedata', title: "打卡方式", searchList: {"0":"tg","1":"web","2":"两者均可"}, formatter: Table.api.formatter.normal},
                        {field: 'choukadata', title: "抽卡方式", searchList: {"0":"tg","1":"web"}, formatter: Table.api.formatter.normal},

                        {field: 'status', title: __("Status"), searchList: {"normal":__('Normal'),"hidden":__('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'logintime', title: __('Login time'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                                if(row.id == Config.admin.id){
                                    return '';
                                }
                                return Table.api.formatter.operate.call(this, value, row, index);
                            },buttons: [
                                {
                                    name: 'lastmonth',
                                    text: __('上个月打卡数据'),
                                    title: __('上个月打卡数据'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',

                                    /* === ① 方案一：95% 大弹窗 === */
                                    area: ['95%', '95%'],

                                    /* === ② 方案二：直接全屏 === */
                                    // extend: 'data-max="true"',

                                    url: function (row) {
                                        var username    = row.nickname;
                                        var currentDate = new Date();
                                        var lastMonth   = currentDate.getMonth();      // 0‑11
                                        var currentYear = currentDate.getFullYear();
                                        if (lastMonth === 0) {
                                            lastMonth   = 12;
                                            currentYear--;
                                        }
                                        return `/ht.php/laoban/dakanew?admin_id=${username}&year=${currentYear}&month=${lastMonth}`;
                                    }
                                },
                                {
                                    name: 'thismonth',
                                    text: __('当前月打卡数据'),
                                    title: __('当前月打卡数据'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    area: ['95%', '95%'],          // 或 extend:'data-max="true"'
                                    url: function (row) {
                                        var username     = row.nickname;
                                        var now          = new Date();
                                        var currentYear  = now.getFullYear();
                                        var currentMonth = now.getMonth() + 1;
                                        return `/ht.php/laoban/dakanew?admin_id=${username}&year=${currentYear}&month=${currentMonth}`;
                                    }
                                }
                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        }
    };
    return Controller;
});
