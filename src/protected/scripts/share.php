<?php

$title_for_share = (isset($title_for_share)) ? $title_for_share : '';

$widjet_like_html =
    '
    <div class="social">'.
        // '<a class="social-vk" target="_blank" href="https://vk.com/share.php?url='.urlencode($url_for_share).'"></a>'.
        '<a class="social-fb" target="_blank" href="https://www.facebook.com/sharer/sharer.php?s=100&amp;p[url]='.urlencode($url_for_share).'"></a>'.
        '<a class="social-twitter" target="_blank" href="https://twitter.com/intent/tweet?source=tweetbutton&amp;text='.rawurldecode($title_for_share).'&amp;original_referer='.urlencode($url_for_share).'&amp;url='.urlencode($url_for_share).'"></a>'.
        // '<a class="social-gplus" target="_blank" href="https://plus.google.com/share?url='.urlencode($url_for_share).'"></a>'.
    '</div>';

if (!isset($share_do_not_echo) || !$share_do_not_echo) {
    echo $widjet_like_html;
}
