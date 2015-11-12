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
                    <a href="/" class="item">首页</a>
                    <a href="/news" class="item">新闻JSON</a>
                    <a href="/meta" class="active item">API数据对照表</a>
                    <div class="right menu">
                        <a href="/login" class="item">登录</a>
                        <a href="/logout" class="item">登出</a>
                    </div>
                </div>
                <h3>舰娘对照表</h3>
                <table class="ui celled table ship">
                    <thead>
                        <tr>
                            <th>编号</th>
                            <th>名称</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ships as $ship)
                        <tr>
                            <td>{{$ship->id}}</td>
                            <td>{{$ship->name}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <h3>地图对照表</h3>
                <table class="ui celled table map">
                    <thead>
                        <tr>
                            <th>编号</th>
                            <th>名称</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maps as $map)
                        <tr>
                            <td>{{$map->id}}</td>
                            <td>{{$map->name}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <h3>装备对照表</h3>
                <table class="ui celled table equip">
                    <thead>
                        <tr>
                            <th>编号</th>
                            <th>名称</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equips as $equip)
                        <tr>
                            <td>{{$equip->id}}</td>
                            <td>{{$equip->name}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="/js/jquery.js"></script>
    <script src="/js/semantic.min.js"></script>
</body>
</html>