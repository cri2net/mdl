<div class="portlet">
    <div class="row" style="padding-left: 25px;">
        <div class="check-box-line">
            <span class="niceCheck <?= ($__userData['notify_email']) ? 'checked' : ''; ?>" id="checkbox_notify_email"><input onclick="$('#checkbox_notify_email').click();" type="checkbox" <?= ($__userData['notify_email']) ? 'checked' : ''; ?> name="notify_email"></span>
            <label onclick="$('#checkbox_notify_email').click();">
                Отримувати листи з новинами
            </label>
        </div>
    </div>
    <div class="row" style="padding-left: 25px;">
        <?php
            $houses = Flat::getUserFlats($__userData['id']);

            if (count($houses) > 0) {
                $have_checked_object = false;
                foreach ($houses as $house) {
                    $have_checked_object = ($have_checked_object || $house['notify']);
                }
                ?>
                <div class="all-objects-checkbox">
                    <div class="check-box-line">
                        <span class="niceCheck check-group-rule <?= ($have_checked_object) ? 'checked' : ''; ?>" id="check-group-rule"><input onclick="$('#check-group-rule').click();" <?= ($have_checked_object) ? 'checked' : ''; ?> type="checkbox"></span>
                        <label onclick="$('#check-group-rule').click();">
                            Отримувати листи з рахунками-повідомленнями для усіх об’єктів
                        </label>
                    </div>
                    <div style="margin-left: 50px;" class="checkboxes">
                        <?php
                            foreach ($houses as $house) {
                                ?>
                                <div class="check-box-line">
                                    <span class="niceCheck check-group <?= ($house['notify']) ? 'checked' : ''; ?>" id="checkbox_notify_object_<?= $house['id']; ?>"><span class="dotted-line"></span><input <?= ($house['notify']) ? 'checked' : ''; ?> type="checkbox" name="notify_object_<?= $house['id']; ?>" onclick="$('#checkbox_notify_object_<?= $house['id']; ?>').click();"></span>
                                    <label onclick="$('#checkbox_notify_object_<?= $house['id']; ?>').click();">
                                        <?php
                                            if ($house['title']) {
                                                echo htmlspecialchars($house['title']), '<br><span class="hint">', $house['address'], '</span>';
                                            } else {
                                                echo $house['address'];
                                            }
                                        ?>
                                    </label>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                </div>
                <?php
            }
        ?>
        <div class="input with-btn">
            <button class="btn btn-blue btn-md">Зберегти</button>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    $(".niceCheck").click(function() {
        changeCheck($(this), 'check-group');
    });
});
</script>
