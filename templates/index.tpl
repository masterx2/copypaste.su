<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Copypaste service</title>
    <link rel="stylesheet" href="static/css/style.css">
    <script src="//yastatic.net/jquery/2.1.3/jquery.min.js"></script>
    <script src="static/js/moment.js"></script>
    <script src="static/js/main.js"></script>
</head>
<body>
    <div id="wrap">
        <h1>CopyPaste Service <small>beta</small></h1>
        <ul class="cat-list">
            <li><span class="cat-title">Result URL</span>
            <input type="text" id="urlbox" placeholder="Insert long url here and press 'Short'"/><button id="short">Short</button>
            </li>
            <li>
            <input type="file" id="filebox"/>
            <div id="drop-area">Drop file or click here for upload</div>
            <span class="description">You can upload up to 30Mb file, it will be automatically deleted after seven days</span>
            </li>
        </ul>
        <div id="last_links" class="links_stat">
            <h3>20 Last Links</h3>
            {if $last_links}
            <ol>
            {foreach $last_links as $link}
            <li class="{$link.type}">
                <a href="http://cppt.su/{$link.url}" target="_blank">{$link.url}</a>
                <span class="count">{$link.click_count}</span>
                <span class="origin">
                    {if $link.type == 'file'} 
                        {$link.original_url|urldecode|substr:33|truncate:20:"..."}
                    {else}
                        {$link.original_url|urldecode|truncate:20:"..."}
                    {/if}
                </span>
                <span class="last_click">{$link.last_click}</span>
            </li>
            {/foreach}
            {else}
            <span class="nothing-message">
                Nothing yet.. upload some files
            </span>
            {/if}
            </ol>
        </div>
        <div id="top_links" class="links_stat">
            <h3>20 Top Links</h3>
            {if $top_links}
            <ol>
            {foreach $top_links as $link}
            <li class="{$link.type}">
                <a href="http://cppt.su/{$link.url}" target="_blank">{$link.url}</a>
                <span class="count">{$link.click_count}</span>
                <span class="origin">
                    {if $link.type == 'file'} 
                        {$link.original_url|urldecode|substr:33|truncate:20:"..."}
                    {else}
                        {$link.original_url|urldecode|truncate:20:"..."}
                    {/if}
                </span>
                <span class="last_click">{$link.last_click}</span>
            </li>
            {/foreach}
            {else}
            <span class="nothing-message">
                Or short some big-long-ugly link...
            </span>
            {/if}
            </ol>
        </div>
    </div>
    <div class="clear-fix"></div>
    {if $last_links|length >= 20}
    <div class="paypal">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/RU/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</div>
{/if}
</body>
</html>
