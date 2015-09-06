<h1 class="big-title">Керівництво</h1>
<?php
    $list = PDO_DB::table_list(DB_TBL_CHIEF, "is_active=1", "pos ASC");

    if (count($list) > 0) {
        ?>
        <div class="chief-line">
            <?php
                for ($i=0; $i < count($list); $i++) {
                    if (($i > 0) && ($i % 2 == 0)) {
                        ?></div><div class="chief-line"><?php
                    }
                    ?>
                    <div class="chief-item">
                        <div class="chief-icon" <?= ($list[$i]['icon']) ? 'style="background-image:url('. BASE_URL .'/db_pic/chief/'. $list[$i]['icon'] . ')"' : ''; ?>>
                        </div>
                        <div class="chief-right">
                            <div class="lastname"><?= htmlspecialchars($list[$i]['lastname']); ?></div>
                            <div class="name"><?= htmlspecialchars("{$list[$i]['name']} {$list[$i]['fathername']}"); ?></div>
                            <div class="role"><?= htmlspecialchars($list[$i]['role']); ?></div>
                            <div onclick="open_feedback_msg('<?= $list[$i]['id']; ?>');" class="btn green bold"><div class="icon-msg"></div>Надiслати повiдомлення</div>
                        </div>
                    </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }
