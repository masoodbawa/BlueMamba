<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>{$smarty.const.SITE_TITLE}{if $lang->pagetitle != ''} - {$lang->pagetitle}{/if}</title>
  <link type="text/css" rel="stylesheet" href="/css/jquery-ui.css">
  <link type="text/css" rel="stylesheet" href="/css/login.css">
  <script type="text/javascript" src="/js/login.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-1.12.3.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-ui-1.10.4.custom.min.js"></script>
  {include file="widget/analytics.tpl"}
</head>
<body>
  <div class="login-page">
    <div class="form">

      <div class="logo"><a href="{$smarty.const.SITE_LINK}"><img src="{$smarty.const.LOGO_LOGIN}" alt="{$smarty.const.SITE_TITLE}"></a></div>
 
      {if $login_error != ""}
        <div class="error_container">
          <span class="error_message">
            {$login_error}
          </span>
        </div>
      {/if}

      <form name="loginform" method="post" action="{$smarty.const.SECURE_ROOT}login" style="display:inline;">
        <input name="action" type="hidden" value="login">
        <input name="default_lang" type="hidden" value="{$default_lang}">
        <input type="text" name="username" id="username" placeholder="{$lang->username}"/>
        <input type="password" name="password" id="password" placeholder="{$lang->password}"/>
        <button type="button" onclick="login();">{$lang->login_button}</button>
        <div class="message">
          {$lang->not_registered} <a href="{$smarty.const.SECURE_ROOT}signup{if $smarty.request.lang != ''}?lang={$smarty.request.lang}{/if}">{$lang->create_account}</a>
        </div>
      </form>

    </div>
        
    <select name="select_lang" id="lang" class="language" onchange="changeLanguage();">
    {foreach from=$languages item=l}
      <option value="{$l->code}" {if $l->code==$default_lang}selected{/if}>{$l->name}</option>
    {/foreach}
    </select>
  </div>

</body>
</html>




    