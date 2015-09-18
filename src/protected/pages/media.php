<script src="https://www.youtube.com/iframe_api"></script>
<h1 class="big-title green h1-media">Відеоматеріали</h1>
<div class="media">
    <?php
        $list = PDO_DB::table_list(TABLE_PREFIX . 'video', "is_active=1 AND url like '%youtube.com%'", 'pos ASC');
        if (count($list) > 0) {
            foreach ($list as $item) {
                
                $parts = parse_url($item['url']);
                parse_str($parts['query'], $query);
                
                if (!$query['v']) {
                    continue;
                }

                if (!$item['title'] || !$item['img_filename']) {
                    $youtube_data = Http::HttpGet('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v='. $query['v'] .'&format=json');
                    $youtube_data = @json_decode($youtube_data);

                    if ($youtube_data === null) {
                        continue;
                    }

                    $item['img_filename'] = ($item['img_filename']) ? $item['img_filename'] : $youtube_data->thumbnail_url;
                    $item['title'] = ($item['title']) ? $item['title'] : $youtube_data->title;
                }

                $date = '';
                if ($item['date']) {
                    $date = getUkraineDate('j m', $item['date']);
                    if (date('Y', $item['date']) != date('Y')) {
                        $date .= date(' Y', $item['date']);
                    }
                }

                ?>
                <div class="thumbnail">
                    <div class="thumb-date"><?= $date; ?>&nbsp;</div>
                    <div class="thumb-container" style="background-image:url('<?= $item['img_filename']; ?>')"><div class="btn-play" onclick="open_video_frame(this, '<?= $query['v']; ?>'); return false;"></div></div>
                    <div class="thumb-title"><b><?= htmlspecialchars($item['title']); ?></b> <?= htmlspecialchars($item['description']); ?></div>
                </div>
                <?php
            }
        }
    ?>
</div>
<button class="btn" onclick="playFullscreen();" id="sdfsdfsf">sdfsdfsf</button>
