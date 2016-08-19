<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{$smarty.const.SITE_TITLE}{if $lang->pagetitle != ''} - {$lang->pagetitle}{/if}</title>
  <link type="text/css" rel="stylesheet" href="/css/jetmenu.css">
  <link type="text/css" rel="stylesheet" href="/css/jquery-ui.css">
  <link type="text/css" rel="stylesheet" href="/css/tablesorter.css">
  <link type="text/css" rel="stylesheet" href="/css/main.css">
  <script type="text/javascript" src="/js/jquery/jquery-1.12.3.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jetmenu.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-ui-1.10.4.custom.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery.tablesorter.min.js"></script>
  <script type="text/javascript" src="/js/main.js"></script>
  <script type="text/javascript" src="/lang/{$member->language}/js/base.js"></script>
</head>
<body>
  <div class="body-container">
    <div class="header-container">
      <div class="logo"><img src="{$smarty.const.LOGO_HEADER}" alt="{$smarty.const.SITE_TITLE}"></div>
      {if $smarty.session.loggedin == true}
        <div class="title">
          <img src="/images/user.png" align="absmiddle"> {$user->username}
        </div>
        <div class="controls">
          <a href="/account">{$base_lang->account}</a>
          <a href="javascript:logout();">{$base_lang->logout}</a>
        </div>
      {/if}
    </div>
    <div class="menu-container">
      <ul class="jetmenu blue">
        {foreach from=$menu item=m}
        <li {if $lang->pageurl == $m->url}class="active"{/if}>
          <a href="{$m->url}">{$menu_lang[$m->url]}</a>
        </li>
        {/foreach}
      </ul>
    </div>
    <div class="content-container">
      {$content}
    </div>
    <div class="footer-container">
      {$smarty.const.SITE_COPYRIGHT}<br>
      <span style="color:#D8D8D8;">{$app_server}</span>
    </div>
  </div>
  <img src="images/loading.gif" style="display:none;"/>
  <div id="mobile-rotate">
    <div><img src="images/mobile-rotate.png"/></div>
  </div>
</body>
</html>