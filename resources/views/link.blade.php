<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kcwiki API</title>
    <link rel="stylesheet" href="/css/semantic.min.css">
    <link rel="shortcut icon" href="/favicon.ico">
    <style>
        .ui.blue.segment { margin-top: 100px; padding-bottom: 0px; }
        body { background-color: #E8ECF2; }
    </style>
</head>
<body>
    <div class="ui three column centered stackable grid">
        <div class="column">
            <div class="ui attached blue segment">
                <h2 class="ui centered dividing header">
                    舰娘百科API
                    <div class="sub header">公开的API集合，{var}表示可变参数</div>
                </h2>
                <div class="ui relaxed divided list">
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="/tweet/15">舰娘官方推特转发</a>
                            <div class="description">api.kcwiki.moe/tweet/{num}</div>
                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="/avatar/latest">舰娘官方推特头像</a>
                            <div class="description">api.kcwiki.moe/avatar/latest</div>

                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="/ship/1">舰娘基本信息（例：睦月）</a>
                            <div class="description">api.kcwiki.moe/ship/{ID或舰娘名称}</div>
                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="/slotitem/1">装备基本信息</a>
                            <div class="description">api.kcwiki.moe/slotitem/{ID}</div>
                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="http:/start2">Start2 数据</a>
                            <div class="description">api.kcwiki.moe/start2</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ui bottom attached center aligned segment">
                <a href="https://github.com/kcwikizh/kcwiki-api/wiki">详情参考文档</a>
            </div>
        </div>
    </div>
    <script src="/js/jquery.js"></script>
    <script src="/js/semantic.min.js"></script>
</body>
</html>
