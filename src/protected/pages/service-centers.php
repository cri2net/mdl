<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>
    <script src="https://webcams.ks.ua/js/vendor/uppod.js"></script>
<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');

    $is_terminal = false;
    $is_webcam   = false;
    $companies   = PDO_DB::table_list(TABLE_PREFIX . 'dict_companies', null, 'pos ASC');
    $regions     = PDO_DB::table_list(TABLE_PREFIX . 'dict_regions', null, 'pos ASC');
    $_company    = 0;
    $_region     = 0;

    if (isset($_GET['by']) && ($_GET['by'] == 'filter')) {
        
        $is_terminal = isset($_GET['is_terminal']);
        $is_webcam   = isset($_GET['is_webcam']);
        
        if (!empty($_GET['company'])) {
            $_company = (int)$_GET['company'];
            $current_company = PDO_DB::row_by_id(TABLE_PREFIX . 'dict_companies', $_company);
        }
        if (!empty($_GET['region'])) {
            $_region = (int)$_GET['region'];
            $current_region = PDO_DB::row_by_id(TABLE_PREFIX . 'dict_regions', $_region);
        }
    }

    $where = '1';
    if ($is_terminal) {
        $where .= ' AND is_terminal=1';
    }
    if ($is_webcam) {
        $where .= " AND webcam IS NOT NULL AND webcam <> ''";
    }
    if ($_company > 0) {
        $where .= " AND id_company = '$_company'";
    }
    if ($_region > 0) {
        $where .= " AND id_region = '$_region'";
    }

    $__service_centers_where = $where;
    $list = PDO_DB::table_list(TABLE_PREFIX . 'service_centers', $where, "id_region ASC");

    $list_regions = array();
    foreach ($list as $item) {

        $list_regions[$item['id_region']][$item['id']] = $item;
    }
?>
<div class="container">
    <content>
        <div class="cabinet-settings object-item object-item-bill">
            <form id="service-centers-filter-form" action="<?= BASE_URL; ?>/service-centers/">
                <input type="hidden" name="by" value="filter">                
                <div class="services-map">
                <?php
                    require_once(PROTECTED_DIR . '/scripts/map-form.php');
                ?>   
                </div> 
                <div class="real-full-width-block">           
                    <div class="thead-bg">
                        <div class="head-gray-2"></div>
                    </div>
                    <div class="table-responsive">             
                        <table class="full-width-table datailbill-table service-tableno-border" id="service-table">
                            <thead>
                                <tr class="head-gray-2">
                                    <th class="th-address">Адреса</th>
                                    <th>Графік роботи</th>
                                    <th>Додаткові функції</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3">
                                    </td>
                                </tr>
                            <?php
                                foreach ($list_regions as $rid => $items) {

                                    $region = PDO_DB::row_by_id(TABLE_PREFIX . 'dict_regions', $rid);
                                    ?>
                                    <tr class="region-row">
                                        <td colspan="3">
                                            <a class="region-spoiler" data-rid="<?= $rid; ?>"><?= $region['title']; ?> <span class="fa fa-plus"></span></a>
                                        </td>
                                    </tr>
                                    <?php
                                    foreach ($items as $item) {

                                        $phone = strlen(trim($item['phone'])) < 3 ? '(044) 247-40-40' : trim($item['phone']);
                                        $isWebcam = strlen(trim($item['webcam'])) > 0;
                                        ?>
                                        <tr class="item-row item-row-spoiler item-rid-<?= $rid; ?>">
                                            <td class="left">
                                                <?= $item['address']; ?>
                                                <div class="phone-more">
                                                    <span class="phone"><?= $phone; ?></span>
                                                    Дякуємо за звернення
                                                </div>                                            
                                            </td>
                                            <td class="center">
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal<?= $item['id'] ?>" ><i class="fa fa-calendar"></i></button>
                                                <div class="modal fade" id="myModal<?= $item['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                                  <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                      <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModalLabel">Розклад роботи</h4>
                                                      </div>
                                                      <div class="modal-body" style="text-align: left;"><?= nl2br($item['worktime']) ?></div>
                                                      <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
                                                      </div>
                                                    </div>
                                                  </div>
                                                </div>
                                            </td>
                                            <td class="center" nowrap="nowrap">
                                                <a class="icon icon-phone"></a>
                                                <a class="icon icon-map" id="icon-map-<?= $item['id']; ?>" data-id="<?= $item['id'] ?>"></a>
                                                <?php if($isWebcam){?><a class="icon icon-webcam" id="icon-webcam-<?= $item['id']; ?>"></a><?}?>
                                            </td>
                                        </tr>
                                        <tr class="item-map">
                                            <td colspan="4">
                                                <div class="div-shadow">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="map-service matchHeight" id="map-service-<?= $item['id'] ?>" data-lat="<?= $item['latitude'] ?>" data-lng="<?= $item['longtitude'] ?>" data-zoom="17"></div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info matchHeight">
                                                                <a href="#" class="close">&times;</a>
                                                                <span><?= $item['address'] ?><br>
                                                                Телефон:    <?= $phone ?></span>


                                                                <input class="txt form-txt-input maps-route-start" placeholder="Початок маршруту" type="text" name="address" onkeypress="$('#google-maps-route-start').val($(this).val()); $('#google-maps-route-end').val('<?= htmlspecialchars($item['address'], ENT_QUOTES); ?>');" />
                                                                <a onclick="$('#google-maps-route-end').val('<?= htmlspecialchars($item['address'], ENT_QUOTES); ?>'); calculateAndDisplayRoute();" class="icon-href icon-trace">Прокласти маршрут до відділення</a>
                                                                <a onclick="$('#icon-webcam-<?= $item['id']; ?>').trigger('click');" class="icon-href icon-web">Переглянути веб камеру у відділенні</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="item-web">
                                            <td colspan="4">
                                                <div class="div-shadow">
                                                    <div class="row">
                                                        <div class="col-md-8 matchHeight"><div id="cks<?= $item['id'] ?>" ></div></div>
                                                        <div class="col-md-4">
                                                            <div class="info matchHeight">
                                                                <a href="#" class="close">&times;</a>
                                                                <span><?= $item['address'] ?><br>
                                                                Телефон: <?= $phone ?></span>

                                                                <a class="icon-href icon-feedback"><span class="short">Залишити відгук</span></a>
                                                                <a onclick="$('#icon-map-<?= $item['id']; ?>').trigger('click');" class="icon-href icon-marker"><span class="short">Перейти до мапи</span></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?
                                    }
                                }
                            ?>   
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </content>
</div>
<div class="modal fade" id="modalCounterConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Видалити лічильник?</h3>
      </div>
      <div class="modal-body">
        <p>видалений лічильник неможливо відновити</p>
      </div>
      <div class="modal-footer">
        <div class="row">
            <div class="col-sm-6">
                <button type="button" class="btn btn-green btn-full btn-md" data-dismiss="modal">Назад</button>
            </div>
            <div class="col-sm-6">
                <button type="button" id="counter-delete-confirm" data-id="" class="btn btn-green-lighter btn-full btn-md">Видалити</button>
            </div>
        </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
<?php
    foreach ($list as $key => $sc) {
        if (strlen(trim($sc['webcam'])) > 3) {
            ?>
            new Uppod({m:"video",uid:"cks<?= $sc['id']; ?>",file:"<?= addslashes($sc['webcam']) ?>",onReady: function(uppod){uppod.Play();}});
            <?php
        }
    }
?>
</script>
