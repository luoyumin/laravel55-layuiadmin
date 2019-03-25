{{csrf_field()}}
<div class="layui-form-item">
    <label for="" class="layui-form-label">名称<em style="color: red">*</em></label>
    <div class="layui-input-inline">
        <input type="text" name="name" value="{{ $position->name ?? old('name') }}" lay-verify="required" placeholder="请输入名称" class="layui-input" >
    </div>
</div>

<div class="layui-form-item">
    <label for="" class="layui-form-label">排序</label>
    <div class="layui-input-inline">
        <input type="number" name="sort" value="{{ $position->sort ?? 0 }}" lay-verify="required|number" placeholder="请输入数字" class="layui-input" >
    </div>
</div>
<div class="layui-form-item">
    <div class="layui-input-block">
        <button type="submit" class="layui-btn" lay-submit="" lay-filter="formDemo">确 认</button>
        <div  class="layui-btn close-iframe" onclick="close_parent('{{$position->name ??''}}','/admin/position')">关闭</div>
    </div>
</div>
@section('script')
    @include('admin.common_edit')
@endsection