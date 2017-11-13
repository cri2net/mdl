<form action="<?= BASE_URL ?>/post/services/request/" method="POST">
    <div class="row">
    <?php
        foreach ($SERVICES as $key => $s) {
            ?>
            <div class="col-md-4 valign-center">
                <label class="checkbox no-label green"><input name="service[<?= $key ?>]" type="checkbox" class="check-toggle" <?= $_POST['service'][$key] ? 'checked' : '' ?> ><span></span></label>&nbsp;<span><?= $s ?></span>
            </div>
            <?php
        }
    ?>
    </div>
    <div class="form-group">
      <label >Замовник (ПIБ) *</label>
      <input name="fio" required="required" type="text" class="form-txt" placeholder="">
    </div>
    <div class="form-group">
      <label >Адреса</label>
      <input name="address" type="text" class="form-txt" placeholder="">
    </div>
    <div class="form-group">
      <label >Телефони *</label>
      <input name="phones" required="required" type="text" class="form-txt" placeholder="">
    </div>
    <div class="form-group">
      <label >Вид робіт</label>
      <textarea name="worktypes" class="form-txt" rows="4"></textarea>
    </div>
    <div class="form-group">
      <label> Додаткові роботи/матеріали,інше</label>
      <textarea name="workadd" class="form-txt" rows="4"></textarea>
    </div>
    <div class="align-center">
        <input type="hidden" name="action" value="request-service">
        <button class="btn btn-blue btn-md" id="pay_button">Вiдправити</button>
    </div>
</form>
