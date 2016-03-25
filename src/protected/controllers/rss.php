<?php
    header("Content-Type: text/xml, charset=utf-8");
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
  <channel>
    <title><![CDATA[<?= SITE_NAME; ?>]]></title>
    <link><?= BASE_URL; ?></link>
    <description><![CDATA[<?= SITE_DESCRIPTION; ?>]]></description>
    <pubDate><?= date("r"); ?></pubDate>
    <lastBuildDate><?= date("r"); ?></lastBuildDate>
    <language>uk</language>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <?php
        $list = PDO_DB::table_list(News::TABLE, "is_actual=1", "`created_at` DESC");
        
        foreach ($list as $item) {
            ?><item>
                <title><![CDATA[<?= htmlspecialchars_decode($item['title']); ?>]]></title>
                <link><?= BASE_URL; ?>/news/<?= composeUrlKey($item['title']); ?>_<?= $item['id']; ?>/</link>
                <pubDate><?= date("r", $item['created_at']); ?></pubDate>
                <description><![CDATA[<?= $item['text']; ?>]]></description>
            </item>
            <?php
        }
    ?>
  </channel>
</rss>
