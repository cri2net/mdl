<h1 class="big-title">Керівництво</h1>
<?php
$_contacts_name = '';
$_contacts_email = '';
$_contacts_text = '';

if (Authorization::isLogin()) {
    $_contacts_name = "{$__userData['lastname']} {$__userData['name']} {$__userData['fathername']}";
    $_contacts_email = $__userData['email'];
}

if (isset($_SESSION['chief']['status']) && $_SESSION['chief']['status']) {
    ?><h2 class="big-success-message">Ваше повідомлення отримано. Дякуємо за звернення</h2> <?php
    unset($_SESSION['chief']);
} elseif (isset($_SESSION['chief']['status'])) {
    ?>
    <h2 class="big-error-message">Під час надсилання повідомлення виникла помилка:</h2>
    <div class="error-description"><?= $_SESSION['chief']['error']['text']; ?></div>
    <?php
    unset($_SESSION['chief']['status']);
}
    
$_contacts_name  = (isset($_SESSION['chief']['name'])  && $_SESSION['chief']['name'])  ? $_SESSION['chief']['name']  : $_contacts_name;
$_contacts_email = (isset($_SESSION['chief']['email']) && $_SESSION['chief']['email']) ? $_SESSION['chief']['email'] : $_contacts_email;
$_contacts_text  = (isset($_SESSION['chief']['text'])  && $_SESSION['chief']['text'])  ? $_SESSION['chief']['text']  : $_contacts_text;

$_contacts_name = htmlspecialchars($_contacts_name, ENT_QUOTES);
$_contacts_text = htmlspecialchars($_contacts_text, ENT_QUOTES);
$_contacts_email = htmlspecialchars($_contacts_email, ENT_QUOTES);


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
                    </div>
                </div>
                <?php
            }
        ?>
    </div>
    <?php
}
