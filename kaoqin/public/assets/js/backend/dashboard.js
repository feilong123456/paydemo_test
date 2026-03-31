define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    chuqindaka_url: 'laoban/daka/add?typelist=0',
                    chouchadaka_url: 'laoban/daka/add?typelist=1',
                    xiabandaka_url: 'laoban/daka/add?typelist=2',
                    tijiaoribao_url: 'laoban/ribao/add',
                    woyaoqingjia_url: 'laoban/approve/add',
                   
                },
                 woyaoqingjia: function () {
                    Controller.api.bindevent();
                },
                tijiaoribao_url: function () {
                    Controller.api.bindevent();
                },
                xiabandaka_url: function () {
                    Controller.api.bindevent();
                },
                chouchadaka_url: function () {
                    Controller.api.bindevent();
                },
                chuqindaka_url: function () {
                    Controller.api.bindevent();
                },
            });
           
            $('#chuqindaka').on('click', function (){
                Fast.api.open('laoban/daka/add?typelist=0','出勤打卡',{area:['500px', '300px']});
            });
            $('#chouchadaka').on('click', function (){
                Fast.api.open('laoban/daka/add?typelist=1','抽查打卡',{area:['500px', '300px']});  
            });
            $('#xiabandaka').on('click', function (){
                Fast.api.open('laoban/daka/add?typelist=2','下班打卡',{area:['500px', '300px']}); 
            });
            
            $('#tijiaoribao').on('click', function (){
                Fast.api.open('laoban/ribao/add','提交日报'); 
            });
            
            $('#woyaoqingjia').on('click', function (){
                Fast.api.open('laoban/approve/add','我要请假'); 
            });
            
            
            

        }
    };

    return Controller;
  
});
