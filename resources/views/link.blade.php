<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kcwiki 新闻API</title>
    <link rel="stylesheet" href="/css/semantic.min.css">
    <style>
        .ui.blue.segment {
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <div class="ui three column centered stackable grid">
        <div class="column">
            <div class="ui blue segment">
                <h2 class="ui centered dividing header">
                    舰娘百科API
                    <div class="sub header">公开的API集合，{var}表示可变参数</div>
                </h2>
                <div class="ui relaxed divided list">
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="http://t.kcwiki.moe/?json=1&count=15">舰娘官方推特转发</a>
                            <div class="description">http://t.kcwiki.moe/?json=1&amp;count={num}</div>
                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="http://static.kcwiki.moe/KanColleStaffAvatar.png">舰娘官方推特头像</a>
                            <div class="description">http://static.kcwiki.moe/KanColleStaffAvatar.png</div>

                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="http://static.kcwiki.moe/KanColleStaffAvatar.png">舰娘抢号时间与服务器维护时间</a>
                            <div class="description">http://zh.kcwiki.moe/api.php?action=parse&amp;text=@{{更新内容}}&amp;format=json</div>
                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="http://db.kcwiki.moe/wiki/enemy/23.json">敌舰配置数据</a>
                            <div class="description">http://db.kcwiki.moe/wiki/enemy/{num}.json</div>
                        </div>
                    </div>
                    <div class="item"> <i class="large linkify middle aligned icon"></i>
                        <div class="content">
                            <a class="header" href="http://api.kcwiki.moe/news">舰娘百科移动端新闻API</a>
                            <div class="description">http://api.kcwiki.moe/news</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <script src="/js/jquery.js"></script>
    <script src="/js/semantic.min.js"></script>
</body>
</html>