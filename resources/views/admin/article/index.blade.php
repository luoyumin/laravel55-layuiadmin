@extends('admin.base')

@section('content')
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto">
            <div class="layui-btn-group ">
                @can('zixun.article.destroy')
                    <button class="layui-btn layui-btn-sm layui-btn-danger" id="listDelete">删 除</button>
                @endcan
                @can('zixun.article.create')
                    <button class="layui-btn layui-btn-sm" id="article_add">添 加</button>
                @endcan

            </div>
            <div class="layui-form" >
                <div class="layui-input-inline">
                    <select name="category_id" lay-verify="required" id="category_id">
                        <option value="">请选择分类</option>
                        @foreach($categorys as $category)
                            <option value="{{ $category->id }}" >{{ $category->name }}</option>
                            @if(isset($category->allChilds)&&!$category->allChilds->isEmpty())
                                @foreach($category->allChilds as $child)
                                    <option value="{{ $child->id }}" >&nbsp;&nbsp;&nbsp;┗━━{{ $child->name }}</option>
                                    @if(isset($child->allChilds)&&!$child->allChilds->isEmpty())
                                        @foreach($child->allChilds as $third)
                                            <option value="{{ $third->id }}" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┗━━{{ $third->name }}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="layui-input-inline">
                    <input type="text" name="title" id="title" placeholder="请输入文章标题" class="layui-input">
                </div>
                <button class="layui-btn" id="searchBtn">搜 索</button>
            </div>
        </div>
        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
            <script type="text/html" id="options">
                <div class="layui-btn-group">
                    @can('zixun.article.edit')
                        <a class="layui-btn layui-btn-sm" lay-event="edit">编辑</a>
                    @endcan
                    @can('zixun.article.destroy')
                        <a class="layui-btn layui-btn-danger layui-btn-sm" lay-event="del">删除</a>
                    @endcan
                </div>
            </script>
            <script type="text/html" id="thumb">
                @{{#  if(d.thumb !==null){ }}
                <a href="@{{d.thumb}}" target="_blank" title="点击查看"><img src="@{{d.thumb}}" alt="" width="28" height="28"></a>
                @{{#  } }}
            </script>
            <script type="text/html" id="tags">
                @{{#  layui.each(d.tags, function(index, item){ }}
                <button type="button" class="layui-btn layui-btn-sm">@{{ item.name }}</button>
                @{{# }); }}
            </script>
            <script type="text/html" id="category">
                @{{ d.category.name }}
            </script>
        </div>
    </div>
@endsection

@section('script')
    @can('zixun.article')
        <script>

            layui.use(['layer','table','form'],function () {
                var layer = layui.layer;
                var form = layui.form;
                var table = layui.table;
                //用户表格初始化
                var dataTable = table.render({
                    elem: '#dataTable'
                    ,autoSort: false
                    ,height: 500
                    ,url: "{{ route('admin.article.data') }}" //数据接口
                    ,page: true //开启分页
                    ,done: function(res, curr, count){
                        //接口回调，处理一些和表格相关的辅助事项
                        if(res.data.length==0 && count>0){
                            dataTable.reload({
                                page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    }
                    ,cols: [[ //表头
                        {checkbox: true,fixed: true}
                        ,{field: 'id', title: 'ID', sort: true,width:80}
                        ,{field: 'category', title: '分类',toolbar:'#category'}
                        ,{field: 'title', title: '标题'}
                        ,{field: 'thumb', title: '缩略图',toolbar:'#thumb',width:100}
                        ,{field: 'keywords', title: '关键词'}
                        ,{field: 'tags', title: '标签',toolbar:'#tags',width:300}
                        ,{field: 'click', title: '点击量'}
                        ,{field: 'created_at', title: '创建时间'}
                        ,{field: 'updated_at', title: '更新时间'}
                        ,{fixed: 'right', width: 220, align:'center', toolbar: '#options'}
                    ]]
                });

                //监听工具条
                table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data //获得当前行数据
                        ,layEvent = obj.event; //获得 lay-event 对应的值
                    if(layEvent === 'del'){
                        layer.confirm('确认删除吗？', function(index){
                            $.post("{{ route('admin.article.destroy') }}",{_method:'delete',ids:[data.id]},function (result) {
                                if (result.code==0){
                                    obj.del(); //删除对应行（tr）的DOM结构
                                }
                                layer.close(index);
                                layer.msg(result.msg)
                                dataTable.reload();
                            });
                        });
                    } else if(layEvent === 'edit'){
                        layer.open({
                            type: 2,
                            shadeClose:true, area: ['100%', '100%'],
                            content: '/admin/article/'+data.id+'/edit',
                            end:function () {
                                dataTable.reload();
                            }
                        });
                    }
                });

                $('#article_add').on('click',function () {
                    layer.open({
                        type: 2,
                        shadeClose: true, area: ['100%', '100%'],
                        content: "{{route('admin.article.create') }}",
                        end: function () {
                            dataTable.reload();
                        }
                    });
                });


                //监听排序事件
                table.on('sort(dataTable)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"

                    //尽管我们的 table 自带排序功能，但并没有请求服务端。
                    //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
                    table.reload('dataTable', {
                        initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。
                        ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                            field: obj.field //排序字段
                            ,order: obj.type //排序方式
                        }
                    });
                });
                @can('zixun.article.edit')
                //监听是否显示
                form.on('switch(isShow)', function(obj){
                    var index = layer.load();
                    var url = $(obj.elem).attr('url')
                    var data = {
                        "is_show" : obj.elem.checked==true?1:0,
                        "_method" : "put"
                    }
                    $.post(url,data,function (res) {
                        layer.close(index)
                        layer.msg(res.msg)
                    },'json');
                });
                @endcan

                //按钮批量删除
                $("#listDelete").click(function () {
                    var ids = []
                    var hasCheck = table.checkStatus('dataTable')
                    var hasCheckData = hasCheck.data
                    if (hasCheckData.length>0){
                        $.each(hasCheckData,function (index,element) {
                            ids.push(element.id)
                        })
                    }
                    if (ids.length>0){
                        layer.confirm('确认删除吗？', function(index){
                            $.post("{{ route('admin.article.destroy') }}",{_method:'delete',ids:ids},function (result) {
                                if (result.code==0){
                                    dataTable.reload()
                                }
                                layer.close(index);
                                layer.msg(result.msg)
                            });
                        })
                    }else {
                        layer.msg('请选择删除项')
                    }
                })

                //搜索
                $("#searchBtn").click(function () {
                    var catId = $("#category_id").val()
                    var title = $("#title").val();
                    dataTable.reload({
                        where:{category_id:catId,title:title},
                        page:{curr:1}
                    })
                })
            })
        </script>
    @endcan
@endsection