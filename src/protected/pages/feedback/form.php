<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<form action="<?= BASE_URL ?>/post/feedback/" method="POST">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
              <label >Прізвище</label>
              <input name="surname" value="<?= (isset($_SESSION['feedback']['surname'])) ? htmlspecialchars($_SESSION['feedback']['surname'], ENT_QUOTES) : ''; ?>" required="required" type="text" class="form-control" placeholder="">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
              <label >І’мя</label>
              <input name="name" value="<?= (isset($_SESSION['feedback']['name'])) ? htmlspecialchars($_SESSION['feedback']['name'], ENT_QUOTES) : ''; ?>" required="required" type="text" class="form-control" placeholder="">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
              <label >По-батькові</label>
              <input name="fathername" value="<?= (isset($_SESSION['feedback']['fathername'])) ? htmlspecialchars($_SESSION['feedback']['fathername'], ENT_QUOTES) : ''; ?>" required="required" type="text" class="form-control" placeholder="">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Район</label>
                <select name="region" class="form-control" required="required">
                    <option value="">-- оберіть район --</option>
                    <?php
                        $regions = PDO_DB::table_list(TABLE_PREFIX . 'dict_regions');
                        foreach ($regions as $r) {
                            ?><option value="<?= $r['id'] ?>"><?= $r['title'] ?></option><?php
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-1 col-xs-4">
            <div class="form-group">
                <label>Тип</label>
                <select name="street_type" class="form-control" >
                    <option value="street" >вулиця</option>
                    <option value="blvd" >бульвар</option>
                    <option value="ave" >проспект</option>
                    <option value="lane" >провулок</option>
                </select>
            </div>
        </div>
        <div class="col-md-3 col-xs-8">
            <div class="form-group">
                <label>Назва вулиці</label>
                <input name="street" value="<?= (isset($_SESSION['feedback']['street'])) ? htmlspecialchars($_SESSION['feedback']['street'], ENT_QUOTES) : ''; ?>" type="text" class="form-control" >
            </div>
        </div>
        <div class="col-md-2 col-xs-6">
            <div class="form-group">
                <label>№ будинку</label>
                <input name="house" value="<?= (isset($_SESSION['feedback']['house'])) ? htmlspecialchars($_SESSION['feedback']['house'], ENT_QUOTES) : ''; ?>" type="text" class="form-control" >
            </div>
        </div>
        <div class="col-md-2 col-xs-6">
            <div class="form-group">
                <label>№ квартири</label>
                <input name="flat" value="<?= (isset($_SESSION['feedback']['flat'])) ? htmlspecialchars($_SESSION['feedback']['flat'], ENT_QUOTES) : ''; ?>" type="text" class="form-control" >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
              <label>Телефон</label>
              <input name="phone" value="<?= (isset($_SESSION['feedback']['phone'])) ? htmlspecialchars($_SESSION['feedback']['phone'], ENT_QUOTES) : ''; ?>" type="text" class="form-control" required="required" >
              <p class="help-block">Формат: +380-XXX-XX-XX</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
              <label>E-mail</label>
              <input name="email" value="<?= (isset($_SESSION['feedback']['email'])) ? htmlspecialchars($_SESSION['feedback']['email'], ENT_QUOTES) : ''; ?>" type="text" class="form-control" required="required" >
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>Напрямок питання</label>
                <select name="id_theme" class="form-control" >
                    <option>-- оберiть --</option>
                    <?php
                        $themes = PDO_DB::table_list(TABLE_PREFIX . 'dict_feedback_themes');
                        foreach ($themes as $t) {
                            ?><option value="<?= $t['id'] ?>" ><?= htmlspecialchars($t['title']); ?></option><?php
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label>Суть питання</label>
                <textarea name="text" cols="30" rows="10" class="form-control" required="required" ><?= (isset($_SESSION['feedback']['text'])) ? htmlspecialchars($_SESSION['feedback']['text'], ENT_QUOTES) : ''; ?></textarea>
            </div>
        </div>
        <div class="col-md-12">
            <div class="valign-center align-center text" style="margin-top: 20px;">
                <label class="checkbox no-label green" ><input name="agree" type="checkbox" class="check-toggle"  required="required" ><span></span></label>&nbsp;<span>Я згоден на <a href="<?= BASE_URL ?>/services_list_and_docs/docs/personal_data/" target="_blank" >обробку персональних даних</a></span>
            </div>              
        </div>
        <div class="col-md-12" style="text-align:center;">
            <div style="display:inline-block;" class="g-recaptcha" data-sitekey="6LfvaiwUAAAAAPgNuUtoP-uz8zwissOWD0u8LIBv"></div>
        </div>
    </div>

    <div class="align-center">
        <input type="hidden" name="action" value="request-service">
        <button class="btn btn-blue btn-md" id="pay_button">Відправити</button>
    </div>          
</form>