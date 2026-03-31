define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'laoban/approve/index' + location.search,
                    add_url: 'laoban/approve/add',
                    shenhe_url: 'laoban/approve/shenhe',
                    jujue_url:'laoban/approve/jujue',
                    edit_url: 'laoban/approve/edit',
                    del_url: 'laoban/approve/del',
                    multi_url: 'laoban/approve/multi',
                    import_url: 'laoban/approve/import',
                    table: 'approve',
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
                        {field: 'admin_id', title: __('Admin_id')},
                        {field: 'starttime', title: __('Starttime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'mainimage', title: __('Mainimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'typelist', title: __('Typelist'), searchList: {"0":__('Typelist 0'),"1":__('Typelist 1'),"2":__('Typelist 2'),"3":__('Typelist 3'),"4":__('Typelist 4')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'pushadmin_id', title: __('Pushadmin_id')},
                        {field: 'shenhetime', title: __('Shenhetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table,buttons:[
                            {
                                name: 'shenhe',
                                text: "审核通过",
                                title: "审核",
                                classname: 'btn btn-xs btn-info btn-magic btn-ajax',
                                icon: 'fa fa-magic',
                                url: 'laoban/approve/shenhe',
                                confirm: '确认审核通过?',
                                success: function (data, ret) {
                                  
                                      table.bootstrapTable('refresh', {});
                                },
                                error: function (data, ret) {
                                    console.log(data, ret);
                                    Layer.alert(ret.msg);
                                    return false;
                                }
                            },{
                                name: 'jujue',
                                text: "拒绝通过",
                                title: "审核",
                                classname: 'btn btn-xs btn-success btn-magic btn-ajax',
                                icon: 'fa fa-angellist',
                                url: 'laoban/approve/jujue',
                                confirm: '确认拒绝?',
                                success: function (data, ret) {
                                 
                                      table.bootstrapTable('refresh', {});
                                },
                                error: function (data, ret) {
                                    console.log(data, ret);
                                    Layer.alert(ret.msg);
                                    return false;
                                }
                            }     
                        ], events: Table.api.events.operate, 
                        //formatter: Table.api.formatter.operate
                      
                             formatter:function(value,row,index){
                                   console.log(value);
                                     console.log(row);
                                       console.log(index);
                               var that = $.extend({},this);//将this赋值给that,
                               var table = $(that.table).clone(true);//通过that去引用table中的信息
                               if(row.status>0){
                                   $(table).data("operate-edit",null);//隐藏操作中的编辑按钮，
                                   $(table).data("operate-del",null);//隐藏操作中的删除按钮，
                               }
                               
                            
                               that.table = table;
                               return Table.api.formatter.operate.call(that,value,row,index);//展示信息
                             }
                        }
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
        shenhe: function () {
            Controller.api.bindevent();
        },
        jujue: function () {
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
