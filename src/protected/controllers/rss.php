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
            
            $postfix = "";
            $stm = PDO_DB::query("SELECT COUNT(*) AS `count` FROM ". News::IMAGES_TABLE ." WHERE `news_id`='{$item['id']}'");
            
            if ($stm->fetchColumn() > 0) {
                $postfix = " ({$row['count']} ФОТО)";
            }

            $desc = $item['text'];
            $img = PDO_DB::first(News::IMAGES_TABLE, "news_id={$item['id']} AND `is_main`=1");
            if ($img) {
                $desc = "<img src='" . BASE_URL . "/db_pic/news/500x500wm/{$img['filename']}.jpg' alt='' /><br>" . $desc;
            }
            
            ?><item>
                <title><![CDATA[<?= htmlspecialchars_decode($item['title']) . $postfix; ?>]]></title>
                <link><?= BASE_URL; ?>/news/<?= composeUrlKey($item['title']); ?>_<?= $item['id']; ?>/</link>
                <pubDate><?= date("r", $item['created_at']); ?></pubDate>
                <description><![CDATA[<?= $desc; ?>]]></description>
            </item>
            <?php
        }
    ?>
  </channel>
</rss>
