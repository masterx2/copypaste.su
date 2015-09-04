<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="format-detection" content="telephone=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="694264213587-q6uleka9emsvjhlu2ls8qgce4qk4fr7f.apps.googleusercontent.com">
    <meta name="theme-color" content="#002616">
    <title>Copypaste service</title>
    <link rel="stylesheet" href="static/css/bootstrap.min.css">
    <link rel="stylesheet" href="static/css/style.css">
    <link rel="stylesheet" href="static/css/pnotify.custom.min.css">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="//yastatic.net/jquery/2.1.3/jquery.min.js"></script>
    <script src="static/js/jquery.form.min.js"></script>
    <script src="static/js/bootstrap.min.js"></script>
    <script src="static/js/pnotify.custom.min.js"></script>
    <script src="static/js/moment.js"></script>
    <script src="static/js/main.js"></script>
    </head>
<body>
<div id="top-nav" class="navbar-fixed-top">
  <div class="row">
    <div class="col-xs-12 col-xs-offset-0 col-sm-12 col-sm-offset-0 col-md-12 col-md-offset-0 col-lg-8 col-lg-offset-2">
      <h1 id="title">Copypaste <span class="beta">beta</span></h1>
      <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_donations">
        <input type="hidden" name="business" value="6XSCR3UCBT79A">
        <input type="hidden" name="lc" value="RU">
        <input type="hidden" name="item_name" value="Copypaste">
        <input type="hidden" name="currency_code" value="USD">
        <input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHosted">
        <button class="beer" type="submit" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">Buy me a beer</button>
      </form>
    </div>
  </div>
</div>
<div id="content">
  <div class="row">
    <div class="col-sm-2 col-sm-offset-0 col-lg-1 col-lg-offset-2">
      <span class="title">Result URL</span>
    </div>
    <div class="col-sm-8 col-lg-6">
      <input type="text" id="urlbox" placeholder="Insert long url here and press 'Short' or Enter"/>
    </div>
    <div class="col-sm-2 col-xs-12 col-md-2 col-lg-1">
      <button id="short">Short</button>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
      <div class="row-header">Advanced options</div>
      <input type="checkbox" id="secure" name="secure">
      <label for="secure"><span>Secure Link</span></label>
      <input type="checkbox" id="zip" name="zip">
      <label for="zip"><span>Compress</span></label>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
      <input type="file" id="filebox" multiple>
      <div id="drop-area">
        <span class="drop-message">Drop file(s) or click here for upload</span>
        <div class="upload-progress">
          <div class="upload-bar" style="width: 0%">
            <span class="upload-progress-title">0% Complete</span>
          </div>
        </div>
      </div>
      <div class="file-description">You can upload up to 500Mb files</div>
    </div>
  </div>
  <div class="row links">
    <div class="col-sm-4 col-sm-offset-2 col-lg-3 col-lg-offset-3">
      <div class="lead">Last Links</div>
      {if $last_links}
        <ul>
        {foreach $last_links as $link}
        <li class="{$link.type}">
            <a href="http://cppt.su/{$link.url}" target="_blank">{$link.url}</a>
            <span class="count">{$link.click_count}</span>
            <span class="origin">
                {if $link.type == 'file'} 
                    {$link.original_url|urldecode|substr:33|truncate:20:"..."}
                {elseif $link.type == 'zip'} 
                    {$link.original_url|urldecode|substr:33|truncate:20:"..."}
                    {if isset($link.extra_data)}
                    <span class="file-stat-show">></span>
                    {/if}
                {else}
                    {$link.original_url|urldecode|truncate:20:"..."}
                {/if}
            </span>
            <span class="last-click">{$link.last_click}</span>
            {if isset($link.extra_data)}
            <div class="file-stat">
                <ul>
                {foreach $link.extra_data as $data}
                    <li>
                        <span class="file-name">{$data.name|truncate:25:"..."}</span>
                        <span class="file-size">{$data.size} bytes</span>
                    </li>
                {/foreach}
                </ul>
            </div>
            {/if}
        </li>
        {/foreach}
        {else}
        <span class="nothing-message">
            Nothing yet.. upload some files
        </span>
        {/if}
        </ul>
    </div>
    <div class="col-sm-4 col-lg-3">
      <div class="lead">Top Links</div>
       {if $top_links}
        <ul>
        {foreach $top_links as $link}
        <li class="{$link.type}">
            <a href="http://cppt.su/{$link.url}" target="_blank">{$link.url}</a>
            <span class="count">{$link.click_count}</span>
            <span class="origin">
                {if $link.type == 'file'} 
                    {$link.original_url|urldecode|substr:33|truncate:20:"..."}
                {elseif $link.type == 'zip'} 
                    {$link.original_url|urldecode|substr:33|truncate:20:"..."}
                    {if isset($link.extra_data)}
                    <span class="file-stat-show">></span>
                    {/if}
                {else}
                    {$link.original_url|urldecode|truncate:20:"..."}
                {/if}
            </span>
            <span class="last-click">{$link.last_click}</span>
            {if isset($link.extra_data)}
            <div class="file-stat">
                <ul>
                {foreach $link.extra_data as $data}
                    <li>
                        <span class="file-name">{$data.name|truncate:25:"..."}</span>
                        <span class="file-size">{$data.size} bytes</span>
                    </li>
                {/foreach}
                </ul>
            </div>
            {/if}
        </li>
        {/foreach}
        {else}
        <span class="nothing-message">
            Or short some big-long-ugly link...
        </span>
        {/if}
        </ul>
    </div>
  </div>
</div>
<script type="text/javascript">document.write('<script type="text/javascript" charset="utf-8" async="true" id="onicon_loader" src="http://cp.onicon.ru/js/simple_loader.js?site_id=55e04a1caba19aab638b4567&srv=1&lang=en&' + (new Date).getTime() + '"></scr' + 'ipt>');
</script>
</body>
