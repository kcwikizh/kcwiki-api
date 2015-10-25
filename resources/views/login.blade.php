<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Kcwiki 新闻API</title>
	<link rel="stylesheet" href="/css/semantic.min.css">
	<style>
		.ui.blue.segment {
			margin-top: 140px;
		}
	</style>
</head>
<body>
   <div class="ui three column centered stackable grid">
   <div class="column">
    <div class="ui blue segment">
        <h2 class="ui centered dividing header">
            舰娘百科API
            <div class="sub header">
                现在可以说啥都没有
            </div>
        </h2>
        <form action="login" method="post" class="ui form">
           <div class="field">
               <div class="ui left icon input">
                   <i class="user icon"></i>
                    <input name="email" type="text" placeholder="用户名">
                </div>
            </div>
            <div class="field">
                <div class="ui left icon input">
                    <i class="privacy icon"></i>
                    <input name="password" type="password" placeholder="密码">
                </div>
            </div>
            <input name="_token" type="hidden" value= "{{ csrf_token() }}">
            <div class="ui error message">
            </div>
            <div class="ui inverted divider"></div>
            <input type="submit" value="登录" class="ui blue submit button right floated">
            <div style="clear:both;"></div>            
        </form>
    </div>
  </div>
</div>  
	<script src="/js/jquery.js"></script>
	<script src="/js/semantic.min.js"></script>
</body>
</html>