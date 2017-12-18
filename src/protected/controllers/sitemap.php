<?php
use cri2net\php_pdo_db\PDO_DB;

header("Content-Type: text/xml, charset=utf-8");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");              // дата в прошлом
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // всегда модифицируется
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                    // HTTP/1.0

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";

$stm = PDO_DB::query("SELECT MAX(updated_at) FROM ". TABLE_PREFIX ."news WHERE is_actual=1");
$news_timestamp = $stm->fetchColumn();
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<url>
    <loc><?= BASE_URL; ?>/</loc>
    <changefreq>always</changefreq>
    <priority>1.00</priority>
    <lastmod>2013-10-25</lastmod>
</url>
<url>
    <loc><?= BASE_URL; ?>/news/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
    <lastmod><?= date("Y-m-d", $news_timestamp); ?></lastmod>
</url>
<url>
    <loc><?= BASE_URL; ?>/services-list/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
</url>
<url>
    <loc><?= BASE_URL; ?>/services/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
</url>
<url>
    <loc><?= BASE_URL; ?>/services/request/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
</url>
<url>
    <loc><?= BASE_URL; ?>/service-centers/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
</url>
<url>
    <loc><?= BASE_URL; ?>/feedback/</loc>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
</url>
<url>
    <loc><?= BASE_URL; ?>/cabinet/</loc>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
</url>

<?php
    $news = PDO_DB::table_list(TABLE_PREFIX . "news", "is_actual = 1", "created_at DESC");
    foreach ($news as $item) {
        getNewsURL
        ?>
        <url>
            <loc><?= BASE_URL; ?>/news/<?= composeUrlKey($item['title']) . '_' . $item['id']; ?>/</loc>
            <changefreq>yearly</changefreq>
            <priority>0.5</priority>
            <lastmod><?= date("Y-m-d", $item['updated_at']); ?></lastmod>
        </url>
        <?php
    }

    $pages = PDO_DB::table_list(TABLE_PREFIX . "pages", "is_active = 1", "created_at DESC");
    foreach ($pages as $item) {
        getNewsURL
        ?>
        <url>
            <loc><?= BASE_URL; ?><?= StaticPage::getPath($item['id']); ?></loc>
            <changefreq>monthly</changefreq>
            <priority>0.5</priority>
            <lastmod><?= date("Y-m-d", $item['updated_at']); ?></lastmod>
        </url>
        <?php
    }
?>
</urlset>
