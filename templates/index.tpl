<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>COPYPASTE</title>
    <link rel="stylesheet" href="static/css/style.css">
    <script src="//yastatic.net/jquery/2.1.3/jquery.min.js"></script>
    <script src="static/js/moment.js"></script>
    <script src="static/js/main.js"></script>
</head>
<body>
    <div id="wrap">
        <h1>CopyPaste Service</h1>
        <ul class="cat-list">
            <li><span class="cat-title">Url Shortener</span>
            <input type="text" id="urlbox"/><button id="short">Short</button>
            </li>
            <!-- <li><span class="cat-title">Image Hosting Service</span>
            <input type="file" id="imagebox"/>
            </li>
            <li><span class="cat-title">File Hosting Service</span>
            <input type="file" id="filebox"/>
            </li> -->
        </ul>
        <div id="last_links" class="links_stat">
            <h3>20 Last Links</h3>
            <ol>
            {foreach $last_links as $link}
            <li>
                <a href="http://cppt.su/{$link.url}" target="_blank">{$link.url}</a>
                <span class="count">{$link.click_count}</span>
                <span class="origin">{$link.original_url|urldecode|truncate:20:"..."}</span>
                <span class="last_click">{$link.last_click}</span>
            </li>
            {/foreach}
            </ol>
        </div>
        <div id="top_links" class="links_stat">
            <h3>20 Top Links</h3>
            <ol>
            {foreach $top_links as $link}
            <li>
                <a href="http://cppt.su/{$link.url}" target="_blank">{$link.url}</a>
                <span class="count">{$link.click_count}</span>
                <span class="origin">{$link.original_url|urldecode|truncate:20:"..."}</span>
                <span class="last_click">{$link.last_click}</span>
            </li>
            {/foreach}
            </ol>
        </div>
    </div>
</body>
</html>
