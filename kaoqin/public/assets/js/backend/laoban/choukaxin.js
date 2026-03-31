define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'laoban/choukaxin/index' + location.search,
                    add_url: 'laoban/choukaxin/add',
                    edit_url: 'laoban/choukaxin/edit',
                    del_url: 'laoban/choukaxin/del',
                    multi_url: 'laoban/choukaxin/multi',
                    import_url: 'laoban/choukaxin/import',
                    table: 'admin',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
              


              queryParams: function(params){
                  console.log( location.search);
                    // console.log(6666666666);

                    /*/ var filter = JSON.parse(params.filter);
                    // var op = JSON.parse(params.search);
                    // console.log(filter);console.log(op);
                    // params.filter = JSON.stringify(filter);
                    // params.op = JSON.stringify(op);
                    // 这里出现问题： 无法获得id。 因为id 是row 上的 已经解决
                    通过html 中取
                    **/

                    // begin by bob 2023-08-04 解决自定义搜索表单 传参 问题. 这里直接js 处理
                    // js 过滤自定义搜索数据.
                    var filter = JSON.parse(params.filter);
                    console.log(filter["riqi"]); // 这里是搜索的值
                    // 如果搜索输入值 有,就用搜索的
                    if (filter["riqi"] != undefined && filter["riqi"] != '') {
                        params.search = filter["riqi"];
                    }
                    // end by bob 2023-08-04 解决自定义搜索表单 传参 问题. 这里直接js 处理

                    // console.log($("#match_detail_table").attr('medicine_match_id'));
                    // console.log($("#match_detail_table").val('medicine_match_id'));
                    if (params.search == undefined || params.search == '') {
                       
                
                        var today = new Date(); // 获取当前日期
                        var yesterday = new Date(today);
                        yesterday.setDate(today.getDate());  // 将日期设为昨天
                        
                        var year = yesterday.getFullYear(); // 获取年份
                        var month = yesterday.getMonth() + 1; // 获取月份（注意月份是从 0 开始的，所以要加 1）
                        var day = yesterday.getDate(); // 获取日期
                        
                        var pptime = year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;  // 输出年月日

                        $("#riqi").val(pptime); 
                    }

                    return params;
                },
                pk: 'id',

                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                      
                        {field: 'id', title: __('Id'), operate:false},
                        {field: 'riqi', title: "日期", datetimeFormat:"YYYY-MM-DD",addclass:'datetimepicker', autocomplete:false},
                        {field: 'nickname', title: "员工名称"},
                        {field: 'daka_shang', title: "上班打卡", operate:false},
                        {field: 'daka_xia', title: "下班打卡", operate:false},
                        {field: 'choukacishu', title:"抽卡次数",operate:false},
                        {field: 'weidakacishu', title: "未打卡次数",operate:false},
                        {field: 'chidaocishu', title:"迟到打卡次数",operate:false},
                        {field: 'zhengchangdaka', title: "正常打卡次数",operate:false},
                         {
                            field: 'operate', title: __('Operate'), table: table,events: Table.api.events.operate,
                           
                            buttons: [
                                {
                                    name: 'detail',
                                    title: __('详情'),
                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-list',
                                    url: 'laoban/chouka/index?admin_id={id}&riqi={riqi}', 
                                    extend:'data-area=\'["80%","90%"]\'',
                                    callback: function (data) {
                                        Layer.alert("接收到回传数据：" + JSON.stringify(data), {title: "回传数据"});
                                    }
                                }],
                                              formatter: function (value, row, index) {
                                          var that = $.extend({}, this);
                                          var table = $(that.table).clone(true);
                                          // 这里可以直接判断  编辑和删除 按钮 隐藏  这是第一种
                                        
                                              $(table).data("operate-del", null);
                                              $(table).data("operate-edit", null);
                                        
                                          that.table = table;
                                          return Table.api.formatter.operate.call(that, value, row, index);
                                   

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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
