<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kcwiki 新闻API</title>
    <link rel="stylesheet" href="/css/semantic.min.css">
    <style>
        .ui.blue.segment {
            margin-top: 50px;
        }
    
    </style>
</head>
<body>
    <div class="ui two column centered stackable grid">
        <div class="column">
            <div class="ui blue segment">
                <h2 class="ui centered dividing header">舰娘百科新闻</h2>
                <div class="ui blue secondary pointing menu">
                    <a href="/" class="active item">首页</a>
                    <a href="/news" class="item">新闻JSON</a>
                    <a href="/meta" class="item">API数据对照表</a>
                    <div class="right menu">
                        <a href="/login" class="item">登录</a>
                        <a href="/logout" class="item">登出</a>
                    </div>
                </div>
                @if (Session::has('success'))
                <div class="ui positive message">
                    <div class="header">{{ Session::get('success') }}</div>
                </div>
                @endif
                @if (count($errors) > 0)
                <div class="ui error message">
                    <div class="header">表单上传错误</div>
                    <ul class="list">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <h3>浏览新闻</h3>
                <div class="ui styled fluid accordion">
                    @foreach ($news as $new)
                    <div class="title"> <i class="dropdown icon"></i>
                        {{ $new->title }}
                    </div>
                    <div class="content">
                        <form action="news/{{$new->id}}" method="post" class="ui form news-{{$new->id}}">
                            <input name="_token" type="hidden" value= "{{ csrf_token() }}">
                            <div class="field">
                                <label>标题</label>
                                <input name="title" type="text" value="{{ $new->title }}" placeholder="请输入新闻标题"></div>
                            <div class="field">
                                <label>舰娘</label>
                                <select class="ui fluid search dropdown ship" multiple="" name="ship[]">
                                <option value="">搜索或选择新闻更新相关的舰娘</option>
                                @foreach($ships as $ship)
                                <option value="{{ $ship->id }}">{{ $ship->name }}({{ $ship->id }})</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>装备</label>
                                <select class="ui fluid search dropdown equip" multiple="" name="equip[]">
                                <option value="">搜索或选择新闻更新相关的装备</option>
                                @foreach($equips as $equip)
                                <option value="{{ $equip->id }}">{{ $equip->name }}({{ $equip->id }})</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>地图</label>
                                <select class="ui fluid search dropdown quest" multiple="" name="quest[]">
                                <option value="">搜索或选择新闻更新相关的地图</option>
                                @foreach($maps as $map)
                                <option value="{{ $map->id }}">{{ $map->name }}({{ $map->id }})</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>内容</label>
                                <textarea name="content" cols="30" rows="5">{{ $new->content }}</textarea>
                            </div>
                            <div class="ui inverted divider"></div>
                            <input type="submit" value="编辑" class="ui green button right floated">
                        </form>
                        <form action="news/{{$new->id}}" method="post">
                            <input name="_token" type="hidden" value= "{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="delete">
                            <input type="submit" value="删除" class="ui red button right floated">
                        </form>
                        <div style="clear:both;"></div>
                    </div>
                    @endforeach
                </div>
                <h3>创建新闻</h3>
                <form action="news" method="post" class="ui form">
                    <input name="_token" type="hidden" value= "{{ csrf_token() }}">
                    <div class="field">
                        <label>标题</label>
                        <input name="title" type="text" value="" placeholder="输入新闻标题"></div>
                    <div class="field">
                        <label>舰娘</label>
                        <select class="ui fluid search dropdown" multiple="" name="ship[]">
                            <option value="">搜索或选择新闻更新相关的舰娘</option>
                            @foreach($ships as $ship)
                            <option value="{{ $ship->id }}">{{ $ship->name }}({{ $ship->id }})</option>
                            @endforeach
                        </select>
                    <div class="field">
                        <label>装备</label>
                        <select class="ui fluid search dropdown" multiple="" name="equip[]">
                        <option value="">搜索或选择新闻更新相关的装备</option>
                        @foreach($equips as $equip)
                        <option value="{{ $equip->id }}">{{ $equip->name }}({{ $equip->id }})</option>
                        @endforeach
                        </select>
                    <div class="field">
                        <label>地图</label>
                        <select class="ui fluid search dropdown" multiple="" name="quest[]">
                        <option value="">搜索或选择新闻更新相关的地图</option>
                        @foreach($maps as $map)
                        <option value="{{ $map->id }}">{{ $map->name }}({{ $map->id }})</option>
                        @endforeach
                        </select>
                    <div class="field">
                        <label>内容</label>
                        <textarea name="content" cols="30" rows="5"></textarea>
                    </div>
                    <div class="ui inverted divider"></div>
                    <input type="submit" value="创建" class="ui primary button right floated">
                    <div style="clear:both;"></div>
                </form>
            </div>
        </div>
    </div>
    <script src="/js/jquery.js"></script>
    <script src="/js/semantic.min.js"></script>
    <script>
        $(function(){
            $('.ui.dropdown').dropdown();
            $('.ui.accordion').accordion();
            var className = '';
            var raw = '';
            @foreach($news as $new)
            className = '.news-{{$new->id}}';
            raw = '{{$new->ship}}';
            $(className + ' .ship').dropdown('set selected', raw.split(','));
            raw = '{{$new->quest}}';
            $(className + ' .quest').dropdown('set selected', raw.split(','));
            raw = '{{$new->equip}}';
            $(className + ' .equip').dropdown('set selected', raw.split(','));
            @endforeach
        });        
    </script>
</body>
</html>